<?php

namespace App\Controllers;

use Config\Database;
use App\Models\UserModel;
use App\Models\LabTestModel;
use App\Models\PatientModel;
use App\Models\PatientTestBookingModel;
use CodeIgniter\HTTP\RedirectResponse;
use App\Services\WhatsAppService;
use App\Services\WhatsAppMessages;
use App\Models\ShareTokenModel;
use App\Models\CityModel;
use App\Models\FranchiseModel;

class BookingController extends BaseController
{
    private function getLabId(): ?int
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id') ?? session()->get('id');

        $lab = $db->table('labs')
            ->where('user_id', $userId)
            ->get()->getRowArray();

        return $lab ? (int)$lab['id'] : null;
    }
    public function index()
    {
        $testModel = new LabTestModel();
        $franchises =new FranchiseModel();
        $data = [
            'tests'   => $testModel->getTestsBySessionLab(),
            'genders' => ['Male', 'Female', 'Other'],
            'franchises' => $franchises ->getFranchisesBySessionLab()
        ];
        return view('Booking/bookingform', $data);
    }

public function add_booking()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }
    $rules = [
        'booking_person_name' => 'required|regex_match[/^[A-Za-z\s]+$/]',
        'patient_name' => 'required|regex_match[/^[A-Za-z\s]+$/]',
        'phone_number' => 'required|max_length[20]',
        'franchise'           => 'required|integer',
        'home_address' => 'required',
        'age'          => 'permit_empty|numeric',
        'gender'       => 'permit_empty|in_list[Male,Female,Other]',
        'pin_address' => 'permit_empty|string',
        'tests'        => 'required',
    ];
    if (! $this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $tests = $this->request->getPost('tests') ?? [];
    log_message('debug', 'Raw tests payload: ' . json_encode($tests));

    $cleanRows = [];

    $bookingPersonName = $this->request->getPost('booking_person_name');
    $franchiseId        = (int) $this->request->getPost('franchise');
    foreach ($tests as $t) {
        $testId   = (int) ($t['test_id'] ?? 0);
        $discount = (int) ($t['discount'] ?? 0);
        $payment  = $t['payment'] ?? 'cash';

        if ($testId <= 0) {
            continue;
        }
        if ($discount < 0 || $discount > 100) {
            $discount = 0;
        }
        if (! in_array($payment, ['cash', 'online', 'card'], true)) {
          $payment = 'cash';
        }

        $cleanRows[] = [
            'fk_test_id'       => $testId,
            'discount_percent' => $discount,
            'payment_method'   => $payment,
            'booking_person_name' => $bookingPersonName,
            'fk_franchise_id'    => $franchiseId,
           // 'city_id'          => (int) $this->request->getPost('city_id'),
        ];
    }

    if (empty($cleanRows)) {
        log_message('debug', 'cleanRows ended up empty — every test row was missing a valid test_id.');
        return redirect()->back()->withInput()->with('error', 'Please add at least one valid test.');
    }

    $patientModel = new PatientModel();

    $patientId = $patientModel->insert([
        'patient_name'   => $this->request->getPost('patient_name'),
        'phone_number'   => $this->request->getPost('phone_number'),
        'age'            => $this->request->getPost('age') ?: null,
        'gender'         => $this->request->getPost('gender'),
        'home_address'   => $this->request->getPost('home_address'),
        'pin_location'   => $this->request->getPost('pin_address'),
        'instructions'   => $this->request->getPost('instructions'),
        'medical_history'   => $this->request->getPost('medical_history'),
        
    ], true);

    if (! $patientId) {
        $modelErrors = $patientModel->errors();
        return redirect()->back()->withInput()->with(
            'errors',
            $modelErrors ?: ['patient' => 'Could not save patient details.']
        );
    }

    $now  = date('Y-m-d H:i:s');
    // $etaDate = $this->request->getPost('eta_date');
// $etaTime = $this->request->getPost('eta_time');
// $preferredEta = ($etaDate && $etaTime) ? $etaDate . ' ' . $etaTime . ':00' : null;


    $rows = array_map(static function (array $row) use ($patientId, $now) {
        return [
            'fk_patient_id'    => $patientId,
            'fk_test_id'       => $row['fk_test_id'],
            'booking_person_name' => $row['booking_person_name'],
            'fk_franchise_id'    => $row['fk_franchise_id'],
            'status'           => 'In Process',
            'discount_percent' => $row['discount_percent'],
            'payment_method'   => $row['payment_method'],
            'payment_status'   => 'unpaid',
            'date_created'     => $now,
            'date_updated'     => $now,
            
        ];
    }, $cleanRows);

    $bookingModel = new PatientTestBookingModel();
    $inserted = $bookingModel->insertBatch($rows);

    if ($inserted === false) {
        log_message('error', 'lab_bookings insertBatch failed: ' . json_encode($bookingModel->errors()));
        log_message('error', 'Last DB error: ' . json_encode(\Config\Database::connect()->error()));
        return redirect()->back()->withInput()->with('error', 'Could not save the test bookings.');
    }
     try {
            $whatsapp = new WhatsAppService();
            $message  = WhatsAppMessages::forStatus(
                'booking_created',
                $this->request->getPost('patient_name')
            );
            $whatsapp->sendText($this->request->getPost('phone_number'), $message);
        } catch (\Exception $e) {
            log_message('error', '[WhatsApp] booking_created failed: ' . $e->getMessage());
        }
        $this->flashWhatsApp(
            $this->request->getPost('phone_number'),
            WhatsAppMessages::forStatus('booking_created', $this->request->getPost('patient_name'))
        );

    // FIX: Get the first inserted ID by querying the database
    $db = \Config\Database::connect();
    $firstBooking = $db->table('patient_test_bookings')
        ->where('fk_patient_id', $patientId)
        ->orderBy('id', 'ASC')
        ->get()
        ->getRowArray();

    if ($firstBooking) {
        $this->logStatusHistory(
            $firstBooking['id'],
            $patientId,
            'In Process',
            'Booking created'
        );
    }

    return redirect()->to(site_url('labDashboard/dashboard'))
        ->with('success', count($rows) . ' test(s) booked successfully.');
}
   public function dashboard()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $labId = $this->getLabId();

    $model = new \App\Models\PatientTestBookingModel();
    $franchiseModel = new \App\Models\FranchiseModel();
    $bookings = [];
    $counts = ['total' => 0, 'in_process' => 0, 'assigned' => 0, 'arrived' => 0, 'collected' => 0, 'report_ready' => 0];

    $filters = [
        'status'       => $this->request->getGet('status'),
        'search'       => $this->request->getGet('search'),
        'date_from'    => $this->request->getGet('date_from'),
        'date_to'      => $this->request->getGet('date_to'),
        'lab_id'       => $labId,
        'franchise_id' => $this->request->getGet('franchise_id'),
    ];

   $franchises = [];
