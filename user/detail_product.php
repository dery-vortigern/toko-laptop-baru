<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$barang_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

// Ambil data produk
$query = "SELECT b.*, k.nama_kategori, m.nama_merk 
          FROM tb_barang b 
          LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
          LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
          WHERE b.barang_id = $barang_id";

$product = query($query);

// Jika produk tidak ditemukan
if (empty($product)) {
    header("Location: index.php");
    exit;
}

$product = $product[0];

// Cek apakah produk ada di wishlist user
$wishlist_query = "SELECT * FROM tb_wishlist WHERE user_id = $user_id AND barang_id = $barang_id";
$wishlist = query($wishlist_query);
$in_wishlist = !empty($wishlist);

// Ambil produk terkait (dengan kategori yang sama)
$related_query = "SELECT b.*, k.nama_kategori, m.nama_merk 
                 FROM tb_barang b 
                 LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
                 LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
                 WHERE b.kategori_id = {$product['kategori_id']}
                 AND b.barang_id != $barang_id 
                 AND b.stok > 0
                 LIMIT 4";

$related_products = query($related_query);

// Proses Beli Sekarang
if (isset($_POST['buy_now'])) {
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    
    if ($qty > $product['stok']) {
        $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: " . $product['stok'];
    } else {
        // Buat sesi keranjang sementara untuk checkout
        $_SESSION['buy_now'] = [
            $barang_id => $qty
        ];
        
        header("Location: buy_now.php");
        exit;
    }
}

// Proses tambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    
    if ($qty > $product['stok']) {
        $_SESSION['error'] = "Stok tidak mencukupi! Stok tersedia: " . $product['stok'];
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$barang_id])) {
            $_SESSION['cart'][$barang_id] += $qty;
        } else {
            $_SESSION['cart'][$barang_id] = $qty;
        }
        
        $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['nama_barang']); ?> - WARINGIN-IT</title>
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

/* Breadcrumb styling */
.breadcrumb {
    margin-bottom: 1.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
    transition: var(--transition);
    font-weight: 500;
}

.breadcrumb-item a:hover {
    color: var(--secondary-color);
}

.breadcrumb-item.active {
    color: #64748b;
    font-weight: 500;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: #94a3b8;
}

/* Product detail card */
.product-detail-card {
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

.product-detail-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--card-hover-shadow);
}

/* Product image gallery */
.product-image-container {
    position: relative;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8fafc;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.product-image {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    transition: var(--transition);
}

.product-image:hover {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.02);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
    cursor: zoom-in;
}

.product-image-container:hover .image-overlay {
    opacity: 1;
}

.image-overlay i {
    color: white;
    font-size: 2rem;
    background: rgba(67, 97, 238, 0.7);
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transform: scale(0.8);
    transition: var(--transition);
}

.product-image-container:hover .image-overlay i {
    transform: scale(1);
}

/* Product info */
.product-info {
    padding: 2rem;
}

.product-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
}

.product-meta {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.product-meta span {
    display: flex;
    align-items: center;
    margin-right: 1.5rem;
    color: #64748b;
}

.product-meta i {
    color: var(--primary-color);
    margin-right: 0.5rem;
}

.product-price {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    display: inline-block;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.stock-badge {
    display: inline-block;
    padding: 0.35em 0.75em;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-left: 1rem;
    vertical-align: middle;
}

.in-stock {
    background-color: #ecfdf5;
    color: #065f46;
}

.limited-stock {
    background-color: #fff7ed;
    color: #9a3412;
}

.out-of-stock {
    background-color: #fef2f2;
    color: #b91c1c;
}

.product-description {
    margin-bottom: 1.5rem;
    color: #475569;
    line-height: 1.6;
    white-space: pre-line;
    word-wrap: break-word;
    max-height: 200px; /* Atur tinggi maksimal sesuai kebutuhan */
    overflow-y: auto; /* Tambahkan scroll jika konten melebihi max-height */
    padding-right: 10px; /* Memberi jarak untuk scrollbar */
}

/* Quantity control */
.quantity-control {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.quantity-control label {
    font-weight: 600;
    color: #334155;
    margin-right: 1rem;
    font-size: 0.95rem;
}

.qty-input-group {
    display: flex;
    border-radius: 8px;
    overflow: hidden;
    width: 120px;
    border: 2px solid #e2e8f0;
}

.qty-btn {
    background: #f1f5f9;
    border: none;
    color: #334155;
    width: 34px;
    font-weight: 600;
    transition: var(--transition);
}

.qty-btn:hover {
    background: #e2e8f0;
}

.qty-input {
    width: 50px;
    border: none;
    text-align: center;
    font-weight: 600;
    color: #334155;
}

.qty-input:focus {
    outline: none;
}

/* Action buttons */
.product-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

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
    flex: 1;
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
    background: white;
}

.btn-outline-primary:hover {
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
}

.btn-outline-primary.active {
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
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

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: white;
    flex: 1;
}

.btn-success::before {
    background: linear-gradient(135deg, #059669, #10b981);
}

.btn-success:hover {
    box-shadow: 0 8px 15px rgba(16, 185, 129, 0.25);
}

.btn i {
    transition: var(--transition);
}

.btn:hover i {
    transform: translateX(3px);
}

/* Related products section */
.related-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    position: relative;
}

.related-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background: var(--primary-gradient);
    border-radius: 2px;
}

/* Related product card */
.related-product-card {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    overflow: hidden;
    position: relative;
    z-index: 1;
    background: white;
    height: 100%;
}

.related-product-card::before {
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

.related-product-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--card-hover-shadow);
}

