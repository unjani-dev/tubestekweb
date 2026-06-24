<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up Balance - Flizzy Cloud Mini</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
            color: #2b3674;
        }
        
        /* Custom Scrollbar for modern look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c5cddf; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a3b1d1; }

        /* Custom CSS untuk Enterprise Radio Cards */
        .selection-card {
            cursor: pointer;
            border: 2px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
            height: 100%;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .selection-card:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
        }
        .radio-hidden {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .radio-hidden:checked + .selection-card {
            border-color: #667eea;
            background-color: #f0f5ff; /* Soft blue tint */
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }
        .radio-hidden:checked + .selection-card .check-icon {
            display: block !important;
            color: #667eea;
            animation: popIn 0.3s ease-out forwards;
        }
        
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .payment-logo { font-size: 2rem; }
        .bento-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        /* Mobile Offcanvas Sidebar Override */
        @media (max-width: 767.98px) {
            .mobile-sidebar { width: 280px; }
            .content-area { margin-top: 60px; } /* Space for fixed mobile navbar */
        }
    </style>
</head>
<body>
    
    <nav class="navbar bg-white shadow-sm fixed-top d-md-none border-bottom px-3 z-3">
        <a class="navbar-brand fw-bold text-dark d-flex align-items-center" href="#">
            <i class="bi bi-cloud-fill text-primary me-2"></i> Flizzy Cloud
        </a>
        <button class="btn btn-light border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list fs-3"></i>
        </button>
    </nav>

    <div class="offcanvas offcanvas-start mobile-sidebar" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
            <h5 class="offcanvas-title fw-bold"><i class="bi bi-cloud-fill me-2"></i> Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0" style="background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);">
             <?= view('dashboard/sidebar') ?>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            
            <?= view('dashboard/sidebar') ?>

            <div class="col-md-10 p-4 p-md-5 content-area">
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 border-bottom pb-3 gap-3">
                    <div class="d-flex align-items-center">
                        <a href="<?= base_url('dashboard/billing') ?>" class="btn btn-white border shadow-sm rounded-circle me-3 d-flex align-items-center justify-content-center hover-scale" style="width: 45px; height: 45px; transition: 0.2s;">
                            <i class="bi bi-arrow-left text-dark fw-bold fs-5"></i>
                        </a>
                        <div>
                            <h2 class="fw-bolder text-dark mb-1">Add Account Balance</h2>
                            <p class="text-muted mb-0 fw-medium">Fund your wallet to keep your instances running</p>
                        </div>
                    </div>
                    <div class="text-md-end bg-white px-4 py-2 rounded-4 shadow-sm border">
                        <span class="text-muted d-block small fw-bold text-uppercase" style="letter-spacing: 1px;">Current Balance</span>
                        <h3 class="fw-bolder text-success mb-0">$<?= number_format($user['balance'] ?? 0, 2) ?></h3>
                    </div>
                </div>

                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger shadow-sm rounded-4 border-0 mb-4">
                        <ul class="mb-0 fw-medium">
                            <?php foreach (session('errors') as $error): ?>
                                <li><i class="bi bi-exclamation-circle-fill me-2"></i><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger shadow-sm rounded-4 border-0 mb-4 fw-medium">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('dashboard/billing/topup') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            
                            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center p-4" style="background-color: #e6f9f4;">
                                <i class="bi bi-lightning-charge-fill fs-1 text-success me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">Instant Top-Up</h6>
                                    <p class="mb-0 small text-dark opacity-75 fw-medium">Balance is credited immediately after you submit this top-up form.</p>
                                </div>
                            </div>

                            <div class="card bento-card mb-4">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder mb-4 text-dark d-flex align-items-center">
                                        <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 1rem;">1</span> 
                                        Select Amount
                                    </h5>
                                    
                                    <div class="row g-3 mb-4">
                                        <?php 
                                        $amounts = [10, 25, 50, 100]; 
                                        foreach($amounts as $index => $amt): 
                                        ?>
                                        <div class="col-md-3 col-6">
                                            <label class="w-100 h-100">
                                                <input type="radio" name="amount" value="<?= $amt ?>" class="radio-hidden amount-selector" onchange="updateSummary()" <?= $index === 1 ? 'checked' : '' ?>>
                                                <div class="card selection-card rounded-4 text-center p-3 p-md-4 position-relative">
                                                    <i class="bi bi-check-circle-fill check-icon position-absolute top-0 end-0 m-2 fs-5" style="display:none;"></i>
                                                    <h2 class="mb-0 fw-bolder text-dark">$<?= $amt ?></h2>
                                                </div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="pt-4 border-top">
                                        <label class="form-label text-muted fw-bold small text-uppercase" style="letter-spacing: 1px;">Or enter custom amount</label>
                                        <div class="input-group input-group-lg w-100 w-md-50 shadow-sm rounded-3 overflow-hidden">
                                            <span class="input-group-text bg-light border-0 fw-bolder text-muted px-4">$</span>
                                            <input type="number" name="custom_amount" class="form-control border-0 bg-light ps-0 custom-amount fw-bold" id="customAmount" placeholder="0.00" min="5" step="0.01" onkeyup="selectCustomAmount()" onchange="selectCustomAmount()">
                                        </div>
                                        <div class="form-text mt-2 fw-medium text-muted"><i class="bi bi-info-circle me-1"></i> Minimum top-up amount is $5.00</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bento-card mb-4 mb-lg-0">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder mb-4 text-dark d-flex align-items-center">
                                        <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 1rem;">2</span> 
                                        Payment Method
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="w-100 h-100">
                                                <input type="radio" name="payment_method" value="bank_transfer" class="radio-hidden method-selector" onchange="updateSummary()" checked required>
                                                <div class="card selection-card rounded-4 p-3 p-md-4 position-relative">
                                                    <i class="bi bi-check-circle-fill check-icon position-absolute top-50 end-0 translate-middle-y me-3 fs-5" style="display:none;"></i>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3 text-primary">
                                                            <i class="bi bi-bank fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bolder text-dark">Bank Transfer</h6>
                                                            <small class="text-muted fw-medium">Manual verification (1-24h)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="w-100 h-100">
                                                <input type="radio" name="payment_method" value="credit_card" class="radio-hidden method-selector" onchange="updateSummary()">
                                                <div class="card selection-card rounded-4 p-3 p-md-4 position-relative">
                                                    <i class="bi bi-check-circle-fill check-icon position-absolute top-50 end-0 translate-middle-y me-3 fs-5" style="display:none;"></i>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3 text-info">
                                                            <i class="bi bi-credit-card-2-front fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bolder text-dark">Credit / Debit Card</h6>
                                                            <small class="text-muted fw-medium">Visa, Mastercard, JCB</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="w-100 h-100">
                                                <input type="radio" name="payment_method" value="paypal" class="radio-hidden method-selector" onchange="updateSummary()">
                                                <div class="card selection-card rounded-4 p-3 p-md-4 position-relative">
                                                    <i class="bi bi-check-circle-fill check-icon position-absolute top-50 end-0 translate-middle-y me-3 fs-5" style="display:none;"></i>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3 text-primary">
                                                            <i class="bi bi-paypal fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bolder text-dark">PayPal</h6>
                                                            <small class="text-muted fw-medium">Instant processing</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="w-100 h-100">
                                                <input type="radio" name="payment_method" value="crypto" class="radio-hidden method-selector" onchange="updateSummary()">
                                                <div class="card selection-card rounded-4 p-3 p-md-4 position-relative">
                                                    <i class="bi bi-check-circle-fill check-icon position-absolute top-50 end-0 translate-middle-y me-3 fs-5" style="display:none;"></i>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3 text-warning">
                                                            <i class="bi bi-currency-bitcoin fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bolder text-dark">Cryptocurrency</h6>
                                                            <small class="text-muted fw-medium">BTC, ETH, USDT</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-4">
                            <div class="card bento-card sticky-top" style="top: 100px;">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder border-bottom pb-3 mb-4 text-dark">Transaction Summary</h5>
                                    
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-medium">Transaction Type</span>
                                        <span class="fw-bold text-dark">Wallet Top-Up</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-medium">Selected Method</span>
                                        <span class="fw-bold text-dark text-end" id="summaryMethod">Bank Transfer</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                                        <span class="text-muted fw-medium">Initial Status</span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold rounded-pill">COMPLETED</span>
                                    </div>

                                    <div class="bg-light rounded-4 p-4 text-center mb-4 border">
                                        <span class="text-muted fw-bold small text-uppercase" style="letter-spacing: 1px;">Amount to Pay</span>
                                        <h1 class="text-success fw-bolder mb-0 mt-2 display-5" id="summaryAmount">$25.00</h1>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-pill py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                        <i class="bi bi-shield-lock-fill me-2"></i> Submit Payment
                                    </button>
                                    
                                    <div class="text-center mt-4">
                                        <small class="text-muted fw-medium"><i class="bi bi-lock-fill text-success me-1"></i> 256-bit SSL Encrypted Transaction</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        // Logika Interaktif untuk Summary Kanan
        function updateSummary() {
            // Update Teks Metode Pembayaran
            const selectedMethod = document.querySelector('.method-selector:checked');
            if (selectedMethod) {
                const methodLabels = {
                    'bank_transfer': 'Bank Transfer',
                    'credit_card': 'Credit / Debit Card',
                    'paypal': 'PayPal',
                    'crypto': 'Cryptocurrency'
                };
                document.getElementById('summaryMethod').innerText = methodLabels[selectedMethod.value];
            }

            // Update Total Nominal Pembayaran
            let amount = 0;
            const customInput = document.getElementById('customAmount').value;
            
            if (customInput && customInput > 0) {
                amount = parseFloat(customInput).toFixed(2);
            } else {
                const selectedAmount = document.querySelector('.amount-selector:checked');
                if (selectedAmount) {
                    amount = parseFloat(selectedAmount.value).toFixed(2);
                }
            }
            
            // Format angka
            document.getElementById('summaryAmount').innerText = '$' + amount;
        }

        // Matikan tombol bulat (radio button) kalau user ngetik angka manual
        function selectCustomAmount() {
            const customInput = document.getElementById('customAmount');
            if(customInput.value.length > 0) {
                const radios = document.querySelectorAll('.amount-selector');
                radios.forEach(radio => radio.checked = false);
            }
            updateSummary();
        }
        
        // Bersihkan ketikan manual kalau user nge-klik salah satu tombol bulat nominal
        document.querySelectorAll('.amount-selector').forEach(radio => {
            radio.addEventListener('click', function() {
                document.getElementById('customAmount').value = '';
                updateSummary();
            });
        });

        // Jalankan pas halaman pertama kali dibuka
        document.addEventListener('DOMContentLoaded', updateSummary);
    </script>
</body>
</html>
