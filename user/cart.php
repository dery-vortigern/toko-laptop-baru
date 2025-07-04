<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
   header("Location: ../auth/login.php");
   exit;
}

if (!isset($_SESSION['cart'])) {
   $_SESSION['cart'] = [];
}

// Proses tambah ke keranjang
if (isset($_POST['action']) && $_POST['action'] == 'add') {
   $barang_id = $_POST['barang_id'];
   $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
   
   $barang = query("SELECT stok FROM tb_barang WHERE barang_id = $barang_id")[0];
   if ($qty > $barang['stok']) {
       $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: " . $barang['stok'];
   } else {
       if (isset($_SESSION['cart'][$barang_id])) {
           $_SESSION['cart'][$barang_id] += $qty;
       } else {
           $_SESSION['cart'][$barang_id] = $qty;
       }
       $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang';
   }
   header("Location: cart.php");
   exit;
}

// Proses update jumlah
if (isset($_POST['action']) && $_POST['action'] == 'update') {
   $barang_id = $_POST['barang_id'];
   $qty = (int)$_POST['qty'];
   
   $barang = query("SELECT stok FROM tb_barang WHERE barang_id = $barang_id")[0];
   if ($qty > $barang['stok']) {
       $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: " . $barang['stok'];
   } else {
       if ($qty > 0) {
           $_SESSION['cart'][$barang_id] = $qty;
       } else {
           unset($_SESSION['cart'][$barang_id]);
       }
   }
   header("Location: cart.php");
   exit;
}

// Proses hapus item
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
   $barang_id = $_GET['id'];
   unset($_SESSION['cart'][$barang_id]);
   
   $_SESSION['success'] = 'Produk berhasil dihapus dari keranjang';
   header("Location: cart.php");
   exit;
}

