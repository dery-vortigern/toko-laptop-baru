<?php
session_start();
require_once '../../config/koneksi.php';

// Cek autentikasi admin
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Inisialisasi variabel-variabel filtering
$where = "";
$where_date = "";
$dari = "";
$sampai = "";

// Filter berdasarkan tanggal jika ada
if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    if (!empty($dari) && !empty($sampai)) {
        $where = "WHERE DATE(p.tanggal) BETWEEN '$dari' AND '$sampai'";
        $where_date = "WHERE DATE(tanggal) BETWEEN '$dari' AND '$sampai'";
    }
}

// Query untuk mendapatkan data penjualan
$query = "SELECT p.*, a.nama as admin_name, u.nama as nama_user, u.telepon, pb.jenis_pembayaran, m.nama_merk as merk,
          (SELECT SUM(dp.subtotal) FROM tb_detail_penjualan dp WHERE dp.penjualan_id = p.penjualan_id) as total_penjualan 
          FROM tb_penjualan p 
          LEFT JOIN tb_admin a ON p.admin_id = a.admin_id
          LEFT JOIN tb_pembelian pmb ON p.id_pembelian = pmb.id_pembelian
          LEFT JOIN tb_user u ON pmb.user_id = u.user_id
          LEFT JOIN tb_pembayaran pb ON pmb.pembayaran_id = pb.pembayaran_id
          LEFT JOIN tb_detail_penjualan dp ON p.penjualan_id = dp.penjualan_id
          LEFT JOIN tb_barang b ON dp.barang_id = b.barang_id
          LEFT JOIN tb_merk m ON b.merk_id = m.merk_id
          $where
          GROUP BY p.penjualan_id
          ORDER BY p.tanggal DESC";

          
$penjualan = query($query);

// Query untuk mendapatkan total produk terjual
$query_produk = "SELECT COALESCE(SUM(dp.jumlah), 0) as total 
                FROM tb_detail_penjualan dp 
                JOIN tb_penjualan p ON dp.penjualan_id = p.penjualan_id 
                " . (empty($where) ? "" : str_replace('WHERE', 'WHERE', $where));
$total_produk = query($query_produk)[0]['total'];

// Query untuk mendapatkan total customer
$query_customer = "SELECT COUNT(DISTINCT pmb.user_id) as total 
                  FROM tb_pembelian pmb 
                  JOIN tb_penjualan p ON pmb.id_pembelian = p.id_pembelian
                  " . (empty($where) ? "" : str_replace('WHERE', 'WHERE', $where));
$total_customer = query($query_customer)[0]['total'];

// Memasukkan header
include_once '../includes/header.php';

// HTML dan kode frontend tetap sama seperti sebelumnya
?>

