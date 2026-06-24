<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DomainModel;
use App\Models\DomainPricingModel;

/**
 * Admin Domain Management Controller
 */
class DomainController extends BaseController
{
    protected $domainModel;
    protected $domainPricingModel;

    public function __construct()
    {
        // Pastikan kamu sudah membuat model-model ini nanti
        $this->domainModel = new DomainModel();
        $this->domainPricingModel = new DomainPricingModel();
        $this->ensureDomainManagementSchema();
        $this->normalizeLegacyUsdPricing();
    }

    // =========================================================================
    // BAGIAN 1: MANAJEMEN DOMAIN USER
    // =========================================================================

    /**
     * Menampilkan semua domain milik user
     */
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Menggunakan query builder untuk join dengan tabel users dan domain_pricing
        $domains = $db->table('domains')
            ->select('domains.*, users.username, users.email, domain_pricing.tld')
            ->join('users', 'domains.user_id = users.id')
            ->join('domain_pricing', 'domains.tld_id = domain_pricing.id')
            ->orderBy('domains.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/domains/index', ['domains' => $domains]);
    }

    /**
     * Suspend domain milik user
     */
    public function suspend($id)
    {
        if ($this->domainModel->update($id, ['status' => 'suspended'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Domain suspended successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to suspend domain'
        ]);
    }

    /**
     * Activate domain milik user
     */
    public function activate($id)
    {
        if ($this->domainModel->update($id, ['status' => 'active'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Domain activated successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to activate domain'
        ]);
    }

    /**
     * Update domain detail dari Domain Manifest.
     */
    public function updateDomain($id)
    {
        $rules = [
            'status' => 'required|in_list[pending,active,expired,suspended]',
            'expiry_date' => 'required|valid_date[Y-m-d]',
            'ns1' => 'required|min_length[3]',
            'ns2' => 'required|min_length[3]',
            'auto_renew' => 'permit_empty|in_list[0,1]',
            'domain_lock' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', \Config\Services::validation()->getErrors());
        }

        $domain = $this->domainModel->find($id);
        if (!$domain) {
            return redirect()->back()->with('error', 'Domain not found.');
        }

        $updated = $this->domainModel->update($id, [
            'status' => $this->request->getPost('status'),
            'expiry_date' => $this->request->getPost('expiry_date') . ' 23:59:59',
            'ns1' => strtolower(trim((string) $this->request->getPost('ns1'))),
            'ns2' => strtolower(trim((string) $this->request->getPost('ns2'))),
            'auto_renew' => (int) ($this->request->getPost('auto_renew') ?? 0),
            'domain_lock' => (int) ($this->request->getPost('domain_lock') ?? 0),
        ]);

        if ($updated) {
            return redirect()->to('/admin/domains')->with('success', 'Domain updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update domain.');
    }


    // =========================================================================
    // BAGIAN 2: MANAJEMEN HARGA TLD (DOMAIN PRICING)
    // =========================================================================

    /**
     * Menampilkan daftar harga ekstensi domain (TLD)
     */
    public function pricing()
    {
        // 1. Inisialisasi Model
        $domainPricingModel = new DomainPricingModel();

        // 2. Ambil semua data TLD dari database (diurutkan berdasarkan yang terbaru)
        $tlds = $domainPricingModel->orderBy('created_at', 'DESC')->findAll();

        // 3. Bungkus data dalam array
        $data = [
            'tlds' => $tlds
        ];

        // 4. Kirim array $data ke view
        return view('admin/domains/pricing', $data);
    }

    /**
     * Menyimpan TLD baru
     */
    public function storeTld()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'tld' => 'required|is_unique[domain_pricing.tld]',
            'register_price' => 'required|numeric',
            'renew_price' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        // Pastikan TLD diawali dengan titik (contoh: .com)
        $tld = $this->request->getPost('tld');
        if (substr($tld, 0, 1) !== '.') {
            $tld = '.' . $tld;
        }

        $data = [
            'tld' => $tld,
            'register_price' => $this->request->getPost('register_price'),
            'renew_price' => $this->request->getPost('renew_price'),
            'status' => $this->request->getPost('status') ?? 'active'
        ];

        if ($this->domainPricingModel->insert($data)) {
            return redirect()->to('/admin/domains/pricing')
                ->with('success', 'TLD Pricing added successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to add TLD Pricing');
    }

    /**
     * Mengupdate data TLD yang sudah ada
     */
    public function updateTld($id)
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'register_price' => 'required|numeric',
            'renew_price' => 'required|numeric',
            'status' => 'required|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $data = [
            'register_price' => $this->request->getPost('register_price'),
            'renew_price' => $this->request->getPost('renew_price'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->domainPricingModel->update($id, $data)) {
            return redirect()->to('/admin/domains/pricing')
                ->with('success', 'TLD Pricing updated successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update TLD Pricing');
    }

    /**
     * Menghapus TLD
     */
    public function deleteTld($id)
    {
        // Cek apakah TLD sedang digunakan oleh domain user
        $inUse = $this->domainModel->where('tld_id', $id)->first();

        if ($inUse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete TLD because it is currently in use by registered domains'
            ]);
        }

        if ($this->domainPricingModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'TLD deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete TLD'
        ]);
    }

    private function normalizeLegacyUsdPricing(): void
    {
        $usdDefaults = [
            '.com' => ['register_price' => 9.99, 'renew_price' => 10.99],
            '.net' => ['register_price' => 11.99, 'renew_price' => 12.99],
            '.my.id' => ['register_price' => 1.49, 'renew_price' => 1.49],
            '.web.id' => ['register_price' => 3.49, 'renew_price' => 3.49],
        ];

        foreach ($usdDefaults as $tld => $prices) {
            $row = $this->domainPricingModel->where('tld', $tld)->first();
            if ($row && ((float) $row['register_price'] > 1000 || (float) $row['renew_price'] > 1000)) {
                $this->domainPricingModel->update($row['id'], $prices);
            }
        }
    }

    private function ensureDomainManagementSchema(): void
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('domains')) {
            return;
        }

        $fields = $db->getFieldNames('domains');
        $columns = [
            'domain_lock' => "ALTER TABLE `domains` ADD `domain_lock` TINYINT(1) DEFAULT 1 AFTER `auto_renew`",
            'epp_code' => "ALTER TABLE `domains` ADD `epp_code` VARCHAR(64) DEFAULT NULL AFTER `domain_lock`",
            'epp_revealed_at' => "ALTER TABLE `domains` ADD `epp_revealed_at` DATETIME DEFAULT NULL AFTER `epp_code`",
            'nameserver_mode' => "ALTER TABLE `domains` ADD `nameserver_mode` ENUM('default','custom') DEFAULT 'default' AFTER `ns2`",
            'nameserver_status' => "ALTER TABLE `domains` ADD `nameserver_status` ENUM('pending','pointed','warning') DEFAULT 'pointed' AFTER `nameserver_mode`",
        ];

        foreach ($columns as $column => $sql) {
            if (!in_array($column, $fields, true)) {
                $db->query($sql);
            }
        }
    }
}
