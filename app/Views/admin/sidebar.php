<?php $uri = uri_string(); ?>
<style>
    .sidebar-logout {
        background: rgba(248, 113, 113, 0.16);
        border: 1px solid rgba(254, 202, 202, 0.28);
        color: #fff;
        transition: 0.2s ease;
    }
    .sidebar-logout:hover {
        background: rgba(239, 68, 68, 0.28);
        border-color: rgba(254, 202, 202, 0.48);
        color: #fff;
        transform: translateY(-1px);
    }
</style>
<div class="col-md-2 sidebar p-0 d-none d-md-block" style="min-height: 100vh; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); position: sticky; top: 0;">
    <div class="p-4 text-center">
        <div class="bg-primary bg-opacity-25 rounded-4 d-inline-flex align-items-center justify-content-center mb-2 shadow-lg" style="width: 55px; height: 55px; border: 1px solid rgba(255,255,255,0.1);">
            <i class="bi bi-shield-lock-fill text-primary fs-2"></i>
        </div>
        <h5 class="mb-0 text-white fw-800 mt-2">ADMIN<span class="text-primary">CORE</span></h5>
        <span class="badge bg-danger text-white mt-2 px-3 rounded-pill shadow-sm" style="font-size: 0.65rem; letter-spacing: 1px;">SYSTEM CONTROL ⚙️</span>
    </div>
    
    <div class="px-3 mt-4" style="height: calc(100vh - 250px); overflow-y: auto;">
        
        <p class="text-white opacity-25 small fw-bold text-uppercase mb-2 px-2" style="font-size: 0.6rem; letter-spacing: 2px;">Main Management</p>
        <nav class="nav flex-column gap-2">
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= ($uri == 'admin') ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin') ?>">
                <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
            </a>
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($uri, 'admin/users') !== false) ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/users') ?>">
                <i class="bi bi-people-fill me-2"></i> Users List
            </a>
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($uri, 'admin/plans') !== false) ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/plans') ?>">
                <i class="bi bi-cpu-fill me-2"></i> Cloud Plans
            </a>
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($uri, 'admin/servers') !== false) ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/servers') ?>">
                <i class="bi bi-hdd-stack-fill me-2"></i> Global Instances
            </a>
        </nav>

        <p class="text-white opacity-25 small fw-bold text-uppercase mb-2 mt-4 px-2" style="font-size: 0.6rem; letter-spacing: 2px;">Domain Services</p>
        <nav class="nav flex-column gap-2">
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= ($uri == 'admin/domains') ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/domains') ?>">
                <i class="bi bi-globe me-2"></i> Domain Manifest
            </a>
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= ($uri == 'admin/domains/pricing') ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/domains/pricing') ?>">
                <i class="bi bi-tags-fill me-2"></i> TLD Pricing Matrix
            </a>
        </nav>

        <p class="text-white opacity-25 small fw-bold text-uppercase mb-2 mt-4 px-2" style="font-size: 0.6rem; letter-spacing: 2px;">Financial Control</p>
        <nav class="nav flex-column gap-2">
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= ($uri == 'admin/billing') ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/billing') ?>">
                <i class="bi bi-clock-history me-2"></i> User Transaction History
            </a>
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= ($uri == 'admin/billing/transactions') ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/billing/transactions') ?>">
                <i class="bi bi-receipt-cutoff me-2"></i> All Transactions
            </a>
        </nav>

        <p class="text-white opacity-25 small fw-bold text-uppercase mb-2 mt-4 px-2" style="font-size: 0.6rem; letter-spacing: 2px;">Analytics</p>
        <nav class="nav flex-column gap-2 mb-4">
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($uri, 'admin/reports') !== false) ? 'active fw-bold bg-primary shadow-sm' : 'opacity-75' ?>" href="<?= base_url('admin/reports') ?>">
                <i class="bi bi-graph-up-arrow me-2"></i> Revenue Analytics
            </a>
        </nav>
    </div>
    
    <div class="position-absolute bottom-0 w-100 p-4" style="background: linear-gradient(0deg, #0f172a 65%, transparent 100%);">
        <a class="sidebar-logout btn w-100 text-start rounded-3 py-2 fw-bold d-flex align-items-center justify-content-between" href="<?= base_url('auth/logout') ?>">
            <span><i class="bi bi-box-arrow-right me-2"></i> Sign Out</span>
            <i class="bi bi-chevron-right small opacity-75"></i>
        </a>
    </div>
</div>
