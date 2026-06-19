use App\Models\ShareTokenModel;







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