// Ambil data keranjang
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
   $barang_ids = array_keys($_SESSION['cart']);
   $barang_ids_str = implode(',', $barang_ids);
   
   $cart_items = query("SELECT b.*, k.nama_kategori, m.nama_merk 
                       FROM tb_barang b 
                       LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
                       LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
                       WHERE b.barang_id IN ($barang_ids_str)");
   
   foreach ($cart_items as $item) {
       $total += $item['harga_jual'] * $_SESSION['cart'][$item['barang_id']];
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Keranjang - WARINGIN-IT</title>
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

/* Badge Enhancement */
.badge {
    font-weight: 600;
    padding: 0.4em 0.75em;
    border-radius: 6px;
    position: relative;
    top: -3px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
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

.alert-success {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #065f46;
}

.alert-danger {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #b91c1c;
}

.alert-info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1e40af;
    display: flex;
    align-items: center;
    padding: 1.25rem 1.5rem;
}

.alert i {
    font-size: 1.2rem;
    margin-right: 0.75rem;
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

.btn-close {
    margin-left: auto;
    opacity: 0.8;
    transition: var(--transition);
}

.btn-close:hover {
    opacity: 1;
    transform: rotate(90deg);
}

/* Cart Card Enhancement */
.cart-card {
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

.cart-card::before {
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

.cart-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--card-hover-shadow);
}

.cart-card:hover::before {
    opacity: 0.3;
    transform: scale(1);
}

.card-body {
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

.table > thead > tr > th {
    background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1.25rem 1rem;
    border: none;
}

.table > tbody > tr > td {
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table > tbody > tr:hover {
    background-color: rgba(67, 97, 238, 0.03) !important;
    transition: var(--transition);
}

.table-light {
    background: linear-gradient(45deg, #f1f5f9, #e2e8f0) !important;
}

/* Product Image & Details */
.product-image {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border-radius: 12px;
    padding: 10px;
    background: #f8fafc;
    transition: var(--transition);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.product-image:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.product-details {
    margin-left: 1.25rem;
}

.product-details h6 {
    margin-bottom: 0.5rem;
    font-weight: 700;
    color: #1e293b;
    font-size: 1.05rem;
    transition: var(--transition);
}

.product-details small i {
    color: var(--primary-color);
    transition: var(--transition);
}

.d-flex:hover .product-details h6 {
    color: var(--primary-color);
}

.d-flex:hover .product-details small i {
    transform: scale(1.2);
}

/* Quantity Input */
.qty-input {
    max-width: 100px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    transition: var(--transition);
    color: #1e293b;
    font-weight: 500;
    text-align: center;
}

.qty-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
    outline: none;
}

/* Price Styling - FIXED */
.text-primary.fw-bold {
    color: var(--primary-color) !important;
    font-size: 1.05rem;
    font-weight: 700 !important;
}

/* Fix for gradient text issue */
.price-gradient {
    position: relative;
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--primary-color) !important;
}

tr.table-light .text-primary {
    font-size: 1.2rem;
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

.btn-secondary {
    background: linear-gradient(135deg, #64748b, #475569);
    border: none;
    color: white;
}

.btn-secondary::before {
    background: linear-gradient(135deg, #475569, #64748b);
}

.btn-secondary:hover {
    box-shadow: 0 8px 15px rgba(100, 116, 139, 0.25);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    border: none;
    color: white;
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.btn-danger::before {
    background: linear-gradient(135deg, #b91c1c, #ef4444);
}

.btn-danger:hover {
    box-shadow: 0 8px 15px rgba(239, 68, 68, 0.25);
}

.btn i {
    transition: var(--transition);
}

.btn-primary:hover i.bi-arrow-right {
    transform: translateX(3px);
}

.btn-secondary:hover i.bi-arrow-left {
    transform: translateX(-3px);
}

.btn-danger:hover i {
    transform: scale(1.15);
}

/* Action Buttons Container */
.d-flex.justify-content-between.mt-4 {
    margin-top: 2rem !important;
    padding: 0 0.5rem;
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

.cart-card {
    animation: fadeIn 0.5s ease forwards;
}

.alert {
    animation: fadeIn 0.4s ease forwards;
}

/* Empty Cart Style */
.alert-info {
    font-size: 1.05rem;
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .table > thead > tr > th,
    .table > tbody > tr > td {
        padding: 1rem 0.75rem;
    }
    
    .product-image {
        width: 80px;
        height: 80px;
    }
    
    .product-details {
        margin-left: 0.75rem;
    }
    
    .product-details h6 {
        font-size: 0.95rem;
    }
    
    .text-muted {
        font-size: 0.8rem;
    }
    
    .btn {
        padding: 0.6rem 1rem;
    }
}

@media (max-width: 576px) {
    .d-flex.justify-content-between.mt-4 {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between.mt-4 .btn {
        width: 100%;
    }
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
                       <a class="nav-link" href="orders.php">Pesanan Saya</a>
                   </li>
               </ul>
               <ul class="navbar-nav">
                   <li class="nav-item">
                       <a class="nav-link active" href="cart.php">
                           <i class="bi bi-cart-fill me-1"></i>Keranjang
                           <?php if (count($_SESSION['cart']) > 0) : ?>
                               <span class="badge bg-danger rounded-pill"><?= count($_SESSION['cart']); ?></span>
                           <?php endif; ?>
                       </a>
                   </li>
               </ul>
           </div>
       </div>
   </nav>

   <div class="container py-4">
       <h2 class="mb-4 fw-bold">
           <i class="bi bi-cart-check me-2"></i>Keranjang Belanja
       </h2>

       <?php if (isset($_SESSION['success'])) : ?>
           <div class="alert alert-success alert-dismissible fade show" role="alert">
               <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success']; ?>
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           </div>
           <?php unset($_SESSION['success']); ?>
       <?php endif; ?>

       <?php if (isset($_SESSION['error'])) : ?>
           <div class="alert alert-danger alert-dismissible fade show" role="alert">
               <i class="bi bi-exclamation-circle me-2"></i><?= $_SESSION['error']; ?>
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           </div>
           <?php unset($_SESSION['error']); ?>
       <?php endif; ?>

       <?php if (empty($cart_items)) : ?>
           <div class="alert alert-info d-flex align-items-center">
               <i class="bi bi-info-circle me-2"></i>
               Keranjang belanja kosong. <a href="index.php" class="ms-2">Belanja sekarang</a>
           </div>
       <?php else : ?>
           <div class="cart-card">
               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-hover mb-0">
                           <thead>
                               <tr>
                                   <th>Produk</th>
                                   <th>Harga</th>
                                   <th>Jumlah</th>
                                   <th>Subtotal</th>
                                   <th>Aksi</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach ($cart_items as $item) : ?>
                               <tr>
                                   <td>
                                       <div class="d-flex align-items-center">
                                           <?php if ($item['gambar'] && file_exists("../assets/img/barang/" . $item['gambar'])) : ?>
                                               <img src="../assets/img/barang/<?= $item['gambar']; ?>" 
                                                    alt="<?= $item['nama_barang']; ?>"
                                                    class="product-image">
                                           <?php else : ?>
                                               <img src="../assets/img/no-image.jpg" 
                                                    alt="No Image"
                                                    class="product-image">
                                           <?php endif; ?>
                                           <div class="product-details">
                                               <h6><?= $item['nama_barang']; ?></h6>
                                               <small class="text-muted">
                                                   <i class="bi bi-tag me-1"></i><?= $item['nama_merk']; ?> | 
                                                   <i class="bi bi-laptop me-1"></i><?= $item['nama_kategori']; ?>
                                               </small>
                                           </div>
                                       </div>
                                   </td>
                                   <td class="text-primary fw-bold">
                                       Rp <?= number_format($item['harga_jual'], 0, ',', '.'); ?>
                                   </td>
                                   <td>
                                       <form action="" method="post" class="d-flex align-items-center">
                                           <input type="hidden" name="action" value="update">
                                           <input type="hidden" name="barang_id" value="<?= $item['barang_id']; ?>">
                                           <input type="number" class="form-control qty-input" name="qty" 
                                                  value="<?= $_SESSION['cart'][$item['barang_id']]; ?>" 
                                                  min="1" max="<?= $item['stok']; ?>"
                                                  onchange="this.form.submit()">
                                       </form>
                                   </td>
                                   <td class="text-primary fw-bold">
                                       Rp <?= number_format($item['harga_jual'] * $_SESSION['cart'][$item['barang_id']], 0, ',', '.'); ?>
                                   </td>
                                   <td>
                                       <a href="?action=remove&id=<?= $item['barang_id']; ?>" 
                                          class="btn btn-danger btn-sm"
                                          onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                           <i class="bi bi-trash"></i>
                                       </a>
                                   </td>
                               </tr>
                               <?php endforeach; ?>
                               <tr class="table-light fw-bold">
                                   <td colspan="3" class="text-end">Total:</td>
                                   <td class="text-primary">Rp <?= number_format($total, 0, ',', '.'); ?></td>
                                   <td></td>
                               </tr>
                           </tbody>
                       </table>
                   </div>

                   <div class="d-flex justify-content-between mt-4">
                       <a href="index.php" class="btn btn-secondary">
                           <i class="bi bi-arrow-left me-2"></i>Lanjut Belanja
                       </a>
                       <a href="checkout.php" class="btn btn-primary">
                           Checkout<i class="bi bi-arrow-right ms-2"></i>
                           </a>
                   </div>
               </div>
           </div>
       <?php endif; ?>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

   <script>
      document.addEventListener('DOMContentLoaded', function() {
    // Auto submit qty form on change with animation
    document.querySelectorAll('.qty-input').forEach(input => {
        // Simpan nilai awal
        const originalValue = input.value;
        
        input.addEventListener('focus', function() {
            this.dataset.original = this.value;
        });
        
        input.addEventListener('change', function() {
            // Cek jika nilai berubah dari nilai aslinya
            if (this.value !== this.dataset.original) {
                // Tambahkan indikator loading
                const row = this.closest('tr');
                row.classList.add('updating');
                
                // Tambahkan efek pulsing pada subtotal
                const subtotalCell = row.querySelector('td:nth-child(4)');
                if (subtotalCell) {
                    subtotalCell.classList.add('updating-price');
                }
                
                // Tampilkan pesan sedang memperbarui
                showToast('Mengupdate jumlah...', 'info');
                
                // Submit form
                this.form.submit();
            }
        });
        
        // Animasi efek hover
        input.addEventListener('mouseenter', function() {
            this.classList.add('input-hover');
        });
        
        input.addEventListener('mouseleave', function() {
            this.classList.remove('input-hover');
        });
    });
    
    // Konfirmasi hapus dengan modal
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.removeAttribute('onclick');
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productName = this.closest('tr').querySelector('.product-details h6').textContent;
            const href = this.getAttribute('href');
            
            showConfirmModal(
                'Hapus Produk',
                `Yakin ingin menghapus <span class="fw-bold">${productName}</span> dari keranjang?`,
                function() {
                    window.location.href = href;
                }
            );
        });
    });
    
    // Hover effects untuk product-image
    document.querySelectorAll('.product-image').forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.classList.add('image-hover');
        });
        
        img.addEventListener('mouseleave', function() {
            this.classList.remove('image-hover');
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
    
    // Animate product rows on load
    const productRows = document.querySelectorAll('tbody tr:not(.table-light)');
    productRows.forEach((row, index) => {
        row.style.opacity = 0;
        setTimeout(() => {
            row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            row.style.opacity = 1;
            row.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
    
    // Animate total row
    const totalRow = document.querySelector('tr.table-light');
    if (totalRow) {
        totalRow.style.opacity = 0;
        setTimeout(() => {
            totalRow.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            totalRow.style.opacity = 1;
            totalRow.style.transform = 'translateY(0)';
        }, 100 + (productRows.length * 100));
    }
    
    // Button hover effects
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.add('icon-' + this.className.match(/btn-(\w+)/)[1]);
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.remove('icon-' + this.className.match(/btn-(\w+)/)[1]);
            }
        });
    });
    
    // Create modal element if it doesn't exist
    if (!document.getElementById('confirmModal')) {
        const modalHtml = `
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger" id="confirmAction">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);
    }
});

// Function untuk menampilkan toast notification
function showToast(message, type = 'info') {
    // Hapus toast yang ada
    const existingToast = document.querySelector('.toast-container');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Buat toast container
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    
    // Tentukan icon berdasarkan type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    if (type === 'danger') icon = 'exclamation-circle';
    
    // Buat toast
    toastContainer.innerHTML = `
        <div class="toast show bg-${type} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <i class="bi bi-${icon} me-2"></i>
                <strong class="me-auto">WARINGIN-IT</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    document.body.appendChild(toastContainer);
    
    // Hapus toast setelah beberapa detik
    setTimeout(() => {
        toastContainer.classList.add('hiding');
        setTimeout(() => {
            toastContainer.remove();
        }, 500);
    }, 3000);
}

// Function untuk menampilkan modal konfirmasi
function showConfirmModal(title, message, callback) {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    document.querySelector('#confirmModal .modal-title').innerHTML = `
        <i class="bi bi-trash me-2 text-danger"></i>${title}
    `;
    document.querySelector('#confirmModal .modal-body').innerHTML = message;
    
    // Setup callback function
    const confirmButton = document.getElementById('confirmAction');
    
    // Remove old event listeners
    const newButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newButton, confirmButton);
    
    // Add new event listener
    newButton.addEventListener('click', function() {
        modal.hide();
        callback();
    });
    
    modal.show();
}

// Tambahkan CSS dinamis
(function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-out {
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }
        
        @keyframes updatePulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .updating-price {
            animation: updatePulse 1s infinite;
        }
        
        .updating {
            background-color: #f1f5f9 !important;
        }
        
        .input-hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .icon-primary {
            transform: translateX(3px);
        }
        
        .icon-secondary {
            transform: translateX(-3px);
        }
        
        .icon-danger {
            transform: scale(1.15);
        }
        
        .image-hover {
            transform: scale(1.1) rotate(3deg);
        }
        
        tbody tr:not(.table-light) {
            transform: translateY(20px);
        }
        
        tr.table-light {
            transform: translateY(20px);
        }
        
        /* Toast styles */
        .toast-container {
            z-index: 9999;
        }
        
        .toast {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .toast.hiding {
            transform: translateX(100%);
            transition: all 0.5s ease;
        }
        
        .toast-header {
            border-bottom: none;
        }
        
        /* Modal styles */
        #confirmModal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        #confirmModal .modal-header {
            background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
            border-bottom: none;
            padding: 1.25rem 1.5rem;
        }
        
        #confirmModal .modal-title {
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
        }
        
        #confirmModal .modal-body {
            padding: 1.5rem;
            font-size: 1.1rem;
        }
        
        #confirmModal .modal-footer {
            border-top: none;
            padding: 1rem 1.5rem 1.5rem;
        }
    `;
    document.head.appendChild(style);
})();
   </script>
</body>
</html>