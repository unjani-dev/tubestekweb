<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainDnsRecordModel extends Model
{
    protected $table            = 'domain_dns_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'domain_id',
        'record_type',
        'host',
        'value',
        'priority',
        'ttl',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $skipValidation = true;
}
