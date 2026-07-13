<?php

namespace App\Controllers;

use App\Models\LocationModel;
use CodeIgniter\RESTful\ResourceController;

class LocationApi extends ResourceController
{
    protected $format = 'json';

    /**
     * POST /api/location/update
     * Called by the phlebotomist's page every few seconds.
     * Body (JSON or form): booking_id, lat, lng, accuracy
     */
 public function update($id = null)
{
    $bookingId = $this->request->getPost('booking_id') ?? $this->request->getJSON(true)['booking_id'] ?? null;
    $lat       = $this->request->getPost('lat')        ?? $this->request->getJSON(true)['lat'] ?? null;
    $lng       = $this->request->getPost('lng')        ?? $this->request->getJSON(true)['lng'] ?? null;
    $accuracy  = $this->request->getPost('accuracy')   ?? $this->request->getJSON(true)['accuracy'] ?? null;

    if (!$bookingId || $lat === null || $lng === null) {
        return $this->failValidationErrors('booking_id, lat and lng are required');
    }

    $model = new LocationModel();
    $model->upsertLocation($bookingId, (float) $lat, (float) $lng, $accuracy !== null ? (float) $accuracy : null);

    return $this->respond(['status' => 'ok']);
}

    /**
     * GET /api/location/(:segment)
     * Called by the patient's tracking page every few seconds (polling).
     */
    public function show($bookingId = null)
    {
        if (!$bookingId) {
            return $this->failValidationErrors('booking_id is required');
        }

        // TODO: verify the requester holds a valid tracking token for this
        // booking (e.g. a signed token in the URL) before returning location data.

        $model = new LocationModel();
        $location = $model->getLatestLocation($bookingId);

        if (!$location) {
            return $this->respond(['status' => 'waiting', 'location' => null]);
        }

        return $this->respond([
            'status' => 'ok',
            'location' => [
                'lat'        => (float) $location['lat'],
                'lng'        => (float) $location['lng'],
                'accuracy'   => $location['accuracy'] !== null ? (float) $location['accuracy'] : null,
                'updated_at' => $location['updated_at'],
            ],
        ]);
    }
}