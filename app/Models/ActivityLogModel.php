<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Activity Log Model - Enterprise Grade (Bug Fix)
 */
class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'created_at' 
    ];

    // MATIKAN TIMESTAMPS OTOMATIS AGAR TIDAK ERROR SQL 1064
    protected $useTimestamps = false;
    protected $skipValidation = true;

    /**
     * Log an activity (Tanggal dimasukkan manual)
     */
    public function logActivity(array $data)
    {
        $logData = [
            'user_id'     => $data['user_id'] ?? null,
            'action'      => $data['action'],
            'description' => $data['description'] ?? null,
            'ip_address'  => $data['ip_address'] ?? null,
            'user_agent'  => $data['user_agent'] ?? null,
            'created_at'  => date('Y-m-d H:i:s') // ISI TANGGAL SECARA MANUAL
        ];

        return $this->insert($logData);
    }

    public function getUserActivities(int $userId, int $limit = 50)
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    public function getAllActivities(int $limit = 100, int $offset = 0)
    {
        return $this->select('activity_logs.*, users.username, users.email, users.role')
                    ->join('users', 'activity_logs.user_id = users.id', 'left')
                    ->orderBy('activity_logs.created_at', 'DESC')->limit($limit, $offset)->findAll();
    }

    public function getByAction(string $action, int $limit = 50)
    {
        return $this->where('action', $action)->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    public function getRecentLogins(int $limit = 20)
    {
        return $this->select('activity_logs.*, users.username, users.email')
                    ->join('users', 'activity_logs.user_id = users.id', 'left')
                    ->whereIn('action', ['login', 'login_failed'])
                    ->orderBy('activity_logs.created_at', 'DESC')->limit($limit)->findAll();
    }

    public function getSecurityActivities(int $limit = 50)
    {
        return $this->select('activity_logs.*, users.username')
                    ->join('users', 'activity_logs.user_id = users.id', 'left')
                    ->whereIn('action', ['login_failed', 'unauthorized_access', 'unauthorized_admin_access', 'user_suspended', 'user_deleted', 'password_reset'])
                    ->orderBy('activity_logs.created_at', 'DESC')->limit($limit)->findAll();
    }

    public function getActivitiesByDateRange(string $startDate, string $endDate, ?int $userId = null)
    {
        $builder = $this->select('activity_logs.*, users.username')
                        ->join('users', 'activity_logs.user_id = users.id', 'left')
                        ->where('activity_logs.created_at >=', $startDate)
                        ->where('activity_logs.created_at <=', $endDate);

        if ($userId) $builder->where('activity_logs.user_id', $userId);
        return $builder->orderBy('activity_logs.created_at', 'DESC')->findAll();
    }

    public function getActivityStats()
    {
        $db = \Config\Database::connect();
        
        return [
            'total' => $this->countAllResults(false),
            'today' => $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
            'active_users' => $db->table('activity_logs')->select('user_id, users.username, COUNT(*) as activity_count')->join('users', 'activity_logs.user_id = users.id')->where('activity_logs.user_id IS NOT NULL')->groupBy('activity_logs.user_id')->orderBy('activity_count', 'DESC')->limit(5)->get()->getResultArray(),
            'common_actions' => $db->table('activity_logs')->select('action, COUNT(*) as count')->groupBy('action')->orderBy('count', 'DESC')->limit(10)->get()->getResultArray()
        ];
    }

    public function cleanOldLogs(int $days = 90)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }

    public function searchActivities(string $keyword, int $limit = 50)
    {
        return $this->select('activity_logs.*, users.username')
                    ->join('users', 'activity_logs.user_id = users.id', 'left')
                    ->groupStart()
                        ->like('activity_logs.action', $keyword)
                        ->orLike('activity_logs.description', $keyword)
                        ->orLike('users.username', $keyword)
                    ->groupEnd()
                    ->orderBy('activity_logs.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getIpStats(int $limit = 20)
    {
        $db = \Config\Database::connect();
        return $db->table('activity_logs')
                 ->select('ip_address, COUNT(*) as count, MAX(created_at) as last_seen')
                 ->where('ip_address IS NOT NULL')
                 ->groupBy('ip_address')
                 ->orderBy('count', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }

    public function exportToCSV(string $startDate, string $endDate)
    {
        $activities = $this->getActivitiesByDateRange($startDate, $endDate);
        $csv = "ID,Username,Action,Description,IP Address,Created At\n";
        
        foreach ($activities as $activity) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s\n",
                $activity['id'],
                $activity['username'] ?? 'N/A',
                $activity['action'],
                str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $activity['description'] ?? ''),
                $activity['ip_address'] ?? 'N/A',
                $activity['created_at']
            );
        }
        return $csv;
    }
}