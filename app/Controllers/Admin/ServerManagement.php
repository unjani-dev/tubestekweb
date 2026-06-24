<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ServerModel;

class ServerManagement extends BaseController
{
    protected $serverModel;

    public function __construct()
    {
        $this->serverModel = new ServerModel();
    }

    /**
     * Tampilkan semua instance dari seluruh user (Read-Only)
     */
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Mengambil data server gabungan dengan User dan Plan
        $data['servers'] = $db->table('servers')
            ->select('servers.*, users.username, users.full_name, server_plans.plan_name')
            ->join('users', 'servers.user_id = users.id')
            ->join('server_plans', 'servers.plan_id = server_plans.id')
            ->orderBy('servers.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Statistik ringkas untuk header
        $data['stats'] = [
            'total' => count($data['servers']),
            'running' => count(array_filter($data['servers'], fn($s) => $s['status'] === 'running')),
            'stopped' => count(array_filter($data['servers'], fn($s) => $s['status'] === 'stopped')),
        ];

        return view('admin/servers/index', $data);
    }

    /**
     * View Details Saja (Tanpa Fungsi Action)
     */
    public function show($id)
    {
        $db = \Config\Database::connect();
        $server = $db->table('servers')
            ->select('servers.*, users.username, users.email, server_plans.plan_name, server_plans.cpu_cores as p_cpu, server_plans.ram_gb as p_ram')
            ->join('users', 'servers.user_id = users.id')
            ->join('server_plans', 'servers.plan_id = server_plans.id')
            ->where('servers.id', $id)
            ->get()
            ->getRowArray();

        if (!$server) {
            return redirect()->to('/admin/servers')->with('error', 'Instance not found.');
        }

        return view('admin/servers/show', ['server' => $server]);
    }
}