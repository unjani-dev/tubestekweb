<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ServerPlanModel;
use App\Models\ServerModel;

/**
 * Plan Management Controller
 * Manages server hosting plans (CRUD operations)
 */
class PlanManagement extends BaseController
{
    protected $planModel;

    public function __construct()
    {
        $this->planModel = new ServerPlanModel();
    }

    /**
     * List all plans
     */
    public function index()
    {
        $plans = $this->planModel->orderBy('price_per_month', 'ASC')->findAll();
        
        return view('admin/plans/index', ['plans' => $plans]);
    }

    /**
     * Create plan form
     */
    public function create()
    {
        return view('admin/plans/create');
    }

    /**
     * Store new plan
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'plan_name' => 'required|min_length[3]|max_length[100]',
            'cpu_cores' => 'required|is_natural_no_zero',
            'ram_gb' => 'required|is_natural_no_zero',
            'storage_gb' => 'required|is_natural_no_zero',
            'bandwidth_gb' => 'required|is_natural_no_zero',
            'price_per_hour' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $pricePerHour = $this->request->getPost('price_per_hour');
        
        $data = [
            'plan_name' => $this->request->getPost('plan_name'),
            'cpu_cores' => $this->request->getPost('cpu_cores'),
            'ram_gb' => $this->request->getPost('ram_gb'),
            'storage_gb' => $this->request->getPost('storage_gb'),
            'bandwidth_gb' => $this->request->getPost('bandwidth_gb'),
            'price_per_hour' => $pricePerHour,
            'price_per_month' => $this->planModel->calculateMonthlyPrice($pricePerHour),
            'description' => $this->request->getPost('description'),
            'status' => 'active'
        ];

        if ($this->planModel->insert($data)) {
            return redirect()->to('/admin/plans')
                ->with('success', 'Plan created successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create plan');
    }

    /**
     * Edit plan form
     */
    public function edit($id)
    {
        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            return redirect()->to('/admin/plans')
                ->with('error', 'Plan not found');
        }

        return view('admin/plans/edit', ['plan' => $plan]);
    }

    /**
     * Update plan
     */
    public function update($id)
    {
        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            return redirect()->to('/admin/plans')
                ->with('error', 'Plan not found');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'plan_name' => 'required|min_length[3]|max_length[100]',
            'cpu_cores' => 'required|is_natural_no_zero',
            'ram_gb' => 'required|is_natural_no_zero',
            'storage_gb' => 'required|is_natural_no_zero',
            'bandwidth_gb' => 'required|is_natural_no_zero',
            'price_per_hour' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $pricePerHour = $this->request->getPost('price_per_hour');
        
        $data = [
            'plan_name' => $this->request->getPost('plan_name'),
            'cpu_cores' => $this->request->getPost('cpu_cores'),
            'ram_gb' => $this->request->getPost('ram_gb'),
            'storage_gb' => $this->request->getPost('storage_gb'),
            'bandwidth_gb' => $this->request->getPost('bandwidth_gb'),
            'price_per_hour' => $pricePerHour,
            'price_per_month' => $this->planModel->calculateMonthlyPrice($pricePerHour),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->planModel->update($id, $data)) {
            return redirect()->to('/admin/plans')
                ->with('success', 'Plan updated successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update plan');
    }

    /**
     * Delete plan
     */
    public function delete($id)
    {
        // Check if plan is in use
        $serverModel = new ServerModel();
        $inUse = $serverModel->where('plan_id', $id)->first();

        if ($inUse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete plan that is currently in use by servers'
            ]);
        }

        if ($this->planModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plan deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete plan'
        ]);
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus($id)
    {
        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found'
            ]);
        }

        $newStatus = $plan['status'] === 'active' ? 'inactive' : 'active';

        if ($this->planModel->update($id, ['status' => $newStatus])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plan status updated successfully',
                'new_status' => $newStatus
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update plan status'
        ]);
    }
}