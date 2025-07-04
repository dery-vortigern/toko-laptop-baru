<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Ambil data admin
$admin_id = $_SESSION['admin_id'];
$admin = query("SELECT * FROM tb_admin WHERE admin_id = $admin_id")[0];

// Statistik dashboard
$total_produk = query("SELECT COUNT(*) as total FROM tb_barang")[0]['total'];
$total_kategori = query("SELECT COUNT(*) as total FROM tb_kategori")[0]['total'];
$total_penjualan = query("SELECT COUNT(*) as total FROM tb_penjualan")[0]['total'];
$total_user = query("SELECT COUNT(*) as total FROM tb_user")[0]['total'];
$total_admin = query("SELECT COUNT(*) as total FROM tb_admin")[0]['total'];

// Query penjualan terbaru dengan info user
// FIX: Changed JOIN condition from p.penjualan_id = pmb.id_pembelian to p.id_pembelian = pmb.id_pembelian
$recent_sales = query("SELECT p.*, a.nama as admin_name, u.nama as nama_user, u.telepon,
                      pb.jenis_pembayaran, SUM(dp.subtotal) as total,
                      GROUP_CONCAT(b.nama_barang SEPARATOR ', ') as produk_dibeli,
                      COUNT(dp.barang_id) as jumlah_item 
                      FROM tb_penjualan p 
                      LEFT JOIN tb_admin a ON p.admin_id = a.admin_id
                      LEFT JOIN tb_pembelian pmb ON p.id_pembelian = pmb.id_pembelian
                      LEFT JOIN tb_user u ON pmb.user_id = u.user_id
                      LEFT JOIN tb_pembayaran pb ON pmb.pembayaran_id = pb.pembayaran_id
                      LEFT JOIN tb_detail_penjualan dp ON p.penjualan_id = dp.penjualan_id
                      LEFT JOIN tb_barang b ON dp.barang_id = b.barang_id
                      GROUP BY p.penjualan_id
                      ORDER BY p.tanggal DESC 
                      LIMIT 10");

// Produk dengan stok menipis
$low_stock = query("SELECT b.*, k.nama_kategori, m.nama_merk 
                   FROM tb_barang b
                   LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id
                   LEFT JOIN tb_merk m ON b.merk_id = m.merk_id
                   WHERE b.stok < 5
                   ORDER BY b.stok ASC");

// Data untuk informasi sistem
$today = date('Y-m-d');
$transaksi_hari_ini = query("SELECT COUNT(*) as total FROM tb_penjualan WHERE DATE(tanggal) = '$today'")[0]['total'];
$pendapatan_hari_ini = query("SELECT SUM(total) as total FROM tb_penjualan WHERE DATE(tanggal) = '$today'")[0]['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin WARINGIN-IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
    --primary-color: #3a36db;
    --primary-hover: #2925b5;
    --secondary-color: #5651fa;
    --accent-color: #4f46e5;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --light-color: #f9fafb;
    --dark-color: #1e293b;
    --background-color: #f3f4f6;
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: var(--background-color);
    overflow-x: hidden;
    color: #334155;
    line-height: 1.5;
}

/* Navbar Styles */
.navbar {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
    padding: 0.75rem 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.35rem;
    padding: 0.75rem 1rem;
    letter-spacing: -0.5px;
}

/* Sidebar Styles */
.sidebar {
    background: linear-gradient(180deg, var(--dark-color), #1a1a2e);
    min-height: 100vh;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
    z-index: 100;
    transition: var(--transition);
}

.sidebar-sticky {
    position: sticky;
    top: 0;
    height: calc(100vh - 48px);
    padding: 1.5rem 0;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.85);
    padding: 0.9rem 1.5rem;
    margin: 0.3rem 1rem;
    border-radius: 8px;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    transform: translateX(5px);
}

.sidebar .nav-link.active {
    background: var(--accent-color);
    color: white;
    font-weight: 600;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.sidebar .bi {
    font-size: 1.25rem;
}

/* Card Styles */
.stat-card {
    border: none;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    background: white;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-hover);
}

.stat-card.primary { border-left: 5px solid var(--primary-color); }
.stat-card.success { border-left: 5px solid var(--success-color); }
.stat-card.warning { border-left: 5px solid var(--warning-color); }
.stat-card.danger { border-left: 5px solid var(--danger-color); }
.stat-card.info { border-left: 5px solid var(--info-color); }

.stat-card .card-body {
    padding: 1.75rem;
}

.stat-card .stat-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(79, 70, 229, 0.1);
    transition: var(--transition);
}

.stat-card.primary .stat-icon { background: rgba(79, 70, 229, 0.1); color: var(--primary-color); }
.stat-card.success .stat-icon { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
.stat-card.warning .stat-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
.stat-card.danger .stat-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
.stat-card.info .stat-icon { background: rgba(59, 130, 246, 0.1); color: var(--info-color); }

.stat-card:hover .stat-icon {
    transform: scale(1.1);
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0.6rem 0;
    color: var(--dark-color);
    letter-spacing: -0.5px;
}

.stat-card .stat-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Progress Bar Styles */
.progress {
    height: 6px;
    border-radius: 3px;
    background-color: #e2e8f0;
    overflow: hidden;
    margin-top: 1rem;
}

.progress-bar {
    transition: width 1.5s ease;
    border-radius: 3px;
}

/* Table Styles */
.table-card {
    border: none;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: var(--transition);
}

.table-card:hover {
    box-shadow: var(--card-shadow-hover);
}

.table-card .card-header {
    background: white;
    border-bottom: 2px solid #f1f5f9;
    padding: 1.25rem 1.75rem;
}

.table-card .card-header h6 {
    font-weight: 700;
    color: var(--dark-color);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: #f8fafc;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1.25rem;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}

.table tbody td {
    padding: 1.25rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr:last-child td:first-child {
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr:last-child td:last-child {
    border-bottom-right-radius: 0.5rem;
}

/* Badge Styles */
.badge {
    padding: 0.6em 1em;
    font-weight: 600;
    border-radius: 6px;
    letter-spacing: 0.3px;
    font-size: 0.75rem;
}

/* Button Styles */
.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
    letter-spacing: 0.3px;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
}

.btn-info {
    background: var(--info-color);
    border: none;
    color: white;
}

.btn-info:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
}

.btn-light {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
}

.btn-light:hover {
    background: #f1f5f9;
    color: var(--dark-color);
    transform: translateY(-2px);
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
    animation: fadeIn 0.6s ease-out forwards;
}

/* Dashboard Header */
main h1.h2 {
    font-weight: 700;
    color: var(--dark-color);
    letter-spacing: -0.5px;
}

main p.text-muted {
    color: #64748b !important;
    font-size: 1.05rem;
}

/* Scrollbar Styles */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* System Info */
.bg-light {
    background-color: #f1f5f9 !important;
}

.text-primary {
    color: var(--primary-color) !important;
}

.text-success {
    color: var(--success-color) !important;
}

.text-warning {
    color: var(--warning-color) !important;
}

.text-muted {
    color: #64748b !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-card .stat-value {
        font-size: 1.75rem;
    }
    
    .table-responsive {
        border-radius: 12px;
    }
}
    </style>
</head>
<body>
    <nav class="navbar navbar-dark sticky-top flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
            <i class="bi bi-laptop me-2"></i>
            WARINGIN-IT
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" 
                data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap d-flex align-items-center">
                <span class="text-light mx-3">
                    <i class="bi bi-person-circle me-2"></i>
                    <?= htmlspecialchars($admin['nama']) ?>
                </span>
                <a class="nav-link px-3" href="../auth/logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Sign out
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="barang/index.php">
                                <i class="bi bi-laptop"></i>
                                Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kategori/index.php">
                                <i class="bi bi-tags"></i>
                                Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="merk/index.php">
                                <i class="bi bi-bookmark"></i>
                                Merk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="supplier/index.php">
                                <i class="bi bi-truck"></i>
                                Supplier
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="penjualan/index.php">
                                <i class="bi bi-cart"></i>
                                Penjualan
                            </a>
                        </li>
                        <!-- Tambahkan menu Manajemen Admin -->
                        <li class="nav-item">
                            <a class="nav-link" href="user/index.php">
                                <i class="bi bi-people"></i>
                                Manajemen Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="custom_orders/index.php">
                                <i class="bi bi-tools"></i>
                                Custom Order
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 animate-fade-in">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <div>
                        <h1 class="h2 mb-0">Dashboard</h1>
                        <p class="text-muted">Welcome back, <?= htmlspecialchars($admin['nama']) ?></p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d M Y') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistik Cards -->
                <div class="row g-4 mb-4">
                    <!-- Total Produk -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-label">Total Produk</div>
                                        <div class="stat-value"><?= $total_produk ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-laptop fs-3"></i>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: 70%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Kategori -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-label">Total Kategori</div>
                                        <div class="stat-value"><?= $total_kategori ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-tags fs-3"></i>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: 85%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Penjualan -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-label">Total Penjualan</div>
                                        <div class="stat-value"><?= $total_penjualan ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-cart fs-3"></i>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 4px;">
                                    <div class="progress-bar bg-warning" style="width: 60%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total User -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card danger h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-label">Total User</div>
                                        <div class="stat-value"><?= $total_user ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people fs-3"></i>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 4px;">
                                    <div class="progress-bar bg-danger" style="width: 75%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Admin -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card info h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-label">Total Admin</div>
                                        <div class="stat-value"><?= $total_admin ?></div>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-person-badge fs-3"></i>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 4px;">
                                    <div class="progress-bar bg-info" style="width: 65%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Tables Row -->
                <div class="row g-4">
                    <!-- Penjualan Terbaru -->
                    <div class="col-lg-7">
                        <div class="card table-card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6>
                                        <i class="bi bi-clock-history text-primary"></i>
                                        Penjualan Terbaru
                                    </h6>
                                    <button class="btn btn-sm btn-light" title="Refresh">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Customer</th>
                                                <th>Produk</th>
                                                <th>Total</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_sales as $sale) : ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar3 text-muted me-2"></i>
                                                        <?= date('d/m/Y H:i', strtotime($sale['tanggal'])) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($sale['nama_user'] ?? 'User tidak ditemukan') ?></div>
                                                        <small class="text-muted">
                                                            <i class="bi bi-box me-1"></i>
                                                            <?= $sale['jumlah_item'] ?> item
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">
                                                        Rp <?= number_format($sale['total'], 0, ',', '.') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="penjualan/detail.php?id=<?= $sale['penjualan_id'] ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stok Menipis -->
                    <div class="col-lg-5">
                        <div class="card table-card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6>
                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                        Stok Menipis
                                    </h6>
                                    <button class="btn btn-sm btn-light" title="Refresh">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Kategori</th>
                                                <th>Merk</th>
                                                <th>Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($low_stock as $item) : ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-light p-2 rounded me-2">
                                                            <i class="bi bi-laptop text-primary"></i>
                                                        </div>
                                                        <?= htmlspecialchars($item['nama_barang']) ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($item['nama_kategori']) ?></td>
                                                <td><?= htmlspecialchars($item['nama_merk']) ?></td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        <?= $item['stok'] ?> unit
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Sistem -->
                <div class="row">
                    <div class="col-12">
                        <div class="card table-card mb-4">
                            <div class="card-header">
                                <h6>
                                    <i class="bi bi-info-circle text-primary"></i>
                                    Informasi Sistem
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light p-3 rounded me-3">
                                                <i class="bi bi-person text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted">Admin</div>
                                                <div class="h5 mb-0"><?= htmlspecialchars($admin['nama']) ?></div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light p-3 rounded me-3">
                                                <i class="bi bi-clock text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted">Login Terakhir</div>
                                                <div class="h5 mb-0"><?= date('d/m/Y H:i') ?></div>
                                            </div>
                                        </div>
                                        </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light p-3 rounded me-3">
                                                <i class="bi bi-graph-up text-success fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted">Total Transaksi Hari Ini</div>
                                                <div class="h5 mb-0"><?= $transaksi_hari_ini ?></div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light p-3 rounded me-3">
                                                <i class="bi bi-cash-stack text-success fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted">Pendapatan Hari Ini</div>
                                                <div class="h5 mb-0">
                                                    Rp <?= number_format($pendapatan_hari_ini ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto refresh setiap 5 menit
        setTimeout(function() {
            window.location.reload();
        }, 300000);

        // Animasi statistik
        document.addEventListener('DOMContentLoaded', function() {
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.innerHTML = value.toLocaleString();
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            };

            // Animasi untuk stat-value
            document.querySelectorAll('.stat-value').forEach(element => {
                const value = parseInt(element.innerText.replace(/\D/g, ''));
                if (!isNaN(value)) {
                    element.innerText = '0';
                    animateValue(element, 0, value, 1000);
                }
            });
        });
    </script>
</body>
</html>