<?php
session_start();
require_once '../../config/koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../../auth/adminlogin.php");
    exit;
}

// Cek parameter id (pastikan id ada dan valid)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID order tidak valid!";
    header("Location: index.php");
    exit;
}

$order_id = (int)$_GET['id']; // Cast ke integer untuk keamanan

// Cari user_id dari session - PENTING: pastikan ada di session!
if (isset($_SESSION['user_id'])) {
    $admin_id = $_SESSION['user_id']; // Dapatkan ID admin untuk mencatat siapa yang memproses order
} else {
    // Jika tidak ada user_id di session, gunakan nilai default (misal admin_id=1)
    $admin_id = 1;
    // Atau bisa juga tampilkan pesan error
    // $_SESSION['error'] = "Session user_id tidak ditemukan! Silakan login ulang.";
    // header("Location: ../../auth/adminlogin.php");
    // exit;
}

// Query untuk mendapatkan detail custom order
$query = "SELECT * FROM tb_custom_orders WHERE order_id = $order_id";
$order_result = mysqli_query($conn, $query);

// Jika order tidak ditemukan
if (!$order_result || mysqli_num_rows($order_result) == 0) {
    $_SESSION['error'] = "Custom order tidak ditemukan! ID: " . $order_id;
    header("Location: index.php");
    exit;
}

$order = mysqli_fetch_assoc($order_result); // Ambil data order

// Query untuk mendapatkan informasi user
$user_query = "SELECT * FROM tb_user WHERE user_id = {$order['user_id']}";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Proses update status
if (isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);
    
    // Validasi status
    if (!in_array($new_status, ['pending', 'processing', 'completed', 'cancelled'])) {
        $_SESSION['error'] = "Status tidak valid!";
    } else {
        // Update order di database - PERBAIKI QUERY UPDATE INI
        $update_query = "UPDATE tb_custom_orders SET 
                        status = '$new_status', 
                        admin_notes = '$admin_notes', 
                        admin_id = $admin_id, 
                        updated_at = NOW() 
                        WHERE order_id = $order_id";
                        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Status order berhasil diupdate menjadi " . ucfirst($new_status) . "!";
            
            // Redirect untuk refresh halaman dengan data yang diperbarui
            header("Location: detail.php?id=$order_id");
            exit;
        } else {
            $_SESSION['error'] = "Gagal mengupdate status order: " . mysqli_error($conn);
        }
    }
}

// Status label mapping
$status_labels = [
    'pending' => [
        'label' => 'Menunggu',
        'class' => 'bg-warning text-dark',
        'icon' => 'clock'
    ],
    'processing' => [
        'label' => 'Diproses',
        'class' => 'bg-info text-white',
        'icon' => 'arrow-repeat'
    ],
    'completed' => [
        'label' => 'Selesai',
        'class' => 'bg-success text-white',
        'icon' => 'check-circle'
    ],
    'cancelled' => [
        'label' => 'Dibatalkan',
        'class' => 'bg-danger text-white',
        'icon' => 'x-circle'
    ]
];

include_once '../includes/header.php';
?>

