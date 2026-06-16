<?php

namespace App\Controllers;

use App\Models\UserModel;

class LabController extends BaseController
{

public function dashboard()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    return view('labDashboard/dashboard');
}
}