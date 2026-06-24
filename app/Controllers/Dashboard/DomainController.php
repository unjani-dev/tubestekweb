<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\BillingTransactionModel;
use App\Models\DomainChildNameserverModel;
use App\Models\DomainDnsRecordModel;
use App\Models\DomainModel;
use App\Models\DomainPricingModel;
use App\Models\UserModel;

class DomainController extends BaseController
{
    protected $domainModel;
    protected $domainPricingModel;
    protected $userModel;
    protected $dnsRecordModel;
    protected $childNameserverModel;

    public function __construct()
    {
        $this->domainModel = new DomainModel();
        $this->domainPricingModel = new DomainPricingModel();
        $this->userModel = new UserModel();
        $this->dnsRecordModel = new DomainDnsRecordModel();
        $this->childNameserverModel = new DomainChildNameserverModel();
        $this->ensureDomainManagementSchema();
        $this->normalizeLegacyUsdPricing();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $data['domains'] = $this->domainModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();

        return view('dashboard/domains/index', $data);
    }

    public function search()
    {
        $query = $this->request->getGet('q');
        $data['searchQuery'] = $query;
        $data['tlds'] = $this->domainPricingModel->where('status', 'active')->findAll();

        if ($query) {
            $cleanQuery = strtolower(preg_replace('/[^a-z0-9.-]/', '', trim($query)));
            $baseName = strtolower(explode('.', $cleanQuery)[0]);
            $data['baseName'] = $baseName;
            $data['domainOptions'] = [];

            foreach ($data['tlds'] as $tld) {
                $candidate = $baseName . $tld['tld'];
                $isTaken = $this->domainModel->where('domain_name', $candidate)->first();
                $data['domainOptions'][] = [
                    'domain' => $candidate,
                    'available' => !$isTaken,
                    'price' => $tld['register_price'],
                    'renew_price' => $tld['renew_price'],
                    'tld_id' => $tld['id'],
                    'tld' => $tld['tld'],
                ];
            }

            $exactDomain = $cleanQuery;
            if (strpos($exactDomain, '.') === false && !empty($data['tlds'])) {
                $exactDomain = $baseName . $data['tlds'][0]['tld'];
            }

            $isTaken = $this->domainModel->where('domain_name', $exactDomain)->first();
            $data['exactMatch'] = [
                'domain' => $exactDomain,
                'available' => !$isTaken,
                'price' => !$isTaken ? $this->getPriceForTld($exactDomain, $data['tlds']) : 0,
                'tld_id' => $this->getTldId($exactDomain, $data['tlds']),
            ];
        }

        return view('dashboard/domains/search', $data);
    }

