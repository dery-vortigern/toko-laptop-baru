<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek parameter id
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data pembelian (tambahkan nama_lengkap)
$order = query("SELECT p.*, pb.jenis_pembayaran, u.nama as nama_user, u.nama_lengkap, u.alamat, u.telepon 
               FROM tb_pembelian p
               LEFT JOIN tb_pembayaran pb ON p.pembayaran_id = pb.pembayaran_id
               LEFT JOIN tb_user u ON p.user_id = u.user_id 
               WHERE p.id_pembelian = $order_id AND p.user_id = $user_id")[0];

// Ambil detail pembelian
$details = query("SELECT dp.*, b.nama_barang, b.harga_jual 
                 FROM tb_detail_pembelian dp 
                 JOIN tb_barang b ON dp.barang_id = b.barang_id 
                 WHERE dp.id_pembelian = $order_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian #<?= $order_id ?></title>
    <style>
body {
    font-family: 'Courier New', Courier, monospace;
    margin: 0;
    padding: 30px;
    font-size: 14px;
    line-height: 1.5;
    background: #f9f9f9;
}

.print-container {
    max-width: 80mm;
    margin: 0 auto;
    background: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.invoice-title {
    text-align: center;
    margin-bottom: 15px;
    border-bottom: 2px dashed #ddd;
    padding-bottom: 15px;
}

.invoice-title h2 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 1px;
}

.store-info {
    text-align: center;
    margin-bottom: 20px;
    font-size: 12px;
    color: #555;
}

.invoice-details {
    margin-bottom: 20px;
    font-size: 13px;
    padding: 10px;
    background: #f8f8f8;
    border-radius: 5px;
}

.customer-info {
    margin-bottom: 20px;
    font-size: 13px;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 5px;
}

.section-title {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    text-transform: uppercase;
    border-bottom: 1px solid #eee;
    padding-bottom: 3px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 12px;
}

th {
    padding: 8px;
    text-align: left;
    border-bottom: 2px dashed #ddd;
    text-transform: uppercase;
    font-size: 11px;
    color: #555;
}

td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px dashed #ddd;
}

.totals {
    text-align: right;
    margin-bottom: 20px;
    font-size: 13px;
    padding: 10px;
    background: #f8f8f8;
    border-radius: 5px;
}

.total-line {
    margin-bottom: 5px;
}

.grand-total {
    font-weight: bold;
    font-size: 16px;
    border-top: 2px dashed #ddd;
    padding-top: 5px;
    margin-top: 5px;
}

.footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px dashed #000;
    font-size: 12px;
    color: #555;
}

.print-button {
    display: block;
    margin: 20px auto;
    padding: 10px 20px;
    background: #4361ee;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.print-button:hover {
    background: #3a0ca3;
    transform: translateY(-2px);
}

@media print {
    @page {
        margin: 0;
        size: 80mm 297mm;
    }
    
    body {
        padding: 5mm;
        background: white;
    }
    
    .print-container {
        box-shadow: none;
        max-width: 100%;
        padding: 0;
    }
    
    .print-button {
        display: none;
    }
}
    </style>
</head>
<body>
    <div class="print-container">
        <div class="invoice-title">
            <h2>WARINGIN-IT</h2>
        </div>

        <div class="store-info">
            Jl. Contoh No. 123<br>
            Telp: (021) 12345678<br>
            Email: info@waringinit.com
        </div>

        <div class="invoice-details">
            <span class="section-title">Detail Invoice</span>
            <div>No. Invoice: #<?= $order_id ?></div>
            <div>Tanggal: <?= date('d/m/Y H:i', strtotime($order['tanggal'])) ?></div>
            <div>Metode Pembayaran: <?= $order['jenis_pembayaran'] ?></div>
            <div>Kasir: Admin</div>
        </div>

        <div class="customer-info">
            <span class="section-title">Informasi Pembeli</span>
            <div>Nama: <?= !empty($order['nama_lengkap']) ? $order['nama_lengkap'] : $order['nama_user'] ?></div>
            <div>Telp: <?= $order['telepon'] ?></div>
            <div>Alamat: <?= $order['alamat'] ?></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $item): ?>
                <tr>
                    <td><?= $item['nama_barang'] ?></td>
                    <td><?= $item['jumlah'] ?></td>
                    <td>Rp <?= number_format($item['harga_jual'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="total-line">Total: Rp <?= number_format($order['jumlah_pembayaran'], 0, ',', '.') ?></div>
            <div class="total-line">Bayar: Rp <?= number_format($order['bayar'], 0, ',', '.') ?></div>
            <div class="grand-total">Kembalian: Rp <?= number_format($order['kembalian'], 0, ',', '.') ?></div>
        </div>

        <div class="footer">
            Terima kasih telah berbelanja<br>
            Barang yang sudah dibeli tidak dapat ditukar/dikembalikan
        </div>
    </div>

    <button class="print-button" onclick="window.print()">Cetak Struk</button>

    <script>
        window.onload = function() {
            // Auto print hanya jika parameter autoprint=true
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === 'true') {
                window.print();
            }
        }
    </script>
</body>
</html>