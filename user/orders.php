<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
   header("Location: ../auth/login.php");
   exit;
}

$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

$query = "SELECT p.*, pb.jenis_pembayaran 
         FROM tb_pembelian p 
         LEFT JOIN tb_pembayaran pb ON p.pembayaran_id = pb.pembayaran_id 
         WHERE p.user_id = $user_id 
         ORDER BY p.tanggal DESC";
$orders = query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pesanan Saya - WARINGIN-IT</title>
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

/* Badge Enhancement */
.badge {
    font-weight: 600;
    padding: 0.4em 0.75em;
    border-radius: 6px;
    position: relative;
    top: -3px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.badge.bg-primary {
    background: var(--primary-gradient) !important;
}

.badge.bg-danger {
    background: var(--danger-color) !important;
}

/* Dropdown Animation */
.dropdown-menu {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.3s ease forwards;
    transform-origin: top center;
    padding: 10px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.dropdown-item {
    border-radius: 8px;
    padding: 8px 16px;
    transition: var(--transition);
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.dropdown-item i {
    transition: var(--transition);
}

.dropdown-item:hover i {
    transform: scale(1.2);
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
    padding: 1rem 1.5rem;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease;
}

.alert-success {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #065f46;
}

.alert-info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1e40af;
    display: flex;
    align-items: center;
    padding: 1.25rem 1.5rem;
}

.alert-info a {
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    background: rgba(59, 130, 246, 0.1);
    margin-left: 0.75rem !important;
}

.alert-info a:hover {
    background: rgba(59, 130, 246, 0.2);
    transform: translateY(-2px);
    color: var(--secondary-color);
}

.alert-info i {
    font-size: 1.5rem;
    margin-right: 1rem;
}

/* Order Card Enhancement */
.order-card {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    overflow: hidden;
    height: 100%;
    position: relative;
    z-index: 1;
}

.order-card::before {
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

.order-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--card-hover-shadow);
}

.order-card:hover::before {
    opacity: 0.5;
    transform: scale(1);
}

.card-header {
    background: #f8fafc;
    border-bottom: 2px solid #f1f5f9;
    padding: 1.25rem 1.5rem;
}

.card-header h6 {
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    font-size: 1.1rem;
}

.card-body {
    padding: 1.5rem;
}

.card-footer {
    background: #f8fafc;
    border-top: 2px solid #f1f5f9;
    padding: 1.25rem 1.5rem;
}

/* Product List Item */
.product-list-item {
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.product-list-item:hover {
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border-left: 3px solid var(--primary-color);
    transform: translateX(5px);
}

.product-list-item .text-primary {
    font-weight: 600;
    color: var(--primary-color) !important;
}

/* Order Meta */
.order-meta {
    padding: 1.25rem;
    background: #f8fafc;
    border-radius: 12px;
    margin-top: 1.25rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    transition: var(--transition);
}

.order-meta:hover {
    background: white;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.06);
}

.order-meta .fw-bold {
    color: #475569;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-meta .text-primary {
    color: var(--primary-color) !important;
    font-size: 1.2rem;
}

/* Button Styles */
.btn-detail {
    background: var(--primary-gradient);
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    transition: var(--transition);
    color: white;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.btn-detail::before {
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

.btn-detail:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(67, 97, 238, 0.25);
    color: white;
}

.btn-detail:hover::before {
    opacity: 1;
}

.btn-detail i {
    transition: var(--transition);
}

.btn-detail:hover i {
    transform: translateX(-3px);
}

/* Modal Styling */
.modal-content {
    border-radius: 24px;
    border: none;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: var(--primary-gradient);
    color: white;
    border: none;
    padding: 1.25rem 1.5rem;
}

.modal-title {
    font-weight: 700;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
}

.modal-title i {
    margin-right: 0.5rem;
    font-size: 1.2rem;
}

.btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
    transition: var(--transition);
}

.btn-close:hover {
    opacity: 1;
    transform: rotate(90deg);
}

.modal-body {
    padding: 1.5rem;
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

.table thead th {
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table tfoot {
    background: #f8fafc;
}

.table tfoot td {
    padding: 0.75rem 1rem;
    border-top: 2px solid #e2e8f0 !important;
}

.table tfoot .fw-bold {
    color: #1e293b;
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
    transition: var(--transition);
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-body, .card-footer {
        padding: 1.25rem 1rem;
    }
    
    .product-list-item {
        padding: 0.75rem 1rem;
    }
    
    .order-meta {
        padding: 1rem;
    }
    
    .table thead th, .table tbody td, .table tfoot td {
        padding: 0.75rem;
    }
}

/* Animated Row for Orders */
.col-md-6 {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
}

.col-md-6:nth-child(odd) {
    animation-delay: 0.1s;
}

.col-md-6:nth-child(even) {
    animation-delay: 0.3s;
}


   </style>
</head>
<body>
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
                       <a class="nav-link active" href="orders.php">Pesanan Saya</a>
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
                           <i class="bi bi-person-circle me-1"></i><?= $user['nama']; ?>
                       </a>
                       <ul class="dropdown-menu dropdown-menu-end">
                           <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                           <li><hr class="dropdown-divider"></li>
                           <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                       </ul>
                   </li>
               </ul>
           </div>
       </div>
   </nav>

   <div class="container py-4">
       <h2 class="mb-4 fw-bold"><i class="bi bi-bag-check me-2"></i>Pesanan Saya</h2>

       <?php if (isset($_SESSION['success'])) : ?>
           <div class="alert alert-success alert-dismissible fade show" role="alert">
               <?= $_SESSION['success']; ?>
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           </div>
           <?php unset($_SESSION['success']); ?>
       <?php endif; ?>

       <?php if (empty($orders)) : ?>
           <div class="alert alert-info d-flex align-items-center">
               <i class="bi bi-info-circle me-2"></i>
               Belum ada pesanan. <a href="index.php" class="ms-2">Belanja sekarang</a>
           </div>
       <?php else : ?>
           <div class="row">
               <?php foreach ($orders as $order) : ?>
                   <div class="col-md-6 mb-4">
                       <div class="order-card card">
                           <div class="card-header py-3">
                               <div class="d-flex justify-content-between align-items-center">
                                   <h6 class="mb-0 fw-bold">Order #<?= $order['id_pembelian']; ?></h6>
                                   <span class="badge bg-primary">
                                       <?= date('d F Y', strtotime($order['tanggal'])); ?>
                                   </span>
                               </div>
                           </div>
                           <div class="card-body">
                               <?php
                               $id_pembelian = $order['id_pembelian'];
                               $detail_query = "SELECT dp.*, b.nama_barang, b.harga_jual 
                                              FROM tb_detail_pembelian dp 
                                              JOIN tb_barang b ON dp.barang_id = b.barang_id 
                                              WHERE dp.id_pembelian = $id_pembelian";
                               $details = query($detail_query);
                               ?>
                               
                               <div class="mb-3">
                                   <h6 class="fw-bold mb-3">Detail Produk:</h6>
                                   <?php foreach ($details as $detail) : ?>
                                       <div class="product-list-item">
                                           <div class="d-flex justify-content-between align-items-center">
                                               <span><?= $detail['nama_barang']; ?></span>
                                               <span><?= $detail['jumlah']; ?>x</span>
                                           </div>
                                           <div class="text-primary mt-1">
                                               Rp <?= number_format($detail['harga_jual'], 0, ',', '.'); ?>
                                           </div>
                                       </div>
                                   <?php endforeach; ?>
                               </div>

                               <div class="order-meta">
                                   <div class="row g-3">
                                       <div class="col-6">
                                           <div class="fw-bold mb-1">Jenis Pembayaran</div>
                                           <div><?= $order['jenis_pembayaran']; ?></div>
                                       </div>
                                       <div class="col-6 text-end">
                                           <div class="fw-bold mb-1">Total Bayar</div>
                                           <div class="text-primary fw-bold">
                                               Rp <?= number_format($order['jumlah_pembayaran'], 0, ',', '.'); ?>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           <div class="card-footer border-0 bg-white text-end py-3">
    <a href="print_order.php?id=<?= $order['id_pembelian']; ?>" class="btn-print me-2" target="_blank">
        <i class="bi bi-printer me-2"></i>Cetak Struk
    </a>
    <button type="button" class="btn-detail" data-bs-toggle="modal" data-bs-target="#orderDetail<?= $order['id_pembelian']; ?>">
        <i class="bi bi-eye me-2"></i>Detail Pesanan
    </button>
</div>
                       </div>
                   </div>

                   <!-- Modal Detail Pesanan -->
                   <div class="modal fade" id="orderDetail<?= $order['id_pembelian']; ?>" tabindex="-1">
                       <div class="modal-dialog modal-lg modal-dialog-centered">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <h5 class="modal-title">
                                       <i class="bi bi-receipt me-2"></i>
                                       Detail Pesanan #<?= $order['id_pembelian']; ?>
                                   </h5>
                                   <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                               </div>
                               <div class="modal-body">
                                   <div class="table-responsive">
                                       <table class="table">
                                           <thead>
                                               <tr>
                                                   <th>Produk</th>
                                                   <th class="text-end">Harga</th>
                                                   <th class="text-center">Jumlah</th>
                                                   <th class="text-end">Subtotal</th>
                                               </tr>
                                           </thead>
                                           <tbody>
                                               <?php foreach ($details as $detail) : ?>
                                                   <tr>
                                                       <td><?= $detail['nama_barang']; ?></td>
                                                       <td class="text-end">
                                                           Rp <?= number_format($detail['harga_jual'], 0, ',', '.'); ?>
                                                       </td>
                                                       <td class="text-center"><?= $detail['jumlah']; ?></td>
                                                       <td class="text-end">
                                                           Rp <?= number_format($detail['subtotal'], 0, ',', '.'); ?>
                                                       </td>
                                                   </tr>
                                               <?php endforeach; ?>
                                           </tbody>
                                           <tfoot class="table-light">
                                               <tr>
                                                   <td colspan="3" class="text-end fw-bold">Total:</td>
                                                   <td class="text-end fw-bold">
                                                       Rp <?= number_format($order['jumlah_pembayaran'], 0, ',', '.'); ?>
                                                   </td>
                                               </tr>
                                               <tr>
                                                   <td colspan="3" class="text-end">Bayar:</td>
                                                   <td class="text-end">
                                                       Rp <?= number_format($order['bayar'], 0, ',', '.'); ?>
                                                   </td>
                                               </tr>
                                               <tr>
                                                   <td colspan="3" class="text-end">Kembalian:</td>
                                                   <td class="text-end">
                                                       Rp <?= number_format($order['kembalian'], 0, ',', '.'); ?>
                                                   </td>
                                               </tr>
                                           </tfoot>
                                       </table>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
               <?php endforeach; ?>
           </div>
       <?php endif; ?>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

   <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Hover effects untuk cards
    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('card-hover');
            
            // Animasi pada badge
            const badge = this.querySelector('.badge');
            if (badge) {
                badge.classList.add('badge-pulse');
                setTimeout(() => {
                    badge.classList.remove('badge-pulse');
                }, 1000);
            }
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('card-hover');
        });
    });
    
    // Hover effects untuk product-list-item
    document.querySelectorAll('.product-list-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            const parent = this.closest('.order-card');
            if (parent) {
                this.style.borderLeftColor = getComputedStyle(document.documentElement)
                    .getPropertyValue('--primary-color').trim();
            }
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.borderLeftColor = 'transparent';
        });
    });
    
    // Hover effects untuk tombol detail
    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.add('btn-icon-effect');
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.remove('btn-icon-effect');
            }
        });
    });
    
    // Animated number counting untuk totals
    document.querySelectorAll('.text-primary.fw-bold').forEach(el => {
        const text = el.textContent;
        if (text.includes('Rp')) {
            const value = parseInt(text.replace(/\D/g, ''));
            if (!isNaN(value) && value > 1000) {
                animateValue(el, 0, value, 1500);
            }
        }
    });
    
    // Modal effects
    const orderDetailModals = document.querySelectorAll('[id^="orderDetail"]');
    orderDetailModals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            // Tambahkan class untuk animasi masuk
            setTimeout(() => {
                const table = this.querySelector('.table-responsive');
                if (table) table.classList.add('table-fade-in');
            }, 200);
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            // Reset animasi ketika modal ditutup
            const table = this.querySelector('.table-responsive');
            if (table) table.classList.remove('table-fade-in');
        });
    });
    
    // Navbar active link
    const currentLocation = window.location.pathname;
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        if (link.getAttribute('href') && currentLocation.endsWith(link.getAttribute('href'))) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
    
    // Dropdown hover effect
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.querySelector('i').classList.add('icon-hover');
        });
        
        item.addEventListener('mouseleave', function() {
            this.querySelector('i').classList.remove('icon-hover');
        });
    });
    
    // Alert dismiss animation
    document.querySelectorAll('.alert .btn-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.classList.add('fade-out');
            setTimeout(() => {
                alert.remove();
            }, 300);
        });
    });
});

// Utility function untuk animasi counting numbers
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        // Format as Indonesian Rupiah
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Tambahkan CSS dinamis untuk animasi tambahan
(function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes badgePulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .badge-pulse {
            animation: badgePulse 0.5s ease;
        }
        
        .btn-icon-effect {
            animation: slideLeft 0.5s ease infinite alternate;
        }
        
        @keyframes slideLeft {
            0% { transform: translateX(0); }
            100% { transform: translateX(-3px); }
        }
        
        .table-fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }
        
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        .card-hover {
            z-index: 5;
        }
        
        .icon-hover {
            transform: scale(1.2);
            color: var(--primary-color);
        }
        
        .fade-out {
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }
        
        /* Total value counters */
        .text-primary.fw-bold {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
    `;
    document.head.appendChild(style);
})();
   </script>
</body>
</html>