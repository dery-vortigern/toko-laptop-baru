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

// Query untuk mendapatkan detail penjualan dengan info user
$query = "SELECT p.*, a.nama as admin_name, u.nama as nama_user, u.alamat, u.telepon,
          pb.jenis_pembayaran
          FROM tb_penjualan p 
          LEFT JOIN tb_admin a ON p.admin_id = a.admin_id
          LEFT JOIN tb_pembelian pmb ON p.id_pembelian = pmb.id_pembelian
          LEFT JOIN tb_user u ON pmb.user_id = u.user_id
          LEFT JOIN tb_pembayaran pb ON pmb.pembayaran_id = pb.pembayaran_id
          WHERE p.penjualan_id = $penjualan_id";
$penjualan = query($query)[0];

// Ambil detail produk yang dibeli
$detail_query = "SELECT dp.*, b.nama_barang, b.harga_jual, m.nama_merk 
                FROM tb_detail_penjualan dp 
                JOIN tb_barang b ON dp.barang_id = b.barang_id 
                LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
                WHERE dp.penjualan_id = $penjualan_id";
$details = query($detail_query);

// HTML dan kode frontend tetap sama seperti sebelumnya
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan #<?= $penjualan_id ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .store-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .customer-info,
        .invoice-details {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .totals {
            text-align: right;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media print {
            .print-button {
                display: none;
            }
            @page {
                margin: 0.5cm;
            }
            body {
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h2 style="margin: 0;"> WARINGIN-IT</h2>
        <p style="margin: 5px 0;">Pusat Penjualan Laptop Terpercaya</p>
    </div>

    <div class="store-info">
        <p style="margin: 5px 0;">
            Jl. Lantai 2 ITC Surabaya, blok G6 no 3a, 5, 6. Waringin IT<br>
            Telp: 085784777172<br>
            Email: waringin@waringinit.com
        </p>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <!-- Informasi Pembeli -->
        <div style="width: 48%;">
            <h4 style="margin-bottom: 10px;">Informasi Pembeli:</h4>
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="border: none; padding: 2px;">Nama</td>
                    <td style="border: none; padding: 2px;">: <?= htmlspecialchars($penjualan['nama_user'] ?? 'User tidak ditemukan'); ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px;">Telepon</td>
                    <td style="border: none; padding: 2px;">: <?= htmlspecialchars($penjualan['telepon'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px;">Alamat</td>
                    <td style="border: none; padding: 2px;">: <?= htmlspecialchars($penjualan['alamat'] ?? '-'); ?></td>
                </tr>
            </table>
        </div>

        <!-- Informasi Transaksi -->
        <div style="width: 48%;">
            <h4 style="margin-bottom: 10px;">Detail Transaksi:</h4>
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="border: none; padding: 2px;">No. Transaksi</td>
                    <td style="border: none; padding: 2px;">: #<?= $penjualan_id ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px;">Tanggal</td>
                    <td style="border: none; padding: 2px;">: <?= date('d/m/Y H:i', strtotime($penjualan['tanggal'])) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px;">Kasir</td>
                    <td style="border: none; padding: 2px;">: <?= htmlspecialchars($penjualan['admin_name']) ?></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px;">Metode Bayar</td>
                    <td style="border: none; padding: 2px;">: <?= htmlspecialchars($penjualan['jenis_pembayaran'] ?? '-') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align: center;">No</th>
                <th>Produk</th>
                <th>Merk</th>
                <th style="text-align: right;">Harga</th>
                <th style="text-align: center;">Jumlah</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($details as $item) : ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                <td><?= htmlspecialchars($item['nama_merk']); ?></td>
                <td style="text-align: right;">Rp <?= number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                <td style="text-align: center;"><?= $item['jumlah']; ?></td>
                <td style="text-align: right;">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                <td style="text-align: right;"><strong>Rp <?= number_format($penjualan['total'], 0, ',', '.'); ?></strong></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;">Bayar:</td>
                <td style="text-align: right;">Rp <?= number_format($penjualan['bayar'], 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;">Kembalian:</td>
                <td style="text-align: right;">Rp <?= number_format($penjualan['kembalian'], 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p style="margin: 5px 0;">
            Terima kasih telah berbelanja di WARINGIN-IT<br>
            Barang yang sudah dibeli tidak dapat ditukar/dikembalikan
        </p>
        <p style="margin: 5px 0; color: #666; font-size: 12px;">
            Dicetak pada: <?= date('d/m/Y H:i:s') ?>
        </p>
    </div>

    <button onclick="window.print()" class="print-button">Cetak</button>

    <script>
    // Auto print when page loads (uncomment to enable)
    // window.onload = function() {
    //     window.print();
    // }
    </script>
</body>
</html>