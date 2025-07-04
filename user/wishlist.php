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

// Cek apakah tabel wishlist sudah ada
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'tb_wishlist'");
if (mysqli_num_rows($checkTable) == 0) {
    // Buat tabel wishlist jika belum ada
    $createTable = "CREATE TABLE tb_wishlist (
        wishlist_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        barang_id INT(11) NOT NULL,
        tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, barang_id)
    )";
    
    mysqli_query($conn, $createTable);
}

// Ambil data wishlist
$query = "SELECT w.*, b.*, k.nama_kategori, m.nama_merk 
          FROM tb_wishlist w 
          JOIN tb_barang b ON w.barang_id = b.barang_id 
          LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
          LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
          WHERE w.user_id = $user_id 
          ORDER BY w.tanggal DESC";

$wishlist_items = query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist - WARINGIN-IT</title>
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

.alert-info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1e40af;
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

/* Wishlist Item Card */
.wishlist-item {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    overflow: hidden;
    position: relative;
    z-index: 1;
    background: white;
    margin-bottom: 1.5rem;
    cursor: pointer;
}

.wishlist-item::before {
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

.wishlist-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--card-hover-shadow);
}

.wishlist-item:hover::before {
    opacity: 0.3;
    transform: scale(1);
}

.wishlist-image-container {
    width: 120px;
    height: 120px;
    overflow: hidden;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    background: #f8fafc;
    padding: 10px;
}

.wishlist-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: var(--transition);
}

.wishlist-item:hover .wishlist-image {
    transform: scale(1.05);
}

