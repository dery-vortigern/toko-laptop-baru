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

// Cek parameter order_id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: custom_order_status.php");
    exit;
}

$order_id = $_GET['id'];

// Ambil data custom order
$order_query = "SELECT * FROM tb_custom_orders WHERE order_id = $order_id AND user_id = $user_id";
$order_result = query($order_query);

// Cek apakah order ditemukan
if (empty($order_result)) {
    header("Location: custom_order_status.php");
    exit;
}

$order = $order_result[0];

// Status label mapping
$down_payment = $order['down_payment'];

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

// Ambil data admin jika ada
$admin_id = $order['admin_id'];
$admin_data = null;
if ($admin_id) {
    $admin_query = "SELECT * FROM tb_admin WHERE admin_id = $admin_id";
    $admin_result = query($admin_query);
    if (!empty($admin_result)) {
        $admin_data = $admin_result[0];
    }
}

// Fungsi untuk mendapatkan waktu yang telah berlalu dari order dibuat
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Custom Order #<?= $order_id ?> - WARINGIN-IT</title>
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


        /* Revisi Styling untuk down payment (DP) agar seragam dengan Budget */
        .info-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            border-left: 3px solid var(--primary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .budget-title, .info-box-title {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .budget-title i, .info-box-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .budget-amount, .info-box-content {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0;
            display: inline-block;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .info-box .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0;
            display: inline-block;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .navbar-brand:hover::after {
            opacity: 1;
            transform: translateY(0);
            width: 100%;
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
            margin-right: 0.5rem;
        }

        /* Card Enhanced */
        .card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
            z-index: 1;
            background: white;
            margin-bottom: 2rem;
        }

        .card::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            z-index: -1;
            background: var(--primary-gradient);
            border-radius: calc(var(--border-radius) + 5px);
            opacity: 0;
            transition: var(--transition);
            transform: scale(0.98);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .card:hover::before {
            opacity: 0.3;
            transform: scale(1);
        }

        .card-body {
            padding: 1.75rem;
        }

        /* Button Styles */
        .btn {
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: var(--transition);
            z-index: -1;
            opacity: 0;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }

        .btn-primary::before {
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
        }

        .btn-primary:hover {
            box-shadow: 0 8px 15px rgba(67, 97, 238, 0.25);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
        }

        .btn i {
            transition: var(--transition);
        }

        .btn-primary:hover i {
            transform: translateX(3px);
        }

        /* Order Detail Header */
        .order-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .order-detail-title {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .order-detail-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        /* Status badge styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge i {
            margin-right: 0.5rem;
        }

        .status-pending {
            background-color: #fff7ed;
            color: #9a3412;
        }

        .status-processing {
            background-color: #eff6ff;
            color: #1e40af;
        }

        .status-completed {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .status-cancelled {
            background-color: #fef2f2;
            color: #b91c1c;
        }

        /* Order Detail Content */
        .order-info-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed #e2e8f0;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 0.75rem;
        }

        /* Specs Table */
        .specs-table {
            width: 100%;
            margin-bottom: 1rem;
        }

        .specs-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .specs-table tr:last-child td {
            border-bottom: none;
        }

        .specs-table .spec-label {
            font-weight: 600;
            color: #475569;
            width: 35%;
        }

        .specs-table .spec-value {
            color: #334155;
        }

        /* Timeline Styling */
        .timeline {
            position: relative;
            margin-top: 2rem;
            padding-left: 25px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -25px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--primary-color);
        }

        .timeline-item.active .timeline-dot {
            background: var(--primary-color);
        }

        .timeline-item.completed .timeline-dot {
            background: var(--success-color);
            border-color: var(--success-color);
        }

        .timeline-item.cancelled .timeline-dot {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }

        .timeline-content {
            padding-left: 1rem;
        }

        .timeline-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .timeline-time {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 400;
        }

        .timeline-description {
            color: #475569;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* Info Box */
        .info-box {
            background: #eff6ff;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-left: 3px solid var(--info-color);
        }

        .info-box-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .info-box-title i {
            margin-right: 0.5rem;
        }

        .info-box-content {
            color: #334155;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* Admin Notes */
        .admin-notes {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            border-left: 3px solid #64748b;
        }

        .admin-notes-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .admin-notes-title {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
        }

        .admin-notes-title i {
            margin-right: 0.5rem;
        }

        .admin-notes-date {
            font-size: 0.85rem;
            color: #64748b;
        }

        .admin-notes-content {
            color: #334155;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* Admin Info */
        .admin-info {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 0.75rem;
        }

        .admin-name {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.25rem;
        }

        .admin-role {
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Status Tracker */
        .status-tracker {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0;
            position: relative;
        }

        .status-line {
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .status-line-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: var(--primary-gradient);
            z-index: 2;
            transition: width 0.5s ease;
        }

        .status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 3;
        }

        .status-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .status-circle i {
            color: #cbd5e1;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }


        .info-box, .budget-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            border-left: 3px solid var(--primary-color);
        }

        .info-box-title, .budget-title {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .info-box-title i, .budget-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .info-box-content, .budget-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0;
        }

        /* Khusus untuk additional specs */
        .additional-specs-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            color: #334155;
            font-size: 0.95rem;
            line-height: 1.6;
            border: 1px solid #e2e8f0;
        }


        .status-text {
            font-size: 0.8rem;
            color: #64748b;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .status-step.active .status-circle {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .status-step.active .status-circle i {
            color: white;
        }

        .status-step.active .status-text {
            color: var(--primary-color);
            font-weight: 600;
        }

        .status-step.completed .status-circle {
            background: var(--success-color);
            border-color: var(--success-color);
        }

        .status-step.completed .status-circle i {
            color: white;
        }

        .status-step.completed .status-text {
            color: var(--success-color);
        }

        .status-step.cancelled .status-circle {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }

        .status-step.cancelled .status-circle i {
            color: white;
        }

        .status-step.cancelled .status-text {
            color: var(--danger-color);
        }

        /* Budget Box */
        .budget-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            border-left: 3px solid var(--primary-color);
        }

        .budget-title {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .budget-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .budget-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0;
            display: inline-block;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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
            .order-detail-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .status-badge {
                margin-top: 0.5rem;
            }

            .specs-table .spec-label {
                width: 40%;
            }

            .status-tracker {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .status-line {
                display: none;
            }

            .status-step {
                flex: 0 0 45%;
                margin-bottom: 1rem;
            }

            /* Revisi CSS untuk layout yang lebih rapi */

/* Layout grid untuk seluruh konten */
.main-content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.left-column, .right-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Styling untuk spesifikasi tambahan agar sejajar dengan processor */
.additional-specs-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    height: fit-content;
}

.additional-specs-section .section-title {
    color: var(--primary-color);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed #e2e8f0;
    display: flex;
    align-items: center;
}

.additional-specs-section .section-title i {
    margin-right: 0.75rem;
}

.additional-specs-content {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    color: #334155;
    font-size: 0.95rem;
    line-height: 1.6;
    border: 1px solid #e2e8f0;
    min-height: 100px;
}

/* Layout untuk budget dan info boxes */
.info-boxes-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-top: 2rem;
}

/* Perbaikan styling untuk budget dan DP boxes */
.budget-box, .info-box.dp-box {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.budget-box::before, .info-box.dp-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-gradient);
}

.budget-title, .dp-title {
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
}

.budget-title i, .dp-title i {
    margin-right: 0.75rem;
    color: var(--primary-color);
    font-size: 1.1rem;
}

.budget-amount, .dp-amount {
    font-size: 1.75rem;
    font-weight: 700;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 0;
    line-height: 1.2;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .main-content-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .info-boxes-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .budget-amount, .dp-amount {
        font-size: 1.5rem;
    }
}

@media (max-width: 767.98px) {
    .main-content-grid {
        gap: 1rem;
    }
    
    .additional-specs-section {
        padding: 1rem;
    }
    
    .budget-box, .info-box.dp-box {
        padding: 1rem;
    }
    
    .budget-amount, .dp-amount {
        font-size: 1.25rem;
    }
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold">
                <i class="bi bi-file-earmark-text me-2"></i>Detail Custom Order
            </h2>
            <a href="custom_order_status.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
            </a>
        </div>

        <div class="card animate-fade-in">
            <div class="card-body">
                <div class="order-detail-header">
                    <div class="order-detail-title">
                        <i class="bi bi-clipboard-check"></i>
                        Custom Order #<?= $order['order_id'] ?>
                    </div>
                    <div class="status-badge <?= $status_labels[$order['status']]['class'] ?>">
                        <i class="bi bi-<?= $status_labels[$order['status']]['icon'] ?>"></i>
                        <?= $status_labels[$order['status']]['label'] ?>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-title">
                        <i class="bi bi-info-circle"></i>Informasi Pesanan
                    </div>
                    <div class="info-box-content">
                        Pesanan kustom ini dibuat pada tanggal <strong><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></strong> 
                        (<?= time_elapsed_string($order['created_at']) ?>).
                        <?php if ($order['status'] == 'processing'): ?>
                            Tim kami sedang mencarikan laptop yang sesuai dengan spesifikasi yang Anda inginkan.
                        <?php elseif ($order['status'] == 'completed'): ?>
                            Pesanan telah selesai diproses. Silakan cek email atau WhatsApp Anda untuk informasi lebih lanjut.
                        <?php elseif ($order['status'] == 'cancelled'): ?>
                            Pesanan telah dibatalkan.
                        <?php else: ?>
                            Pesanan sedang menunggu untuk diproses oleh admin kami.
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Tracker -->
                <div class="status-tracker">
                    <div class="status-line">
                        <?php
                        $progress_width = 0;
                        switch ($order['status']) {
                            case 'pending':
                                $progress_width = 0;
                                break;
                            case 'processing':
                                $progress_width = 50;
                                break;
                            case 'completed':
                                $progress_width = 100;
                                break;
                            case 'cancelled':
                                $progress_width = 100;
                                break;
                        }
                        ?>
                        <div class="status-line-progress" style="width: <?= $progress_width ?>%;"></div>
                    </div>
                    
                    <div class="status-step <?= ($order['status'] == 'pending' || $order['status'] == 'processing' || $order['status'] == 'completed') ? 'completed' : ''; ?>">
                        <div class="status-circle">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="status-text">Pesanan Diterima</div>
                    </div>
                    
                    <div class="status-step <?= ($order['status'] == 'processing' || $order['status'] == 'completed') ? 'active' : ''; ?> <?= ($order['status'] == 'completed') ? 'completed' : ''; ?> <?= ($order['status'] == 'cancelled') ? 'cancelled' : ''; ?>">
                        <div class="status-circle">
                            <i class="bi bi-<?= ($order['status'] == 'cancelled') ? 'x' : 'gear' ?>"></i>
                        </div>
                        <div class="status-text">
                            <?= ($order['status'] == 'cancelled') ? 'Pesanan Dibatalkan' : 'Diproses Admin' ?>
                        </div>
                    </div>
                    
                    <div class="status-step <?= ($order['status'] == 'completed') ? 'completed' : ''; ?>">
                        <div class="status-circle">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="status-text">Selesai</div>
                    </div>
                </div>

<div class="main-content-grid">
    <!-- Kolom Kiri - Spesifikasi Utama dan Budget/DP -->
    <div class="left-column">
        <div class="order-info-section">
            <div class="section-title">
                <i class="bi bi-cpu"></i>Spesifikasi Komputer
            </div>
            <table class="specs-table">
                <tr>
                    <td class="spec-label">Processor</td>
                    <td class="spec-value"><?= htmlspecialchars($order['processor']) ?></td>
                </tr>
                <tr>
                    <td class="spec-label">RAM</td>
                    <td class="spec-value"><?= htmlspecialchars($order['ram']) ?></td>
                </tr>
                <tr>
                    <td class="spec-label">Storage</td>
                    <td class="spec-value"><?= htmlspecialchars($order['storage']) ?></td>
                </tr>
                <tr>
                    <td class="spec-label">VGA/GPU</td>
                    <td class="spec-value"><?= htmlspecialchars($order['vga']) ?></td>
                </tr>
            </table>
        </div>

        <div class="order-info-section">
            <div class="section-title">
                <i class="bi bi-display"></i>Spesifikasi Layar & OS
            </div>
            <table class="specs-table">
                <tr>
                    <td class="spec-label">Ukuran Layar</td>
                    <td class="spec-value"><?= htmlspecialchars($order['screen_size']) ?></td>
                </tr>
                <tr>
                    <td class="spec-label">Tipe Layar</td>
                    <td class="spec-value"><?= htmlspecialchars($order['screen_type']) ?></td>
                </tr>
                <tr>
                    <td class="spec-label">Sistem Operasi</td>
                    <td class="spec-value"><?= htmlspecialchars($order['operating_system']) ?></td>
                </tr>
            </table>
        </div>

        <!-- Container untuk Budget dan DP -->
        <div class="info-boxes-container">
            <!-- Budget Box -->
            <div class="budget-box">
                <div class="budget-title">
                    <i class="bi bi-wallet2"></i>Budget
                </div>
                <div class="budget-amount">Rp <?= number_format($order['budget'], 0, ',', '.') ?></div>
            </div>

            <!-- DP Box -->
            <div class="info-box dp-box">
                <div class="dp-title">
                    <i class="bi bi-cash-coin"></i>Uang Muka (DP)
                </div>
                <div class="dp-amount">
                    Rp <?= number_format($down_payment, 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan - Spesifikasi Tambahan, Admin Notes, dan Timeline -->
    <div class="right-column">
        <?php if (!empty($order['additional_specs'])): ?>
            <div class="additional-specs-section">
                <div class="section-title">
                    <i class="bi bi-list-check"></i>Spesifikasi Tambahan
                </div>
                <div class="additional-specs-content">
                    <?= nl2br(htmlspecialchars($order['additional_specs'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($order['admin_notes']) && ($order['status'] == 'processing' || $order['status'] == 'completed' || $order['status'] == 'cancelled')): ?>
            <div class="admin-notes">
                <div class="admin-notes-header">
                    <div class="admin-notes-title">
                        <i class="bi bi-chat-left-text"></i>Catatan dari Admin
                    </div>
                    <div class="admin-notes-date">
                        <?= date('d F Y, H:i', strtotime($order['updated_at'])) ?>
                    </div>
                </div>
                <div class="admin-notes-content">
                    <?= nl2br(htmlspecialchars($order['admin_notes'])) ?>
                </div>

                <?php if ($admin_data): ?>
                    <div class="admin-info">
                        <div class="admin-avatar">
                            <?= strtoupper(substr($admin_data['nama'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="admin-name"><?= htmlspecialchars($admin_data['nama']) ?></div>
                            <div class="admin-role">Admin</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="order-info-section">
            <div class="section-title">
                <i class="bi bi-clock-history"></i>Status & Riwayat
            </div>
            <div class="timeline">
                <div class="timeline-item <?= ($order['status'] != 'cancelled') ? 'completed' : 'cancelled' ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">
                            Pesanan Dibuat
                            <span class="timeline-time"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        <p class="timeline-description">
                            Anda telah berhasil membuat pesanan kustom dan sedang menunggu review dari admin.
                        </p>
                    </div>
                </div>

                <?php if ($order['status'] == 'processing' || $order['status'] == 'completed'): ?>
                    <div class="timeline-item <?= ($order['status'] == 'completed') ? 'completed' : 'active' ?>">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                Sedang Diproses
                                <span class="timeline-time"><?= date('d F Y, H:i', strtotime($order['updated_at'])) ?></span>
                            </div>
                            <p class="timeline-description">
                                Admin sedang mencarikan laptop yang sesuai dengan spesifikasi yang Anda inginkan.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($order['status'] == 'completed'): ?>
                    <div class="timeline-item completed">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                Pesanan Selesai
                                <span class="timeline-time"><?= date('d F Y, H:i', strtotime($order['updated_at'])) ?></span>
                            </div>
                            <p class="timeline-description">
                                Pesanan Anda telah selesai diproses. Silakan cek email atau WhatsApp Anda untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($order['status'] == 'cancelled'): ?>
                    <div class="timeline-item cancelled">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">
                                Pesanan Dibatalkan
                                <span class="timeline-time"><?= date('d F Y, H:i', strtotime($order['updated_at'])) ?></span>
                            </div>
                            <p class="timeline-description">
                                Pesanan Anda telah dibatalkan. Silakan lihat catatan admin untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>