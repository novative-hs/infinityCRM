<?php

namespace App\Controllers;

class TrackController extends BaseController
{
    public function redirect($token = null)
    {
        if (!$token) {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();
        $row = $db->table('phlebotomist_locations')
            ->where('token', $token)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setBody('Link not found or expired.');
        }

        return redirect()->to(base_url('track.html') . '?booking_id=' . $row['booking_id']);
    }
}