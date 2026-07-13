<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    // Show login page
    public function index()
    {
        return view('login/loginform');
    }

   // Handle login form submit
public function login()
{
    $email    = $this->request->getPost('email');
    $password = $this->request->getPost('password');

    $userModel = new UserModel();
    $user = $userModel->getUserByEmail($email);

    // Check user exists and password matches
    if (!$user || !password_verify($password, $user['password'])) {
        return redirect()->to('/login')->with('error', 'Invalid email or password');
    }

      if ($user['role'] === 'franchise') {
        $franchiseModel = new \App\Models\FranchiseModel();
        $franchise = $franchiseModel->where('user_id', $user['id'])->first();

        if ($franchise && (int)$franchise['is_deleted'] === 1) {
            return redirect()->to('/login')->with('error', 'This franchise account has been deleted. Please contact the lab admin.');
        }

        if ($franchise && $franchise['status'] === 'inactive') {
            return redirect()->to('/login')->with('error', 'This franchise account has been deactivated. Please contact the lab admin.');
        }

        if ($user['status'] === 'inactive') {
            return redirect()->to('/login')->with('error', 'Your account has been deactivated. Please contact the lab admin.');
        }
    }

    // Save user in session
    session()->set([
        'user_id'   => $user['id'],
        'user_name' => $user['name'],
        'user_role' => $user['role'],
        'logged_in' => true,
    ]);

    // ─── Redirect based on role ───────────────────────────────
   if ($user['role'] === 'admin') {
        return redirect()->to('/dbadmin/dashboard');
    }

    if ($user['role'] === 'franchise') {
        return redirect()->to('/franchiseDashboard/dashboard');
    }

    return redirect()->to('/labDashboard/dashboard');
    
}

    // Dashboard page
    public function dashboard()
    {
        // Block if not logged in
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('/dbadmin/dashboard');
    }

    // Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}