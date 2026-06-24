<?php 
// Mengambil URL saat ini untuk menentukan menu mana yang sedang aktif
$currentUri = uri_string(); 
?>
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
<div class="col-md-2 sidebar p-0 d-none d-md-block" style="min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); position: sticky; top: 0;">
    <div class="p-4 text-center">
        <div class="bg-white bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 60px; height: 60px;">
            <i class="bi bi-cloud-fill text-white fs-2"></i>
        </div>
        <h4 class="mb-0 text-white fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">Flizzy Cloud</h4>
        <span class="badge bg-white text-dark mt-2 px-3 rounded-pill shadow-sm" style="font-size: 0.7rem; letter-spacing: 1px;">GEN-Z EDITION ⚡</span>
    </div>
    
    <div class="px-3 mt-4" style="height: calc(100vh - 250px); overflow-y: auto;">
        
        <p class="text-white opacity-50 small fw-bold text-uppercase mb-2 px-2" style="font-size: 0.65rem; letter-spacing: 1.5px;">Main Menu</p>
        <nav class="nav flex-column gap-2">
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= ($currentUri == 'dashboard') ? 'active fw-bold shadow-sm' : 'opacity-75' ?>" 
               href="<?= base_url('dashboard') ?>" <?= ($currentUri == 'dashboard') ? 'style="background: rgba(255,255,255,0.2);"' : '' ?>>
                <i class="bi bi-grid-1x2-fill me-2"></i> Overview
            </a>
            
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($currentUri, 'dashboard/servers') !== false) ? 'active fw-bold shadow-sm' : 'opacity-75' ?>" 
               href="<?= base_url('dashboard/servers') ?>" <?= (strpos($currentUri, 'dashboard/servers') !== false) ? 'style="background: rgba(255,255,255,0.2);"' : '' ?>>
                <i class="bi bi-hdd-network-fill me-2"></i> Instances 
            </a>
            
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($currentUri, 'dashboard/domains') !== false) ? 'active fw-bold shadow-sm' : 'opacity-75' ?>" 
               href="<?= base_url('dashboard/domains') ?>" <?= (strpos($currentUri, 'dashboard/domains') !== false) ? 'style="background: rgba(255,255,255,0.2);"' : '' ?>>
                <i class="bi bi-globe me-2"></i> Domains 
            </a>

            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($currentUri, 'dashboard/billing') !== false) ? 'active fw-bold shadow-sm' : 'opacity-75' ?>" 
               href="<?= base_url('dashboard/billing') ?>" <?= (strpos($currentUri, 'dashboard/billing') !== false) ? 'style="background: rgba(255,255,255,0.2);"' : '' ?>>
                <i class="bi bi-wallet-fill me-2"></i> Billing 
            </a>
        </nav>

        <p class="text-white opacity-50 small fw-bold text-uppercase mb-2 mt-4 px-2" style="font-size: 0.65rem; letter-spacing: 1.5px;">Account</p>
        <nav class="nav flex-column gap-2 mb-4">
            <a class="nav-link rounded-3 px-3 py-2 text-white <?= (strpos($currentUri, 'dashboard/profile') !== false) ? 'active fw-bold shadow-sm' : 'opacity-75' ?>" 
               href="<?= base_url('dashboard/profile') ?>" <?= (strpos($currentUri, 'dashboard/profile') !== false) ? 'style="background: rgba(255,255,255,0.2);"' : '' ?>>
                <i class="bi bi-emoji-sunglasses-fill me-2"></i> My Profile
            </a>
        </nav>
    </div>
    
    <div class="position-absolute bottom-0 w-100 p-4" style="background: linear-gradient(0deg, #764ba2 65%, transparent 100%);">
        <a class="sidebar-logout btn w-100 text-start rounded-3 py-2 fw-bold d-flex align-items-center justify-content-between" href="<?= base_url('auth/logout') ?>">
            <span><i class="bi bi-box-arrow-right me-2"></i> Sign Out</span>
            <i class="bi bi-chevron-right small opacity-75"></i>
        </a> 
    </div>
</div>