<style>
:root {
    --primary-color: #2563eb;
    --primary-hover: #1d4ed8;
    --secondary-color: #475569;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --border-radius: 0.75rem;
    --box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

/* Header styling to match index.php */
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

/* Button styling to match index.php */
.btn {
    padding: 0.625rem 1.25rem;
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
    background-color: #f1f5f9;
    color: var(--secondary-color);
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover, .btn-secondary:focus {
    background-color: #e2e8f0;
    color: #1e293b;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
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

.btn-info {
    background: var(--info-color);
    border: none;
    color: white;
}

.btn-info:hover, .btn-info:focus {
    background: #2563eb;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5), 0 2px 4px -2px rgba(59, 130, 246, 0.5);
    transform: translateY(-2px);
}

.btn-danger {
    background: var(--danger-color);
    border: none;
    color: white;
}

.btn-danger:hover, .btn-danger:focus {
    background: #dc2626;
    box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.5), 0 2px 4px -2px rgba(239, 68, 68, 0.5);
    transform: translateY(-2px);
}

/* Card styling to match index.php */
.card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card:hover {
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.card-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.25rem 1.5rem;
}

/* Alert styles to match index.php */
.alert {
    border: none;
    border-radius: var(--border-radius);
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
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

/* Badge styling to match index.php */
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

/* Status badge colors to match index.php */
.bg-warning {
    background-color: #fff7ed !important;
    color: #9a3412 !important;
}

.bg-info {
    background-color: #eff6ff !important;
    color: #1e40af !important;
}

.bg-success {
    background-color: #ecfdf5 !important;
    color: #065f46 !important;
}

.bg-danger {
    background-color: #fef2f2 !important;
    color: #991b1b !important;
}

/* Add animations for cards */
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
    animation: fadeIn 0.5s ease-out forwards;
}

/* Timeline styling enhancements */
.position-relative[style="padding-left: 28px;"] > div {
    animation: fadeIn 0.5s ease-out forwards;
    animation-delay: calc(var(--animation-order) * 0.1s);
    opacity: 0;
}

.position-relative[style="padding-left: 28px;"] > div:nth-child(2) {
    --animation-order: 1;
}

.position-relative[style="padding-left: 28px;"] > div:nth-child(3) {
    --animation-order: 2;
}

.position-relative[style="padding-left: 28px;"] > div:nth-child(4) {
    --animation-order: 3;
}

.position-relative[style="padding-left: 28px;"] > div:nth-child(5) {
    --animation-order: 4;
}

/* Pulse animation for badges */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(245, 158, 11, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
    }
}

.bg-warning.text-dark {
    animation: pulse 2s infinite;
}

/* Specs icon styling */
.d-flex.align-items-center.mb-3 .rounded-circle {
    transition: var(--transition);
}

.d-flex.align-items-center.mb-3:hover .rounded-circle {
    transform: scale(1.1) rotate(5deg);
    background-color: rgba(79, 70, 229, 0.1);
}

/* Budget styling enhancement */
.fs-4.fw-bold.text-success {
    background: linear-gradient(90deg, var(--success-color), #059669);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    display: inline-block;
}

/* Admin actions button enhancement */
.d-grid.gap-2.mb-4 .btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.d-grid.gap-2.mb-4 .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
}

.d-grid.gap-2.mb-4 .btn-primary {
    position: relative;
    overflow: hidden;
}

.d-grid.gap-2.mb-4 .btn-primary::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    bottom: -50%;
    left: -50%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    transform: rotate(45deg);
    transition: all 0.5s ease-out;
}

.d-grid.gap-2.mb-4 .btn-primary:hover::after {
    transform: rotate(45deg) translate(100%, 100%);
}

/* Admin notes enhancement */
.p-3.bg-light.rounded.border-start.border-4.border-primary {
    position: relative;
    transition: var(--transition);
}

.p-3.bg-light.rounded.border-start.border-4.border-primary:hover {
    background-color: #f8fafc !important;
    transform: translateX(5px);
}

