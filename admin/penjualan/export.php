<?php
session_start();
require_once '../../config/koneksi.php';

// Cek autentikasi admin
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Data_Penjualan_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Inisialisasi variabel-variabel filtering
$where = "";
$dari = "";
$sampai = "";

// Filter berdasarkan tanggal jika ada
if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    if (!empty($dari) && !empty($sampai)) {
        $where = "WHERE DATE(p.tanggal) BETWEEN '$dari' AND '$sampai'";
    }
}

// Pengaturan sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'p.tanggal';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validasi kolom sorting untuk keamanan
$allowed_sort_columns = [
    'p.tanggal', 'u.nama', 'u.telepon', 'pb.jenis_pembayaran', 
    'p.total', 'p.bayar', 'p.kembalian', 'a.nama', 'm.nama_merk'
];

if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'p.tanggal';
}

if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
    $order = 'DESC';
}

// Query untuk mendapatkan data penjualan
$query = "SELECT p.*, a.nama as admin_name, u.nama as nama_user, u.telepon, 
          pb.jenis_pembayaran, m.nama_merk as merk,
          (SELECT SUM(dp.subtotal) FROM tb_detail_penjualan dp WHERE dp.penjualan_id = p.penjualan_id) as total_penjualan 
          FROM tb_penjualan p 
          LEFT JOIN tb_admin a ON p.admin_id = a.admin_id
          LEFT JOIN tb_pembelian pmb ON p.id_pembelian = pmb.id_pembelian
          LEFT JOIN tb_user u ON pmb.user_id = u.user_id
          LEFT JOIN tb_pembayaran pb ON pmb.pembayaran_id = pb.pembayaran_id
          LEFT JOIN tb_detail_penjualan dp ON p.penjualan_id = dp.penjualan_id
          LEFT JOIN tb_barang b ON dp.barang_id = b.barang_id
          LEFT JOIN tb_merk m ON b.merk_id = m.merk_id
          $where
          GROUP BY p.penjualan_id
          ORDER BY $sort $order";

$penjualan = query($query);

// Query untuk mendapatkan total produk terjual
$query_produk = "SELECT COALESCE(SUM(dp.jumlah), 0) as total 
                FROM tb_detail_penjualan dp 
                JOIN tb_penjualan p ON dp.penjualan_id = p.penjualan_id 
                " . (empty($where) ? "" : str_replace('WHERE', 'WHERE', $where));
$total_produk_result = query($query_produk);
$total_produk = $total_produk_result[0]['total'];

// Query untuk mendapatkan total customer
$query_customer = "SELECT COUNT(DISTINCT pmb.user_id) as total 
                  FROM tb_pembelian pmb 
                  JOIN tb_penjualan p ON pmb.id_pembelian = p.id_pembelian
                  " . (empty($where) ? "" : str_replace('WHERE', 'WHERE', $where));
$total_customer_result = query($query_customer);
$total_customer = $total_customer_result[0]['total'];

// Mulai output HTML untuk Excel
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Penjualan</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .header {
            background-color: #D9E2F3;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary {
            margin-bottom: 20px;
        }
        
        .summary table {
            width: 50%;
        }
        
        .summary th {
            background-color: #70AD47;
            width: 40%;
        }
        
        .summary td {
            background-color: #E2EFDA;
        }
        
        .number {
            text-align: right;
        }
        
        tr:nth-child(even) {
            background-color: #F2F2F2;
        }
        
        .total-row {
            background-color: #FFE699 !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header Laporan -->
    <div class="header">
        <h2>LAPORAN DATA PENJUALAN</h2>
        <?php if (!empty($dari) && !empty($sampai)): ?>
            <p>Periode: <?= date('d/m/Y', strtotime($dari)); ?> s/d <?= date('d/m/Y', strtotime($sampai)); ?></p>
        <?php else: ?>
            <p>Semua Data</p>
        <?php endif; ?>
        <p>Tanggal Cetak: <?= date('d/m/Y H:i:s'); ?></p>
    </div>

    <!-- Ringkasan Data -->
    <div class="summary">
        <h3>RINGKASAN</h3>
        <table>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
            <tr>
                <td>Total Transaksi</td>
                <td class="number"><?= count($penjualan); ?> transaksi</td>
            </tr>
            <tr>
                <td>Total Pendapatan</td>
                <td class="number">Rp <?= number_format(array_sum(array_column($penjualan, 'total')), 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td>Total Produk Terjual</td>
                <td class="number"><?= $total_produk; ?> unit</td>
            </tr>
            <tr>
                <td>Total Customer</td>
                <td class="number"><?= $total_customer; ?> customer</td>
            </tr>
        </table>
    </div>

    <br><br>

    <!-- Tabel Data Penjualan -->
    <h3>DETAIL DATA PENJUALAN</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Pembeli</th>
                <th style="width: 12%;">Telepon</th>
                <th style="width: 10%;">Merk</th>
                <th style="width: 12%;">Jenis Pembayaran</th>
                <th style="width: 12%;">Total</th>
                <th style="width: 12%;">Bayar</th>
                <th style="width: 12%;">Kembalian</th>
                <th style="width: 8%;">Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $grand_total = 0;
            $grand_bayar = 0;
            $grand_kembalian = 0;
            
            foreach ($penjualan as $row): 
                $grand_total += $row['total'];
                $grand_bayar += $row['bayar'];
                $grand_kembalian += $row['kembalian'];
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                <td><?= htmlspecialchars($row['nama_user'] ?? 'User tidak ditemukan'); ?></td>
                <td><?= htmlspecialchars($row['telepon'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['merk'] ?? '-'); ?></td>
                <td><?= htmlspecialchars($row['jenis_pembayaran'] ?? '-'); ?></td>
                <td class="number">Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                <td class="number">Rp <?= number_format($row['bayar'], 0, ',', '.'); ?></td>
                <td class="number">Rp <?= number_format($row['kembalian'], 0, ',', '.'); ?></td>
                <td><?= htmlspecialchars($row['admin_name'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="6" style="text-align: center; font-weight: bold;">TOTAL</td>
                <td class="number" style="font-weight: bold;">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                <td class="number" style="font-weight: bold;">Rp <?= number_format($grand_bayar, 0, ',', '.'); ?></td>
                <td class="number" style="font-weight: bold;">Rp <?= number_format($grand_kembalian, 0, ',', '.'); ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <!-- Footer -->
    <div style="margin-top: 30px;">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 70%;"></td>
                <td style="border: none; text-align: center;">
                    <p>Dicetak oleh: <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'); ?></p>
                    <p>Tanggal: <?= date('d/m/Y H:i:s'); ?></p>
                    <br><br><br>
                    <p>(_____________________)</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>