<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek data pembelian
if (!isset($_SESSION['purchase_complete'])) {
    header("Location: orders.php");
    exit;
}

// Ambil data pembelian
$purchase_data = $_SESSION['purchase_complete'];
$id_pembelian = $purchase_data['id_pembelian'];
$items = $purchase_data['items'];
$total = $purchase_data['total'];
$bayar = $purchase_data['bayar'];
$kembalian = $purchase_data['kembalian'];
$tanggal = $purchase_data['tanggal'];

// Ambil informasi user
$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

// Ambil metode pembayaran
$pembayaran_id = query("SELECT pembayaran_id FROM tb_pembelian WHERE id_pembelian = $id_pembelian")[0]['pembayaran_id'];
$metode_pembayaran = query("SELECT jenis_pembayaran FROM tb_pembayaran WHERE pembayaran_id = $pembayaran_id")[0]['jenis_pembayaran'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembelian - WARINGIN-IT</title>
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

/* Success Card & Animation */
.success-header {
    padding: 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #dcfce7, #d1fae5);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    position: relative;
    overflow: hidden;
}

.success-header::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: repeating-linear-gradient(
        45deg,
        rgba(16, 185, 129, 0.1),
        rgba(16, 185, 129, 0.1) 10px,
        rgba(16, 185, 129, 0.15) 10px,
        rgba(16, 185, 129, 0.15) 20px
    );
    top: -50%;
    left: -50%;
    animation: move-background 20s linear infinite;
    opacity: 0.6;
}