.wishlist-details h5 {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.wishlist-item:hover .wishlist-details h5 {
    color: var(--primary-color);
}

.wishlist-details p {
    color: #64748b;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

/* Price Styling */
.wishlist-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1rem;
    transition: var(--transition);
}

.wishlist-item:hover .wishlist-price {
    transform: scale(1.05);
}

/* Stock Badge */
.stock-status {
    font-size: 0.85rem;
    padding: 0.35em 0.65em;
    border-radius: 6px;
    margin-bottom: 1rem;
    display: inline-block;
}

.stock-available {
    background-color: #ecfdf5;
    color: #065f46;
}

.stock-limited {
    background-color: #fff7ed;
    color: #9a3412;
}

.stock-out {
    background-color: #fef2f2;
    color: #b91c1c;
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

.btn-outline-danger {
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    background: transparent;
}

.btn-outline-danger::before {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
}

.btn-outline-danger:hover {
    color: white;
    border-color: transparent;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.btn i {
    transition: var(--transition);
}

.btn:hover i {
    transform: translateX(3px);
}

/* NEW: Lihat Detail Button */
.btn-detail {
    background: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    margin-right: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-detail:hover {
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
}

.btn-detail i {
    margin-right: 6px;
}

.btn-detail:hover i {
    transform: translateX(3px);
}

/* Empty State */
.empty-wishlist {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-bottom: 2rem;
}

.empty-wishlist i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
}

.empty-wishlist h4 {
    color: #1e293b;
    margin-bottom: 1rem;
}

.empty-wishlist p {
    color: #64748b;
    margin-bottom: 1.5rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
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

.wishlist-item {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
}

/* Staggered animation for items */
.wishlist-item:nth-child(1) { animation-delay: 0.1s; }
.wishlist-item:nth-child(2) { animation-delay: 0.2s; }
.wishlist-item:nth-child(3) { animation-delay: 0.3s; }
.wishlist-item:nth-child(4) { animation-delay: 0.4s; }
.wishlist-item:nth-child(5) { animation-delay: 0.5s; }

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .wishlist-item {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
    }
    
    .wishlist-image-container {
        margin-bottom: 1rem;
        width: 100px;
        height: 100px;
    }
    
    .wishlist-details {
        margin-left: 0;
        margin-bottom: 1rem;
    }
    
    .wishlist-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .wishlist-actions .btn {
        margin: 0.5rem 0;
    }
}

/* Loading Spinner */
.spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s ease;
}

.spinner-overlay.show {
    visibility: visible;
    opacity: 1;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid var(--primary-color);
    border-top: 5px solid transparent;
    border-radius: 50%;
    animation: spinner 1s linear infinite;
}

@keyframes spinner {
    to {transform: rotate(360deg);}
}

/* Toast Notification */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 12px;
    padding: 15px 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    animation: toastIn 0.3s ease;
}

@keyframes toastIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast.hide {
    animation: toastOut 0.3s ease forwards;
}

@keyframes toastOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.toast.success {
    border-left: 4px solid var(--success-color);
}

.toast.success i {
    color: var(--success-color);
}

.toast.danger {
    border-left: 4px solid var(--danger-color);
}

.toast.danger i {
    color: var(--danger-color);
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
                       <a class="nav-link active" href="wishlist.php">Wishlist</a>
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

   <!-- Loading Spinner -->
   <div class="spinner-overlay" id="spinner">
       <div class="spinner"></div>
   </div>

   <!-- Toast Container -->
   <div class="toast-container" id="toast-container"></div>

   <div class="container py-4">
       <h2 class="mb-4 fw-bold">
           <i class="bi bi-heart me-2"></i>Wishlist Saya
       </h2>

       <?php if (isset($_SESSION['success'])) : ?>
           <div class="alert alert-success alert-dismissible fade show" role="alert">
               <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success']; ?>
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           </div>
           <?php unset($_SESSION['success']); ?>
       <?php endif; ?>

       <?php if (empty($wishlist_items)) : ?>
           <div class="empty-wishlist">
               <i class="bi bi-heart"></i>
               <h4>Wishlist Anda Kosong</h4>
               <p>Anda belum menambahkan produk ke wishlist. Jelajahi koleksi laptop kami dan simpan produk favorit Anda di sini.</p>
               <a href="index.php" class="btn btn-primary">
                   <i class="bi bi-laptop me-2"></i>Lihat Produk
               </a>
           </div>
       <?php else : ?>
           <div id="wishlist-items-container">
               <?php foreach ($wishlist_items as $item) : ?>
                   <div class="wishlist-item d-flex p-3 align-items-center" data-id="<?= $item['barang_id']; ?>">
                       <!-- Clickable image that redirects to detail page -->
                       <a href="detail_product.php?id=<?= $item['barang_id']; ?>" class="wishlist-image-container">
                           <img src="../assets/img/barang/<?= htmlspecialchars($item['gambar'] ?: 'no-image.jpg'); ?>" 
                                alt="<?= htmlspecialchars($item['nama_barang']); ?>"
                                class="wishlist-image">
                       </a>
                       
                       <div class="wishlist-details ms-4 flex-grow-1">
                           <!-- Clickable product name that redirects to detail page -->
                           <a href="detail_product.php?id=<?= $item['barang_id']; ?>" class="text-decoration-none">
                               <h5><?= htmlspecialchars($item['nama_barang']); ?></h5>
                           </a>
                           <p>
                               <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['nama_merk']); ?> | 
                               <i class="bi bi-laptop me-1"></i><?= htmlspecialchars($item['nama_kategori']); ?>
                           </p>
                           <div class="wishlist-price">
                               Rp <?= number_format($item['harga_jual'], 0, ',', '.'); ?>
                           </div>
                           
                           <?php if ($item['stok'] > 0) : ?>
                               <?php if ($item['stok'] <= 5) : ?>
                                   <span class="stock-status stock-limited">
                                       <i class="bi bi-exclamation-triangle me-1"></i>Stok Terbatas: <?= $item['stok']; ?>
                                   </span>
                               <?php else : ?>
                                   <span class="stock-status stock-available">
                                       <i class="bi bi-check-circle me-1"></i>Stok Tersedia: <?= $item['stok']; ?>
                                   </span>
                               <?php endif; ?>
                           <?php else : ?>
                               <span class="stock-status stock-out">
                                   <i class="bi bi-x-circle me-1"></i>Stok Habis
                               </span>
                           <?php endif; ?>
                       </div>
                       
                       <div class="wishlist-actions d-flex gap-2">
                           <!-- New: Lihat Detail Button -->
                           <a href="detail_product.php?id=<?= $item['barang_id']; ?>" class="btn btn-detail btn-sm">
                               <i class="bi bi-eye"></i> Lihat Detail
                           </a>
                           
                           <?php if ($item['stok'] > 0) : ?>
                               <form action="cart.php" method="post">
                                   <input type="hidden" name="barang_id" value="<?= $item['barang_id']; ?>">
                                   <input type="hidden" name="action" value="add">
                                   <input type="hidden" name="qty" value="1">
                                   <button type="submit" class="btn btn-primary btn-sm">
                                       <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                                   </button>
                               </form>
                           <?php endif; ?>
                           
                           <button type="button" class="btn btn-outline-danger btn-sm remove-wishlist" 
                                   data-id="<?= $item['barang_id']; ?>">
                               <i class="bi bi-heart-fill me-2"></i>Hapus
                           </button>
                       </div>
                   </div>
               <?php endforeach; ?>
           </div>
       <?php endif; ?>
   </div>

   <!-- JavaScript for Wishlist functionality -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script>
   document.addEventListener('DOMContentLoaded', function() {
       // Click handler for wishlist item (image and title will redirect to detail page)
       const wishlistItems = document.querySelectorAll('.wishlist-item');
       
       // Get all remove wishlist buttons
       const removeButtons = document.querySelectorAll('.remove-wishlist');
       
       // Add event listeners to all remove buttons
       removeButtons.forEach(button => {
           button.addEventListener('click', function(e) {
               // Stop event propagation to prevent navigating to detail page
               e.stopPropagation();
               
               const barangId = this.getAttribute('data-id');
               const wishlistItem = this.closest('.wishlist-item');
               
               // Show loading spinner
               document.getElementById('spinner').classList.add('show');
               
               // Send AJAX request to remove item from wishlist
               fetch('wishlist_action.php', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/x-www-form-urlencoded',
                   },
                   body: `action=remove&barang_id=${barangId}`
               })
               .then(response => response.json())
               .then(data => {
                   // Hide loading spinner
                   document.getElementById('spinner').classList.remove('show');
                   
                   if (data.status === 'success') {
                       // Show success toast
                       showToast('success', data.message);
                       
                       // Remove item from DOM with animation
                       wishlistItem.style.animation = 'fadeOut 0.5s ease forwards';
                       setTimeout(() => {
                           wishlistItem.remove();
                           
                           // Check if wishlist is empty and show empty state if needed
                           if (document.querySelectorAll('.wishlist-item').length === 0) {
                               const emptyState = `
                                   <div class="empty-wishlist">
                                       <i class="bi bi-heart"></i>
                                       <h4>Wishlist Anda Kosong</h4>
                                       <p>Anda belum menambahkan produk ke wishlist. Jelajahi koleksi laptop kami dan simpan produk favorit Anda di sini.</p>
                                       <a href="index.php" class="btn btn-primary">
                                           <i class="bi bi-laptop me-2"></i>Lihat Produk
                                       </a>
                                   </div>
                               `;
                               document.getElementById('wishlist-items-container').innerHTML = emptyState;
                           }
                       }, 500);
                   } else {
                       // Show error toast
                       showToast('danger', data.message);
                   }
               })
               .catch(error => {
                   // Hide loading spinner
                   document.getElementById('spinner').classList.remove('show');
                   // Show error toast
                   showToast('danger', 'Terjadi kesalahan: ' + error);
               });
           });
       });
       
       // Function to show toast notification
       function showToast(type, message) {
           const toastContainer = document.getElementById('toast-container');
           const toast = document.createElement('div');
           toast.className = `toast ${type}`;
           
           const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
           
           toast.innerHTML = `
               <i class="bi ${icon}"></i>
               <span>${message}</span>
           `;
           
           toastContainer.appendChild(toast);
           
           // Remove toast after 3 seconds
           setTimeout(() => {
               toast.classList.add('hide');
               setTimeout(() => {
                   toast.remove();
               }, 300);
           }, 3000);
       }
       
       // Add fadeOut animation
       const style = document.createElement('style');
       style.textContent = `
           @keyframes fadeOut {
               from {
                   opacity: 1;
                   transform: translateY(0);
               }
               to {
                   opacity: 0;
                   transform: translateY(-20px);
               }
           }
       `;
       document.head.appendChild(style);
   });
   </script>
</body>
</html>