<!-- Custom CSS -->
<style>
    :root {
        --primary-color: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary-color: #475569;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --border-radius: 0.75rem;
        --box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Inter', 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: #f8fafc;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #3b82f6);
        color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        box-shadow: var(--box-shadow);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        bottom: -50%;
        left: -50%;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        transform: rotate(45deg);
        z-index: 0;
    }

    .page-header * {
        position: relative;
        z-index: 1;
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .breadcrumb-item a:hover {
        color: white;
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: white;
        font-weight: 400;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        color: var(--dark-color);
    }

    .table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background: #1e293b;
        color: white;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border: none;
    }

    .table th:first-child {
        border-top-left-radius: 0.5rem;
    }

    .table th:last-child {
        border-top-right-radius: 0.5rem;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        transition: var(--transition);
    }

    .table tr:hover td {
        background-color: #f8fafc;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:last-child td:first-child {
        border-bottom-left-radius: 0.5rem;
    }

    .table tr:last-child td:last-child {
        border-bottom-right-radius: 0.5rem;
    }

    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
        border-radius: 2rem;
        letter-spacing: 0.5px;
        transition: var(--transition);
    }

    .badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    }

    .btn {
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        color: white;
    }

    .btn-primary:hover, .btn-primary:focus {
        background: var(--primary-hover);
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5), 0 2px 4px -2px rgba(37, 99, 235, 0.5);
        transform: translateY(-2px);
    }

    .btn-warning {
        background: var(--warning-color);
        border: none;
        color: white;
    }

    .btn-warning:hover, .btn-warning:focus {
        background: #ea580c;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.5), 0 2px 4px -2px rgba(245, 158, 11, 0.5);
        transform: translateY(-2px);
    }

    .btn-danger {
        background: var(--danger-color);
        border: none;
    }

    .btn-danger:hover, .btn-danger:focus {
        background: #dc2626;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.5), 0 2px 4px -2px rgba(239, 68, 68, 0.5);
        transform: translateY(-2px);
    }

    .btn-light {
        background: white;
        border: 1px solid #e2e8f0;
        color: var(--secondary-color);
    }

    .btn-light:hover, .btn-light:focus {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--dark-color);
    }

    .btn-info {
        background: #3b82f6;
        border: none;
        color: white;
    }

    .btn-info:hover, .btn-info:focus {
        background: #2563eb;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5), 0 2px 4px -2px rgba(59, 130, 246, 0.5);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #64748b;
        border: none;
        color: white;
    }

    .btn-secondary:hover, .btn-secondary:focus {
        background: #475569;
        box-shadow: 0 4px 6px -1px rgba(100, 116, 139, 0.5), 0 2px 4px -2px rgba(100, 116, 139, 0.5);
        transform: translateY(-2px);
    }

    .btn-success {
        background: var(--success-color);
        border: none;
        color: white;
    }

    .btn-success:hover, .btn-success:focus {
        background: #059669;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.5), 0 2px 4px -2px rgba(16, 185, 129, 0.5);
        transform: translateY(-2px);
    }

    .alert {
        border: none;
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
    }

    .alert-danger {
        background-color: #fef2f2;
        color: #991b1b;
    }

    .alert-dismissible .btn-close {
        color: inherit;
        opacity: 0.8;
    }

    /* Stats Card Styling */
    .stats-card {
        border-radius: var(--border-radius);
        background: white;
        overflow: hidden;
        height: 100%;
    }

    .stats-card-body {
        display: flex;
        align-items: center;
        padding: 1.5rem;
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .stats-icon.primary {
        background-color: rgba(37, 99, 235, 0.1);
        color: var(--primary-color);
    }

    .stats-icon.success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }

    .stats-icon.warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }

    .stats-icon.danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }

    .stats-info {
        flex-grow: 1;
    }

    .stats-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .stats-label {
        color: var(--secondary-color);
        font-size: 0.95rem;
        margin: 0;
    }

    /* Filter card */
    .filter-card {
        margin-bottom: 2rem;
        padding: 1.5rem;
    }

    .filter-card .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #334155;
    }

    .filter-card .form-control {
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        background-color: #f8fafc;
        transition: var(--transition);
    }

    .filter-card .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
    }

    /* DataTables customization */
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        font-size: 0.875rem;
        background-position: right 0.5rem center;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        min-width: 250px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        outline: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: var(--secondary-color) !important;
        font-weight: 500;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        transition: var(--transition);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border-color: var(--primary-color);
        background: #f1f5f9;
        color: var(--primary-color) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
        font-size: 0.875rem;
        color: var(--secondary-color);
    }

    /* Badge colors */
    .badge.bg-info {
        background-color: #dbeafe !important;
        color: #1e40af;
    }

    .badge.bg-success {
        background-color: #d1fae5 !important;
        color: #065f46;
    }

    .badge.bg-danger {
        background-color: #fecaca !important;
        color: #991b1b;
    }

    .badge.bg-warning {
        background-color: #fef3c7 !important;
        color: #92400e;
    }

    .badge.bg-light {
        background-color: #f3f4f6 !important;
        color: #374151;
    }

    /* Animasi untuk alert */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert {
        animation: fadeInDown 0.5s ease-out;
    }

    /* Tombol export */
    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-buttons .btn {
        margin-right: 0.5rem;
    }
</style>

