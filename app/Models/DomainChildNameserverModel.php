<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainChildNameserverModel extends Model
{
    protected $table            = 'domain_child_nameservers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'domain_id',
        'hostname',
        'ipv4',
        'ipv6',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $skipValidation = true;
}
