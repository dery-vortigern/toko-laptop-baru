<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Cek parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$penjualan_id = $_GET['id'];

// Query untuk mendapatkan detail penjualan dengan info user dan pembayaran
$query = "SELECT p.*, a.nama as admin_name, u.nama as nama_user, u.alamat, u.telepon, 
          pb.jenis_pembayaran, GROUP_CONCAT(DISTINCT b.nama_barang SEPARATOR ', ') as produk_dibeli
          FROM tb_penjualan p 
          LEFT JOIN tb_admin a ON p.admin_id = a.admin_id
          LEFT JOIN tb_pembelian pmb ON p.id_pembelian = pmb.id_pembelian
          LEFT JOIN tb_user u ON pmb.user_id = u.user_id
          LEFT JOIN tb_pembayaran pb ON pmb.pembayaran_id = pb.pembayaran_id
          LEFT JOIN tb_detail_penjualan dp ON p.penjualan_id = dp.penjualan_id
          LEFT JOIN tb_barang b ON dp.barang_id = b.barang_id
          WHERE p.penjualan_id = $penjualan_id
          GROUP BY p.penjualan_id";
$penjualan = query($query)[0];

// Ambil detail produk yang dibeli
$detail_query = "SELECT dp.*, b.nama_barang, b.harga_jual, b.gambar,
                k.nama_kategori, m.nama_merk 
                FROM tb_detail_penjualan dp 
                JOIN tb_barang b ON dp.barang_id = b.barang_id 
                LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
                LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
                WHERE dp.penjualan_id = $penjualan_id";
$details = query($detail_query);

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

    /* Detail section styling */
    .detail-section {
        background-color: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .detail-section:hover {
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    }

    .detail-section h6 {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .detail-label {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .detail-value {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 1rem;
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

    .img-thumbnail {
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: transform 0.2s;
        border: 2px solid #e2e8f0;
        padding: 0.25rem;
        object-fit: cover;
    }

    .img-thumbnail:hover {
        transform: scale(1.05);
        border-color: var(--primary-color);
    }

    /* Table footer styling */
    tfoot tr td {
        background-color: #f8fafc;
        font-weight: 500;
    }

    tfoot tr:last-child td {
        border-bottom: none;
    }

    /* Badge styling */
    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
        border-radius: 2rem;
        letter-spacing: 0.5px;
    }

    .badge-info {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }

    /* Info icons */
    .info-icon {
        color: var(--primary-color);
        background-color: rgba(37, 99, 235, 0.1);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.25rem;
    }

    /* Print button styling */
    .print-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        transition: all 0.3s ease;
    }

    .print-btn:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5);
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h1 class="mb-2 fw-bold">Detail Penjualan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Penjualan</a></li>
                <li class="breadcrumb-item active">Detail Penjualan</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="info-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <span class="fs-5 fw-bold">Detail Transaksi #<?= $penjualan_id ?></span>
                        </div>
                        <div>
                            <a href="cetak.php?id=<?= $penjualan_id ?>" target="_blank" class="btn print-btn">
                                <i class="bi bi-printer me-2"></i> Cetak Nota
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <!-- Informasi Pembeli -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="detail-section h-100">
                                <h6 class="d-flex align-items-center">
                                    <i class="bi bi-person me-2 text-primary"></i>
                                    Data Pembeli
                                </h6>
                                <div class="mb-3">
                                    <div class="detail-label">Nama</div>
                                    <div class="detail-value"><?= htmlspecialchars($penjualan['nama_user'] ?? 'User tidak ditemukan'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Telepon</div>
                                    <div class="detail-value"><?= htmlspecialchars($penjualan['telepon'] ?? '-'); ?></div>
                                </div>
                                <div>
                                    <div class="detail-label">Alamat</div>
                                    <div class="detail-value"><?= htmlspecialchars($penjualan['alamat'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Transaksi -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="detail-section h-100">
                                <h6 class="d-flex align-items-center">
                                    <i class="bi bi-receipt me-2 text-primary"></i>
                                    Detail Transaksi
                                </h6>
                                <div class="mb-3">
                                    <div class="detail-label">Tanggal</div>
                                    <div class="detail-value">
                                        <i class="bi bi-calendar-date text-muted me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($penjualan['tanggal'])); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Admin</div>
                                    <div class="detail-value">
                                        <i class="bi bi-person-badge text-muted me-1"></i>
                                        <?= htmlspecialchars($penjualan['admin_name']); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="detail-label">Metode Pembayaran</div>
                                    <div class="detail-value">
                                        <span class="badge badge-info">
                                            <i class="bi bi-credit-card me-1"></i>
                                            <?= htmlspecialchars($penjualan['jenis_pembayaran'] ?? '-'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Pembayaran -->
                        <div class="col-lg-4 col-md-12 mb-4">
                            <div class="detail-section h-100">
                                <h6 class="d-flex align-items-center">
                                    <i class="bi bi-cash-coin me-2 text-primary"></i>
                                    Pembayaran
                                </h6>
                                <div class="mb-3">
                                    <div class="detail-label">Total</div>
                                    <div class="detail-value fs-5 fw-bold text-success">
                                        Rp <?= number_format($penjualan['total'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Bayar</div>
                                    <div class="detail-value">
                                        Rp <?= number_format($penjualan['bayar'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="detail-label">Kembalian</div>
                                    <div class="detail-value">
                                        Rp <?= number_format($penjualan['kembalian'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Produk -->
                    <div class="detail-section">
                        <h6 class="d-flex align-items-center mb-3">
                            <i class="bi bi-box-seam me-2 text-primary"></i>
                            Detail Produk
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="8%">Gambar</th>
                                        <th width="20%">Produk</th>
                                        <th width="12%">Kategori</th>
                                        <th width="12%">Merk</th>
                                        <th width="13%">Harga</th>
                                        <th width="10%">Jumlah</th>
                                        <th width="15%">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($details as $item) : ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <?php if ($item['gambar'] && file_exists("../../assets/img/barang/" . $item['gambar'])) : ?>
                                                <img src="../../assets/img/barang/<?= $item['gambar']; ?>" 
                                                     alt="<?= $item['nama_barang']; ?>" 
                                                     class="img-thumbnail"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else : ?>
                                                <img src="../../assets/img/no-image.jpg" 
                                                     alt="No Image" 
                                                     class="img-thumbnail"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-primary"><?= htmlspecialchars($item['nama_barang']); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($item['nama_kategori']); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($item['nama_merk']); ?></td>
                                        <td>Rp <?= number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                                        <td class="text-center"><?= $item['jumlah']; ?></td>
                                        <td class="fw-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-end"><strong>Total:</strong></td>
                                        <td class="fw-bold fs-5 text-success">Rp <?= number_format($penjualan['total'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end">Bayar:</td>
                                        <td>Rp <?= number_format($penjualan['bayar'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end">Kembalian:</td>
                                        <td>Rp <?= number_format($penjualan['kembalian'], 0, ',', '.'); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>