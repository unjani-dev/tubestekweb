<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainPricingModel extends Model
{
    protected $table            = 'domain_pricing';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // Kolom yang diizinkan
    protected $allowedFields    = [
        'tld', 
        'register_price', 
        'renew_price', 
        'status'
    ];

    // Pengaturan Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}