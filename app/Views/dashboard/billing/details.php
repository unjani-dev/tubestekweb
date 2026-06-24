<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= esc($transaction['reference_id']) ?> - Flizzy Cloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
            color: #2b3674;
        }
        .invoice-card {
            background: white;
            border-radius: 30px;
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.02);
            overflow: hidden;
            position: relative;
        }
        /* Dekorasi Kertas Struk */
        .invoice-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        .receipt-brand {
            font-weight: 800;
            letter-spacing: -1px;
            font-size: 1.5rem;
        }
        .info-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #a3b1cc;
            letter-spacing: 1.5px;
            margin-bottom: 4px;
        }
        .info-value {
            font-weight: 700;
            color: #2b3674;
            font-size: 0.95rem;
        }
        .price-display {
            background: #f8f9fc;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
        }
        /* Print Styles */
        @media print {
            .sidebar, .navbar, .btn-print, .back-link { display: none !important; }
            .content-area { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            body { background: white; }
            .invoice-card { box-shadow: none; border: 1px solid #eee; }
        }
        @media (max-width: 767.98px) {
            .content-area { margin-top: 70px; }
            .mobile-sidebar { width: 280px; }
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
        <div class="offcanvas-body p-0" style="background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);">
             <?= view('dashboard/sidebar') ?>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            
            <?= view('dashboard/sidebar') ?>

            <div class="col-md-10 p-4 p-md-5 content-area">
                
                <div class="d-flex justify-content-between align-items-center mb-5 no-print">
                    <a href="<?= base_url('dashboard/billing/history') ?>" class="btn btn-white shadow-sm rounded-circle border d-flex align-items-center justify-content-center back-link" style="width: 45px; height: 45px;">
                        <i class="bi bi-arrow-left fw-bold"></i>
                    </a>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm border-0 btn-print" style="background: #2b3674;">
                            <i class="bi bi-printer-fill me-2"></i> Print Invoice
                        </a>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        
                        <div class="card invoice-card p-4 p-md-5">
                            
                            <div class="d-flex flex-column flex-md-row justify-content-between mb-5 gap-4">
                                <div>
                                    <div class="receipt-brand text-primary mb-1">
                                        <i class="bi bi-cloud-fill"></i> FLIZZY<span class="text-dark">CLOUD</span>
                                    </div>
                                    <p class="text-muted small fw-medium mb-0">Platform Mini Enterprise Edition</p>
                                    <p class="text-muted small fw-medium">Unjani Project v1.0</p>
                                </div>
                                <div class="text-md-end">
                                    <h4 class="fw-800 text-dark mb-1">INVOICE</h4>
                                    <div class="text-primary fw-bold">#<?= esc($transaction['reference_id']) ?></div>
                                    <div class="text-muted small mt-2">Issued on <?= date('M d, Y', strtotime($transaction['created_at'])) ?></div>
                                </div>
                            </div>

                            <hr class="opacity-5 mb-5">

                            <div class="row g-4 mb-5">
                                <div class="col-6 col-md-3">
                                    <div class="info-label">Customer</div>
                                    <div class="info-value"><?= esc(session()->get('full_name')) ?></div>
                                </div>
                                <div class="col-6 col-md-3 text-md-center">
                                    <div class="info-label">Status</div>
                                    <?php 
                                        $status = $transaction['status'];
                                        $color = ($status === 'completed') ? 'text-success' : (($status === 'pending') ? 'text-warning' : 'text-danger');
                                    ?>
                                    <div class="info-value <?= $color ?> text-uppercase"><?= $status ?></div>
                                </div>
                                <div class="col-6 col-md-3 text-md-center">
                                    <div class="info-label">Method</div>
                                    <div class="info-value"><?= strtoupper(str_replace('_', ' ', $transaction['payment_method'] ?? 'System')) ?></div>
                                </div>
                                <div class="col-6 col-md-3 text-md-end">
                                    <div class="info-label">Type</div>
                                    <div class="info-value text-capitalize"><?= esc($transaction['transaction_type']) ?></div>
                                </div>
                            </div>

                            <div class="table-responsive mb-5">
                                <table class="table align-middle">
                                    <thead class="border-bottom">
                                        <tr>
                                            <th class="ps-0 border-0 text-muted small fw-bold text-uppercase py-3">Description</th>
                                            <th class="border-0 text-muted small fw-bold text-uppercase py-3 text-end">Quantity</th>
                                            <th class="pe-0 border-0 text-muted small fw-bold text-uppercase py-3 text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-0 py-4">
                                                <div class="fw-bolder text-dark mb-1"><?= esc($transaction['description']) ?></div>
                                                <div class="text-muted small fw-medium">Transaction handled by cloud-billing system</div>
                                            </td>
                                            <td class="text-end fw-bold">1</td>
                                            <td class="pe-0 text-end fw-800 text-dark">$<?= number_format($transaction['amount'], 2) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end">
                                <div class="col-md-5">
                                    <div class="price-display">
                                        <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 1px;">Amount Charged</div>
                                        <h1 class="fw-bolder text-primary mb-0">$<?= number_format($transaction['amount'], 2) ?></h1>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-5 border-top opacity-50 text-center">
                                <p class="small fw-medium mb-0">This is a computer-generated document for simulation purposes.</p>
                                <p class="small fw-medium">Thank you for being part of Flizzy Cloud Gen-Z Edition.</p>
                            </div>

                        </div>

                        <div class="text-center mt-4 no-print">
                            <p class="text-muted small fw-medium"><i class="bi bi-shield-check text-success me-1"></i> Transaction Secured by 256-bit Encryption</p>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>