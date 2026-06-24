<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Domain - Flizzy Cloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; }
        .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.035); }
        .field { width: 100%; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; color: #0f172a; font-weight: 700; outline: none; }
        .field:focus { border-color: #4f46e5; background: #fff; }
        .label { display: block; color: #64748b; font-size: 0.72rem; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; }
        @media (max-width: 768px) { .content-area { padding: 20px !important; } }
    </style>
</head>
<body class="bg-[#f0f2f5] text-slate-700">

<?php
    $dnsRecords = $dnsRecords ?? [];
    $childNameservers = $childNameservers ?? [];
    $statusClass = [
        'pointed' => 'bg-emerald-100 text-emerald-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'warning' => 'bg-orange-100 text-orange-700',
    ];
    $nsStatus = $domain['nameserver_status'] ?? 'pointed';
?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('dashboard/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area min-h-screen">
            <?php if(!isset($domain) || empty($domain)): ?>
                <div class="panel p-8 text-center max-w-2xl mx-auto mt-10">
                    <i class="bi bi-exclamation-triangle-fill text-5xl text-rose-500 mb-4 block"></i>
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Domain Data Missing</h2>
                    <p class="text-slate-500 font-medium mb-6">Domain tidak ditemukan atau bukan milik akun ini.</p>
                    <a href="<?= base_url('dashboard/domains') ?>" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition">Back to Domains</a>
                </div>
            <?php else: ?>

                <div class="mb-6">
                    <a href="<?= base_url('dashboard/domains') ?>" class="text-slate-500 hover:text-indigo-600 text-sm font-bold mb-4 inline-flex items-center transition">
                        <i class="bi bi-arrow-left me-2"></i> Back to Domains
                    </a>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-3xl font-extrabold text-slate-900"><?= esc($domain['domain_name']) ?></h1>
                                <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase <?= ($domain['status'] ?? '') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <?= esc($domain['status'] ?? 'unknown') ?>
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 mt-2 font-bold">
                                <i class="bi bi-calendar-event me-2"></i>Expires <?= date('d M Y', strtotime($domain['expiry_date'] ?? date('Y-m-d'))) ?>
                            </p>
                        </div>
                        <span class="px-4 py-2 rounded-xl text-xs font-extrabold uppercase <?= $statusClass[$nsStatus] ?? 'bg-slate-100 text-slate-600' ?>">
                            NS <?= esc($nsStatus) ?>
                        </span>
                    </div>
                </div>

                <?php if(session()->getFlashdata('success')): ?>
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-bold">
                        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <?php if(session()->getFlashdata('error')): ?>
                    <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-bold">
                        <i class="bi bi-exclamation-circle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 space-y-6">
                        <section class="panel p-6 md:p-7">
                            <div class="flex items-center justify-between gap-4 mb-6">
                                <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase"><i class="bi bi-hdd-network-fill me-2"></i>Nameserver & Pointing</h2>
                                <span class="text-xs font-bold text-slate-500"><?= esc($domain['nameserver_mode'] ?? 'default') ?> mode</span>
                            </div>
                            <form action="<?= base_url('dashboard/domains/update-settings/' . $domain['id']) ?>" method="POST" class="space-y-5">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="label">Nameserver 1</label>
                                        <input type="text" name="ns1" value="<?= esc($domain['ns1'] ?? 'ns1.cloudplatform.local') ?>" class="field font-mono text-sm">
                                    </div>
                                    <div>
                                        <label class="label">Nameserver 2</label>
                                        <input type="text" name="ns2" value="<?= esc($domain['ns2'] ?? 'ns2.cloudplatform.local') ?>" class="field font-mono text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                        <p class="text-xs text-slate-500 font-bold mb-1">SIMULATION CHECK</p>
                                        <p class="font-extrabold text-slate-900"><?= $nsStatus === 'pointed' ? 'Resolved' : ($nsStatus === 'warning' ? 'Needs review' : 'Pending') ?></p>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                        <p class="text-xs text-slate-500 font-bold mb-1">DNS RECORDS</p>
                                        <p class="font-extrabold text-slate-900"><?= count($dnsRecords) ?> active rows</p>
                                    </div>
                                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                        <p class="text-xs text-slate-500 font-bold mb-1">CHILD NS</p>
                                        <p class="font-extrabold text-slate-900"><?= count($childNameservers) ?> registered</p>
                                    </div>
                                </div>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-7 py-3 rounded-xl transition text-sm">
                                    <i class="bi bi-save-fill me-2"></i>Save Nameservers
                                </button>
                            </form>
                        </section>

                        <section class="panel p-6 md:p-7">
                            <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase mb-6"><i class="bi bi-diagram-3-fill me-2"></i>DNS Zone</h2>
                            <form action="<?= base_url('dashboard/domains/manage/' . $domain['id'] . '/dns') ?>" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-6">
                                <?= csrf_field() ?>
                                <div class="md:col-span-2">
                                    <label class="label">Type</label>
                                    <select name="record_type" class="field">
                                        <?php foreach(['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS'] as $type): ?>
                                            <option value="<?= $type ?>"><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="label">Host</label>
                                    <input type="text" name="host" placeholder="@ or www" class="field">
                                </div>
                                <div class="md:col-span-4">
                                    <label class="label">Value</label>
                                    <input type="text" name="value" placeholder="IP, hostname, or text value" class="field">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="label">Prio</label>
                                    <input type="number" name="priority" placeholder="10" class="field">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="label">TTL</label>
                                    <input type="number" name="ttl" value="3600" class="field">
                                </div>
                                <div class="md:col-span-1 flex items-end">
                                    <button type="submit" class="w-full bg-slate-900 hover:bg-indigo-700 text-white font-bold rounded-xl py-3 transition">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </form>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-xs uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                            <th class="py-3">Type</th>
                                            <th>Host</th>
                                            <th>Value</th>
                                            <th>TTL</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($dnsRecords)): ?>
                                            <tr><td colspan="5" class="py-8 text-center text-slate-400 font-bold">No DNS records yet.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($dnsRecords as $record): ?>
                                                <tr class="border-b border-slate-100">
                                                    <td class="py-4 font-extrabold text-indigo-600"><?= esc($record['record_type']) ?></td>
                                                    <td class="font-mono text-slate-700"><?= esc($record['host']) ?></td>
                                                    <td class="font-mono text-slate-700"><?= esc($record['value']) ?><?= $record['priority'] !== null ? ' (prio ' . esc($record['priority']) . ')' : '' ?></td>
                                                    <td class="font-bold text-slate-500"><?= esc($record['ttl']) ?></td>
                                                    <td class="text-right">
                                                        <form action="<?= base_url('dashboard/domains/manage/' . $domain['id'] . '/dns/' . $record['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this DNS record?')" class="inline">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold"><i class="bi bi-trash-fill"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="panel p-6 md:p-7">
                            <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase mb-6"><i class="bi bi-signpost-split-fill me-2"></i>Child Nameserver / Glue Records</h2>
                            <form action="<?= base_url('dashboard/domains/manage/' . $domain['id'] . '/child-ns') ?>" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-6">
                                <?= csrf_field() ?>
                                <div class="md:col-span-5">
                                    <label class="label">Hostname</label>
                                    <input type="text" name="hostname" placeholder="ns1.<?= esc($domain['domain_name']) ?>" class="field font-mono text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="label">IPv4 Glue</label>
                                    <input type="text" name="ipv4" placeholder="192.0.2.10" class="field font-mono text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="label">IPv6 Glue</label>
                                    <input type="text" name="ipv6" placeholder="2001:db8::10" class="field font-mono text-sm">
                                </div>
                                <div class="md:col-span-1 flex items-end">
                                    <button type="submit" class="w-full bg-slate-900 hover:bg-indigo-700 text-white font-bold rounded-xl py-3 transition">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </form>

                            <div class="space-y-3">
                                <?php if(empty($childNameservers)): ?>
                                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 text-center text-slate-400 font-bold">No child nameservers registered.</div>
                                <?php else: ?>
                                    <?php foreach($childNameservers as $child): ?>
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                            <div>
                                                <p class="font-extrabold text-slate-900 font-mono"><?= esc($child['hostname']) ?></p>
                                                <p class="text-xs text-slate-500 font-bold mt-1">
                                                    IPv4: <?= esc($child['ipv4'] ?: '-') ?> / IPv6: <?= esc($child['ipv6'] ?: '-') ?>
                                                </p>
                                            </div>
                                            <form action="<?= base_url('dashboard/domains/manage/' . $domain['id'] . '/child-ns/' . $child['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this child nameserver?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-sm"><i class="bi bi-trash-fill me-1"></i>Delete</button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                    <div class="space-y-6">
                        <section class="panel p-6">
                            <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase mb-5"><i class="bi bi-info-circle-fill me-2"></i>Domain Info</h2>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">REGISTERED</p>
                                    <p class="font-extrabold text-slate-900"><?= date('d M Y', strtotime($domain['registration_date'] ?? date('Y-m-d'))) ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">EXPIRES</p>
                                    <p class="font-extrabold text-slate-900"><?= date('d M Y', strtotime($domain['expiry_date'] ?? date('Y-m-d'))) ?></p>
                                </div>
                            </div>
                        </section>

                        <section class="panel p-6">
                            <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase mb-5"><i class="bi bi-shield-lock-fill me-2"></i>Security</h2>
                            <div class="flex items-center justify-between py-4 border-b border-slate-100">
                                <div>
                                    <p class="text-slate-900 font-bold text-sm mb-1">Auto Renewal</p>
                                    <p class="text-xs text-slate-500 font-medium">Renew from wallet balance</p>
                                </div>
                                <form action="<?= base_url('dashboard/domains/update-settings/' . $domain['id']) ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_renew">
                                    <button type="submit" class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors <?= !empty($domain['auto_renew']) ? 'bg-indigo-600' : 'bg-slate-300' ?>">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white transition <?= !empty($domain['auto_renew']) ? 'translate-x-6' : 'translate-x-1' ?>"></span>
                                    </button>
                                </form>
                            </div>
                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-slate-900 font-bold text-sm mb-1">Domain Lock</p>
                                    <p class="text-xs text-slate-500 font-medium">Prevent transfer requests</p>
                                </div>
                                <form action="<?= base_url('dashboard/domains/update-settings/' . $domain['id']) ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_lock">
                                    <button type="submit" class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors <?= !empty($domain['domain_lock']) ? 'bg-indigo-600' : 'bg-slate-300' ?>">
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white transition <?= !empty($domain['domain_lock']) ? 'translate-x-6' : 'translate-x-1' ?>"></span>
                                    </button>
                                </form>
                            </div>
                        </section>

                        <section class="panel p-6 border-rose-100">
                            <h2 class="text-sm font-extrabold text-rose-600 tracking-widest uppercase mb-4"><i class="bi bi-key-fill me-2"></i>EPP/Auth Code</h2>
                            <p class="text-xs text-slate-500 font-medium mb-4 leading-relaxed">Kode ini disimulasikan untuk transfer domain. Jangan bagikan ke orang lain pada skenario produksi.</p>
                            <?php if(session()->getFlashdata('epp_code')): ?>
                                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-4">
                                    <p class="text-xs text-rose-500 font-bold mb-1">CURRENT CODE</p>
                                    <p class="font-mono font-extrabold text-rose-700 break-all"><?= esc(session()->getFlashdata('epp_code')) ?></p>
                                </div>
                            <?php else: ?>
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-4">
                                    <p class="text-xs text-slate-500 font-bold mb-1">MASKED CODE</p>
                                    <p class="font-mono font-extrabold text-slate-900"><?= esc($eppPreview ?? 'Not generated yet') ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="grid grid-cols-1 gap-3">
                                <form action="<?= base_url('dashboard/domains/manage/' . $domain['id'] . '/epp/reveal') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-full bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold py-3 rounded-xl transition text-sm">
                                        Reveal Auth Code
                                    </button>
                                </form>
                                <form action="<?= base_url('dashboard/domains/manage/' . $domain['id'] . '/epp/regenerate') ?>" method="POST" onsubmit="return confirm('Generate a new EPP/Auth code?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 rounded-xl transition text-sm">
                                        Regenerate Code
                                    </button>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
