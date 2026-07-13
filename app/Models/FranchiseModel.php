<?php

namespace App\Models;

use CodeIgniter\Model;

class FranchiseModel extends Model
{
    protected $table         = 'franchises';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id', 'lab_id', 'city_id', 'contact_number', 'discount',
        'status', 'is_deleted', 'created_at', 'updated_at'
    ];

    public function createFranchise($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function getFranchisesBySessionLab(): array
    {
        $userId = session()->get('user_id');

        $db = \Config\Database::connect();

        $lab = $db->table('labs')
                  ->where('user_id', $userId)
                  ->get()
                  ->getRowArray();

        if (!$lab) {
            return [];
        }

        return $db->table('franchises f')
            ->select('f.id, f.contact_number, f.discount, f.status, c.name as city_name, u.name as franchise_name')
            ->join('cities c', 'c.id = f.city_id', 'left')
            ->join('users u', 'u.id = f.user_id', 'left')
            ->where('f.lab_id', $lab['id'])
            ->where('f.status', 'active')
            ->where('f.is_deleted', 0)   
            ->get()
            ->getResultArray();
    }

    public function isDeletedByUserId(int $userId): bool
    {
        $row = $this->where('user_id', $userId)->first();
        return $row ? (bool)$row['is_deleted'] : false;
    }
}