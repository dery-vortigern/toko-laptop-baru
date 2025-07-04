<?php
session_start();
require_once '../config/koneksi.php';

// Existing PHP code remains the same until the HTML part
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// All existing queries remain the same
$total_produk = query("SELECT COUNT(*) as total FROM tb_barang")[0]['total'];
$total_penjualan = query("SELECT COUNT(*) as total FROM tb_penjualan")[0]['total'];
$total_user = query("SELECT COUNT(*) as total FROM tb_user")[0]['total'];
$pendapatan = query("SELECT SUM(total) as total FROM tb_penjualan")[0]['total'];
$produk_terlaris = query("SELECT b.nama_barang, SUM(dp.jumlah) as total_terjual 
                         FROM tb_detail_penjualan dp 
                         JOIN tb_barang b ON dp.barang_id = b.barang_id 
                         GROUP BY dp.barang_id 
                         ORDER BY total_terjual DESC 
                         LIMIT 5");
$penjualan_terbaru = query("SELECT p.*, u.nama as nama_user 
                           FROM tb_penjualan p 
                           JOIN tb_user u ON p.user_id = u.user_id 
                           ORDER BY p.tanggal DESC 
                           LIMIT 5");
$stok_menipis = query("SELECT b.*, k.nama_kategori 
                      FROM tb_barang b 
                      JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
                      WHERE b.stok < 5");

include_once '../includes/header.php';
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
        color: var(--secondary-color);
    }

    .stats-card {
        height: 100%;
        overflow: hidden;
    }

    .stats-card-body {
        padding: 1.5rem;
        display: flex;
        align-items: center;
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

    .stats-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .stats-link:hover {
        background-color: rgba(37, 99, 235, 0.05);
        color: var(--primary-hover);
    }

    /* Table styling */
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

    /* Badge Styling */
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

    .badge-primary {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    /* Button styling */
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

    .btn-outline-primary {
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        background: transparent;
    }

    .btn-outline-primary:hover, .btn-outline-primary:focus {
        background: rgba(37, 99, 235, 0.05);
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1), 0 2px 4px -2px rgba(37, 99, 235, 0.1);
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

    /* Item icon styling */
    .item-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        margin-right: 0.75rem;
        background-color: #f1f5f9;
        color: var(--primary-color);
        transition: var(--transition);
    }

    .item-icon.warning {
        color: var(--warning-color);
    }

    .item-icon.danger {
        color: var(--danger-color);
    }

    /* Footer styling */
    .card-footer {
        background: white;
        border-top: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h1 class="mb-2 fw-bold">Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Dashboard Overview</li>
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

    <!-- Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Produk -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value"><?= $total_produk ?></div>
                        <p class="stats-label">Total Produk</p>
                    </div>
                </div>
                <a href="produk/index.php" class="stats-link">
                    <span>Lihat Detail</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Penjualan -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon success">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value"><?= $total_penjualan ?></div>
                        <p class="stats-label">Total Penjualan</p>
                    </div>
                </div>
                <a href="penjualan/index.php" class="stats-link">
                    <span>Lihat Detail</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Total User -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon warning">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value"><?= $total_user ?></div>
                        <p class="stats-label">Total User</p>
                    </div>
                </div>
                <a href="user/index.php" class="stats-link">
                    <span>Lihat Detail</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card h-100">
                <div class="stats-card-body">
                    <div class="stats-icon danger">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-value">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
                        <p class="stats-label">Total Pendapatan</p>
                    </div>
                </div>
                <a href="laporan/index.php" class="stats-link">
                    <span>Lihat Laporan</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Produk Terlaris -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-bar-chart-line fs-5 text-primary me-2"></i>
                            <span class="fw-bold">Produk Terlaris</span>
                        </div>
                        <button class="btn btn-light btn-sm" onclick="window.location.reload()" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Total Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produk_terlaris as $produk) : ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="item-icon">
                                                <i class="bi bi-box"></i>
                                            </div>
                                            <span class="fw-medium"><?= htmlspecialchars($produk['nama_barang']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-primary">
                                            <?= $produk['total_terjual'] ?> unit
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

        <!-- Stok Menipis -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-exclamation-triangle fs-5 text-warning me-2"></i>
                            <span class="fw-bold">Stok Menipis</span>
                        </div>
                        <button class="btn btn-light btn-sm" onclick="window.location.reload()" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stok_menipis as $stok) : ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="item-icon warning">
                                                <i class="bi bi-box"></i>
                                            </div>
                                            <span class="fw-medium"><?= htmlspecialchars($stok['nama_barang']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary">
                                            <?= htmlspecialchars($stok['nama_kategori']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-danger">
                                            <?= $stok['stok'] ?> unit
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

    <!-- Penjualan Terbaru -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-clock-history fs-5 text-primary me-2"></i>
                    <span class="fw-bold">Penjualan Terbaru</span>
                </div>
                <button class="btn btn-light btn-sm" onclick="window.location.reload()" title="Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penjualan_terbaru as $penjualan) : ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">#<?= $penjualan['penjualan_id'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-date text-muted me-2"></i>
                                    <?= date('d/m/Y H:i', strtotime($penjualan['tanggal'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="item-icon">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-medium"><?= htmlspecialchars($penjualan['nama_user']) ?></span>
                                </div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                Rp <?= number_format($penjualan['total'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Selesai
                                </span>
                            </td>
                            <td>
                                <a href="penjualan/detail.php?id=<?= $penjualan['penjualan_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    Menampilkan 5 penjualan terbaru
                </span>
                <a href="penjualan/index.php" class="btn btn-sm btn-primary">
                    Lihat Semua Penjualan
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Add smooth animation for cards
document.addEventListener('DOMContentLoaded', function() {
    // Animate numbers in stat cards
    const animateValue = (element, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            
            // Format number with thousand separator if it's a number
            if (element.innerText.includes('Rp')) {
                // Format as currency
                element.innerHTML = 'Rp ' + value.toLocaleString('id-ID');
            } else {
                // Format as regular number
                element.innerHTML = value.toLocaleString('id-ID');
            }
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };

    // Apply animation to stat values
    document.querySelectorAll('.stats-value').forEach(element => {
        let value;
        if (element.innerText.includes('Rp')) {
            // Extract numeric value from currency string
            value = parseInt(element.innerText.replace(/\D/g, ''));
            element.innerText = 'Rp 0';
        } else {
            value = parseInt(element.innerText);
            element.innerText = '0';
        }
        
        if (!isNaN(value)) {
            animateValue(element, 0, value, 1000);
        }
    });

    // Add hover effect to tables
    document.querySelectorAll('.table tr').forEach(row => {
        row.style.transition = 'background-color 0.3s ease';
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>