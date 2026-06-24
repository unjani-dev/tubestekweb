<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Server Plan Model
 * Manages hosting plans and pricing
 */
class ServerPlanModel extends Model
{
    protected $table            = 'server_plans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'plan_name',
        'cpu_cores',
        'ram_gb',
        'storage_gb',
        'bandwidth_gb',
        'price_per_hour',
        'price_per_month',
        'description',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'plan_name' => 'required|min_length[3]|max_length[100]',
        'cpu_cores' => 'required|is_natural_no_zero',
        'ram_gb' => 'required|is_natural_no_zero',
        'storage_gb' => 'required|is_natural_no_zero',
        'price_per_hour' => 'required|decimal',
        'price_per_month' => 'required|decimal'
    ];

    protected $validationMessages = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active plans
     */
    public function getActivePlans()
    {
        return $this->where('status', 'active')
                    ->orderBy('price_per_month', 'ASC')
                    ->findAll();
    }

    /**
     * Get plan by ID with validation
     */
    public function getPlan($id)
    {
        return $this->where('id', $id)
                    ->where('status', 'active')
                    ->first();
    }

    /**
     * Calculate monthly price from hourly
     */
    public function calculateMonthlyPrice($hourlyPrice)
    {
        // Assuming 730 hours per month (365 days / 12 months * 24 hours)
        return round($hourlyPrice * 730, 2);
    }
}