<div class="container-fluid px-4">
    <!-- Header Halaman -->
    <div class="page-header">
        <h1 class="mb-2 fw-bold">Data Penjualan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Penjualan</li>
            </ol>
        </nav>
    </div>

    <!-- Tombol & Pesan -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div></div>
        <div class="d-flex align-items-center">
            <i class="bi bi-clock-history text-muted me-2"></i>
            <span class="text-muted">Terakhir diperbarui: <?= date('d M Y H:i') ?></span>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
                <strong>Berhasil!</strong> <?= $_SESSION['success']; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card filter-card mb-4">
        <form action="" method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">
                    <i class="bi bi-calendar-event me-1"></i>
                    Dari Tanggal
                </label>
                <input type="date" class="form-control" name="dari" 
                       value="<?= isset($_GET['dari']) ? $_GET['dari'] : ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">
                    <i class="bi bi-calendar-event me-1"></i>
                    Sampai Tanggal
                </label>
                <input type="date" class="form-control" name="sampai" 
                       value="<?= isset($_GET['sampai']) ? $_GET['sampai'] : ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <?php if (isset($_GET['dari']) || isset($_GET['sampai'])) : ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    <?php endif; ?>
                    <!-- Tombol Export yang Terpisah dari Kondisi Filter -->
                    <button type="button" class="btn btn-success" onclick="exportExcel()">
                        <i class="bi bi-file-excel me-1"></i> Export Excel
                    </button>

                    <button type="button" class="btn btn-danger" onclick="exportPDF()">
            <i class="bi bi-file-pdf me-1"></i> Export PDF
        </button>
                    <!-- Tambahan tombol untuk PhpSpreadsheet jika sudah diinstal -->
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Transaksi -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon primary">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value"><?= count($penjualan); ?></div>
                        <p class="stats-label">Total Transaksi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon success">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value">
                            Rp <?= number_format(array_sum(array_column($penjualan, 'total')), 0, ',', '.'); ?>
                        </div>
                        <p class="stats-label">Total Pendapatan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Produk -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value"><?= $total_produk; ?></div>
                        <p class="stats-label">Total Produk Terjual</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customer -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon danger">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value"><?= $total_customer; ?></div>
                        <p class="stats-label">Total Customer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-table fs-5 text-primary me-2"></i>
                    <span class="fw-bold">Data Penjualan</span>
                </div>
                <button class="btn btn-light btn-sm" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pembeli</th>
                            <th>Telepon</th>
                            <th>Merk</th>
                            <th>Jenis Pembayaran</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                            <th>Admin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($penjualan as $row) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <i class="bi bi-calendar2 text-muted me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($row['tanggal'])); ?>
                            </td>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($row['nama_user'] ?? 'User tidak ditemukan'); ?></div>
                            </td>
                            <td>
                                <i class="bi bi-telephone text-muted me-1"></i>
                                <?= htmlspecialchars($row['telepon'] ?? '-'); ?>
                            </td>

                                    <td>
            <span class="badge bg-light">
                <?= htmlspecialchars($row['merk'] ?? '-'); ?>
            </span>
        </td>

                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($row['jenis_pembayaran'] ?? '-'); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-success">
                                Rp <?= number_format($row['total'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                Rp <?= number_format($row['bayar'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                Rp <?= number_format($row['kembalian'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <span class="badge bg-light">
                                    <i class="bi bi-person text-muted me-1"></i>
                                    <?= htmlspecialchars($row['admin_name']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="detail.php?id=<?= $row['penjualan_id']; ?>" 
                                       class="btn btn-info btn-sm" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="cetak.php?id=<?= $row['penjualan_id']; ?>" 
                                       target="_blank" 
                                       class="btn btn-secondary btn-sm" 
                                       title="Cetak">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script JavaScript untuk Export dan DataTables -->
<!-- Script JavaScript untuk Export dan DataTables -->
<script>
/**
 * Fungsi untuk export data ke Excel dengan format sederhana
 */

function exportPDF() {
    // Mendapatkan parameter filter dari URL saat ini
    var urlParams = new URLSearchParams(window.location.search);
    var dari = urlParams.get('dari') || '';
    var sampai = urlParams.get('sampai') || '';
    
    // Variabel untuk menyimpan informasi sorting
    var sort = 'p.tanggal';  // Default sort column
    var sortOrder = 'DESC';  // Default sort order
    
    // Jika DataTables tersedia, ambil informasi sorting dari sana
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#dataTable')) {
        var dataTable = jQuery('#dataTable').DataTable();
        var order = dataTable.order();
        
        if (order && order.length > 0) {
            // Pemetaan indeks kolom DataTables ke nama kolom database
            var columnMapping = [
                null,           // Kolom No (tidak perlu diurutkan di database)
                'p.tanggal',    // Kolom Tanggal
                'u.nama',       // Kolom Pembeli
                'u.telepon',    // Kolom Telepon
                'pb.jenis_pembayaran', // Kolom Jenis Pembayaran
                'p.total',      // Kolom Total
                'p.bayar',      // Kolom Bayar
                'p.kembalian',  // Kolom Kembalian
                'a.nama'        // Kolom Admin
            ];
            
            // Mendapatkan indeks kolom dan arah pengurutan dari DataTables
            var columnIndex = order[0][0];
            var direction = order[0][1];
            
            // Memastikan indeks kolom valid untuk pemetaan kita
            if (columnIndex < columnMapping.length && columnMapping[columnIndex]) {
                sort = columnMapping[columnIndex];
                sortOrder = direction.toUpperCase();
            }
        }
    }
    
    // Membuat URL export PDF
    var exportUrl = "export_pdf.php?";
    var params = [];
    
    if (dari) {
        params.push("dari=" + dari);
    }
    if (sampai) {
        params.push("sampai=" + sampai);
    }
    
    // Tambahkan parameter sorting ke URL export
    params.push("sort=" + sort);
    params.push("order=" + sortOrder);
    
    // Gabungkan semua parameter ke URL
    exportUrl += params.join('&');
    
    // Arahkan ke script export PDF
    window.location.href = exportUrl;
}

function exportExcel() {
    // Mendapatkan parameter filter dari URL saat ini
    var urlParams = new URLSearchParams(window.location.search);
    var dari = urlParams.get('dari') || '';
    var sampai = urlParams.get('sampai') || '';
    
    // Variabel untuk menyimpan informasi sorting
    var sort = 'p.tanggal';  // Default sort column
    var sortOrder = 'DESC';  // Default sort order
    
    // Jika DataTables tersedia, ambil informasi sorting dari sana
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#dataTable')) {
        var dataTable = jQuery('#dataTable').DataTable();
        var order = dataTable.order();
        
        if (order && order.length > 0) {
            // Pemetaan indeks kolom DataTables ke nama kolom database
            var columnMapping = [
                null,           // Kolom No (tidak perlu diurutkan di database)
                'p.tanggal',    // Kolom Tanggal
                'u.nama',       // Kolom Pembeli
                'u.telepon',    // Kolom Telepon
                'm.nana_merk', // Kolom Merk
                'pb.jenis_pembayaran', // Kolom Jenis Pembayaran
                'p.total',      // Kolom Total
                'p.bayar',      // Kolom Bayar
                'p.kembalian',  // Kolom Kembalian
                'a.nama'        // Kolom Admin
            ];
            
            // Mendapatkan indeks kolom dan arah pengurutan dari DataTables
            var columnIndex = order[0][0];
            var direction = order[0][1];
            
            // Memastikan indeks kolom valid untuk pemetaan kita
            if (columnIndex < columnMapping.length && columnMapping[columnIndex]) {
                sort = columnMapping[columnIndex];
                sortOrder = direction.toUpperCase();
            }
        }
    }
    
    // Membuat URL export
    var exportUrl = "export.php?";
    var params = [];
    
    if (dari) {
        params.push("dari=" + dari);
    }
    if (sampai) {
        params.push("sampai=" + sampai);
    }
    
    // Tambahkan parameter sorting ke URL export
    params.push("sort=" + sort);
    params.push("order=" + sortOrder);
    
    // Gabungkan semua parameter ke URL
    exportUrl += params.join('&');
    
    // Arahkan ke script export
    window.location.href = exportUrl;
}

/**
 * Fungsi untuk export Excel dengan format yang lebih baik menggunakan PhpSpreadsheet
 */
function exportExcelAdvanced() {
    // Mendapatkan parameter filter dari URL saat ini
    var urlParams = new URLSearchParams(window.location.search);
    var dari = urlParams.get('dari') || '';
    var sampai = urlParams.get('sampai') || '';
    
    // Variabel untuk menyimpan informasi sorting
    var sort = 'p.tanggal';  // Default sort column
    var sortOrder = 'DESC';  // Default sort order
    
    // Jika DataTables tersedia, ambil informasi sorting dari sana
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#dataTable')) {
        var dataTable = jQuery('#dataTable').DataTable();
        var order = dataTable.order();
        
        if (order && order.length > 0) {
            // Pemetaan indeks kolom DataTables ke nama kolom database
            var columnMapping = [
                null,           // Kolom No (tidak perlu diurutkan di database)
                'p.tanggal',    // Kolom Tanggal
                'u.nama',       // Kolom Pembeli
                'u.telepon',    // Kolom Telepon
                'pb.jenis_pembayaran', // Kolom Jenis Pembayaran
                'p.total',      // Kolom Total
                'p.bayar',      // Kolom Bayar
                'p.kembalian',  // Kolom Kembalian
                'a.nama'        // Kolom Admin
            ];
            
            // Mendapatkan indeks kolom dan arah pengurutan dari DataTables
            var columnIndex = order[0][0];
            var direction = order[0][1];
            
            // Memastikan indeks kolom valid untuk pemetaan kita
            if (columnIndex < columnMapping.length && columnMapping[columnIndex]) {
                sort = columnMapping[columnIndex];
                sortOrder = direction.toUpperCase();
            }
        }
    }
    
    // Membuat URL export
    var exportUrl = "export_phpspreadsheet.php?";
    var params = [];
    
    if (dari) {
        params.push("dari=" + dari);
    }
    if (sampai) {
        params.push("sampai=" + sampai);
    }
    
    // Tambahkan parameter sorting ke URL export
    params.push("sort=" + sort);
    params.push("order=" + sortOrder);
    
    // Gabungkan semua parameter ke URL
    exportUrl += params.join('&');
    
    // Arahkan ke script export dengan PhpSpreadsheet
    window.location.href = exportUrl;
}

// Tunggu sampai dokumen siap dan jQuery tersedia
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi DataTables jika jQuery tersedia
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
        jQuery('#dataTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            },
            order: [[1, 'desc']] // Default sort by tanggal (column 1) in descending order
        });
    }
});
</script>