try {
    $db = \Config\Database::connect();
    if ($db->tableExists('franchises')) {
        $franchises = $db->table('franchises f')
            ->select('f.id, f.contact_number, u.name as name')
            ->join('users u', 'u.id = f.user_id', 'left')
            ->where('f.lab_id', $labId)
            ->where('f.is_deleted', 0)
            ->get()
            ->getResultArray();
    }
} catch (\Exception $e) {
    log_message('error', 'Franchise fetch error: ' . $e->getMessage());
}

    try {
        $db = \Config\Database::connect();
        if ($db->tableExists('patient_test_bookings')) {
            $bookings = $model->getFilteredBookings($filters);
            $counts   = $model->getStatusCounts($labId, $filters['franchise_id']);
            $model->attachTestDetails($bookings);
        }
    } catch (\Exception $e) {
        log_message('error', 'Dashboard error: ' . $e->getMessage());
    }

    return view('labDashboard/dashboard', compact('bookings', 'counts', 'filters', 'franchises'));
}

   public function viewBooking($patientId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $db = \Config\Database::connect();
    $labId = $this->getLabId();

    // Get patient
    $patient = $db->table('patients')->where('id', $patientId)->get()->getRowArray();
    if (!$patient) {
        return redirect()->to('/labDashboard/dashboard')->with('error', 'Patient not found.');
    }

    // Get all bookings for this patient filtered by lab
    $bookingRows = $db->table('patient_test_bookings ptb')
        ->select('ptb.*, lt.test_code, lt.test_name, lt.rate, lt.reporting_time, ph.name as phleb_name')
        ->join('lab_tests lt', 'lt.id = ptb.fk_test_id', 'left')
        ->join('phlebotomists ph', 'ph.id = ptb.phleb_id', 'left')
        ->where('ptb.fk_patient_id', $patientId)
        ->where('lt.lab_id', $labId)
        ->orderBy('ptb.date_created', 'ASC')
        ->get()->getResultArray();

    if (empty($bookingRows)) {
        return redirect()->to('/labDashboard/dashboard')->with('error', 'No bookings found.');
    }

    // Use the latest booking row for status/eta/meta
    $latestBooking = end($bookingRows);
    $currentStatus = $latestBooking['status'];
    $bookingId = $latestBooking['id'];

    // Get reports for each test
    $reportModel = new \App\Models\LabReportModel();
    $reports = $reportModel->where('fk_patient_id', $patientId)->findAll();
    $reportMap = [];
    foreach ($reports as $report) {
        $reportMap[$report['fk_test_id']] = $report;
    }

    // Status steps
    $statusSteps = ['In Process', 'Phlebotomist Assigned', 'Arrived', 'Sample Collected', 'Report Ready'];
    $currentStepIdx = array_search($currentStatus, $statusSteps);
    if ($currentStepIdx === false) {
        $currentStepIdx = $currentStatus === 'Patient Refused' ? 2 : 0;
    }
    if ($currentStepIdx === false) $currentStepIdx = 0;

    // Build tests ordered with financials and report status
    $originalTotal = 0;
    $discountTotal = 0;
    $patientPays = 0;
    $testsOrdered = [];

    foreach ($bookingRows as $row) {
        $rate = (float)($row['rate'] ?? 0);
        $discPct = (float)($row['discount_percent'] ?? 0);
        $discAmt = round($rate * $discPct / 100);
        $patientPrice = $rate - $discAmt;

        $originalTotal += $rate;
        $discountTotal += $discAmt;
        $patientPays += $patientPrice;

        // Check if this test has a report
        $hasReport = isset($reportMap[$row['fk_test_id']]);
        $reportFile = $hasReport ? $reportMap[$row['fk_test_id']]['report_file'] : null;

        $testsOrdered[] = [
            'booking' => $row,
            'test' => [
                'test_code' => $row['test_code'] ?? '—',
                'test_name' => $row['test_name'] ?? 'Unknown Test',
                'reporting_time' => $row['reporting_time'] ?? '—',
            ],
            'patient_price' => $patientPrice,
            'discount_amt' => $discAmt,
            'has_report' => $hasReport,
            'report_file' => $reportFile,
        ];
    }

    // Status history
    $historyModel = new \App\Models\BookingStatusHistoryModel();
    $statusHistory = $historyModel->getHistoryForPatient($patientId);

    // Phlebotomists for this lab only
    // $phlebotomists = $labId
    //     ? $db->table('phlebotomists')
    //         ->where('lab_id', $labId)
    //         ->where('status', 'active')
    //         ->orderBy('name', 'ASC')
    //         ->get()->getResultArray()
    //     : [];
    $booking = $db->table('patient_test_bookings')
    ->where('id', $bookingId)
    ->get()
    ->getRowArray();

$franchiseId = $booking['fk_franchise_id'] ?? null;

$phlebotomists = $franchiseId
    ? $db->table('phlebotomists ph')
        ->select('ph.id, ph.name')
        ->join('franchises f', 'f.id = ph.franchise_id', 'left')
        ->where('ph.franchise_id', $franchiseId)
        ->where('f.lab_id', $labId)   // extra safety: ensures franchise belongs to this lab
        ->where('ph.status', 'active')
        ->orderBy('ph.name', 'ASC')
        ->get()
        ->getResultArray()
    : [];
    $data = compact(
        'patient',
        'latestBooking',
        'currentStatus',
        'statusSteps',
        'currentStepIdx',
        'testsOrdered',
        'originalTotal',
        'discountTotal',
        'patientPays',
        'statusHistory',
        'bookingId',
        'phlebotomists',
        'franchiseId'
    );

    return view('labDashboard/booking_details', $data);
}
public function downloadReport($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Please login to download reports');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    $db = \Config\Database::connect();
    $reportModel = new \App\Models\LabReportModel();

    // Get the booking row (single test row — has fk_patient_id + fk_test_id)
    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->get()
        ->getRowArray();

    if (!$booking) {
        return redirect()->back()->with('error', 'Booking not found');
    }

    // IMPORTANT: reports are saved keyed by patient + test (see uploadReport()),
    // not by fk_booking_id == this row's id. Look it up that way instead.
    $report = $reportModel
        ->where('fk_patient_id', $booking['fk_patient_id'])
        ->where('fk_test_id', $booking['fk_test_id'])
        ->orderBy('uploaded_at', 'DESC')
        ->first();

    if (!$report || empty($report['report_file'])) {
        return redirect()->back()->with('error', 'No report found for this booking');
    }

    $filePath = WRITEPATH . $report['report_file'];

    if (!file_exists($filePath)) {
        return redirect()->back()->with('error', 'Report file not found');
    }

    $patient = $db->table('patients')->where('id', $booking['fk_patient_id'])->get()->getRowArray();
    $namePart = $patient ? preg_replace('/[^A-Za-z0-9_-]+/', '_', $patient['patient_name']) : 'report';
    $downloadName = $namePart . '_' . $bookingId . '.pdf';

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'attachment; filename="' . $downloadName . '"')
        ->setHeader('Content-Length', (string) filesize($filePath))
        ->setBody(file_get_contents($filePath));
}
public function assignPhlebotomist($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    $phlebId           = (int) $this->request->getPost('phleb_id');
    $eta                = $this->request->getPost('eta') ?: null;
    $preferredEta       = $this->request->getPost('preferred_eta') ?: null;
    $reportingTime  = $this->request->getPost('show_reporting_time') === '0' ? 0 : 1; // default to show

    if (!$phlebId) {
        return redirect()->back()->with('error', 'Please select a phlebotomist.');
    }

    $db = \Config\Database::connect();

    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->get()->getRowArray();

    if (!$booking) {
        return redirect()->back()->with('error', 'Booking not found.');
    }

    $db->table('patient_test_bookings')
        ->where('fk_patient_id', $booking['fk_patient_id'])
        ->update([
            'phleb_id'            => $phlebId,
            'eta'                 => $eta,
            'preferred_eta'       => $preferredEta,
            'reporting_time' => $reportingTime,
            'status'              => 'Phlebotomist Assigned',
            'date_updated'        => date('Y-m-d H:i:s'),
        ]);

    $this->logStatusHistory(
        $booking['id'],
        $booking['fk_patient_id'],
        'Phlebotomist Assigned',
        'Phlebotomist assigned'
    );

    $patient = $db->table('patients')
        ->where('id', $booking['fk_patient_id'])
        ->get()->getRowArray();

    $phleb = $db->table('phlebotomists')
        ->where('id', $phlebId)
        ->get()->getRowArray();

   // --- Notify patient ---
    try {
        if ($patient && !empty($patient['phone_number'])) {

            $token = bin2hex(random_bytes(6)); // 12-char random string

            // Check if a row already exists for this booking
            $existing = $db->table('phlebotomist_locations')
                ->where('booking_id', $booking['id'])
                ->get()->getRowArray();

            if ($existing) {
                $db->table('phlebotomist_locations')
                    ->where('booking_id', $booking['id'])
                    ->update(['token' => $token]);
            } else {
                $db->table('phlebotomist_locations')->insert([
                    'booking_id' => $booking['id'],
                    'token'      => $token,
                    'lat'        => 0,
                    'lng'        => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $trackingUrl = base_url('t/' . $token);

            $whatsapp = new WhatsAppService();
            $message  = WhatsAppMessages::forStatus(
                'Phlebotomist Assigned',
                $patient['patient_name'],
                [
                    'phleb_name'   => $phleb['name'] ?? '',
                    'eta'          => $eta,
                ]
            );
            $whatsapp->sendText($patient['phone_number'], $message);
            $this->flashWhatsApp($patient['phone_number'], $message, 'wa');
        }
    } catch (\Exception $e) {
        log_message('error', '[WhatsApp] phleb_assigned failed: ' . $e->getMessage());
    }
    // --- Notify franchise ---
    try {
        if (!empty($booking['fk_franchise_id'])) {
            $franchise = $db->table('franchises f')
                ->select('f.contact_number, u.name as franchise_name')
                ->join('users u', 'u.id = f.user_id', 'left')
                ->where('f.id', $booking['fk_franchise_id'])
                ->get()->getRowArray();

        if ($franchise && !empty($franchise['contact_number'])) {
            $franchiseMessage = WhatsAppMessages::forFranchiseAssignment(
                $franchise['franchise_name'] ?? 'Franchise',
                $patient['patient_name'] ?? '',
                $phleb['name'] ?? '',
                $eta,
                $patient['home_address'] ?? ''
            );

            $whatsappFranchise = new WhatsAppService();
            $sent = $whatsappFranchise->sendText($franchise['contact_number'], $franchiseMessage);

            if ($sent) {
                log_message('info', '[WhatsApp] Franchise notified: ' . $franchise['contact_number']);
            } else {
                log_message('warning', '[WhatsApp] Franchise API send failed, flashing popup fallback');
            }

            // Fallback popup — normalized number, separate key
            $this->flashWhatsApp($franchise['contact_number'], $franchiseMessage, 'wa_franchise');
        } else {
            log_message('warning', '[WhatsApp] Franchise ' . $booking['fk_franchise_id'] . ' has no contact_number set.');
        }
        }
    } catch (\Exception $e) {
        log_message('error', '[WhatsApp] franchise notify failed: ' . $e->getMessage());
    }

    return redirect()->to('/booking/view/' . $booking['fk_patient_id'])
        ->with('success', 'Phlebotomist assigned successfully!');
}

    // Already existing — add 'refused' to the match:
    public function updateStatus(int $bookingId, string $status): RedirectResponse
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login to perform this action');
        }

        $allowed = ['arrived', 'collected', 'refused'];
        if (!in_array($status, $allowed)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $statusMap = [
            'arrived' => [
                'status'  => 'Arrived',
                'message' => 'Phlebotomist marked as arrived successfully!'
            ],
            'collected' => [
                'status'  => 'Sample Collected',
                'message' => 'Sample marked as collected successfully!'
            ],
            'refused' => [
                'status'  => 'Refused',
                'message' => 'Patient marked as refused successfully!'
            ],
        ];

        $db = \Config\Database::connect();

        $booking = $db->table('patient_test_bookings')
            ->where('id', $bookingId)
            ->get()->getRowArray();

        if (!$booking) {
            return redirect()->back()->with('error', 'Booking not found.');
        }

        $newStatus = $statusMap[$status]['status'];

        $db->table('patient_test_bookings')
            ->where('fk_patient_id', $booking['fk_patient_id'])
            ->update([
                'status'       => $newStatus,
                'date_updated' => date('Y-m-d H:i:s'),
            ]);

        $this->logStatusHistory(
            $booking['id'],
            $booking['fk_patient_id'],
            $newStatus,
            $status === 'refused' ? 'Patient refused' : ''
        );

        // NEW: WhatsApp for Arrived / Sample Collected
        if (in_array($newStatus, ['Arrived', 'Sample Collected'])) {
            $patient = $db->table('patients')->where('id', $booking['fk_patient_id'])->get()->getRowArray();
            if ($patient && !empty($patient['phone_number'])) {
                $this->flashWhatsApp(
                    $patient['phone_number'],
                    WhatsAppMessages::forStatus($newStatus, $patient['patient_name'])
                );
            }
        }

        return redirect()->to('/booking/view/' . $booking['fk_patient_id'])
            ->with('success', $statusMap[$status]['message']);
    }

    // Fix requestRevisit:
    public function requestRevisit(int $bookingId): RedirectResponse
    {
        $revisitDatetime = $this->request->getPost('revisit_datetime');
        $phlebId         = $this->request->getPost('phleb_id')      ?: null;
        $notes           = $this->request->getPost('revisit_notes') ?: null;

        $db = \Config\Database::connect();

        $booking = $db->table('patient_test_bookings')
            ->where('id', $bookingId)
            ->get()->getRowArray();

        if (!$booking) {
            return redirect()->back()->with('error', 'Booking not found.');
        }

        $patientId = $booking['fk_patient_id'];

        // Convert datetime-local format ("2026-07-05T21:00") to MySQL format ("2026-07-05 21:00:00")
        $etaFormatted = null;
        if ($revisitDatetime) {
            $etaFormatted = date('Y-m-d H:i:s', strtotime($revisitDatetime));
        }

        // Update ALL rows for this patient
        $updateData = [
            'status'       => 'Phlebotomist Assigned',
            'date_updated' => date('Y-m-d H:i:s'),
        ];
        if ($etaFormatted) $updateData['eta']      = $etaFormatted;
        if ($phlebId)      $updateData['phleb_id'] = $phlebId;

        $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->update($updateData);

        // Log ONE clear history entry per row, with reason and ETA on separate lines
        $allRows = $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->get()->getResultArray();

        $historyNote = 'Re-visit scheduled';
        if (!empty($notes)) {
            $historyNote .= "\nReason: " . $notes;
        }
        if (!empty($etaFormatted)) {
            $historyNote .= "\nNew ETA: " . date('d-M-y, g:i A', strtotime($etaFormatted));
        }

        foreach ($allRows as $b) {
            $this->logStatusHistory(
                $b['id'],
                $patientId,
                'Phlebotomist Assigned',
                $historyNote
            );
        }

        return redirect()->to('/booking/view/' . $patientId)
            ->with('success', 'Re-visit scheduled successfully.');
    }


   public function uploadReport($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Please login to perform this action');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    // Validate input
    $validation = \Config\Services::validation();
    $validation->setRules([
        'test_ids' => 'required',
        'report_file' => 'uploaded[report_file]|max_size[report_file,10240]|ext_in[report_file,pdf]',
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        return redirect()->back()
            ->withInput()
            ->with('errors', $validation->getErrors());
    }

    $testIds = $this->request->getPost('test_ids');
    $file = $this->request->getFile('report_file');

    if (!$file->isValid()) {
        return redirect()->back()->with('error', 'Please upload a valid PDF file.');
    }

    $db = \Config\Database::connect();
    
    // Get the booking
    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->get()
        ->getRowArray();

    if (!$booking) {
        return redirect()->back()->with('error', 'Booking not found');
    }

    if (empty($booking['payment_proof_file'])) {
        return redirect()->back()->with('error', 'Please upload proof of payment before uploading the lab report.');
    }

    $patientId = $booking['fk_patient_id'];

    // Generate unique filename
    $newName = 'report_' . $bookingId . '_' . time() . '_' . uniqid() . '.' . $file->getExtension();
    $uploadPath = WRITEPATH . 'uploads/reports/';

    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    if (!$file->move($uploadPath, $newName)) {
        return redirect()->back()->with('error', 'Failed to upload file.');
    }

    $filePath = 'uploads/reports/' . $newName;
    $reportModel = new \App\Models\LabReportModel();

    // Insert reports for selected tests
    $inserted = 0;
    foreach ($testIds as $testId) {
        // Check if report already exists for this test
        $existing = $reportModel->where('fk_booking_id', $bookingId)
            ->where('fk_test_id', $testId)
            ->first();

        $data = [
            'fk_booking_id' => $bookingId,
            'fk_test_id' => $testId,
            'fk_patient_id' => $patientId,
            'report_file' => $filePath,
            'report_status' => 'uploaded',
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $reportModel->update($existing['id'], $data);
        } else {
            $reportModel->insert($data);
        }
        $inserted++;
    }

    // Check if ALL tests for this patient now have reports
    // Get ALL tests for this patient (not just this booking)
    $allTestBookings = $db->table('patient_test_bookings')
        ->where('fk_patient_id', $patientId)
        ->get()
        ->getResultArray();

    $totalTests = count($allTestBookings);
    
    // Get all test IDs for this patient
    $testIdsForPatient = array_column($allTestBookings, 'fk_test_id');
    
    // Count how many of these tests have reports uploaded
    $reportedCount = 0;
    if (!empty($testIdsForPatient)) {
        $reportedCount = $db->table('lab_reports')
            ->where('fk_patient_id', $patientId)
            ->whereIn('fk_test_id', $testIdsForPatient)
            ->where('report_status', 'uploaded')
            ->countAllResults();
    }

    // Check if ALL tests have reports
    $allReported = ($reportedCount >= $totalTests && $totalTests > 0);

    // Log the status
    log_message('debug', 'Patient ' . $patientId . ': ' . $reportedCount . '/' . $totalTests . ' tests reported');

    // If all tests are reported, update status to "Report Ready"
    if ($allReported) {
        // Update ALL bookings for this patient to "Report Ready"
        $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->update([
                'status' => 'Report Ready',
                'date_updated' => date('Y-m-d H:i:s')
            ]);
        
        // Log status change
        $this->logStatusHistory(
            $bookingId,
            $patientId,
            'Report Ready',
            'All reports uploaded (' . $totalTests . ' tests)'
        );

        try {
                    $patient = $db->table('patients')
                        ->where('id', $patientId)
                        ->get()->getRowArray();

                    if ($patient && !empty($patient['phone_number'])) {
                        $whatsapp = new WhatsAppService();
                        $message  = WhatsAppMessages::forStatus(
                            'Report Ready',
                            $patient['patient_name']
                        );
                        $whatsapp->sendText($patient['phone_number'], $message);
                        $this->flashWhatsApp($patient['phone_number'], $message);
                    }
                } catch (\Exception $e) {
                    log_message('error', '[WhatsApp] report_ready failed: ' . $e->getMessage());
                }

        
        return redirect()->to('/booking/view/' . $patientId)
            ->with('success', 'All ' . $totalTests . ' reports uploaded! Booking marked as Report Ready.');
    }

    // Not all reports uploaded yet
    return redirect()->to('/booking/view/' . $patientId)
        ->with('info', $inserted . ' report(s) uploaded. ' . ($totalTests - $reportedCount) . ' more report(s) pending.');
}
    public function markPaymentPaid($bookingId = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login to perform this action');
        }

        if (!$bookingId) {
            return redirect()->back()->with('error', 'Invalid booking ID');
        }

        $db = \Config\Database::connect();

        // Get the booking to find patient_id
        $booking = $db->table('patient_test_bookings')
            ->where('id', $bookingId)
            ->get()
            ->getRowArray();

        if (!$booking) {
            return redirect()->back()->with('error', 'Booking not found');
        }

        if (empty($booking['payment_proof_file'])) {
            return redirect()->back()->with('error', 'Please upload proof of payment before marking as paid.');
        }

        $patientId = $booking['fk_patient_id'];

        // Update ALL unpaid test rows for this patient
        $updated = $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->where('payment_status', 'unpaid')
            ->update([
                'payment_status' => 'paid',
                'payment_date'   => date('Y-m-d H:i:s'),
                'date_updated'   => date('Y-m-d H:i:s'),
            ]);

        if (!$updated) {
            return redirect()->back()->with('error', 'Failed to update payment status');
        }

        $this->logStatusHistory(
            $bookingId,
            $patientId,
            $booking['status'],
            'Payment marked as paid (cash collected)'
        );

        return redirect()->to('/booking/view/' . $patientId)
            ->with('success', 'Payment marked as paid successfully!');
    }

    public function uploadPaymentProof($bookingId = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login to perform this action');
        }

        if (!$bookingId) {
            return redirect()->back()->with('error', 'Invalid booking ID');
        }

        $userId = session()->get('user_id') ?? session()->get('id');
        $labId  = $this->getLabId();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'payment_method'      => 'required|in_list[cash,online,card]',
            'payment_received_by' => 'required|in_list[main_branch,franchise]',
            'proof_file'          => 'uploaded[proof_file]|max_size[proof_file,10240]|ext_in[proof_file,pdf,jpg,jpeg,png]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->with('errors', $validation->getErrors());
        }

        $db = \Config\Database::connect();

        $booking = $db->table('patient_test_bookings ptb')
            ->select('ptb.*, lt.lab_id')
            ->join('lab_tests lt', 'lt.id = ptb.fk_test_id', 'left')
            ->where('ptb.id', $bookingId)
            ->get()->getRowArray();

        if (!$booking) {
            return redirect()->back()->with('error', 'Booking not found.');
        }

        // Safety: this booking's test must belong to the logged-in lab
        if ($labId && (int) $booking['lab_id'] !== (int) $labId) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        if (!in_array($booking['status'], ['Sample Collected', 'Report Ready'])) {
            return redirect()->back()->with('error', 'Payment proof can only be uploaded after sample collection.');
        }

        $patientId = $booking['fk_patient_id'];

        $file = $this->request->getFile('proof_file');
        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Please upload a valid file.');
        }

        $newName    = 'proof_' . $patientId . '_' . time() . '_' . uniqid() . '.' . $file->getExtension();
        $uploadPath = WRITEPATH . 'uploads/payment_proofs/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if (!$file->move($uploadPath, $newName)) {
            return redirect()->back()->with('error', 'Failed to upload file.');
        }

        $filePath = 'uploads/payment_proofs/' . $newName;

        // Update ALL rows for this patient — same pattern as markPaymentPaid()
        $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->update([
                'payment_proof_file'                => $filePath,
                'payment_method'                    => $this->request->getPost('payment_method'),
                'payment_received_by'               => $this->request->getPost('payment_received_by'),
                'payment_proof_uploaded_by_user_id' => $userId,
                'payment_proof_uploaded_at'         => date('Y-m-d H:i:s'),
                'date_updated'                      => date('Y-m-d H:i:s'),
            ]);

        $this->logStatusHistory(
            $bookingId,
            $patientId,
            $booking['status'],
            'Proof of payment uploaded (received by ' . str_replace('_', ' ', $this->request->getPost('payment_received_by')) . ')'
        );

        return redirect()->to('/booking/view/' . $patientId)
            ->with('success', 'Proof of payment uploaded successfully.');
    }

    public function viewPaymentProof($bookingId = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login to perform this action');
        }

        if (!$bookingId) {
            return redirect()->back()->with('error', 'Invalid booking ID');
        }

        $db = \Config\Database::connect();

        $booking = $db->table('patient_test_bookings')
            ->where('id', $bookingId)
            ->get()->getRowArray();

        if (!$booking || empty($booking['payment_proof_file'])) {
            return redirect()->back()->with('error', 'Proof of payment not found.');
        }

        $filePath = WRITEPATH . $booking['payment_proof_file'];

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Proof file not found on server.');
        }

        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = $ext === 'pdf' ? 'application/pdf' : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setBody(file_get_contents($filePath));
    }

    private function logStatusHistory(int $bookingId, int $patientId, string $status, string $notes = '', string $changedBy = ''): void
    {
        // Silently skip if booking_status_history table doesn't exist yet
        $db = \Config\Database::connect();
        if (!$db->tableExists('booking_status_history')) {
            return;
        }

        $db->table('booking_status_history')->insert([
            'booking_id' => $bookingId,
            'patient_id' => $patientId,
            'status'     => $status,
            'changed_by' => $changedBy ?: (session()->get('username') ?? 'System'),
            'notes'      => $notes,
            'changed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function flashWhatsApp(string $phone, string $message, string $keyPrefix = 'wa'): void
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        if (!str_starts_with($digits, '92')) {
            $digits = '92' . $digits;
        }

        session()->setFlashdata($keyPrefix . '_phone', $digits);
        session()->setFlashdata($keyPrefix . '_message', $message);
    }

    public function saveNotes($patientId = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (!$patientId) {
            return redirect()->back()->with('error', 'Invalid patient ID');
        }

        $db = \Config\Database::connect();
        $db->table('patients')
            ->where('id', $patientId)
            ->update([
                'medical_history' => $this->request->getPost('medical_history'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to('/booking/view/' . $patientId)
            ->with('success', 'Notes saved successfully.');
    }
    // Add to BookingController

    public function editTests($patientId = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $patient = $db->table('patients')->where('id', $patientId)->get()->getRowArray();
        if (!$patient) {
            return redirect()->back()->with('error', 'Patient not found.');
        }

        // Current bookings for this patient
        $currentBookings = $db->table('patient_test_bookings ptb')
            ->select('ptb.id, ptb.fk_test_id, ptb.discount_percent, ptb.payment_method, ptb.fk_franchise_id, lt.test_name, lt.test_code, lt.rate')
            ->join('lab_tests lt', 'lt.id = ptb.fk_test_id', 'left')
            ->where('ptb.fk_patient_id', $patientId)
            ->orderBy('ptb.date_created', 'ASC')
            ->get()->getResultArray();

        // All available tests
        $allTests = $db->table('lab_tests')
            ->orderBy('test_name', 'ASC')
            ->get()->getResultArray();
        $franchiseId = null;
            foreach ($currentBookings as $b) {
                if (!empty($b['fk_franchise_id'])) {
                    $franchiseId = $b['fk_franchise_id'];
                    break;
                }
            }

        $maxAllowedDiscount = 100; // fallback: no cap if we can't determine franchise
        if ($franchiseId) {
            $franchise = $db->table('franchises')
                ->where('id', $franchiseId)
                ->get()->getRowArray();
            if ($franchise && isset($franchise['discount'])) {
                $maxAllowedDiscount = (float) $franchise['discount'];
            }
        }
        return view('labDashboard/edit_tests', [
            'patient'         => $patient,
            'currentBookings' => $currentBookings,
            'allTests'        => $allTests,
             'maxAllowedDiscount' => $maxAllowedDiscount,
        ]);
    }

    public function updateTests($patientId = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db           = \Config\Database::connect();
        $bookingModel = new PatientTestBookingModel();
        $now          = date('Y-m-d H:i:s');

        // IDs to delete (removed tests)
        $deleteIds = $this->request->getPost('delete_ids') ?? [];
        if (!empty($deleteIds)) {
            $bookingModel->whereIn('id', $deleteIds)
                ->where('fk_patient_id', $patientId)  // safety check
                ->delete();
        }
        $newTests       = $this->request->getPost('new_tests') ?? [];
    $existingUpdates = $this->request->getPost('existing') ?? [];
        $existingBooking = $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->orderBy('date_created', 'ASC')
            ->get()->getRowArray();

    $franchiseId       = $existingBooking['fk_franchise_id'] ?? null;
     $maxAllowedDiscount = 100;
    if ($franchiseId) {
        $franchise = $db->table('franchises')->where('id', $franchiseId)->get()->getRowArray();
        if ($franchise && isset($franchise['discount'])) {
            $maxAllowedDiscount = (float) $franchise['discount'];
        }
    }

    // NEW: sum discount % across all surviving rows (existing not being deleted + new)
    $totalDiscount = 0;
    foreach ($existingUpdates as $rowId => $vals) {
        if (in_array($rowId, $deleteIds)) continue;
        $totalDiscount += (int) ($vals['discount'] ?? 0);
    }
    foreach ($newTests as $t) {
        $totalDiscount += (int) ($t['discount'] ?? 0);
    }

    if ($totalDiscount > $maxAllowedDiscount + 0.01) {
        return redirect()->back()->with('error',
            "Total discount ({$totalDiscount}%) exceeds this franchise's limit of {$maxAllowedDiscount}%. Please adjust before saving.");
    }
    $bookingPersonName = $existingBooking['booking_person_name'] ?? null;
        // New tests to add
        $newTests = $this->request->getPost('new_tests') ?? [];
        $insertRows = [];

        foreach ($newTests as $t) {
            $testId   = (int)($t['test_id'] ?? 0);
            $discount = (int)($t['discount'] ?? 0);
            $payment  = $t['payment'] ?? 'cash';

            if ($testId <= 0) continue;
            if ($discount < 0 || $discount > 100) $discount = 0;
            if (!in_array($payment, ['cash', 'card', 'online'], true)) $payment = 'cash';

            $insertRows[] = [
                'fk_patient_id'    => $patientId,
                'fk_test_id'       => $testId,
                'fk_franchise_id'     => $franchiseId,       
                'booking_person_name' => $bookingPersonName,
                'status'           => 'In Process',
                'discount_percent' => $discount,
                'payment_method'      => $payment,
                'payment_status'   => 'unpaid',
                'date_created'     => $now,
                'date_updated'     => $now,
            ];
        }

        if (!empty($insertRows)) {
            if (!$bookingModel->insertBatch($insertRows)) {
                log_message('error', 'insertBatch failed: ' . json_encode($bookingModel->errors()));
                return redirect()->back()->with('error', 'Failed to add new tests: ' . implode(', ', $bookingModel->errors() ?? []));
            }
        }

        // Update existing rows (discount / payment changes)
        $existingUpdates = $this->request->getPost('existing') ?? [];
        foreach ($existingUpdates as $rowId => $vals) {
            $discount = (int)($vals['discount'] ?? 0);
            $payment  = $vals['payment'] ?? 'cash';

            if ($discount < 0 || $discount > 100) $discount = 0;
            if (!in_array($payment, ['cash', 'card','online'], true)) $payment = 'cash';

            $bookingModel->where('id', (int)$rowId)
                ->where('fk_patient_id', $patientId)
                ->set(['discount_percent' => $discount, 'payment_method' => $payment, 'date_updated' => $now])
                ->update();
        }

        return redirect()->to(site_url('booking/view/' . $patientId))
            ->with('success', 'Tests updated successfully.');
    }
    public function viewInvoice($bookingId)
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login to view invoice');
        }

        $db = \Config\Database::connect();
        $patientModel = new PatientModel();
        
        // Get the specific booking record
        $booking = $db->table('patient_test_bookings')
            ->where('id', $bookingId)
            ->get()
            ->getRowArray();
        
        if (!$booking) {
            return redirect()->back()->with('error', 'Invoice not found');
        }
        
        // Get patient
        $patient = $patientModel->find($booking['fk_patient_id']);
        
        // Get ALL tests for this patient on the same date with lab details
        // Join labs table to get user_id, then join users table to get lab name
        $tests = $db->table('patient_test_bookings ptb')
            ->select('
                ptb.*,
                lt.test_name,
                lt.test_code,
                lt.rate as rack_rate,
                lt.reporting_time,
                u.name as lab_name,
                l.address as lab_address,
                l.phone as lab_phone,
                (lt.rate * ptb.discount_percent / 100) as discount_amt,
                (lt.rate - (lt.rate * ptb.discount_percent / 100)) as patient_price
            ')
            ->join('lab_tests lt', 'lt.id = ptb.fk_test_id', 'left')
            ->join('labs l', 'l.id = lt.lab_id', 'left')
            ->join('users u', 'u.id = l.user_id', 'left')  // Join users table to get lab name
            ->where('ptb.fk_patient_id', $booking['fk_patient_id'])
            ->where('DATE(ptb.date_created)', date('Y-m-d', strtotime($booking['date_created'])))
            ->get()
            ->getResultArray();
        
        // Calculate financials
        $originalTotal = 0;
        $discountTotal = 0;
        $patientPays = 0;
        $labName = '';
        $labAddress = '';
        $labPhone = '';
        
        foreach ($tests as $test) {
            $originalTotal += (float)($test['rack_rate'] ?? 0);
            $discountTotal += (float)($test['discount_amt'] ?? 0);
            $patientPays += (float)($test['patient_price'] ?? 0);
            if (empty($labName) && !empty($test['lab_name'])) {
                $labName = $test['lab_name'];
                $labAddress = $test['lab_address'] ?? '';
                $labPhone = $test['lab_phone'] ?? '';
            }
        }
        
        $data = [
            'booking' => $booking,
            'patient' => $patient,
            'tests' => $tests,
            'labName' => $labName,
            'labAddress' => $labAddress,
            'labPhone' => $labPhone,
            'originalTotal' => $originalTotal,
            'discountTotal' => $discountTotal,
            'patientPays' => $patientPays,
            'invoiceNumber' => 'INV-' . str_pad($booking['fk_patient_id'], 6, '0', STR_PAD_LEFT) . '-' . date('Ymd', strtotime($booking['date_created'])),
            'issuedDate' => date('d M Y', strtotime($booking['date_created'])),
            'isShared' => false,
            'shareToken' => null
        ];
        
        return view('labDashboard/invoice', $data);
    }

    public function generateShareLink($bookingId)
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login to generate share link'
            ]);
        }
        
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Save token in database with expiry (24 hours)
        $shareModel = new ShareTokenModel();
        $shareModel->insert([
            'booking_id' => $bookingId,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]);
        
        $shareUrl = base_url('booking/sharedInvoice/' . $bookingId . '/' . $token);
        
        return $this->response->setJSON([
            'success' => true,
            'share_url' => $shareUrl
        ]);
    }


    public function sharedInvoice($bookingId, $token = null)
    {
        // Validate token
        if (!$token) {
            return redirect()->to('/login')->with('error', 'Invalid share link');
        }
        
        // Check token in database
        $shareModel = new ShareTokenModel();
        $shareRecord = $shareModel->where('booking_id', $bookingId)
                                ->where('token', $token)
                                ->where('expires_at >', date('Y-m-d H:i:s'))
                                ->first();
        
        if (!$shareRecord) {
            return redirect()->to('/login')->with('error', 'Share link has expired or is invalid');
        }
        
        // Fetch booking data
        $db = \Config\Database::connect();
        $patientModel = new PatientModel();
        
        $booking = $db->table('patient_test_bookings')
            ->where('id', $bookingId)
            ->get()
            ->getRowArray();
        
        if (!$booking) {
            return redirect()->to('/login')->with('error', 'Invoice not found');
        }
        
        $patient = $patientModel->find($booking['fk_patient_id']);
        
        // Get ALL tests for this patient on the same date with lab details
        // Join labs table to get user_id, then join users table to get lab name
        $tests = $db->table('patient_test_bookings ptb')
            ->select('
                ptb.*,
                lt.test_name,
                lt.test_code,
                lt.rate as rack_rate,
                lt.reporting_time,
                u.name as lab_name,
                l.address as lab_address,
                l.phone as lab_phone,
                (lt.rate * ptb.discount_percent / 100) as discount_amt,
                (lt.rate - (lt.rate * ptb.discount_percent / 100)) as patient_price
            ')
            ->join('lab_tests lt', 'lt.id = ptb.fk_test_id', 'left')
            ->join('labs l', 'l.id = lt.lab_id', 'left')
            ->join('users u', 'u.id = l.user_id', 'left')  // Join users table to get lab name
            ->where('ptb.fk_patient_id', $booking['fk_patient_id'])
            ->where('DATE(ptb.date_created)', date('Y-m-d', strtotime($booking['date_created'])))
            ->get()
            ->getResultArray();
        
        $originalTotal = 0;
        $discountTotal = 0;
        $patientPays = 0;
        $labName = '';
        $labAddress = '';
        $labPhone = '';
        
        foreach ($tests as $test) {
            $originalTotal += (float)($test['rack_rate'] ?? 0);
            $discountTotal += (float)($test['discount_amt'] ?? 0);
            $patientPays += (float)($test['patient_price'] ?? 0);
            if (empty($labName) && !empty($test['lab_name'])) {
                $labName = $test['lab_name'];
                $labAddress = $test['lab_address'] ?? '';
                $labPhone = $test['lab_phone'] ?? '';
            }
        }
        
        // Increment view count
        $shareModel->update($shareRecord['id'], [
            'view_count' => ($shareRecord['view_count'] ?? 0) + 1
        ]);
        
        $data = [
            'booking' => $booking,
            'patient' => $patient,
            'tests' => $tests,
            'labName' => $labName,
            'labAddress' => $labAddress,
            'labPhone' => $labPhone,
            'originalTotal' => $originalTotal,
            'discountTotal' => $discountTotal,
            'patientPays' => $patientPays,
            'invoiceNumber' => 'INV-' . str_pad($booking['fk_patient_id'], 6, '0', STR_PAD_LEFT) . '-' . date('Ymd', strtotime($booking['date_created'])),
            'issuedDate' => date('d M Y', strtotime($booking['date_created'])),
            'isShared' => true,
            'shareToken' => $token
        ];
        
        return view('labDashboard/invoice', $data);
    }

    public function phlebotomistSchedule($franchiseId = null)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        if (!$franchiseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid franchise']);
        }

        $labId = $this->getLabId();
        $db = \Config\Database::connect();

        $rows = $db->table('patient_test_bookings ptb')
            ->select('ph.id as phleb_id, ph.name as phleb_name, ptb.eta, ptb.status, p.patient_name, ptb.fk_patient_id')
            ->join('phlebotomists ph', 'ph.id = ptb.phleb_id', 'inner')
            ->join('franchises f', 'f.id = ph.franchise_id', 'left')
            ->join('patients p', 'p.id = ptb.fk_patient_id', 'left')
            ->where('ph.franchise_id', $franchiseId)
            ->where('ph.status', 'active') 
            ->where('f.lab_id', $labId)
            ->whereIn('ptb.status', ['Phlebotomist Assigned', 'Arrived'])
            ->where('ptb.eta IS NOT NULL', null, false)
            ->groupBy('ptb.fk_patient_id, ph.id')
            ->orderBy('ptb.eta', 'ASC')
            ->get()->getResultArray();

        // Group flat rows by phlebotomist
        $schedule = [];
        foreach ($rows as $row) {
            $pid = $row['phleb_id'];
            if (!isset($schedule[$pid])) {
                $schedule[$pid] = [
                    'phleb_id'   => $pid,
                    'phleb_name' => $row['phleb_name'],
                    'bookings'   => [],
                ];
            }
            $schedule[$pid]['bookings'][] = [
                'eta'          => $row['eta'],
                'patient_name' => $row['patient_name'],
                'status'       => $row['status'],
            ];
        }

        return $this->response->setJSON([
            'success'  => true,
            'schedule' => array_values($schedule),
        ]);
    }

    public function uploadProofAndMarkPaid($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Please login to perform this action');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    $userId = session()->get('user_id') ?? session()->get('id');
    $labId  = $this->getLabId();

    $validation = \Config\Services::validation();
    $validation->setRules([
        'payment_method'      => 'required|in_list[cash,online,card]',
        'payment_received_by' => 'required|in_list[main_branch,franchise]',
        'proof_file'          => 'uploaded[proof_file]|max_size[proof_file,10240]|ext_in[proof_file,pdf,jpg,jpeg,png]',
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        return redirect()->back()->with('errors', $validation->getErrors());
    }

    $db = \Config\Database::connect();

    $booking = $db->table('patient_test_bookings ptb')
        ->select('ptb.*, lt.lab_id')
        ->join('lab_tests lt', 'lt.id = ptb.fk_test_id', 'left')
        ->where('ptb.id', $bookingId)
        ->get()->getRowArray();

    if (!$booking) {
        return redirect()->back()->with('error', 'Booking not found.');
    }

    // Safety: this booking's test must belong to the logged-in lab
    if ($labId && (int) $booking['lab_id'] !== (int) $labId) {
        return redirect()->back()->with('error', 'Unauthorized.');
    }

    if (!in_array($booking['status'], ['Sample Collected', 'Report Ready'])) {
        return redirect()->back()->with('error', 'Payment proof can only be uploaded after sample collection.');
    }

    $patientId = $booking['fk_patient_id'];

    $file = $this->request->getFile('proof_file');
    if (!$file->isValid()) {
        return redirect()->back()->with('error', 'Please upload a valid file.');
    }

    $newName    = 'proof_' . $patientId . '_' . time() . '_' . uniqid() . '.' . $file->getExtension();
    $uploadPath = WRITEPATH . 'uploads/payment_proofs/';

    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    if (!$file->move($uploadPath, $newName)) {
        return redirect()->back()->with('error', 'Failed to upload file.');
    }

    $filePath = 'uploads/payment_proofs/' . $newName;

    $db->transStart();

    // 1. Save proof of payment on ALL rows for this patient
    $db->table('patient_test_bookings')
        ->where('fk_patient_id', $patientId)
        ->update([
            'payment_proof_file'                => $filePath,
            'payment_method'                    => $this->request->getPost('payment_method'),
            'payment_received_by'               => $this->request->getPost('payment_received_by'),
            'payment_proof_uploaded_by_user_id' => $userId,
            'payment_proof_uploaded_at'         => date('Y-m-d H:i:s'),
            'date_updated'                      => date('Y-m-d H:i:s'),
        ]);

    // 2. Immediately mark all unpaid test rows for this patient as paid
    $db->table('patient_test_bookings')
        ->where('fk_patient_id', $patientId)
        ->where('payment_status', 'unpaid')
        ->update([
            'payment_status' => 'paid',
            'payment_date'   => date('Y-m-d H:i:s'),
            'date_updated'   => date('Y-m-d H:i:s'),
        ]);

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->back()->with('error', 'Failed to upload proof and update payment status.');
    }

    $this->logStatusHistory(
        $bookingId,
        $patientId,
        $booking['status'],
        'Proof of payment uploaded and payment marked as paid (received by '
            . str_replace('_', ' ', $this->request->getPost('payment_received_by')) . ')'
    );

    return redirect()->to('/booking/view/' . $patientId)
        ->with('success', 'Proof of payment uploaded and payment marked as paid successfully!');
}