.related-product-card:hover::before {
    opacity: 0.3;
    transform: scale(1);
}

.related-product-image {
    width: 100%;
    height: 180px;
    object-fit: contain;
    background: #f8fafc;
    transition: var(--transition);
}

.related-product-card:hover .related-product-image {
    transform: scale(1.05);
}

.related-product-card .card-body {
    padding: 1.25rem;
}

.related-product-card .card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.related-product-card:hover .card-title {
    color: var(--primary-color);
}

.related-product-card .card-text {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

/* Tabs for product details */
.nav-tabs {
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 1.5rem;
}

.nav-tabs .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: var(--transition);
    position: relative;
}

.nav-tabs .nav-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    width: 0;
    height: 3px;
    background: var(--primary-gradient);
    transition: var(--transition);
    transform: translateX(-50%);
}

.nav-tabs .nav-link:hover {
    color: var(--primary-color);
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    background: transparent;
    border-color: transparent;
}

.nav-tabs .nav-link.active::after {
    width: 100%;
}

.tab-content {
    padding: 1.5rem 0;
}

.tab-pane {
    animation: fadeIn 0.5s ease;
}

/* Specifications table */
.specs-table {
    width: 100%;
    margin-bottom: 0;
}

.specs-table tr:nth-child(even) {
    background: #f8fafc;
}

.specs-table th,
.specs-table td {
    padding: 0.75rem 1rem;
    border: none;
}

.specs-table th {
    font-weight: 600;
    color: #475569;
    width: 30%;
}

