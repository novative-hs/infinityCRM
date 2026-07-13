<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table            = 'phlebotomist_locations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'booking_id',
        'lat',
        'lng',
        'accuracy',
        'updated_at',
    ];

    /**
     * Insert or update the single "latest location" row for a booking.
     */
    public function upsertLocation(string $bookingId, float $lat, float $lng, ?float $accuracy): void
    {
        $existing = $this->where('booking_id', $bookingId)->first();

        $data = [
            'booking_id' => $bookingId,
            'lat'        => $lat,
            'lng'        => $lng,
            'accuracy'   => $accuracy,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }

    public function getLatestLocation(string $bookingId): ?array
    {
        return $this->where('booking_id', $bookingId)->first();
    }
}