.p-3.bg-light.rounded.border-start.border-4.border-primary::before {
    content: """;
    position: absolute;
    top: -15px;
    left: 10px;
    font-size: 3rem;
    color: rgba(79, 70, 229, 0.1);
    font-family: Georgia, serif;
}
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h1 class="mb-2 fw-bold">Detail Custom Order</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Custom Orders</a></li>
                <li class="breadcrumb-item active">Detail Order #<?= $order_id ?></li>
            </ol>
        </nav>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>
                <strong>Berhasil!</strong> <?= $_SESSION['success']; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Gagal!</strong> <?= $_SESSION['error']; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Order Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">#<?= $order_id ?></h4>
                    <div class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d F Y, H:i', strtotime($order['created_at'])) ?>
                    </div>
                </div>
                <div>
                    <span class="badge <?= $status_labels[$order['status']]['class'] ?> fs-6 px-3 py-2">
                        <i class="bi bi-<?= $status_labels[$order['status']]['icon'] ?> me-1"></i>
                        <?= $status_labels[$order['status']]['label'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-person text-primary me-2"></i> Informasi Pelanggan
                    </h5>

                    <div class="mb-3">
                        <div class="text-muted mb-1">Nama</div>
                        <div class="fw-semibold"><?= htmlspecialchars($user_data['nama']) ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted mb-1">Telepon</div>
                        <div class="fw-semibold"><?= htmlspecialchars($user_data['telepon']) ?></div>
                    </div>

                    <?php if (isset($user_data['email']) && !empty($user_data['email'])) : ?>
                    <div class="mb-3">
                        <div class="text-muted mb-1">Email</div>
                        <div class="fw-semibold"><?= htmlspecialchars($user_data['email']) ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <div class="text-muted mb-1">Alamat</div>
                        <div class="fw-semibold"><?= !empty($user_data['alamat']) ? htmlspecialchars($user_data['alamat']) : '<em class="text-muted">Tidak ada alamat</em>' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laptop Specifications -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-laptop text-primary me-2"></i> Spesifikasi Laptop
                    </h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-cpu text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Processor</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['processor']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-memory text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">RAM</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['ram']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-device-ssd text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Storage</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['storage']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-gpu-card text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">VGA / Graphics Card</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['vga']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-display text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Screen Size</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['screen_size']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-laptop text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Screen Type</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['screen_type']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                                    <i class="bi bi-windows text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Operating System</div>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['operating_system']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted mb-1">Budget</div>
                        <div class="fs-4 fw-bold text-success">Rp <?= number_format($order['budget'], 0, ',', '.') ?></div>
                    </div>

                    <?php if (!empty($order['additional_specs'])) : ?>
                    <div class="mb-3">
                        <div class="text-muted mb-2">Spesifikasi Tambahan</div>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($order['additional_specs'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Admin Actions & Notes -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-gear text-primary me-2"></i> Tindakan Admin
                    </h5>

                    <?php if ($order['status'] == 'pending') : ?>
                    <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-status="processing">
                            <i class="bi bi-arrow-repeat me-2"></i> Proses Order
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-status="cancelled">
                            <i class="bi bi-x-circle me-2"></i> Batalkan Order
                        </button>
                    </div>
                    <?php elseif ($order['status'] == 'processing') : ?>
                    <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-status="completed">
                            <i class="bi bi-check-circle me-2"></i> Selesaikan Order
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-status="cancelled">
                            <i class="bi bi-x-circle me-2"></i> Batalkan Order
                        </button>
                    </div>
                    <?php else : ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Order sudah dalam status <?= $status_labels[$order['status']]['label'] ?>. Tidak ada tindakan yang tersedia.
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <div class="text-muted mb-2">Catatan Admin</div>
                        <?php if (!empty($order['admin_notes'])) : ?>
                            <div class="p-3 bg-light rounded border-start border-4 border-primary">
                                <?= nl2br(htmlspecialchars($order['admin_notes'])) ?>
                            </div>
                        <?php else : ?>
                            <div class="text-muted fst-italic">Belum ada catatan dari admin</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Timeline & History -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-clock-history text-primary me-2"></i> Status dan Riwayat
                    </h5>

                    <div class="position-relative" style="padding-left: 28px;">
                        <!-- Timeline vertical line -->
                        <div class="position-absolute top-0 bottom-0 start-0 bg-light" style="width: 2px; left: 9px;"></div>
                        
                        <!-- Created Timeline -->
                        <div class="mb-4 position-relative">
                            <div class="position-absolute top-0 start-0 rounded-circle bg-warning" style="width: 20px; height: 20px; left: -10px; border: 3px solid #fff;"></div>
                            <div>
                                <div class="text-muted small"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                                <div class="fw-semibold">Order Dibuat</div>
                                <p class="mb-0 text-muted small">Pelanggan membuat custom order baru</p>
                            </div>
                        </div>
                        
                        <?php if ($order['status'] != 'pending') : ?>
                        <!-- Processing Timeline -->
                        <div class="mb-4 position-relative">
                            <div class="position-absolute top-0 start-0 rounded-circle bg-info" style="width: 20px; height: 20px; left: -10px; border: 3px solid #fff;"></div>
                            <div>
                                <div class="text-muted small"><?= date('d M Y, H:i', strtotime($order['updated_at'])) ?></div>
                                <div class="fw-semibold">Diproses</div>
                                <p class="mb-0 text-muted small">Admin mulai memproses permintaan pelanggan</p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] == 'completed') : ?>
                        <!-- Completed Timeline -->
                        <div class="mb-4 position-relative">
                            <div class="position-absolute top-0 start-0 rounded-circle bg-success" style="width: 20px; height: 20px; left: -10px; border: 3px solid #fff;"></div>
                            <div>
                                <div class="text-muted small"><?= date('d M Y, H:i', strtotime($order['updated_at'])) ?></div>
                                <div class="fw-semibold">Diselesaikan</div>
                                <p class="mb-0 text-muted small">Custom order telah selesai diproses</p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] == 'cancelled') : ?>
                        <!-- Cancelled Timeline -->
                        <div class="mb-4 position-relative">
                            <div class="position-absolute top-0 start-0 rounded-circle bg-danger" style="width: 20px; height: 20px; left: -10px; border: 3px solid #fff;"></div>
                            <div>
                                <div class="text-muted small"><?= date('d M Y, H:i', strtotime($order['updated_at'])) ?></div>
                                <div class="fw-semibold">Dibatalkan</div>
                                <p class="mb-0 text-muted small">Custom order dibatalkan karena pesanan tidak ada</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Status Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="new_status" id="new_status">
                    
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Catatan Admin</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4" placeholder="Tambahkan catatan untuk order ini (opsional)"><?= htmlspecialchars($order['admin_notes']) ?></textarea>
                        <div class="form-text">Catatan ini akan ditampilkan kepada pelanggan.</div>
                    </div>
                    
                    <div id="status_message_pending" class="alert alert-warning d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Anda akan mengubah status order menjadi <strong>Menunggu</strong>.
                    </div>
                    
                    <div id="status_message_processing" class="alert alert-info d-none">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Anda akan mengubah status order menjadi <strong>Diproses</strong>.
                        <p class="mb-0 mt-2"><small>Pelanggan akan melihat status order berubah menjadi "Sedang Diproses" dan akan dapat melihat catatan admin.</small></p>
                    </div>
                    
                    <div id="status_message_completed" class="alert alert-success d-none">
                        <i class="bi bi-check-circle me-2"></i>
                        Anda akan mengubah status order menjadi <strong>Selesai</strong>.
                        <p class="mb-0 mt-2"><small>Pelanggan akan diberitahu bahwa order mereka telah selesai diproses.</small></p>
                    </div>
                    
                    <div id="status_message_cancelled" class="alert alert-danger d-none">
                        <i class="bi bi-x-circle me-2"></i>
                        Anda akan mengubah status order menjadi <strong>Dibatalkan</strong>.
                        <p class="mb-0 mt-2"><small>Pastikan untuk menjelaskan alasan pembatalan di catatan admin.</small></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle modal status selection
    const updateStatusModal = document.getElementById('updateStatusModal');
    if (updateStatusModal) {
        updateStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const status = button.getAttribute('data-status');
            
            // Set the status value in the hidden input
            document.getElementById('new_status').value = status;
            
            // Show the appropriate status message
            document.querySelectorAll('[id^="status_message_"]').forEach(function(el) {
                el.classList.add('d-none');
            });
            
            const statusMessage = document.getElementById('status_message_' + status);
            if (statusMessage) {
                statusMessage.classList.remove('d-none');
            }
            
            // Update modal title based on status
            const modalTitle = document.getElementById('updateStatusModalLabel');
            if (status === 'processing') {
                modalTitle.textContent = 'Proses Custom Order';
            } else if (status === 'completed') {
                modalTitle.textContent = 'Selesaikan Custom Order';
            } else if (status === 'cancelled') {
                modalTitle.textContent = 'Batalkan Custom Order';
            } else {
                modalTitle.textContent = 'Update Status Order';
            }
        });
    }

    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not([id^="status_message_"])');
        alerts.forEach(function(alert) {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            } else {
                alert.style.display = 'none';
            }
        });
    }, 5000);
});
</script>

<?php include_once '../includes/footer.php'; ?>