<?php

namespace App\Controllers;

use App\Models\LabModel;
use App\Models\CityModel;

class LabController extends BaseController
{

public function index()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $labModel = new LabModel();
    $labs = $labModel->select('labs.*, users.name, users.email, users.status, users.password_hint')
                     ->join('users', 'users.id = labs.user_id')
                     ->orderBy('labs.id', 'DESC')
                     ->findAll();

    $db = \Config\Database::connect();
    foreach ($labs as &$lab) {
        $lab['test_count'] = $db->table('lab_tests')
                                ->where('lab_id', $lab['id'])
                                ->countAllResults();
    }

    return view('dbadmin/lablist', ['labs' => $labs]);
}

public function priceList($labId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $labModel = new LabModel();
    $lab = $labModel->select('labs.*, users.name, users.email')
                    ->join('users', 'users.id = labs.user_id')
                    ->find($labId);

    if (!$lab) {
        return redirect()->to('/lablist')->with('error', 'Lab not found.');
    }

    $db    = \Config\Database::connect();
    $tests = $db->table('lab_tests')->where('lab_id', $labId)->get()->getResultArray();
    $mode  = $this->request->getGet('mode') ?? 'import';

    return view('dbadmin/pricelist', ['lab' => $lab, 'tests' => $tests, 'mode' => $mode]);
}

    public function importPriceList($labId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

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
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows);

            $db = \Config\Database::connect();
            $db->table('lab_tests')->where('lab_id', $labId)->delete();

            $data = [];
            foreach ($rows as $row) {
                if (empty($row[1])) continue;
                $data[] = [
                    'lab_id' => $labId,
                    'test_code' => $row[0] ?? '',
                    'test_name' => $row[1] ?? '',
                    'rate' => $row[2] ?? 0,
                    'sample' => $row[3] ?? '',
                    'reporting_time' => $row[4] ?? '',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }

            if (!empty($data)) {
                $db->table('lab_tests')->insertBatch($data);
            }

               (new \App\Models\ActivityLogModel())->record(
                    'lab',
                    $labId,
                    'price_list_imported',
                    count($data) . ' tests imported via Excel (previous price list replaced)'
                );

            return redirect()->to(base_url('labs/' . $labId . '/pricelist'))
                             ->with('success', count($data) . ' tests imported successfully.');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to read file: ' . $e->getMessage());
    }
}
public function updatePriceList($labId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

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
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray();

        // Skip header row
        array_shift($rows);

        $db = \Config\Database::connect();

        $updated = 0;
        $inserted = 0;

        foreach ($rows as $row) {
            if (empty($row[1])) continue;

            $testCode = $row[0] ?? '';
            $testName = $row[1] ?? '';
            $rate     = $row[2] ?? 0;
            $sample   = $row[3] ?? '';
            $time     = $row[4] ?? '';

            // Check if this test_code already exists for this lab
            $existing = $db->table('lab_tests')
                           ->where('lab_id', $labId)
                           ->where('test_code', $testCode)
                           ->get()
                           ->getRowArray();

            if ($existing) {
                // Update existing
                $db->table('lab_tests')
                   ->where('id', $existing['id'])
                   ->update([
                       'test_name'      => $testName,
                       'rate'           => $rate,
                       'sample'         => $sample,
                       'reporting_time' => $time,
                   ]);
                $updated++;
            } else {
                // Insert new
                $db->table('lab_tests')->insert([
                    'lab_id'         => $labId,
                    'test_code'      => $testCode,
                    'test_name'      => $testName,
                    'rate'           => $rate,
                    'sample'         => $sample,
                    'reporting_time' => $time,
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
                $inserted++;
            }
        }

             (new \App\Models\ActivityLogModel())->record(
                'lab',
                $labId,
                'price_list_updated',
                "{$updated} tests updated, {$inserted} new tests added via Excel"
            );

        return redirect()->to(base_url('labs/' . $labId . '/pricelist'))
                         ->with('success', "$updated tests updated, $inserted new tests added.");

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to read file: ' . $e->getMessage());
    }
}
public function edit($labId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $labModel = new LabModel();
    $lab = $labModel->select('labs.*, users.name, users.email, users.status')
                    ->join('users', 'users.id = labs.user_id')
                    ->find($labId);

    if (!$lab) {
        return redirect()->to('/lablist')->with('error', 'Lab not found.');
    }

    return view('dbadmin/editlab', ['lab' => $lab]);
}

public function update($labId)
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $db       = \Config\Database::connect();
    $labModel = new LabModel();

    $lab = $labModel->find($labId);
    if (!$lab) {
        return redirect()->to('/lablist')->with('error', 'Lab not found.');
    }

    $currentUser = $db->table('users')->where('id', $lab['user_id'])->get()->getRowArray();
    $oldStatus   = $currentUser['status'] ?? null;
    $newStatus   = $this->request->getPost('status');

    $db->table('users')->where('id', $lab['user_id'])->update([
        'name'   => $this->request->getPost('name'),
        'email'  => $this->request->getPost('email'),
        'status' => $newStatus,
    ]);

    $passwordChanged = false;
    $password = $this->request->getPost('password');
    if (!empty($password)) {
        $db->table('users')->where('id', $lab['user_id'])->update([
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'password_hint' => $password,
        ]);
        $passwordChanged = true;
    }

    $labModel->update($labId, [
        'contact_person' => $this->request->getPost('contact_person'),
        'phone'          => $this->request->getPost('phone'),
        'license_number' => $this->request->getPost('license_number'),
        'address'        => $this->request->getPost('address'),
    ]);

    $logModel = new \App\Models\ActivityLogModel();
    $desc = "Lab details updated (name, email, contact person, phone, license, address)";
    if ($passwordChanged) {
        $desc .= " · password changed";
    }
    $logModel->record('lab', $labId, 'updated', $desc);

    if ($oldStatus && $oldStatus !== $newStatus) {
        $logModel->record(
            'lab',
            $labId,
            $newStatus === 'active' ? 'activated' : 'deactivated',
            "Status changed from {$oldStatus} to {$newStatus}"
        );
    }

    return redirect()->to('/lablist')->with('success', 'Lab updated successfully.');
}

