<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domains - Flizzy Cloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; }
        @media (max-width: 768px) { .content-area { padding: 20px !important; } }
    </style>
</head>
<body class="bg-[#f0f2f5] text-slate-700">

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('dashboard/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area min-h-screen">
            
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-wide uppercase">Domains</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage registrations, nameservers, DNS records, child NS, and transfer codes.</p>
                </div>
                <a href="<?= base_url('dashboard/domains/search') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-indigo-600/30">
                    + REGISTER NEW
                </a>
            </div>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-bold shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(empty($domains)): ?>
                    <div class="col-span-full text-center py-12 bg-white rounded-3xl border border-slate-200 shadow-sm">
                        <i class="bi bi-globe text-5xl text-slate-300 mb-3 block"></i>
                        <p class="text-slate-500 font-medium">No active domains found. Start your project by registering one.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($domains as $domain): ?>
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 hover:border-indigo-300 transition group relative overflow-hidden shadow-sm hover:shadow-md">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-extrabold text-slate-900"><?= esc($domain['domain_name']) ?></h3>
                            <span class="px-3 py-1 rounded-full text-[0.65rem] font-bold tracking-wider uppercase <?= $domain['status'] == 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                <?= esc($domain['status']) ?>
                            </span>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 font-medium">Auto Renew</span>
                                <span class="<?= !empty($domain['auto_renew']) ? 'text-indigo-600 font-bold' : 'text-slate-400 font-bold' ?>">
                                    <?= !empty($domain['auto_renew']) ? 'ON' : 'OFF' ?>
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 font-medium">Expires</span>
                                <span class="text-slate-700 font-bold"><?= date('d M Y', strtotime($domain['expiry_date'])) ?></span>
                            </div>
                        </div>

                        <a href="<?= base_url('dashboard/domains/manage/' . $domain['id']) ?>" class="block w-full text-center bg-slate-50 hover:bg-indigo-600 text-slate-700 hover:text-white border border-slate-200 hover:border-indigo-600 text-sm font-bold py-3 rounded-xl transition">
                            MANAGE DOMAIN
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>
