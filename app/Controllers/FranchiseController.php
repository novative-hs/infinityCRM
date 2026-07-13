<?php

namespace App\Controllers;

use App\Models\FranchiseModel;
use App\Models\LabModel;
use App\Models\CityModel;

class FranchiseController extends BaseController
{

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $franchiseModel = new FranchiseModel();
        $franchises = $franchiseModel
                        ->select('
                                    franchises.*,
                                    fu.name as name,
                                    fu.email as email,
                                    fu.password_hint,
                                    fu.status as status,
                                    lu.name as lab_name,
                                    cities.name as city_name
                                ')
                        ->join('users fu', 'fu.id = franchises.user_id')
                        ->join('labs', 'labs.id = franchises.lab_id')
                        ->join('users lu', 'lu.id = labs.user_id')
                        ->join('cities', 'cities.id = franchises.city_id')
                        ->where('franchises.is_deleted', 0)
                        ->orderBy('franchises.id', 'DESC')
                        ->findAll();

        return view('dbadmin/franchiselist', ['franchises' => $franchises]);
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $cityModel = new CityModel();

        $db   = \Config\Database::connect();
        $labs = $db->table('labs l')
                   ->select('l.id, u.name as lab_name')
                   ->join('users u', 'u.id = l.user_id')
                   ->get()->getResultArray();

        return view('dbadmin/franchiseform', [
            'labs'   => $labs,
            'cities' => $cityModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $name          = $this->request->getPost('name');
        $email         = $this->request->getPost('email');
        $password      = $this->request->getPost('password');
        $labId         = $this->request->getPost('lab_id');
        $cityId        = $this->request->getPost('city_id');
        $contactNumber = $this->request->getPost('contact_number');
        $discount      = $this->request->getPost('discount');

        if (empty($email) || empty($password) || empty($labId) || empty($cityId)) {
            return redirect()->back()->withInput()->with('error', 'Please fill all required fields.');
        }

        $db = \Config\Database::connect();

        $existing = $db->table('users')->where('email', $email)->get()->getRowArray();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Email already exists.');
        }

        $db->transStart();

        $db->table('users')->insert([
            'name'          => $name,
            'email'         => $email,
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'password_hint' => $password,
            'role'          => 'franchise',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $userId = $db->insertID();

        $franchiseModel = new FranchiseModel();
        $franchiseModel->createFranchise([
            'user_id'        => $userId,
            'lab_id'         => $labId,
            'city_id'        => $cityId,
            'contact_number' => $contactNumber,
            'discount'       => $discount ?: 0,
            'status'         => 'active',
        ]);

        $db->transComplete();

        if ($db->transStatus() !== false) {
            $franchiseId = $franchiseModel->getInsertID();
            (new \App\Models\ActivityLogModel())->record(
                'franchise', $franchiseId, 'registered',
                "Franchise '{$name}' registered with email {$email}"
            );
        }

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create franchise.');
        }

        return redirect()->to('/franchiselist')->with('success', 'Franchise added successfully.');
    }

    public function edit($franchiseId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $franchiseModel = new FranchiseModel();
        $franchise = $franchiseModel
                        ->select('franchises.*, fu.name as name, fu.email as email, fu.status as status')
                        ->join('users fu', 'fu.id = franchises.user_id')
                        ->find($franchiseId);

        if (!$franchise) {
            return redirect()->to('/franchiselist')->with('error', 'Franchise not found.');
        }

        $cityModel = new CityModel();

        $db   = \Config\Database::connect();
        $labs = $db->table('labs l')
                   ->select('l.id, u.name as lab_name')
                   ->join('users u', 'u.id = l.user_id')
                   ->get()->getResultArray();

        return view('dbadmin/franchiseform', [
            'franchise' => $franchise,
            'labs'      => $labs,
            'cities'    => $cityModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

   public function update($franchiseId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $db = \Config\Database::connect();
    $franchiseModel = new FranchiseModel();

    $franchise = $franchiseModel->find($franchiseId);
    if (!$franchise) {
        return redirect()->to('/franchiselist')->with('error', 'Franchise not found.');
    }

    $currentUser = $db->table('users')->where('id', $franchise['user_id'])->get()->getRowArray();
    $oldStatus   = $currentUser['status'] ?? null;
    $newStatus   = $this->request->getPost('status');

    $db->table('users')->where('id', $franchise['user_id'])->update([
        'name'   => $this->request->getPost('name'),
        'email'  => $this->request->getPost('email'),
        'status' => $newStatus,
    ]);

    $passwordChanged = false;
    $password = $this->request->getPost('password');
    if (!empty($password)) {
        $db->table('users')->where('id', $franchise['user_id'])->update([
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'password_hint' => $password,
        ]);
        $passwordChanged = true;
    }

    $franchiseModel->update($franchiseId, [
        'lab_id'         => $this->request->getPost('lab_id'),
        'city_id'        => $this->request->getPost('city_id'),
        'contact_number' => $this->request->getPost('contact_number'),
        'discount'       => $this->request->getPost('discount'),
        'updated_at'     => date('Y-m-d H:i:s'),
    ]);

    $logModel = new \App\Models\ActivityLogModel();
    $desc = "Franchise details updated (name, email, lab, city, contact, discount)";
    if ($passwordChanged) {
        $desc .= " · password changed";
    }
    $logModel->record('franchise', $franchiseId, 'updated', $desc);

    if ($oldStatus && $oldStatus !== $newStatus) {
        $logModel->record(
            'franchise', $franchiseId,
            $newStatus === 'active' ? 'activated' : 'deactivated',
            "Status changed from {$oldStatus} to {$newStatus}"
        );
    }

    return redirect()->to('/franchiselist')->with('success', 'Franchise updated successfully.');
}

    public function phlebotomist($franchiseId)
    {
        if (!session()->get('logged_in')) return redirect()->to('/login');

        $franchiseModel = new FranchiseModel();
        $franchise = $franchiseModel
                        ->select('franchises.*, fu.name as name, fu.email as email')
                        ->join('users fu', 'fu.id = franchises.user_id')
                        ->find($franchiseId);

        if (!$franchise) return redirect()->to('/franchiselist')->with('error', 'Franchise not found.');

        $db = \Config\Database::connect();

        $phlebotomists = $db->table('phlebotomists')
                            ->where('franchise_id', $franchiseId)
                            ->get()->getResultArray();

        return view('dbadmin/franchisephlebotomist', [
            'franchise'     => $franchise,
            'phlebotomists' => $phlebotomists,
            'count'         => count($phlebotomists),
        ]);
    }

    public function addPhlebotomist($franchiseId)
    {
        if (!session()->get('logged_in')) return redirect()->to('/login');

        $name = $this->request->getPost('name');

        if (empty($name)) {
            return redirect()->back()->with('error', 'Name is required.');
        }

        $db = \Config\Database::connect();
        $db->table('phlebotomists')->insert([
            'franchise_id' => $franchiseId,
            'name'         => $name,
            'status'       => 'active',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        (new \App\Models\ActivityLogModel())->record(
            'franchise', $franchiseId, 'phlebotomist_added',
            "Phlebotomist '{$name}' added"
        );

        return redirect()->to(base_url('franchise/' . $franchiseId . '/phlebotomist'))
                         ->with('success', 'Phlebotomist added successfully.');
    }

    public function importPhlebotomist($franchiseId)
    {
        if (!session()->get('logged_in')) return redirect()->to('/login');

        $file = $this->request->getFile('excel_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Please upload a valid Excel file.');
        }

        $allowed = ['xlsx', 'xls', 'csv'];
        if (!in_array(strtolower($file->getExtension()), $allowed)) {
            return redirect()->back()->with('error', 'Only .xlsx, .xls, .csv files allowed.');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $rows        = $spreadsheet->getActiveSheet()->toArray();
            array_shift($rows); // skip header

            $db = \Config\Database::connect();

            $db->table('phlebotomists')->where('franchise_id', $franchiseId)->delete();

            $data = [];
            foreach ($rows as $row) {
                if (empty($row[0])) continue;

                $data[] = [
                    'franchise_id' => $franchiseId,
                    'name'         => $row[0],
                    'status'       => 'active',
                    'created_at'   => date('Y-m-d H:i:s'),
                ];
            }

            if (!empty($data)) {
                $db->table('phlebotomists')->insertBatch($data);
            }

            (new \App\Models\ActivityLogModel())->record(
                'franchise', $franchiseId, 'phlebotomist_imported',
                count($data) . " phlebotomists imported via Excel"
            );

            return redirect()->to(base_url('franchise/' . $franchiseId . '/phlebotomist'))
                             ->with('success', count($data) . ' phlebotomists imported successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to read file: ' . $e->getMessage());
        }
    }
    
public function dashboard()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $userId = session()->get('user_id');
    $db = \Config\Database::connect();

    // Franchise Info
    $franchise = $db->table('franchises f')
                    ->select('
                        f.*,
                        u.name as name,
                        u.email as email,
                        l.id as lab_id,
                        lu.name as lab_name,
                        c.name as city_name
                    ')
                    ->join('users u', 'u.id = f.user_id')
                    ->join('labs l', 'l.id = f.lab_id')
                    ->join('users lu', 'lu.id = l.user_id')
                    ->join('cities c', 'c.id = f.city_id')
                    ->where('f.user_id', $userId)
                    ->get()
                    ->getRowArray();

    if (!$franchise) {
        return redirect()->to('/login')
                         ->with('error', 'Franchise not found.');
    }

    // Phlebotomists Count
    $phlebCount = $db->table('phlebotomists')
                     ->where('franchise_id', $franchise['id'])
                     ->countAllResults();

    // Filters
    $search    = $this->request->getGet('search') ?? '';
    $status    = $this->request->getGet('status') ?? '';
    $dateFrom  = $this->request->getGet('date_from') ?? '';
    $dateTo    = $this->request->getGet('date_to') ?? '';

    // Booking Query
    $builder = $db->table('patient_test_bookings b')
            ->select('
                b.*,
                b.reporting_time as show_reporting_time,

                p.patient_name,
                p.phone_number,
                p.age,
                p.gender,
                p.home_address,
                p.pin_location,
                p.instructions,
                p.medical_history,

                t.test_name,
                t.test_code,
                t.rate,
                t.reporting_time as test_reporting_time,

                ph.name as phlebotomist_name
            ')
            ->join('patients p', 'p.id = b.fk_patient_id', 'left')
            ->join('lab_tests t', 't.id = b.fk_test_id', 'left')
            ->join('phlebotomists ph', 'ph.id = b.phleb_id', 'left')
            ->where('b.fk_franchise_id', $franchise['id'])
            ->where('b.status !=', 'In Process');

    // Search Filter
    if (!empty($search)) {
        $builder->groupStart()
                ->like('p.patient_name', $search)
                ->orLike('p.phone_number', $search)
                ->groupEnd();
    }

    // Status Filter
    if (!empty($status) && $status !== 'All') {
        $builder->where('b.status', $status);
    } else {
        // "All" should not include bookings that are already Report Ready —
        // those only show up under the dedicated "Report Ready" tab.
        $builder->where('b.status !=', 'Report Ready');
    }

    // Report Ready Count (kept separate since "All" excludes these bookings above)
    $reportReadyBuilder = $db->table('patient_test_bookings b')
                             ->join('patients p', 'p.id = b.fk_patient_id', 'left')
                             ->where('b.fk_franchise_id', $franchise['id'])
                             ->where('b.status', 'Report Ready');

    if (!empty($search)) {
        $reportReadyBuilder->groupStart()
                           ->like('p.patient_name', $search)
                           ->orLike('p.phone_number', $search)
                           ->groupEnd();
    }
    if (!empty($dateFrom)) {
        $reportReadyBuilder->where('DATE(b.date_created) >=', $dateFrom);
    }
    if (!empty($dateTo)) {
        $reportReadyBuilder->where('DATE(b.date_created) <=', $dateTo);
    }

    $reportReadyBookings = $reportReadyBuilder
                          ->select('b.fk_patient_id')
                          ->groupBy('b.fk_patient_id')
                          ->countAllResults();

    // Date Filters
    if (!empty($dateFrom)) {
        $builder->where('DATE(b.date_created) >=', $dateFrom);
    }

    if (!empty($dateTo)) {
        $builder->where('DATE(b.date_created) <=', $dateTo);
    }

    // Raw Bookings
    $rawBookings = $builder
                    ->orderBy('b.date_created', 'DESC')
                    ->get()
                    ->getResultArray();

    // Group Bookings by Patient
    $grouped = [];

    foreach ($rawBookings as $row) {

        $pid = $row['fk_patient_id'];

        if (!isset($grouped[$pid])) {

            $grouped[$pid] = [
                'booking_id'          => $row['id'],
                'fk_patient_id'       => $pid,
                'patient_name'        => $row['patient_name'],
                'phone_number'        => $row['phone_number'],
                'gender'              => $row['gender'],
                'age'                 => $row['age'],
                'home_address'        => $row['home_address'],
                'pin_location'        => $row['pin_location'],
                'instructions'        => $row['instructions'],
                'medical_history'     => $row['medical_history'],
                'booking_person_name' => $row['booking_person_name'],
                'status'              => $row['status'],
                'eta'                 => $row['eta'],
                'phlebotomist_name'   => $row['phlebotomist_name'],
                'payment_method'      => $row['payment_method'],
                'payment_status'      => $row['payment_status'],
                'date_created'        => $row['date_created'],
                'total'               => 0,
                'payable'             => 0,
                'discount_percent'    => $row['discount_percent'] ?? 0,
                'show_reporting_time' => (int) $row['show_reporting_time'],
                'payment_proof_file'  => $row['payment_proof_file'] ?? null,      
                'payment_received_by' => $row['payment_received_by'] ?? null, 
                'tests'               => [],
            ];
        }

        $rate = (float)($row['rate'] ?? 0);
        $disc = (float)($row['discount_percent'] ?? 0);

        $discAmt = round($rate * $disc / 100);

        $grouped[$pid]['total'] += $rate;
        $grouped[$pid]['payable'] += ($rate - $discAmt);

        $grouped[$pid]['tests'][] = [
            'test_name'      => $row['test_name'],
            'test_code'      => $row['test_code'],
            'reporting_time' => $row['test_reporting_time'], // renamed
            'rate'           => $rate,
            'discount'       => $disc,
            'discount_amt'   => $discAmt,
        ];
    }

    $bookings = array_values($grouped);

    // Dashboard Stats (patient/booking-wise, NOT test-wise)
    $totalBookings     = count($bookings);
    $pendingBookings   = 0;
    $completedBookings = 0;

    foreach ($bookings as $bk) {

        if (in_array($bk['status'], [
            'Phlebotomist Assigned',
            'Arrived'
        ])) {
            $pendingBookings++;
        }

        if ($bk['status'] === 'Sample Collected') {
            $completedBookings++;
        }
    }

    // Filters Array
    $filters = compact(
        'search',
        'status',
        'dateFrom',
        'dateTo'
    );

    return view('franchiseDashboard/dashboard', [

        'franchise'         => $franchise,

        'phlebCount'        => $phlebCount,

        'bookings'          => $bookings,

        'totalBookings'     => $totalBookings,
        'pendingBookings'   => $pendingBookings,
        'completedBookings' => $completedBookings,
        'reportReadyBookings' => $reportReadyBookings,

        'filters'           => $filters,
    ]);
}

public function myPhlebotomists()
{
    if (!session()->get('logged_in')) return redirect()->to('/login');

    $userId = session()->get('user_id');
    $db = \Config\Database::connect();

    $franchise = $db->table('franchises')
                    ->where('user_id', $userId)
                    ->get()->getRowArray();

    if (!$franchise) return redirect()->to('/franchiseDashboard/dashboard');

    $phlebotomists = $db->table('phlebotomists')
                        ->where('franchise_id', $franchise['id'])
                        ->get()->getResultArray();

    return view('franchiseDashboard/phlebotomists', [
        'phlebotomists' => $phlebotomists,
        'count'         => count($phlebotomists),
    ]);
}

public function uploadPaymentProof($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Please login to perform this action');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    $userId = session()->get('user_id');
    $db = \Config\Database::connect();

    $franchise = $db->table('franchises')->where('user_id', $userId)->get()->getRowArray();
    if (!$franchise) {
        return redirect()->to('/login')->with('error', 'Franchise not found.');
    }

    $validation = \Config\Services::validation();
    $validation->setRules([
        'payment_method'      => 'required|in_list[cash,online,card]',
        'payment_received_by' => 'required|in_list[main_branch,franchise]',
        'proof_file'          => 'uploaded[proof_file]|max_size[proof_file,10240]|ext_in[proof_file,pdf,jpg,jpeg,png]',
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        return redirect()->back()->with('errors', $validation->getErrors());
    }

    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->where('fk_franchise_id', $franchise['id'])
        ->get()->getRowArray();

    if (!$booking) {
        return redirect()->back()->with('error', 'Booking not found for this franchise.');
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

    $db->table('patient_test_bookings')
        ->where('fk_patient_id', $patientId)
        ->update([
            'payment_proof_file'              => $filePath,
            'payment_method'                  => $this->request->getPost('payment_method'),
            'payment_received_by'             => $this->request->getPost('payment_received_by'),
            'payment_proof_uploaded_by_user_id' => $userId,
            'payment_proof_uploaded_at'       => date('Y-m-d H:i:s'),
            'payment_status'                  => 'paid',
            'payment_date'                    => date('Y-m-d H:i:s'),
            'date_updated'                    => date('Y-m-d H:i:s'),
        ]);

    return redirect()->to(base_url('franchiseDashboard/dashboard'))
        ->with('success', 'Payment proof uploaded and booking marked as paid.');
}

public function viewPaymentProof($bookingId = null)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Please login to perform this action');
    }

    if (!$bookingId) {
        return redirect()->back()->with('error', 'Invalid booking ID');
    }

    $userId = session()->get('user_id');
    $db = \Config\Database::connect();

    $franchise = $db->table('franchises')->where('user_id', $userId)->get()->getRowArray();
    if (!$franchise) {
        return redirect()->to('/login')->with('error', 'Franchise not found.');
    }

    $booking = $db->table('patient_test_bookings')
        ->where('id', $bookingId)
        ->where('fk_franchise_id', $franchise['id'])
        ->get()->getRowArray();

    if (!$booking || empty($booking['payment_proof_file'])) {
        return redirect()->back()->with('error', 'Proof of payment not found.');
    }

    $filePath = WRITEPATH . $booking['payment_proof_file'];

    if (!file_exists($filePath)) {
        return redirect()->back()->with('error', 'Proof file not found on server.');
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mime = $ext === 'pdf' ? 'application/pdf' : 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);

    return $this->response
        ->setHeader('Content-Type', $mime)
        ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
        ->setBody(file_get_contents($filePath));
}

public function toggleStatus($franchiseId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $db = \Config\Database::connect();
    $franchiseModel = new FranchiseModel();

    $franchise = $franchiseModel->find($franchiseId);

    if (!$franchise) {
        return redirect()->to('/franchiselist')->with('error', 'Franchise not found.');
    }

    $currentUser = $db->table('users')->where('id', $franchise['user_id'])->get()->getRowArray();
    $newStatus = ($currentUser && $currentUser['status'] === 'active') ? 'inactive' : 'active';
  
    $db->table('users')->where('id', $franchise['user_id'])->update([
        'status' => $newStatus,
    ]);

    $franchiseModel->update($franchiseId, [
        'status'     => $newStatus,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    (new \App\Models\ActivityLogModel())->record(
        'franchise', $franchiseId, $newStatus === 'active' ? 'activated' : 'deactivated',
        "Franchise marked as {$newStatus}"
    );

    return redirect()->to('/franchiselist')
        ->with('success', 'Franchise marked as ' . $newStatus . '.');
}

public function delete($franchiseId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $db = \Config\Database::connect();
    $franchiseModel = new FranchiseModel();

    $franchise = $franchiseModel->find($franchiseId);
    if (!$franchise) {
        return redirect()->to('/franchiselist')->with('error', 'Franchise not found.');
    }

    $franchiseModel->update($franchiseId, [
        'is_deleted' => 1,
        'status'     => 'inactive',   
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    (new \App\Models\ActivityLogModel())->record(
        'franchise', $franchiseId, 'deleted',
        "Franchise soft-deleted"
    );

    $db->table('users')->where('id', $franchise['user_id'])->update([
        'status' => 'inactive',
    ]);

    return redirect()->to('/franchiselist')
        ->with('success', 'Franchise deleted successfully.');
}

public function togglePhlebotomistStatus($franchiseId, $phlebId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $db = \Config\Database::connect();

    $phleb = $db->table('phlebotomists')
        ->where('id', $phlebId)
        ->where('franchise_id', $franchiseId)   // safety: sirf isi franchise ka phlebotomist
        ->get()->getRowArray();

    if (!$phleb) {
        return redirect()->back()->with('error', 'Phlebotomist not found.');
    }

    $newStatus = $phleb['status'] === 'active' ? 'inactive' : 'active';

    $db->table('phlebotomists')
        ->where('id', $phlebId)
        ->update(['status' => $newStatus]);

          (new \App\Models\ActivityLogModel())->record(
                'franchise', $franchiseId,
                $newStatus === 'active' ? 'phlebotomist_activated' : 'phlebotomist_deactivated',
                "Phlebotomist '{$phleb['name']}' marked as {$newStatus}"
            );

    return redirect()->to(base_url('franchise/' . $franchiseId . '/phlebotomist'))
        ->with('success', 'Phlebotomist marked as ' . $newStatus . '.');
}

public function history($franchiseId)
{
    if (!session()->get('logged_in')) {
        return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
    }

    $logs = (new \App\Models\ActivityLogModel())->getHistory('franchise', $franchiseId);
    return $this->response->setJSON(['logs' => $logs]);
}
}