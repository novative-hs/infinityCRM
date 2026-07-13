<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['entity_type', 'entity_id', 'action', 'description', 'performed_by', 'created_at'];
    protected $useTimestamps = false;

    public function record($entityType, $entityId, $action, $description = null, $performedBy = null)
    {
        return $this->insert([
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'action'       => $action,
            'description'  => $description,
            'performed_by' => $performedBy ?? session()->get('user_id'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function getHistory($entityType, $entityId)
    {
        return $this->select('activity_logs.*, users.name as performed_by_name')
                    ->join('users', 'users.id = activity_logs.performed_by', 'left')
                    ->where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->orderBy('activity_logs.created_at', 'DESC')
                    ->findAll();
    }
}