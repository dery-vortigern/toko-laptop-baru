<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

// Ambil semua custom orders milik user
$orders_query = "SELECT * FROM tb_custom_orders WHERE user_id = $user_id ORDER BY created_at DESC";
$custom_orders = query($orders_query);

// Status label mapping
$status_labels = [
    'pending' => [
        'label' => 'Menunggu',
        'class' => 'status-pending',
        'icon' => 'clock'
    ],
    'processing' => [
        'label' => 'Sedang Diproses',
        'class' => 'status-processing',
        'icon' => 'arrow-repeat'
    ],
    'completed' => [
        'label' => 'Selesai',
        'class' => 'status-completed',
        'icon' => 'check-circle'
    ],
    'cancelled' => [
        'label' => 'Dibatalkan',
        'class' => 'status-cancelled',
        'icon' => 'x-circle'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Custom Order - WARINGIN-IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-gradient: linear-gradient(135deg, #4361ee, #3a0ca3);
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --hover-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            --card-hover-shadow: 0 15px 30px rgba(67, 97, 238, 0.15);
            --border-radius: 16px;
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding-bottom: 30px;
            color: #334155;
        }

        /* Navbar Enhanced */
        .navbar {
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            background: var(--primary-gradient) !important;
            padding: 15px 0;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: -0.5px;
            position: relative;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 3px;
            background: white;
            border-radius: 5px;
            opacity: 0;
            transform: translateY(5px);
            transition: var(--transition);
        }

        .navbar-brand:hover::after {
            opacity: 1;
            transform: translateY(0);
            width: 100%;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            padding: 8px 16px;
            margin: 0 3px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        /* Title Section */
        h2.mb-4.fw-bold {
            color: #1e293b;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
            margin-bottom: 1.5rem !important;
            padding-bottom: 0.75rem;
            position: relative;
        }

        h2.mb-4.fw-bold::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        h2.mb-4.fw-bold i {
            color: var(--primary-color);
            margin-right: 0.75rem;
        }

        .order-date {
            font-size: 0.85rem;
            color: #64748b;
        }

        .order-body {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .spec-item {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
        }

        .spec-label {
            width: 120px;
            font-weight: 600;
            color: #475569;
            flex-shrink: 0;
        }

        .spec-value {
            color: #334155;
            flex-grow: 1;
        }

        .spec-category {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .spec-category i {
            margin-right: 0.5rem;
        }

        .order-footer {
            padding: 1rem 1.5rem;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .budget-badge {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            font-weight: 700;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Timeline Messages */
        .timeline-container {
            margin-top: 1.5rem;
            border-top: 1px dashed #e2e8f0;
            padding-top: 1.5rem;
        }

        .timeline-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .timeline-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 8px;
            width: 2px;
            background-color: #e2e8f0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: -30px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .timeline-content {
            position: relative;
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border-radius: 8px;
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 10px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 8px 8px 8px 0;
            border-color: transparent #f8fafc transparent transparent;
        }

        .timeline-time {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .timeline-message {
            color: #334155;
        }

        /* Admin Notes */
        .admin-notes {
            margin-top: 1.5rem;
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            border-left: 3px solid var(--info-color);
        }

        .admin-notes-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .admin-notes-title i {
            margin-right: 0.5rem;
        }

        .admin-notes-content {
            color: #334155;
            font-size: 0.95rem;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-date {
                margin-top: 0.5rem;
            }
            
            .order-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .spec-item {
                flex-direction: column;
            }
            
            .spec-label {
                width: 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-laptop me-2"></i>WARINGIN-IT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">Pencarian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="custom_order.php">Custom Order</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item me-3">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart-fill me-1"></i>Keranjang
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) : ?>
                                <span class="badge bg-danger rounded-pill"><?= count($_SESSION['cart']); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4 fw-bold">
            <i class="bi bi-clipboard-check me-2"></i>Status Custom Order
        </h2>

        <?php if (isset($_SESSION['success'])) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="mb-0">Berikut adalah daftar pesanan kustom yang telah Anda buat.</p>
            <a href="custom_order.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Buat Custom Order Baru
            </a>
        </div>

        <?php if (empty($custom_orders)) : ?>
            <div class="empty-state animate-fade-in">
                <i class="bi bi-clipboard"></i>
                <h4>Belum Ada Custom Order</h4>
                <p>Anda belum membuat pesanan kustom apapun. Buat pesanan kustom untuk mendapatkan laptop sesuai dengan spesifikasi yang Anda inginkan.</p>
                <a href="custom_order.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Buat Custom Order
                </a>
            </div>
        <?php else : ?>
            <?php foreach ($custom_orders as $index => $order) : ?>
                <div class="order-card animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="order-header">
                        <div class="order-id">
                            <i class="bi bi-clipboard-check"></i>
                            Order #<?= $order['order_id'] ?>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="order-date">
                                <i class="bi bi-calendar-event me-1"></i>
                                <?= date('d F Y, H:i', strtotime($order['created_at'])) ?>
                            </div>
                            <div class="status-badge <?= $status_labels[$order['status']]['class'] ?>">
                                <i class="bi bi-<?= $status_labels[$order['status']]['icon'] ?>"></i>
                                <?= $status_labels[$order['status']]['label'] ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="spec-category">
                                    <i class="bi bi-cpu"></i>Spesifikasi Utama
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">Processor</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['processor']) ?></div>
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">RAM</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['ram']) ?></div>
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">Storage</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['storage']) ?></div>
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">VGA/GPU</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['vga']) ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="spec-category">
                                    <i class="bi bi-display"></i>Spesifikasi Lainnya
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">Ukuran Layar</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['screen_size']) ?></div>
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">Tipe Layar</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['screen_type']) ?></div>
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-label">Sistem Operasi</div>
                                    <div class="spec-value"><?= htmlspecialchars($order['operating_system']) ?></div>
                                </div>
                                
                                <?php if (!empty($order['additional_specs'])) : ?>
                                    <div class="spec-item">
                                        <div class="spec-label">Info Tambahan</div>
                                        <div class="spec-value"><?= nl2br(htmlspecialchars($order['additional_specs'])) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['admin_notes']) && $order['status'] != 'pending') : ?>
                            <div class="admin-notes">
                                <div class="admin-notes-title">
                                    <i class="bi bi-chat-left-text"></i>Catatan dari Admin
                                </div>
                                <div class="admin-notes-content">
                                    <?= nl2br(htmlspecialchars($order['admin_notes'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] == 'processing') : ?>
                            <div class="timeline-container">
                                <div class="timeline-title">
                                    <i class="bi bi-clock-history"></i>Status Pemrosesan
                                </div>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-icon"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></div>
                                            <div class="timeline-message">Pesanan kustom Anda telah diterima dan sedang diproses oleh tim kami.</div>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-icon"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time"><?= date('d F Y, H:i', strtotime($order['updated_at'])) ?></div>
                                            <div class="timeline-message">Admin sedang mencari laptop yang sesuai dengan spesifikasi yang Anda inginkan.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-footer">
                        <div class="budget-badge">
                            <i class="bi bi-cash me-1"></i>Budget: Rp <?= number_format($order['budget'], 0, ',', '.') ?>
                        </div>
                        <a href="custom_order_detail.php?id=<?= $order['order_id'] ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Add hover effect to order cards
            const orderCards = document.querySelectorAll('.order-card');
            orderCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                    this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>