<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainModel extends Model
{
    protected $table            = 'domains';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // Kolom yang diizinkan untuk diisi secara massal
    protected $allowedFields    = [
        'user_id', 
        'tld_id', 
        'domain_name', 
        'status', 
        'registration_date', 
        'expiry_date', 
        'auto_renew', 
        'ns1', 
        'ns2',
        'domain_lock',
        'epp_code',
        'epp_revealed_at',
        'nameserver_mode',
        'nameserver_status'
    ];

    // Pengaturan Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Opsi tambahan jika ingin melakukan relasi langsung di model
    public function getDomainWithDetails($id = null)
    {
        $builder = $this->select('domains.*, users.username, users.email, domain_pricing.tld')
                        ->join('users', 'users.id = domains.user_id')
                        ->join('domain_pricing', 'domain_pricing.id = domains.tld_id');
        
        if ($id) {
            return $builder->where('domains.id', $id)->first();
        }
        
        return $builder->findAll();
    }
}