    public function purchase()
    {
        $userId = session()->get('user_id');
        $domainName = strtolower(trim((string) $this->request->getPost('domain_name')));
        $tldId = (int) $this->request->getPost('tld_id');
        $price = (float) $this->request->getPost('price');

        if (!$domainName || !$tldId || $price <= 0) {
            return redirect()->back()->with('error', 'Invalid domain checkout request.');
        }

        if ($this->domainModel->where('domain_name', $domainName)->first()) {
            return redirect()->back()->with('error', 'Domain is already registered.');
        }

        $user = $this->userModel->find($userId);
        if (!$user || $user['balance'] < $price) {
            return redirect()->back()->with('error', 'Insufficient balance. Please top-up your wallet.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $billingModel = new BillingTransactionModel();
        $referenceId = 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $billingModel->insert([
            'user_id' => $userId,
            'server_id' => null,
            'transaction_type' => 'charge',
            'amount' => $price,
            'description' => "Domain Registration: $domainName (1 Year)",
            'balance_before' => $user['balance'],
            'balance_after' => $user['balance'] - $price,
            'status' => 'completed',
            'reference_id' => $referenceId,
        ]);

        $this->userModel->adjustBalance($userId, $price, 'subtract');

        $this->domainModel->insert([
            'user_id' => $userId,
            'tld_id' => $tldId,
            'domain_name' => $domainName,
            'status' => 'active',
            'registration_date' => date('Y-m-d H:i:s'),
            'expiry_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
            'ns1' => 'ns1.cloudplatform.local',
            'ns2' => 'ns2.cloudplatform.local',
            'auto_renew' => 1,
            'domain_lock' => 1,
            'epp_code' => $this->generateEppCode($domainName),
            'nameserver_mode' => 'default',
            'nameserver_status' => 'pointed',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'System error during provisioning.');
        }

        return redirect()->to('/dashboard/domains')->with('success', 'Domain registered successfully!');
    }

    public function manage($id)
    {
        $domain = $this->getOwnedDomain((int) $id);
        if (!$domain) {
            return redirect()->to('/dashboard/domains')->with('error', 'Domain not found or access denied.');
        }

        $dnsRecords = $this->dnsRecordModel->where('domain_id', $domain['id'])->orderBy('record_type', 'ASC')->findAll();
        $childNameservers = $this->childNameserverModel->where('domain_id', $domain['id'])->orderBy('hostname', 'ASC')->findAll();

        return view('dashboard/domains/manage', [
            'domain' => $domain,
            'dnsRecords' => $dnsRecords,
            'childNameservers' => $childNameservers,
            'eppPreview' => $this->maskEppCode($domain['epp_code'] ?? ''),
        ]);
    }

    public function updateSettings($id)
    {
        $domain = $this->getOwnedDomain((int) $id);
        if (!$domain) {
            return redirect()->to('/dashboard/domains')->with('error', 'Domain not found or access denied.');
        }

        $action = $this->request->getPost('action');
        if ($action === 'toggle_renew') {
            $this->domainModel->update($id, ['auto_renew' => !empty($domain['auto_renew']) ? 0 : 1]);
            return redirect()->back()->with('success', 'Auto renewal updated successfully.');
        }

        if ($action === 'toggle_lock') {
            $this->domainModel->update($id, ['domain_lock' => !empty($domain['domain_lock']) ? 0 : 1]);
            return redirect()->back()->with('success', 'Domain lock updated successfully.');
        }

        $ns1 = strtolower(trim((string) $this->request->getPost('ns1')));
        $ns2 = strtolower(trim((string) $this->request->getPost('ns2')));

        if (!$this->isValidHostname($ns1) || !$this->isValidHostname($ns2)) {
            return redirect()->back()->with('error', 'Nameserver must be a valid hostname.');
        }

        $status = $this->simulateNameserverStatus($ns1, $ns2);
        $this->domainModel->update($id, [
            'ns1' => $ns1,
            'ns2' => $ns2,
            'nameserver_mode' => $this->isDefaultNameserver($ns1, $ns2) ? 'default' : 'custom',
            'nameserver_status' => $status,
        ]);

        return redirect()->back()->with('success', 'Nameserver configuration updated successfully.');
    }

    public function storeDnsRecord($id)
    {
        $domain = $this->getOwnedDomain((int) $id);
        if (!$domain) {
            return redirect()->to('/dashboard/domains')->with('error', 'Domain not found or access denied.');
        }

        $type = strtoupper(trim((string) $this->request->getPost('record_type')));
        $host = trim((string) $this->request->getPost('host'));
        $value = trim((string) $this->request->getPost('value'));
        $ttl = (int) $this->request->getPost('ttl') ?: 3600;
        $priority = $this->request->getPost('priority') !== '' ? (int) $this->request->getPost('priority') : null;

        if (!in_array($type, ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS'], true) || $host === '' || $value === '') {
            return redirect()->back()->with('error', 'DNS record type, host, and value are required.');
        }

        if ($type === 'MX' && $priority === null) {
            return redirect()->back()->with('error', 'MX record requires priority.');
        }

        $this->dnsRecordModel->insert([
            'domain_id' => $domain['id'],
            'record_type' => $type,
            'host' => $host,
            'value' => $value,
            'priority' => $priority,
            'ttl' => max(300, $ttl),
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'DNS record added successfully.');
    }

    public function deleteDnsRecord($domainId, $recordId)
    {
        $domain = $this->getOwnedDomain((int) $domainId);
        $record = $this->dnsRecordModel->where('id', (int) $recordId)->where('domain_id', (int) $domainId)->first();

        if (!$domain || !$record) {
            return redirect()->to('/dashboard/domains')->with('error', 'DNS record not found or access denied.');
        }

        $this->dnsRecordModel->delete((int) $recordId);
        return redirect()->back()->with('success', 'DNS record deleted successfully.');
    }

    public function storeChildNameserver($id)
    {
        $domain = $this->getOwnedDomain((int) $id);
        if (!$domain) {
            return redirect()->to('/dashboard/domains')->with('error', 'Domain not found or access denied.');
        }

        $hostname = strtolower(trim((string) $this->request->getPost('hostname')));
        $ipv4 = trim((string) $this->request->getPost('ipv4'));
        $ipv6 = trim((string) $this->request->getPost('ipv6'));

        if (!$this->isChildNameserverHostname($hostname, $domain['domain_name'])) {
            return redirect()->back()->with('error', 'Child nameserver hostname must be inside this domain.');
        }

        if (!filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return redirect()->back()->with('error', 'Provide at least one valid IPv4 or IPv6 glue address.');
        }

        $this->childNameserverModel->insert([
            'domain_id' => $domain['id'],
            'hostname' => $hostname,
            'ipv4' => $ipv4 ?: null,
            'ipv6' => $ipv6 ?: null,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Child nameserver registered successfully.');
    }

    public function deleteChildNameserver($domainId, $childId)
    {
        $domain = $this->getOwnedDomain((int) $domainId);
        $child = $this->childNameserverModel->where('id', (int) $childId)->where('domain_id', (int) $domainId)->first();

        if (!$domain || !$child) {
            return redirect()->to('/dashboard/domains')->with('error', 'Child nameserver not found or access denied.');
        }

        $this->childNameserverModel->delete((int) $childId);
        return redirect()->back()->with('success', 'Child nameserver deleted successfully.');
    }

    public function revealEpp($id)
    {
        $domain = $this->getOwnedDomain((int) $id);
        if (!$domain) {
            return redirect()->to('/dashboard/domains')->with('error', 'Domain not found or access denied.');
        }

        if (empty($domain['epp_code'])) {
            $domain['epp_code'] = $this->generateEppCode($domain['domain_name']);
        }

        $this->domainModel->update($id, [
            'epp_code' => $domain['epp_code'],
            'epp_revealed_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('epp_code', $domain['epp_code'])->with('success', 'EPP/Auth code revealed for simulation.');
    }

    public function regenerateEpp($id)
    {
        $domain = $this->getOwnedDomain((int) $id);
        if (!$domain) {
            return redirect()->to('/dashboard/domains')->with('error', 'Domain not found or access denied.');
        }

        $newCode = $this->generateEppCode($domain['domain_name']);
        $this->domainModel->update($id, [
            'epp_code' => $newCode,
            'epp_revealed_at' => null,
        ]);

        return redirect()->back()->with('success', 'New EPP/Auth code generated successfully.');
    }

    private function getOwnedDomain(int $id)
    {
        return $this->domainModel
            ->where('id', $id)
            ->where('user_id', session()->get('user_id'))
            ->first();
    }

    private function getPriceForTld($domain, $tlds)
    {
        foreach ($tlds as $tld) {
            if (str_ends_with($domain, $tld['tld'])) {
                return $tld['register_price'];
            }
        }

        return 0;
    }

    private function getTldId($domain, $tlds)
    {
        foreach ($tlds as $tld) {
            if (str_ends_with($domain, $tld['tld'])) {
                return $tld['id'];
            }
        }

        return 0;
    }

    private function generateEppCode(string $domainName): string
    {
        return strtoupper(substr(hash('sha256', $domainName . microtime(true) . random_int(1000, 9999)), 0, 6))
            . '-'
            . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    private function maskEppCode(string $code): string
    {
        if ($code === '') {
            return 'Not generated yet';
        }

        return substr($code, 0, 3) . '****' . substr($code, -4);
    }

    private function isValidHostname(string $hostname): bool
    {
        return (bool) preg_match('/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $hostname);
    }

    private function isDefaultNameserver(string $ns1, string $ns2): bool
    {
        return $ns1 === 'ns1.cloudplatform.local' && $ns2 === 'ns2.cloudplatform.local';
    }

    private function simulateNameserverStatus(string $ns1, string $ns2): string
    {
        if ($ns1 === $ns2) {
            return 'warning';
        }

        return ($this->isValidHostname($ns1) && $this->isValidHostname($ns2)) ? 'pointed' : 'pending';
    }

    private function isChildNameserverHostname(string $hostname, string $domainName): bool
    {
        return $this->isValidHostname($hostname) && str_ends_with($hostname, '.' . strtolower($domainName));
    }

    private function ensureDomainManagementSchema(): void
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('domains')) {
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

        if (!$db->tableExists('domain_dns_records')) {
            $db->query("
                CREATE TABLE `domain_dns_records` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `domain_id` INT(11) UNSIGNED NOT NULL,
                    `record_type` ENUM('A','AAAA','CNAME','MX','TXT','NS') NOT NULL,
                    `host` VARCHAR(255) NOT NULL,
                    `value` TEXT NOT NULL,
                    `priority` INT(11) DEFAULT NULL,
                    `ttl` INT(11) DEFAULT 3600,
                    `status` ENUM('active','disabled') DEFAULT 'active',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `domain_id` (`domain_id`),
                    CONSTRAINT `domain_dns_records_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!$db->tableExists('domain_child_nameservers')) {
            $db->query("
                CREATE TABLE `domain_child_nameservers` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `domain_id` INT(11) UNSIGNED NOT NULL,
                    `hostname` VARCHAR(255) NOT NULL,
                    `ipv4` VARCHAR(45) DEFAULT NULL,
                    `ipv6` VARCHAR(45) DEFAULT NULL,
                    `status` ENUM('active','disabled') DEFAULT 'active',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `domain_child_hostname` (`domain_id`, `hostname`),
                    KEY `domain_id` (`domain_id`),
                    CONSTRAINT `domain_child_nameservers_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
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
}
