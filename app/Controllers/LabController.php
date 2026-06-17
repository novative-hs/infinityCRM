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

 public function sampleCollected($id = null)
    {
        // Sample data - in real application, fetch from database using $id
        $data = [
            'booking_id' => 'cmqfan2e2000zt5083orfxtd1j',
            'patient_name' => 'Nadia Tariq',
            'phone' => '+923228117733',
            'address' => 'House 90 Dream Villas Society',
            'gender' => 'Female',
            'notes' => '16 june 3 pm',
            'phlebotomist' => 'Khawar',
            'eta' => '16 Jun 2026, 3:00 PM',
            'tests' => [
                [
                    'code' => '6668',
                    'name' => 'LFTs (T-Bili, ALT, AST, ALP, ALB, GGT, T-Prot, Globulins, A/G)',
                    'reporting_time' => '—',
                    'price' => 'PKR 1,365',
                    'payment' => 'Cash'
                ],
                [
                    'code' => '4303',
                    'name' => 'Beta HCG',
                    'reporting_time' => 'Same Day After 3 Hour',
                    'price' => 'PKR 1,575',
                    'payment' => 'Pending'
                ]
            ],
            'original_total' => '4,200',
            'discount' => '1,260',
            'patient_pays' => 'PKR 2,940',
            'status_history' => ['In Process', 'Phlebotomist Assigned', 'Phlebotomist Arrived', 'Sample Collected'],
            'created_at' => 'Jun 15, 2026 7:13 PM',
            'created_by' => 'Marham Agent',
            'assigned_to' => 'INFINITY Lab'
        ];

        return view('labDashboard/sample_collected', $data);        
    }

    private function getSampleBookingData($id)
    {
        return (object) [
            'id' => $id,
            'patient_name' => 'Nadia Tariq',
            'patient_phone' => '+923228117733',
            'patient_address' => 'House 90 Dream Villas Society',
            'patient_gender' => 'Female',
            'phlebotomist' => 'Khawar',
            'eta' => '16 Jun 2026, 3:00 PM',
            'status' => 'Sample Collected',
            'created_at' => 'Jun 15, 2026 7:13 PM',
            'created_by' => 'Marham Agent',
            'assigned_to' => 'INFINITY Lab',
            'booking_id' => 'cmqfan2e2000zt5083orfxtd1j',
            'notes' => '16 june 3 pm',
            'total_original' => 4200,
            'discount' => 1260,
            'patient_pays' => 2940
        ];
    }

    private function getSampleTestsData()
    {
        return [
            (object) [
                'code' => '6668',
                'name' => 'LFTs (T-Bili, ALT, AST, ALP, ALB, GGT, T-Prot, Globulins, A/G)',
                'reporting_time' => '—',
                'price' => 1365,
                'payment_status' => 'Cash'
            ],
            (object) [
                'code' => '4303',
                'name' => 'Beta HCG',
                'reporting_time' => 'Same Day After 3 Hour',
                'price' => 1575,
                'payment_status' => 'Pending'
            ]
        ];
    }
}