@keyframes move-background {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.success-icon {
    width: 80px;
    height: 80px;
    background-color: #10b981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    position: relative;
    z-index: 1;
    box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
    animation: success-bounce 1s ease-in-out both;
}

@keyframes success-bounce {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    70% {
        transform: scale(0.95);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.success-icon i {
    font-size: 40px;
    color: white;
}

.success-title {
    color: #065f46;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
    animation: fade-in-up 0.6s ease-out 0.5s both;
}

.success-subtitle {
    color: #047857;
    font-size: 1.1rem;
    margin-bottom: 0;
    position: relative;
    z-index: 1;
    animation: fade-in-up 0.6s ease-out 0.7s both;
}

@keyframes fade-in-up {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Order Details */
.order-details {
    padding: 1.5rem;
    background: white;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
}

.order-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px dashed #e2e8f0;
}

.order-info-item {
    flex: 1;
    min-width: 150px;
    animation: fade-in-up 0.6s ease-out 0.9s both;
}

.order-info-item h6 {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.order-info-item p {
    font-size: 1rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0;
}

/* Item List */
.item-list {
    margin-bottom: 1.5rem;
    animation: fade-in-up 0.6s ease-out 1.1s both;
}

.item-list h6 {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 1rem;
}

.item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.item:last-child {
    border-bottom: none;
}

.item-name {
    flex: 1;
    font-weight: 500;
    color: #334155;
}

.item-qty {
    font-weight: 600;
    color: #475569;
    min-width: 50px;
    text-align: center;
}

.item-price {
    font-weight: 600;
    color: var(--primary-color);
    min-width: 120px;
    text-align: right;
}

/* Payment Summary */
.payment-summary {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 12px;
    animation: fade-in-up 0.6s ease-out 1.3s both;
}

.payment-summary h6 {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 1rem;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.payment-row.total {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed #e2e8f0;
}

.payment-row .label {
    color: #64748b;
}

.payment-row .value {
    font-weight: 600;
    color: #334155;
}

/* Call to action buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    animation: fade-in-up 0.6s ease-out 1.5s both;
}

.btn {
    border-radius: 12px;
    padding: 0.9rem 1.5rem;
    font-weight: 600;
    transition: var(--transition);
    flex: 1;
}

.btn-primary {
    background: var(--primary-gradient);
    border: none;
    color: white;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(67, 97, 238, 0.25);
}

.btn-outline-primary {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: white;
}

.btn-outline-primary:hover {
    background-color: rgba(67, 97, 238, 0.1);
    transform: translateY(-3px);
}

.btn i {
    margin-right: 0.5rem;
}

/* Print Section */
.print-only {
    display: none;
}

@media print {
    .print-only {
        display: block;
    }
    
    .no-print {
        display: none;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #e2e8f0;
    }
    
    .order-details {
        break-inside: avoid;
    }
    
    .success-header {
        break-before: page;
    }
    
    body {
        background: white !important;
    }
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

.card {
    animation: fadeIn 0.5s ease forwards;
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .order-info {
        flex-direction: column;
        gap: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .success-title {
        font-size: 1.25rem;
    }
    
    .success-subtitle {
        font-size: 1rem;
    }
}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top no-print">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-laptop me-2"></i>WARINGIN-IT
            </a>
        </div>
    </nav>

    <!-- Print Header untuk invoice -->
    <div class="print-only">
        <div class="container py-4">
            <div class="text-center mb-4">
                <h1><i class="bi bi-laptop me-2"></i>WARINGIN-IT</h1>
                <p>Jl. Ketintang, Surabaya, Jawa Timur</p>
                <h2>INVOICE</h2>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <h2 class="mb-4 fw-bold no-print">
            <i class="bi bi-check-circle-fill me-2"></i>Pembelian Berhasil
        </h2>

        <div class="card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h3 class="success-title">Terima Kasih Atas Pembelian Anda!</h3>
                <p class="success-subtitle">Pesanan Anda telah berhasil diproses dengan ID: #<?= $id_pembelian ?></p>
            </div>
            <div class="order-details">
                <div class="order-info">
                    <div class="order-info-item">
                        <h6>Tanggal Pembelian</h6>
                        <p><i class="bi bi-calendar-event me-2"></i><?= date('d F Y', strtotime($tanggal)) ?></p>
                    </div>
                    <div class="order-info-item">
                        <h6>Metode Pembayaran</h6>
                        <p><i class="bi bi-credit-card me-2"></i><?= $metode_pembayaran ?></p>
                    </div>
                    <div class="order-info-item">
                        <h6>Status</h6>
                        <p><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Lunas</span></p>
                    </div>
                </div>

                <div class="item-list">
                    <h6>Produk yang Dibeli</h6>
                    <?php foreach ($items as $item): ?>
                    <div class="item">
                        <div class="item-name"><?= $item['barang']['nama_barang'] ?></div>
                        <div class="item-qty">x<?= $item['qty'] ?></div>
                        <div class="item-price">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="payment-summary">
                    <h6>Ringkasan Pembayaran</h6>
                    <div class="payment-row">
                        <div class="label">Total Belanja</div>
                        <div class="value">Rp <?= number_format($total, 0, ',', '.') ?></div>
                    </div>
                    <div class="payment-row">
                        <div class="label">Jumlah Bayar</div>
                        <div class="value">Rp <?= number_format($bayar, 0, ',', '.') ?></div>
                    </div>
                    <div class="payment-row">
                        <div class="label">Kembalian</div>
                        <div class="value">Rp <?= number_format($kembalian, 0, ',', '.') ?></div>
                    </div>
                    <div class="payment-row total">
                        <div class="label">Total Pembayaran</div>
                        <div class="value">Rp <?= number_format($total, 0, ',', '.') ?></div>
                    </div>
                </div>

                <div class="action-buttons no-print">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i>Kembali ke Beranda
                    </a>
                    <a href="orders.php" class="btn btn-primary">
                        <i class="bi bi-list-check"></i>Lihat Pesanan Saya
                    </a>
                    <button id="print-invoice" class="btn btn-outline-primary">
                        <i class="bi bi-printer"></i>Cetak Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animasi konfetti untuk merayakan pembelian berhasil
        document.addEventListener('DOMContentLoaded', function() {
            createConfetti();
            
            // Tombol print invoice
            document.getElementById('print-invoice').addEventListener('click', function() {
                window.print();
            });
        });
        
        // Fungsi untuk membuat konfetti
        function createConfetti() {
            const confettiCount = 150;
            const container = document.createElement('div');
            
            container.style.position = 'fixed';
            container.style.top = '0';
            container.style.left = '0';
            container.style.width = '100%';
            container.style.height = '100%';
            container.style.pointerEvents = 'none';
            container.style.zIndex = '9999';
            container.className = 'no-print';
            
            document.body.appendChild(container);
            
            const colors = ['#10b981', '#4361ee', '#3b82f6', '#8b5cf6', '#ec4899'];
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'absolute';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 5 + 3 + 'px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = -20 + 'px';
                confetti.style.opacity = Math.random() + 0.5;
                confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                confetti.style.transition = 'transform ' + (Math.random() * 2 + 1) + 's linear';
                
                container.appendChild(confetti);
                
                // Animasi jatuh
                setTimeout(() => {
                    confetti.style.transform = 'translateY(' + (window.innerHeight + 20) + 'px) rotate(' + Math.random() * 360 + 'deg)';
                    confetti.style.transition = 'transform ' + (Math.random() * 3 + 2) + 's ease-in';
                }, Math.random() * 500);
                
                // Hapus setelah animasi selesai
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
            
            // Hapus container setelah semua konfetti selesai
            setTimeout(() => {
                container.remove();
            }, 6000);
        }
    </script>
</body>
</html>
<?php
// Hapus data pembelian dari session setelah ditampilkan
unset($_SESSION['purchase_complete']);
?>