public function phlebotomist($labId)
{
    if (!session()->get('logged_in')) return redirect()->to('/login');

    $labModel = new LabModel();
    $lab = $labModel->select('labs.*, users.name, users.email')
                    ->join('users', 'users.id = labs.user_id')
                    ->find($labId);

    if (!$lab) return redirect()->to('/lablist')->with('error', 'Lab not found.');

    $db = \Config\Database::connect();

    $phlebotomists = $db->table('phlebotomists p')
                        ->select('p.*, c.name as city_name')
                        ->join('cities c', 'c.id = p.fk_city_id', 'left')
                        ->where('p.lab_id', $labId)
                        ->get()->getResultArray();

    $cityModel = new CityModel();

    return view('dbadmin/phlebotomist', [
        'lab'           => $lab,
        'phlebotomists' => $phlebotomists,
        'count'         => count($phlebotomists),
        'cities'        => $cityModel->orderBy('name', 'ASC')->findAll(),
    ]);
}

public function addPhlebotomist($labId)
{
    if (!session()->get('logged_in')) return redirect()->to('/login');

    $name      = $this->request->getPost('name');
    $cityId    = $this->request->getPost('fk_city_id');

    if (empty($name)) {
        return redirect()->back()->with('error', 'Name is required.');
    }

    $db = \Config\Database::connect();
    $db->table('phlebotomists')->insert([
        'lab_id'     => $labId,
        'name'       => $name,
        'fk_city_id' => $cityId ?: null,
        'status'     => 'active',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    return redirect()->to(base_url('labs/' . $labId . '/phlebotomist'))
                     ->with('success', 'Phlebotomist added successfully.');
}

public function importPhlebotomist($labId)
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

        $citiesMap = [];
        $allCities = $db->table('cities')->get()->getResultArray();
        foreach ($allCities as $c) {
            $citiesMap[strtolower(trim($c['name']))] = $c['id'];
        }

        $db->table('phlebotomists')->where('lab_id', $labId)->delete();

        $data = [];
        foreach ($rows as $row) {
            if (empty($row[0])) continue;

            $cityName = strtolower(trim($row[1] ?? ''));
            $cityId   = $citiesMap[$cityName] ?? null;

            $data[] = [
                'lab_id'     => $labId,
                'name'       => $row[0],
                'fk_city_id' => $cityId,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($data)) {
            $db->table('phlebotomists')->insertBatch($data);
        }

        return redirect()->to(base_url('labs/' . $labId . '/phlebotomist'))
                         ->with('success', count($data) . ' phlebotomists imported successfully.');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to read file: ' . $e->getMessage());
    }
}

public function labPriceList()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $userId = session()->get('user_id');

    $db  = \Config\Database::connect();

    $lab = $db->table('labs')->where('user_id', $userId)->get()->getRowArray();

    if (!$lab) {
        return redirect()->to('/labDashboard/dashboard')->with('error', 'Lab not found.');
    }

    $tests = $db->table('lab_tests')
                ->where('lab_id', $lab['id'])
                ->orderBy('test_name', 'ASC')
                ->get()->getResultArray();

    return view('labDashboard/pricelist', [
        'lab'   => $lab,
        'tests' => $tests,
    ]);
}

public function history($labId)
{
    if (!session()->get('logged_in')) {
        return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
    }

    $logs = (new \App\Models\ActivityLogModel())->getHistory('lab', $labId);
    return $this->response->setJSON(['logs' => $logs]);
}
}