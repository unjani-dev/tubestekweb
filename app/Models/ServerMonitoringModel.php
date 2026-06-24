<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Server Monitoring Model
 * Stores server performance metrics
 */
class ServerMonitoringModel extends Model
{
    protected $table            = 'server_monitoring';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'server_id',
        'cpu_usage',
        'ram_usage',
        'disk_usage',
        'network_in',
        'network_out',
        'recorded_at'
    ];

    protected $useTimestamps = false;

    /**
     * Record server metrics (simulated)
     */
    public function recordMetrics(int $serverId)
    {
        return $this->insert([
            'server_id' => $serverId,
            'cpu_usage' => rand(10, 90) + (rand(0, 99) / 100),
            'ram_usage' => rand(20, 85) + (rand(0, 99) / 100),
            'disk_usage' => rand(15, 75) + (rand(0, 99) / 100),
            'network_in' => rand(100, 5000) / 100,
            'network_out' => rand(100, 5000) / 100,
            'recorded_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get latest metrics for server
     */
    public function getLatestMetrics(int $serverId, int $hours = 24)
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->where('server_id', $serverId)
                    ->where('recorded_at >=', $since)
                    ->orderBy('recorded_at', 'ASC')
                    ->findAll();
    }

    /**
     * Clean old monitoring data (older than 30 days)
     */
    public function cleanOldData()
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        return $this->where('recorded_at <', $cutoffDate)->delete();
    }
}

/**
 * Activity Log Model
 * Logs all user and system activities
 */
