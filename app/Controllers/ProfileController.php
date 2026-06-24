<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;

class ProfileController extends BaseController
{
    protected $userModel;
    protected $activityLog;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activityLog = new ActivityLogModel();
    }

    /**
     * Tampilkan halaman Profile
     */
    public function index()
    {
        $userId = session()->get('user_id');
        
        $data = [
            'user' => $this->userModel->find($userId)
        ];
        
        return view('dashboard/profile/index', $data);
    }

    /**
     * Proses update data diri
     */
    public function update()
    {
        $userId = session()->get('user_id');
        
        // Aturan validasi
        $rules = [
            'full_name' => 'required|min_length[3]',
            'phone'     => 'permit_empty|min_length[9]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Ambil data dari form
        $updateData = [
            'full_name' => $this->request->getPost('full_name'),
            'phone'     => $this->request->getPost('phone')
        ];

        // Simpan ke database
        $this->userModel->update($userId, $updateData);

        // Update session agar nama di pojok atas ikut berubah
        session()->set('full_name', $updateData['full_name']);

        // Catat aktivitas
        $this->activityLog->logActivity([
            'user_id'     => $userId,
            'action'      => 'profile_update',
            'description' => 'User updated their profile information',
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => $this->request->getUserAgent()->getAgentString()
        ]);

        return redirect()->to('/dashboard/profile')->with('success', 'Profile updated successfully!');
    }

    /**
     * Proses ganti Password
     */
    public function changePassword()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        // Aturan validasi password
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Cek apakah password lama benar
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Current password is incorrect!');
        }

        // Simpan password baru (Otomatis di-hash oleh fungsi beforeUpdate di UserModel)
        $this->userModel->update($userId, [
            'password' => $newPassword 
        ]);

        // Catat aktivitas keamanan
        $this->activityLog->logActivity([
            'user_id'     => $userId,
            'action'      => 'password_change',
            'description' => 'User changed their account password',
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => $this->request->getUserAgent()->getAgentString()
        ]);

        return redirect()->to('/dashboard/profile')->with('success', 'Password changed successfully! You can use it next time you login.');
    }
}