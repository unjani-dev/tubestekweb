<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Domain - Flizzy Cloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; }
        .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.035); }
        .field { width: 100%; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; color: #0f172a; font-weight: 800; outline: none; }
        .field:focus { border-color: #4f46e5; background: #fff; }
        @media (max-width: 768px) { .content-area { padding: 20px !important; } }
    </style>
</head>
<body class="bg-[#f0f2f5] text-slate-700">

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('dashboard/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area min-h-screen">
            <div class="mb-8">
                <a href="<?= base_url('dashboard/domains') ?>" class="text-slate-500 hover:text-indigo-600 text-sm font-bold mb-4 inline-flex items-center transition">
                    <i class="bi bi-arrow-left me-2"></i> Back to Domains
                </a>
                <h1 class="text-3xl font-extrabold text-slate-900">Register New Domain</h1>
                <p class="text-sm text-slate-500 mt-2 font-medium">Cari domain, cek simulasi ketersediaan, lalu beli dengan saldo wallet.</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-bold">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <section class="panel p-6 md:p-8 mb-6">
                <form action="<?= base_url('dashboard/domains/search') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-10">
                        <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" placeholder="contoh: namabrand.com atau namabrand" class="field text-lg">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full h-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-2xl px-6 py-4 transition">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </section>

            <?php if(!empty($exactMatch)): ?>
                <section class="panel p-6 md:p-8 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        <div>
                            <p class="text-xs text-slate-400 font-extrabold uppercase tracking-widest mb-2">Exact Match</p>
                            <h2 class="text-2xl font-extrabold text-slate-900"><?= esc($exactMatch['domain']) ?></h2>
                            <p class="text-sm font-bold mt-2 <?= $exactMatch['available'] ? 'text-emerald-600' : 'text-rose-600' ?>">
                                <?= $exactMatch['available'] ? 'Available for registration' : 'Already registered' ?>
                            </p>
                        </div>
                        <?php if($exactMatch['available'] && $exactMatch['price'] > 0): ?>
                            <div class="flex flex-col md:flex-row md:items-center gap-4">
                                <div class="text-left md:text-right">
                                    <p class="text-xs text-slate-400 font-extrabold uppercase tracking-widest">1 Year Price</p>
                                    <p class="text-2xl font-extrabold text-slate-900">$<?= number_format($exactMatch['price'], 2, '.', ',') ?></p>
                                </div>
                                <form action="<?= base_url('dashboard/domains/purchase') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="domain_name" value="<?= esc($exactMatch['domain']) ?>">
                                    <input type="hidden" name="tld_id" value="<?= esc($exactMatch['tld_id']) ?>">
                                    <input type="hidden" name="price" value="<?= esc($exactMatch['price']) ?>">
                                    <button type="submit" class="bg-slate-900 hover:bg-indigo-700 text-white px-7 py-3 rounded-xl text-sm font-extrabold transition">
                                        <i class="bi bi-bag-check-fill me-2"></i>Register Now
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <span class="bg-slate-100 text-slate-500 px-5 py-3 rounded-xl text-sm font-extrabold">Unavailable</span>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if(!empty($domainOptions)): ?>
                <section class="panel p-6 md:p-8 mb-6">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Choose Extension</h2>
                            <p class="text-sm text-slate-500 font-medium mt-2">Semua TLD aktif dari admin pricing ditampilkan di sini.</p>
                        </div>
                        <span class="text-xs font-extrabold text-slate-400 uppercase"><?= count($domainOptions) ?> options</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <?php foreach($domainOptions as $option): ?>
                            <div class="bg-slate-50 border <?= $option['available'] ? 'border-emerald-100' : 'border-slate-200 opacity-75' ?> rounded-2xl p-5">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div>
                                        <p class="text-lg font-extrabold text-slate-900 break-all"><?= esc($option['domain']) ?></p>
                                        <p class="text-xs font-bold mt-1 <?= $option['available'] ? 'text-emerald-600' : 'text-rose-600' ?>">
                                            <?= $option['available'] ? 'Available' : 'Already registered' ?>
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-extrabold <?= $option['available'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' ?>">
                                        <?= esc($option['tld']) ?>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs text-slate-400 font-extrabold uppercase">1 Year</p>
                                        <p class="text-xl font-extrabold text-slate-900">$<?= number_format($option['price'], 2, '.', ',') ?></p>
                                    </div>
                                    <?php if($option['available']): ?>
                                        <form action="<?= base_url('dashboard/domains/purchase') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="domain_name" value="<?= esc($option['domain']) ?>">
                                            <input type="hidden" name="tld_id" value="<?= esc($option['tld_id']) ?>">
                                            <input type="hidden" name="price" value="<?= esc($option['price']) ?>">
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-extrabold transition">
                                                Register
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="bg-slate-200 text-slate-500 px-5 py-2.5 rounded-xl text-sm font-extrabold">Taken</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="panel p-6 md:p-8">
                <h2 class="text-sm font-extrabold text-slate-400 tracking-widest uppercase mb-5"><i class="bi bi-tags-fill me-2"></i>Available TLD Pricing</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <?php if(empty($tlds)): ?>
                        <div class="col-span-full text-center py-8 text-slate-400 font-bold">No active TLD pricing yet.</div>
                    <?php else: ?>
                        <?php foreach($tlds as $tld): ?>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5">
                                <p class="text-xl font-extrabold text-slate-900"><?= esc($tld['tld']) ?></p>
                                <p class="text-sm text-slate-500 font-bold mt-2">Register: $<?= number_format($tld['register_price'], 2, '.', ',') ?></p>
                                <p class="text-sm text-slate-500 font-bold">Renew: $<?= number_format($tld['renew_price'], 2, '.', ',') ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

</body>
</html>
