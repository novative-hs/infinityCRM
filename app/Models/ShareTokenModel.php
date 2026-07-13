<?php

namespace App\Models;

use CodeIgniter\Model;

class ShareTokenModel extends Model
{
    protected $table            = 'share_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'booking_id',
        'token',
        'created_at',
        'expires_at',
        'view_count',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'booking_id' => 'required|integer',
        'token'      => 'required|string|max_length[64]',
        'expires_at' => 'required|valid_date',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
}