public function trackingLink($bookingId = null)
{
    if (!$bookingId) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid booking ID']);
    }

    $db = \Config\Database::connect();

    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->get()->getRowArray();

    if (!$booking) {
        return $this->response->setJSON(['success' => false, 'message' => 'Booking not found']);
    }

    $patient = $db->table('patients')
        ->where('id', $booking['fk_patient_id'])
        ->get()->getRowArray();

    if (!$patient || empty($patient['phone_number'])) {
        return $this->response->setJSON(['success' => false, 'message' => 'Patient phone not found']);
    }

    $locRow = $db->table('phlebotomist_locations')
        ->where('booking_id', $booking['id'])
        ->get()->getRowArray();

    if (!$locRow || empty($locRow['token'])) {
        return $this->response->setJSON(['success' => false, 'message' => 'Tracking token not found for this booking']);
    }

    $trackingUrl = base_url('t/' . $locRow['token']);
    $message     = WhatsAppMessages::forTrackingLink($patient['patient_name'], $trackingUrl);

    $digits = preg_replace('/\D/', '', $patient['phone_number'] ?? '');
    if (str_starts_with($digits, '0')) {
        $digits = substr($digits, 1);
    }
    if (!str_starts_with($digits, '92')) {
        $digits = '92' . $digits;
    }

    $waUrl = 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);

    return $this->response->setJSON(['success' => true, 'wa_url' => $waUrl]);
}

