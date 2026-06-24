<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Cloud Platform Mini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .profile-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .avatar-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto;
            border: 4px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-2 sidebar p-0 d-none d-md-block">
                <div class="p-4 text-center">
                    <h4 class="mb-0 text-white fw-bold"><i class="bi bi-cloud-fill"></i> Cloud Mini</h4>
                    <span class="badge bg-light text-dark mt-2 opacity-75">v1.0 Enterprise</span>
                </div>
                
                <div class="px-3 mt-3">
                    <nav class="nav flex-column gap-2">
                        <a class="nav-link rounded px-3 py-2" href="<?= base_url('dashboard') ?>">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                        <a class="nav-link rounded px-3 py-2" href="<?= base_url('dashboard/servers') ?>">
                            <i class="bi bi-hdd-network me-2"></i> My Servers
                        </a>
                        <a class="nav-link rounded px-3 py-2" href="<?= base_url('dashboard/billing') ?>">
                            <i class="bi bi-wallet2 me-2"></i> Billing
                        </a>
                        <a class="nav-link rounded px-3 py-2 active fw-bold shadow-sm" href="<?= base_url('dashboard/profile') ?>" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                    </nav>
                </div>

                <div class="position-absolute bottom-0 w-100 p-3">
                    <hr class="text-white opacity-25">
                    <a class="btn btn-outline-light w-100 text-start" href="<?= base_url('auth/logout') ?>">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <div class="col-md-10 p-4 p-md-5">
                
                <div class="mb-4 border-bottom pb-3">
                    <h2 class="fw-bold text-dark mb-1"><i class="bi bi-person-circle text-primary"></i> Account Settings</h2>
                    <p class="text-muted mb-0">Manage your personal information and security preferences</p>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-5 border-success">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger shadow-sm border-0 border-start border-5 border-danger">
                        <h6 class="fw-bold"><i class="bi bi-exclamation-triangle"></i> Update Failed</h6>
                        <ul class="mb-0">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger shadow-sm border-0 border-start border-5 border-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    
                    <div class="col-lg-4">
                        <div class="card profile-card text-center pb-4">
                            <div class="card-body mt-4">
                                <?php 
                                    // Mengambil huruf pertama dari nama untuk Avatar
                                    $initial = strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); 
                                ?>
                                <div class="avatar-circle mb-3 fw-bold">
                                    <?= $initial ?>
                                </div>
                                <h4 class="fw-bold text-dark mb-0"><?= esc($user['full_name']) ?></h4>
                                <p class="text-muted mb-2">@<?= esc($user['username']) ?></p>
                                
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill mb-4">
                                    <i class="bi bi-patch-check-fill me-1"></i> Active Account
                                </span>

                                <ul class="list-group list-group-flush text-start mt-3">
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="text-muted small">Account Role</span>
                                        <span class="fw-semibold text-capitalize"><i class="bi bi-shield-check text-primary me-1"></i> <?= esc($user['role']) ?></span>
                                    </li>
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="text-muted small">Joined Date</span>
                                        <span class="fw-semibold"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        
                        <div class="card profile-card mb-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 fw-bold text-dark">Personal Information</h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="<?= base_url('dashboard/profile/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                                            <input type="text" name="full_name" class="form-control form-control-lg" value="<?= old('full_name', esc($user['full_name'])) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold text-uppercase">Phone Number</label>
                                            <input type="text" name="phone" class="form-control form-control-lg" value="<?= old('phone', esc($user['phone'])) ?>" placeholder="+62...">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold text-uppercase">Email Address (Read-Only)</label>
                                            <input type="email" class="form-control form-control-lg bg-light" value="<?= esc($user['email']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold text-uppercase">Username (Read-Only)</label>
                                            <input type="text" class="form-control form-control-lg bg-light" value="<?= esc($user['username']) ?>" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="background: #667eea; border: none;">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card profile-card border-0 border-top border-4 border-danger">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark">Security Settings</h5>
                                <i class="bi bi-shield-lock text-danger fs-5"></i>
                            </div>
                            <div class="card-body p-4">
                                <form action="<?= base_url('dashboard/profile/change-password') ?>" method="post">
                                    <?= csrf_field() ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                                    </div>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold text-uppercase">New Password</label>
                                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold text-uppercase">Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control" placeholder="Retype new password" required minlength="8">
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> You will need to login again after changing your password.</small>
                                        <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>