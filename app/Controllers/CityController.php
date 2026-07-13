<?php
namespace App\Controllers;

use App\Models\CityModel;

class CityController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) return redirect()->to('/login');

        $model = new CityModel();
        return view('dbadmin/cities', [
            'cities' => $model->orderBy('name', 'ASC')->findAll(),
            'count'  => $model->countAll(),
        ]);
    }

    public function import()
    {
        if (!session()->get('logged_in')) return redirect()->to('/login');

        $file = $this->request->getFile('excel_file');
        if (!$file || !$file->isValid()) {
            return redirect()->to('/cities')->with('error', 'Invalid file.');
        }

        $ext = strtolower($file->getClientExtension());
        $tmpPath = $file->getTempName();

        $rows = [];
        if ($ext === 'csv') {
            if (($handle = fopen($tmpPath, 'r')) !== false) {
                $first = true;
                while (($line = fgetcsv($handle)) !== false) {
                    if ($first) { $first = false; continue; } // skip header
                    if (!empty($line[0])) $rows[] = trim($line[0]);
                }
                fclose($handle);
            }
        } else {
            // xlsx/xls — use PhpSpreadsheet if available
            require_once APPPATH . '../vendor/autoload.php';
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            array_shift($sheet); // skip header
            foreach ($sheet as $row) {
                if (!empty($row[0])) $rows[] = trim($row[0]);
            }
        }

        $model = new CityModel();
        $now   = date('Y-m-d H:i:s');
        foreach ($rows as $cityName) {
            // avoid duplicates
            if (!$model->where('name', $cityName)->first()) {
                $model->insert(['name' => $cityName, 'status' => 'active']);
            }
        }

        return redirect()->to('/cities')->with('success', count($rows) . ' cities imported successfully.');
    }

    public function add()
    {
        if (!session()->get('logged_in')) return redirect()->to('/login');

        $name = trim($this->request->getPost('name'));
        if (empty($name)) {
            return redirect()->to('/cities')->with('error', 'City name is required.');
        }

        $model = new CityModel();
        if ($model->where('name', $name)->first()) {
            return redirect()->to('/cities')->with('error', 'City already exists.');
        }

        $model->insert(['name' => $name, 'status' => 'active']);
        return redirect()->to('/cities')->with('success', 'City added successfully.');
    }
}