// delete report
public function deleteReport($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Please login to perform this action');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    $db = \Config\Database::connect();

    // This is a row in patient_test_bookings (one row per test), same as downloadReport() uses
    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->get()
        ->getRowArray();

    if (!$booking) {
        return redirect()->back()->with('error', 'Booking not found');
    }

    $patientId = $booking['fk_patient_id'];
    $testId    = $booking['fk_test_id'];

    $reportModel = new \App\Models\LabReportModel();

    // Reports are keyed by patient + test, exactly like downloadReport() looks them up
    $report = $reportModel
        ->where('fk_patient_id', $patientId)
        ->where('fk_test_id', $testId)
        ->orderBy('uploaded_at', 'DESC')
        ->first();

    if (!$report) {
        return redirect()->back()->with('error', 'No report found for this test.');
    }

    // Delete the physical PDF (report_file is stored as 'uploads/reports/xxx.pdf')
    if (!empty($report['report_file'])) {
        $filePath = WRITEPATH . $report['report_file'];
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    // Remove the report record so has_report becomes false again for this test
    $reportModel->delete($report['id']);

    // If the patient's overall status was "Report Ready", revert to "Sample Collected"
    // since this test no longer has a report attached.
    if ($booking['status'] === 'Report Ready') {
        $db->table('patient_test_bookings')
            ->where('fk_patient_id', $patientId)
            ->update([
                'status'       => 'Sample Collected',
                'date_updated' => date('Y-m-d H:i:s'),
            ]);

        $this->logStatusHistory(
            $bookingId,
            $patientId,
            'Sample Collected',
            'Report deleted for one test — status reverted from Report Ready'
        );
    }

    return redirect()->to('/booking/view/' . $patientId)
        ->with('success', 'Report deleted. You can now upload a new one for this test.');
}

}