.specs-table td {
    color: #334155;
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

/* Loading spinner */
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

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .product-image-container {
        height: 350px;
    }
    
    .product-info {
        padding: 1.5rem;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .product-price {
        font-size: 1.5rem;
    }
}

@media (max-width: 767.98px) {
    .product-image-container {
        height: 300px;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .related-product-image {
        height: 160px;
    }
}

@media (max-width: 575.98px) {
    .product-meta {
        flex-wrap: wrap;
    }
    
    .product-meta span {
        margin-bottom: 0.5rem;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}

/* Wishlist button active state */
.add-to-wishlist.active i {
    color: #ef4444;
}

/* Additional animation effects */
.zoom-effect {
    overflow: hidden;
}

.zoom-effect img {
    transition: transform 0.5s ease;
}

.zoom-effect:hover img {
    transform: scale(1.1);
}

/* Modal styles */
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
}

.modal-body {
    padding: 1.5rem;
}

.modal-body img {
    max-width: 100%;
    border-radius: 12px;
}

/* Additional styles for highlights */
.product-highlights {
    margin-top: 1.5rem;
}

.highlight-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.highlight-icon {
    color: var(--success-color);
    margin-right: 0.75rem;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.highlight-text {
    color: #475569;
    flex: 1;
}

/* Tambahkan efek pulsing pada tombol Beli Sekarang */
.btn-success {
    position: relative;
    overflow: hidden;
}

.btn-success::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 120%;
    height: 120%;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%) scale(0);
    opacity: 0;
    z-index: -1;
    animation: pulse-effect 2s infinite;
}

@keyframes pulse-effect {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 0.6;
    }
    50% {
        opacity: 0;
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0;
    }
}
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="spinner">
        <div class="spinner"></div>
    </div>

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
                        <a class="nav-link" href="wishlist.php">
                            <i class="bi bi-heart me-1"></i>Wishlist
                        </a>
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

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Main Content -->
    <div class="container py-4">
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

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?kategori=<?= $product['kategori_id']; ?>"><?= htmlspecialchars($product['nama_kategori']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['nama_barang']); ?></li>
            </ol>
        </nav>

        <!-- Product Detail -->
        <div class="product-detail-card">
            <div class="row g-0">
                <div class="col-md-6">
                    <div class="product-image-container" onclick="showImageModal('<?= htmlspecialchars($product['nama_barang'], ENT_QUOTES); ?>', '../assets/img/barang/<?= htmlspecialchars($product['gambar'] ?: 'no-image.jpg'); ?>')">
                        <img src="../assets/img/barang/<?= htmlspecialchars($product['gambar'] ?: 'no-image.jpg'); ?>" 
                             class="product-image" 
                             alt="<?= htmlspecialchars($product['nama_barang']); ?>">
                        <div class="image-overlay">
                            <i class="bi bi-zoom-in"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product-info">
                        <h1 class="product-title"><?= htmlspecialchars($product['nama_barang']); ?></h1>
                        
                        <div class="product-meta">
                            <span><i class="bi bi-tag"></i><?= htmlspecialchars($product['nama_merk']); ?></span>
                            <span><i class="bi bi-laptop"></i><?= htmlspecialchars($product['nama_kategori']); ?></span>
                        </div>
                        
                        <div>
                            <span class="product-price">Rp <?= number_format($product['harga_jual'], 0, ',', '.'); ?></span>
                            <?php if ($product['stok'] > 0) : ?>
                                <?php if ($product['stok'] <= 5) : ?>
                                    <span class="stock-badge limited-stock">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Stok Terbatas: <?= $product['stok']; ?>
                                    </span>
                                <?php else : ?>
                                    <span class="stock-badge in-stock">
                                        <i class="bi bi-check-circle me-1"></i>Stok Tersedia: <?= $product['stok']; ?>
                                    </span>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="stock-badge out-of-stock">
                                    <i class="bi bi-x-circle me-1"></i>Stok Habis
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Tabs for Description, Specs, etc. -->
                        <ul class="nav nav-tabs mt-4" id="productTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Deskripsi</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">Spesifikasi</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="productTabsContent">
                            <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                                <p class="product-description"><?= htmlspecialchars($product['jenis_barang']); ?></p>
                                
                                <!-- Highlights -->
                                <div class="product-highlights">
                                    <div class="highlight-item">
                                        <i class="bi bi-check-circle-fill highlight-icon"></i>
                                        <span class="highlight-text">Garansi Resmi <?= htmlspecialchars($product['nama_merk']); ?> Indonesia</span>
                                    </div>
                                    <div class="highlight-item">
                                        <i class="bi bi-check-circle-fill highlight-icon"></i>
                                        <span class="highlight-text">Pengiriman Cepat & Aman</span>
                                    </div>
                                    <div class="highlight-item">
                                        <i class="bi bi-check-circle-fill highlight-icon"></i>
                                        <span class="highlight-text">Free Konsultasi dengan Tim Teknis</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                                <table class="specs-table">
                                    <tr>
                                        <th>Merk</th>
                                        <td><?= htmlspecialchars($product['nama_merk']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kategori</th>
                                        <td><?= htmlspecialchars($product['nama_kategori']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Stok</th>
                                        <td><?= htmlspecialchars($product['stok']); ?> Unit</td>
                                    </tr>
                                    <!-- Tambahkan spesifikasi lainnya sesuai kebutuhan -->
                                </table>
                            </div>
                        </div>
                        
                        <?php if ($product['stok'] > 0) : ?>
                            <div class="quantity-control mt-4">
                                <label>Jumlah:</label>
                                <div class="qty-input-group">
                                    <button type="button" class="qty-btn" id="decrease-qty">-</button>
                                    <input type="number" id="qty-input" class="qty-input" value="1" min="1" max="<?= $product['stok']; ?>">
                                    <button type="button" class="qty-btn" id="increase-qty">+</button>
                                </div>
                            </div>
                            
                            <div class="product-actions">
                                <form id="buy-now-form" action="" method="post" class="flex-grow-1">
                                    <input type="hidden" name="buy_now" value="1">
                                    <input type="hidden" name="qty" id="buy-now-qty" value="1">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-lightning-fill me-2"></i>Beli Sekarang
                                    </button>
                                </form>
                                
                                <form id="add-to-cart-form" action="" method="post" class="flex-grow-1">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <input type="hidden" name="qty" id="add-to-cart-qty" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-outline-primary add-to-wishlist" data-id="<?= $product['barang_id']; ?>" <?= $in_wishlist ? 'data-in-wishlist="true"' : ''; ?>>
                                    <i class="bi <?= $in_wishlist ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                </button>
                            </div>
                        <?php else : ?>
                            <div class="mt-4">
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Stok Habis
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)) : ?>
            <h3 class="related-title">Produk Terkait</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
                <?php foreach ($related_products as $related) : ?>
                    <div class="col">
                        <div class="related-product-card">
                            <a href="detail_product.php?id=<?= $related['barang_id']; ?>" class="text-decoration-none">
                                <img src="../assets/img/barang/<?= htmlspecialchars($related['gambar'] ?: 'no-image.jpg'); ?>" 
                                    class="related-product-image" 
                                    alt="<?= htmlspecialchars($related['nama_barang']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title text-truncate"><?= htmlspecialchars($related['nama_barang']); ?></h5>
                                    <p class="card-text">Rp <?= number_format($related['harga_jual'], 0, ',', '.'); ?></p>
                                    <a href="detail_product.php?id=<?= $related['barang_id']; ?>" class="btn btn-outline-primary btn-sm w-100">Lihat Detail</a>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imageModalImg" src="" class="img-fluid rounded" alt="">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function untuk menampilkan modal gambar dengan animasi
        function showImageModal(title, src) {
            const modalLabel = document.getElementById('imageModalLabel');
            const modalImg = document.getElementById('imageModalImg');
            
            modalLabel.textContent = title;
            modalImg.src = src;
            modalImg.alt = title;
            
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
            
            // Tambahkan animasi zoom-in pada gambar
            setTimeout(() => {
                modalImg.classList.add('zoom-effect');
            }, 100);
            
            // Reset animasi saat modal ditutup
            document.getElementById('imageModal').addEventListener('hidden.bs.modal', function () {
                modalImg.classList.remove('zoom-effect');
            });
        }

        // Fungsi untuk menampilkan toast notification
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
            
            // Hapus toast setelah 3 detik
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Quantity control
            const qtyInput = document.getElementById('qty-input');
            const buyNowQty = document.getElementById('buy-now-qty');
            const addToCartQty = document.getElementById('add-to-cart-qty');
            const decreaseBtn = document.getElementById('decrease-qty');
            const increaseBtn = document.getElementById('increase-qty');
            
            if (qtyInput) {
                const maxQty = parseInt(qtyInput.getAttribute('max'));
                
                // Update hidden inputs when qty changes
                qtyInput.addEventListener('change', function() {
                    const value = parseInt(this.value);
                    
                    if (value < 1) {
                        this.value = 1;
                    } else if (value > maxQty) {
                        this.value = maxQty;
                        showToast('danger', `Stok tersedia hanya ${maxQty} unit`);
                    }
                    
                    buyNowQty.value = this.value;
                    addToCartQty.value = this.value;
                });
                
                // Decrease button
                decreaseBtn.addEventListener('click', function() {
                    let value = parseInt(qtyInput.value);
                    if (value > 1) {
                        qtyInput.value = value - 1;
                        buyNowQty.value = qtyInput.value;
                        addToCartQty.value = qtyInput.value;
                    }
                });
                
                // Increase button
                increaseBtn.addEventListener('click', function() {
                    let value = parseInt(qtyInput.value);
                    if (value < maxQty) {
                        qtyInput.value = value + 1;
                        buyNowQty.value = qtyInput.value;
                        addToCartQty.value = qtyInput.value;
                    } else {
                        showToast('danger', `Stok tersedia hanya ${maxQty} unit`);
                    }
                });
            }
            
            // Tambahkan event listener untuk tombol wishlist
            const wishlistButton = document.querySelector('.add-to-wishlist');
            
            if (wishlistButton) {
                // Cek jika barang sudah ada di wishlist
                if (wishlistButton.getAttribute('data-in-wishlist') === 'true') {
                    wishlistButton.classList.add('active');
                }
                
                wishlistButton.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    const isInWishlist = this.classList.contains('active');
                    const action = isInWishlist ? 'remove' : 'add';
                    
                    // Tampilkan loading
                    const loadingOverlay = document.getElementById('spinner');
                    loadingOverlay.classList.add('show');
                    
                    // Simpan referensi ke button untuk digunakan di dalam fetch
                    const buttonEl = this;
                    
                    // Kirim request AJAX
                    fetch('wishlist_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=${action}&barang_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Sembunyikan loading overlay
                        loadingOverlay.classList.remove('show');
                        
                        if (data.status === 'success') {
                            // Tampilkan pesan sukses
                            showToast('success', data.message);
                            
                            // Update tampilan tombol wishlist
                            if (action === 'add') {
                                buttonEl.classList.add('active');
                                buttonEl.querySelector('i').classList.remove('bi-heart');
                                buttonEl.querySelector('i').classList.add('bi-heart-fill');
                            } else {
                                buttonEl.classList.remove('active');
                                buttonEl.querySelector('i').classList.remove('bi-heart-fill');
                                buttonEl.querySelector('i').classList.add('bi-heart');
                            }
                        } else {
                            // Tampilkan pesan error
                            showToast('danger', data.message);
                        }
                    })
                    .catch(error => {
                        // Sembunyikan loading overlay
                        loadingOverlay.classList.remove('show');
                        
                        // Tampilkan pesan error
                        showToast('danger', 'Terjadi kesalahan. Silakan coba lagi.');
                        console.error('Error:', error);
                    });
                });
            }
            
            // Animasi produk terkait saat di-hover
            document.querySelectorAll('.related-product-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('is-hovered');
                });
                
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('is-hovered');
                });
            });
        });
    </script>