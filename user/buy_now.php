<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek sesi buy_now
if (!isset($_SESSION['buy_now']) || empty($_SESSION['buy_now'])) {
    header("Location: index.php");
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

// Ambil data metode pembayaran
$payments = query("SELECT * FROM tb_pembayaran");

// Hitung total dan ambil detail barang di "buy now"
$total = 0;
$buy_now_items = [];
foreach ($_SESSION['buy_now'] as $barang_id => $qty) {
    $barang = query("SELECT * FROM tb_barang WHERE barang_id = $barang_id")[0];
    $subtotal = $barang['harga_jual'] * $qty;
    $total += $subtotal;
    
    $buy_now_items[] = [
        'barang' => $barang,
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

// Proses checkout
// Proses checkout
if (isset($_POST['checkout'])) {
    $pembayaran_id = $_POST['pembayaran_id'];
    
    // Jika tidak ada input bayar, setel nilai bayar otomatis dengan total
    if (empty($_POST['bayar'])) {
        $bayar = $total;
    } else {
        // Jika ada input bayar dari user, gunakan input tersebut
        $bayar = str_replace(['Rp', '.', ','], '', $_POST['bayar']);
    }
    
    $kembalian = $bayar - $total;

    // Validasi pembayaran
    if ($bayar < $total) {
        $error = "Pembayaran kurang dari total belanja!";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // Insert pembelian
            $data_pembelian = [
                'user_id' => $user_id,
                'pembayaran_id' => $pembayaran_id,
                'tanggal' => date('Y-m-d'),
                'bayar' => $bayar,
                'jumlah_pembayaran' => $total,
                'kembalian' => $kembalian
            ];

            if (tambah('tb_pembelian', $data_pembelian)) {
                $id_pembelian = mysqli_insert_id($conn);
                
                // Ambil admin_id dari database (admin yang aktif)
                $query_admin = "SELECT admin_id FROM tb_admin WHERE status = 1 LIMIT 1";
                $result_admin = mysqli_query($conn, $query_admin);

                if (mysqli_num_rows($result_admin) > 0) {
                    $admin = mysqli_fetch_assoc($result_admin);
                    $admin_id = $admin['admin_id'];
                } else {
                    // Cari admin dengan ID 1 untuk fallback
                    $query_admin_default = "SELECT admin_id FROM tb_admin WHERE admin_id = 1";
                    $result_admin_default = mysqli_query($conn, $query_admin_default);
                    if (mysqli_num_rows($result_admin_default) > 0) {
                        $admin_id = 1;
                    } else {
                        // Ambil admin ID pertama yang tersedia
                        $query_any_admin = "SELECT admin_id FROM tb_admin LIMIT 1";
                        $result_any_admin = mysqli_query($conn, $query_any_admin);
                        if (mysqli_num_rows($result_any_admin) > 0) {
                            $admin = mysqli_fetch_assoc($result_any_admin);
                            $admin_id = $admin['admin_id'];
                        } else {
                            throw new Exception("Tidak ada admin yang tersedia di sistem!");
                        }
                    }
                }
                
                $data_penjualan = [
                    'admin_id' => $admin_id,
                    'tanggal' => date('Y-m-d H:i:s'),
                    'bayar' => $bayar,
                    'total' => $total,
                    'kembalian' => $kembalian,
                    'id_pembelian' => $id_pembelian  // Tambahkan relasi ke tb_pembelian
                ];

                if (tambah('tb_penjualan', $data_penjualan)) {
                    $penjualan_id = mysqli_insert_id($conn);
                    
                    // Update tb_pembelian untuk menambahkan referensi ke tb_penjualan
                    $update_pembelian = "UPDATE tb_pembelian SET penjualan_id = $penjualan_id WHERE id_pembelian = $id_pembelian";
                    if (!mysqli_query($conn, $update_pembelian)) {
                        throw new Exception("Gagal menyimpan referensi pembelian: " . mysqli_error($conn));
                    }
                    
                    $all_success = true;

                    foreach ($buy_now_items as $item) {
                        $barang = $item['barang'];
                        $qty = $item['qty'];
                        $subtotal = $item['subtotal'];

                        // Detail pembelian
                        $detail_pembelian_success = tambah('tb_detail_pembelian', [
                            'barang_id' => $barang['barang_id'],
                            'id_pembelian' => $id_pembelian,
                            'jumlah' => $qty,
                            'subtotal' => $subtotal
                        ]);

                        // Detail penjualan
                        $detail_penjualan_success = tambah('tb_detail_penjualan', [
                            'penjualan_id' => $penjualan_id,
                            'barang_id' => $barang['barang_id'],
                            'jumlah' => $qty,
                            'subtotal' => $subtotal
                        ]);

                        // Update stok
                        $stok_baru = $barang['stok'] - $qty;
                        if ($stok_baru < 0) {
                            throw new Exception("Stok barang {$barang['nama_barang']} tidak mencukupi!");
                        }
                        $update_stok_success = ubah('tb_barang', ['stok' => $stok_baru], "barang_id = {$barang['barang_id']}");

                        if (!$detail_pembelian_success || !$detail_penjualan_success || !$update_stok_success) {
                            $all_success = false;
                            break;
                        }
                    }

                    if ($all_success) {
                        mysqli_commit($conn);
                        // Logging untuk debugging
                        error_log("Pembelian langsung berhasil disimpan dengan ID: $id_pembelian, Penjualan ID: $penjualan_id");
                        
                        // Bersihkan data buy_now
                        unset($_SESSION['buy_now']);
                        
                        // Set pesan sukses dengan informasi kembalian
                        $_SESSION['success'] = "Pembelian berhasil! Order ID: #$id_pembelian. Kembalian Anda: Rp " . number_format($kembalian, 0, ',', '.');
                        
                        // Redirect ke halaman orders
                        header("Location: orders.php");
                        exit;
                    } else {
                        throw new Exception("Gagal menyimpan detail transaksi!");
                    }
                } else {
                    throw new Exception("Gagal menyimpan data penjualan!");
                }
            } else {
                throw new Exception("Gagal menyimpan data pembelian!");
            }
            
            mysqli_rollback($conn);
            $error = "Gagal memproses pembelian!";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Error: " . $e->getMessage();
            // Logging untuk debugging
            error_log("Error saat buy now checkout: " . $e->getMessage());
        }
    }
}


// HTML dan kode frontend tetap sama seperti sebelumnya
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout "Beli Sekarang" - WARINGIN-IT</title>
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

/* Alert Styling */
.alert {
    border-radius: 16px;
    border: none;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
    display: flex;
    align-items: center;
}

.alert-danger {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #b91c1c;
}

.alert i {
    font-size: 1.2rem;
    margin-right: 0.75rem;
}

.btn-close {
    margin-left: auto;
    opacity: 0.8;
    transition: var(--transition);
}

.btn-close:hover {
    opacity: 1;
    transform: rotate(90deg);
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

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.card-title i {
    color: var(--primary-color);
    margin-right: 0.5rem;
    font-size: 1.2rem;
    transition: var(--transition);
}

.card:hover .card-title i {
    transform: scale(1.1);
}

/* User Details Box */
.p-3.bg-light.rounded {
    background: #f1f5f9 !important;
    border-radius: 12px !important;
    padding: 1.25rem !important;
    transition: var(--transition);
    border-left: 4px solid var(--primary-color);
}

.card:hover .p-3.bg-light.rounded {
    background: #f8fafc !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.p-3.bg-light.rounded p {
    margin-bottom: 0.75rem !important;
    display: flex;
    align-items: center;
}

.p-3.bg-light.rounded p:last-child {
    margin-bottom: 0 !important;
}

.p-3.bg-light.rounded p strong {
    min-width: 80px;
    display: inline-block;
    color: #475569;
    font-weight: 600;
}

/* Table Styling */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.table {
    margin-bottom: 0;
}

.table > thead {
    background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
}

.table > thead th {
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1.25rem 1rem;
    border: none;
}

.table > tbody td {
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

tfoot {
    background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
    font-weight: 600;
}

tfoot td {
    padding: 1.25rem 1rem !important;
    color: #1e293b;
}

/* Form Elements */
.form-label {
    color: #475569;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-select, 
.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: var(--transition);
    color: #1e293b;
}

.form-select:focus,
.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
}

.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%234361ee' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
    background-position: right 0.75rem center;
    background-size: 12px;
}

.input-group-text {
    border: 2px solid #e2e8f0;
    background: #f1f5f9;
    border-radius: 12px 0 0 12px;
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: #475569;
}

.input-group .form-control {
    border-left: 0;
    border-radius: 0 12px 12px 0;
}

.form-control.is-invalid {
    border-color: var(--danger-color) !important;
    background-image: none;
    padding-right: 0.75rem;
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.15);
}

/* Button Styles */
.btn-primary {
    background: var(--primary-gradient);
    border: none;
    border-radius: 12px;
    padding: 0.9rem 1.5rem;
    font-weight: 600;
    transition: var(--transition);
    color: white;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #3a0ca3, #4361ee);
    transition: var(--transition);
    z-index: -1;
    opacity: 0;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(67, 97, 238, 0.25);
    color: white;
}

.btn-primary:hover::before {
    opacity: 1;
}

.btn-primary i {
    transition: var(--transition);
}

.btn-primary:hover i {
    transform: scale(1.1);
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

.row > * {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
}

.col-md-8 {
    animation-delay: 0.1s;
}

.col-md-4 {
    animation-delay: 0.3s;
}

/* Checkout-specific Enhancements */
.form-control.rupiah-input {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .card-body {
        padding: 1.25rem;
    }
    
    .table > thead th,
    .table > tbody td,
    tfoot td {
        padding: 0.75rem;
    }
    
    .btn-primary {
        padding: 0.75rem 1.25rem;
    }
}

/* Product Row Hover */
.table > tbody tr {
    transition: var(--transition);
}

.table > tbody tr:hover {
    background-color: #f8fafc;
}

.table > tbody tr:hover td {
    color: #1e293b;
}

/* Price Styling */
.table td:nth-child(2),
.table td:nth-child(4) {
    font-weight: 500;
    color: var(--primary-color);
}

tfoot td:last-child {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
}

/* Buy Now Header */
.buy-now-header {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.buy-now-header i {
    margin-right: 0.5rem;
}

/* Buy Now Animation */
@keyframes pulse-btn {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

.btn-buy-now {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: white;
    animation: pulse-btn 2s infinite;
}

.btn-buy-now:hover {
    background: linear-gradient(135deg, #059669, #10b981);
    box-shadow: 0 8px 15px rgba(16, 185, 129, 0.25);
    color: white;
}

/* Pembelian Langsung Callout */
.buy-now-callout {
    background: #ecfdf5;
    border-left: 4px solid #10b981;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.buy-now-callout h6 {
    color: #065f46;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.buy-now-callout h6 i {
    margin-right: 0.5rem;
}

.buy-now-callout p {
    color: #047857;
    margin-bottom: 0;
    font-size: 0.95rem;
}

/* Shake animation for invalid elements */
@keyframes shake {
    0%, 100% {transform: translateX(0);}
    10%, 30%, 50%, 70%, 90% {transform: translateX(-5px);}
    20%, 40%, 60%, 80% {transform: translateX(5px);}
}

.shake-effect {
    animation: shake 0.5s ease-in-out;
}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-laptop me-2"></i>WARINGIN-IT
            </a>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4 fw-bold">
            <i class="bi bi-lightning-fill me-2"></i>Beli Sekarang
        </h2>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Callout Buy Now -->
        <div class="buy-now-callout">
            <h6><i class="bi bi-info-circle-fill"></i> Pembelian Langsung</h6>
            <p>Anda sedang melakukan pembelian langsung produk. Silahkan selesaikan pembayaran untuk menyelesaikan transaksi.</p>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-person-lines-fill me-2"></i>Detail Pengiriman
                        </h5>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-1"><strong>Nama:</strong> <?= $user['nama']; ?></p>
                            <p class="mb-1"><strong>Alamat:</strong> <?= $user['alamat']; ?></p>
                            <p class="mb-0"><strong>Telepon:</strong> <?= $user['telepon']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-cart-check me-2"></i>Detail Pesanan
                        </h5>
                        <div class="buy-now-header mb-3">
                            <i class="bi bi-lightning-fill"></i> Pembelian Langsung
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($buy_now_items as $item): ?>
                                    <tr>
                                        <td><?= $item['barang']['nama_barang']; ?></td>
                                        <td>Rp <?= number_format($item['barang']['harga_jual'], 0, ',', '.'); ?></td>
                                        <td><?= $item['qty']; ?></td>
                                        <td>Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">Total:</td>
                                        <td>Rp <?= number_format($total, 0, ',', '.'); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-wallet2 me-2"></i>Pembayaran
                        </h5>
                        <form action="" method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="total" value="<?= $total; ?>">

                            <div class="mb-3">
                                <label class="form-label">Jenis Pembayaran</label>
                                <select class="form-select" name="pembayaran_id" required>
                                    <option value="">Pilih Jenis Pembayaran</option>
                                    <?php foreach ($payments as $payment): ?>
                                        <option value="<?= $payment['pembayaran_id']; ?>">
                                            <?= $payment['jenis_pembayaran']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Silakan pilih jenis pembayaran.
                                </div>
                            </div>

                            <!-- Kembalian preview container -->
                            <div id="kembalian-preview" class="mt-3 p-3 rounded bg-danger-subtle" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Kurang:</span>
                                    <span class="fw-bold fs-5">Rp 0</span>
                                </div>
                            </div>

                            <button type="submit" name="checkout" class="btn btn-buy-now w-100 mt-3" id="btn-checkout">
                                <i class="bi bi-check-circle me-2"></i>Proses Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Format number to Rupiah
        function formatRupiah(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }
        
        // Format all rupiah inputs on page load
        document.querySelectorAll('.rupiah-input').forEach(function(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) {
                input.value = formatRupiah(value);
                // Calculate change if there's an initial value
                calculateChange(value);
            } else {
                // Show default change container
                const kembalianEl = document.getElementById('kembalian-preview');
                if (kembalianEl) {
                    kembalianEl.style.display = 'block';
                }
            }
        
            // Event listener for input
            input.addEventListener('keyup', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                this.value = formatRupiah(value);
                
                const min = parseInt(this.dataset.min);
                const current = parseInt(value);
                
                if (current < min) {
                    this.classList.add('is-invalid');
                    showValidationFeedback(this, `Minimal pembayaran: Rp ${formatRupiah(min.toString())}`);
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    hideValidationFeedback(this);
                    
                    // Remove is-valid class after some time
                    setTimeout(() => {
                        this.classList.remove('is-valid');
                    }, 2000);
                }
                
                // Calculate and display change in real-time
                calculateChange(value);
            });
        });
        
        // Function to calculate change in real-time
        function calculateChange(inputValue) {
            const total = document.querySelector('input[name="total"]').value;
            const bayar = parseInt(inputValue) || 0;
            const kembalian = bayar - parseInt(total);
            
            // Get or create change preview container
            let kembalianEl = document.getElementById('kembalian-preview');
            
            if (!kembalianEl) {
                kembalianEl = document.createElement('div');
                kembalianEl.id = 'kembalian-preview';
                kembalianEl.className = 'mt-3 p-3 rounded';
                
                const form = document.querySelector('form.needs-validation');
                form.insertBefore(kembalianEl, form.querySelector('button[type="submit"]'));
            }
            
            // Update content and styling
            if (kembalian >= 0) {
                kembalianEl.className = 'mt-3 p-3 rounded bg-success-subtle';
                kembalianEl.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Kembalian:</span>
                        <span class="fw-bold fs-5">Rp ${formatRupiah(kembalian.toString())}</span>
                    </div>
                `;
                kembalianEl.style.display = 'block';
            } else {
                kembalianEl.className = 'mt-3 p-3 rounded bg-danger-subtle';
                kembalianEl.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Kurang:</span>
                        <span class="fw-bold fs-5">Rp ${formatRupiah(Math.abs(kembalian).toString())}</span>
                    </div>
                `;
                kembalianEl.style.display = 'block';
            }
        }
        
        // Add visual feedback for form validation
        function showValidationFeedback(inputElement, message) {
            let feedback = inputElement.parentNode.querySelector('.invalid-feedback');
            
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                inputElement.parentNode.appendChild(feedback);
            }
            
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
        
        function hideValidationFeedback(inputElement) {
            const feedback = inputElement.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
        
        // Improve select UI
        document.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('selected');
                } else {
                    this.classList.remove('selected');
                }
            });
        });
        
        // Animasi alert dismiss
        document.querySelectorAll('.alert .btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const alert = this.closest('.alert');
                alert.classList.add('fade-out');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        });
        
        // Form validation
        const form = document.querySelector('form.needs-validation');
        if (form) {
            form.addEventListener('submit', function(event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Tambah efek shake untuk field yang invalid
                    this.querySelectorAll(':invalid').forEach(field => {
                        field.classList.add('shake-effect');
                        setTimeout(() => {
                            field.classList.remove('shake-effect');
                        }, 500);
                    });
                } else {
                    // Verifikasi pembayaran
                    const bayarInput = document.querySelector('.rupiah-input');
                    const bayarValue = parseInt(bayarInput.value.replace(/\D/g, '')) || 0;
                    const minValue = parseInt(bayarInput.dataset.min);
                    
                    if (bayarValue < minValue) {
                        event.preventDefault();
                        bayarInput.classList.add('is-invalid');
                        showValidationFeedback(bayarInput, `Minimal pembayaran: Rp ${formatRupiah(minValue.toString())}`);
                        bayarInput.classList.add('shake-effect');
                        setTimeout(() => {
                            bayarInput.classList.remove('shake-effect');
                        }, 500);
                        return false;
                    }
                    
                    // Tambahkan animasi pada button submit dan mencegah multiple submit
                    const submitBtn = document.getElementById('btn-checkout');
                    if (!submitBtn.disabled) {
                        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses Pembayaran...';
                        submitBtn.disabled = true;
                    }
                }
                
                this.classList.add('was-validated');
            });
        }
    });