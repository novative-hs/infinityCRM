<?php
namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table         = 'cities';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'status'];

    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = '';
}