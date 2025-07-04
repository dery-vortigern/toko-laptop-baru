<?php
session_start();
require_once '../../config/koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek autentikasi admin
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../../auth/adminlogin.php");
    exit;
}

// Filter berdasarkan tanggal jika ada
$where = "";
if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    if (!empty($dari) && !empty($sampai)) {
        $where = "WHERE DATE(co.created_at) BETWEEN '$dari' AND '$sampai'";
    }
}

// Filter berdasarkan status jika ada
if (isset($_GET['status']) && in_array($_GET['status'], ['pending', 'processing', 'completed', 'cancelled'])) {
    $status = $_GET['status'];
    $where = empty($where) ? "WHERE co.status = '$status'" : $where . " AND co.status = '$status'";
}

// Mendapatkan parameter sort dan order jika ada
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'co.created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validasi kolom dan order untuk keamanan
$allowed_columns = ['co.created_at', 'co.order_id', 'u.nama', 'u.telepon', 'co.status', 'co.budget'];
$sort_column = in_array($sort_column, $allowed_columns) ? $sort_column : 'co.created_at';
$sort_order = in_array(strtoupper($sort_order), ['ASC', 'DESC']) ? strtoupper($sort_order) : 'DESC';

// Removed "u.email" from the query since it doesn't exist
$query = "SELECT co.*, u.nama as nama_user, u.telepon, a.nama as admin_name
          FROM tb_custom_orders co 
          LEFT JOIN tb_user u ON co.user_id = u.user_id
          LEFT JOIN tb_admin a ON co.admin_id = a.admin_id
          $where
          ORDER BY $sort_column $sort_order";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error in query: " . mysqli_error($conn));
}

$custom_orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $custom_orders[] = $row;
}

// Hitung total untuk ringkasan
$total_orders = count($custom_orders);
$total_budget = 0;

// Hitung jumlah order per status
$count_pending = 0;
$count_processing = 0;
$count_completed = 0;
$count_cancelled = 0;

foreach ($custom_orders as $order) {
    $total_budget += $order['budget'];
    
    switch ($order['status']) {
        case 'pending':
            $count_pending++;
            break;
        case 'processing':
            $count_processing++;
            break;
        case 'completed':
            $count_completed++;
            break;
        case 'cancelled':
            $count_cancelled++;
            break;
    }
}

// Judul periode laporan
$periode = "Semua Data";
if (!empty($dari) && !empty($sampai)) {
    $periode = "Periode: " . date('d/m/Y', strtotime($dari)) . " - " . date('d/m/Y', strtotime($sampai));
}

// Status label mapping
$status_labels = [
    'pending' => 'Menunggu',
    'processing' => 'Diproses',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

// Set header untuk file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_custom_orders_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Custom Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        h2, h3 {
            text-align: center;
            margin: 5px 0;
        }
        .summary {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .summary td {
            padding: 5px 10px;
        }
        .status-pending {
            background-color: #fff7ed;
            color: #9a3412;
        }
        .status-processing {
            background-color: #eff6ff;
            color: #1e40af;
        }
        .status-completed {
            background-color: #ecfdf5;
            color: #065f46;
        }
        .status-cancelled {
            background-color: #fef2f2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <h2>LAPORAN CUSTOM ORDERS</h2>
    <h3><?= $periode ?></h3>
    
    <!-- Ringkasan -->
    <table class="summary" border="0">
        <tr>
            <td width="200">Total Custom Orders</td>
            <td>: <?= $total_orders ?></td>
        </tr>
        <tr>
            <td>Total Budget Semua Order</td>
            <td>: Rp <?= number_format($total_budget, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Status Menunggu</td>
            <td>: <?= $count_pending ?></td>
        </tr>
        <tr>
            <td>Status Diproses</td>
            <td>: <?= $count_processing ?></td>
        </tr>
        <tr>
            <td>Status Selesai</td>
            <td>: <?= $count_completed ?></td>
        </tr>
        <tr>
            <td>Status Dibatalkan</td>
            <td>: <?= $count_cancelled ?></td>
        </tr>
    </table>
    
    <!-- Tabel Data Custom Orders -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Order ID</th>
                <th>Nama Pelanggan</th>
                <th>Telepon</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Processor</th>
                <th>RAM</th>
                <th>Storage</th>
                <th>VGA</th>
                <th>Screen Size</th>
                <th>Screen Type</th>
                <th>OS</th>
                <th>Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total_orders > 0): ?>
                <?php $no = 1; foreach ($custom_orders as $order) : ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= $order['nama_user'] ?? 'User tidak ditemukan' ?></td>
                    <td><?= $order['telepon'] ?? '-' ?></td>
                    <td class="status-<?= $order['status'] ?>"><?= $status_labels[$order['status']] ?? $order['status'] ?></td>
                    <td class="text-right">Rp <?= number_format($order['budget'], 0, ',', '.') ?></td>
                    <td><?= $order['processor'] ?></td>
                    <td><?= $order['ram'] ?></td>
                    <td><?= $order['storage'] ?></td>
                    <td><?= $order['vga'] ?></td>
                    <td><?= $order['screen_size'] ?></td>
                    <td><?= $order['screen_type'] ?></td>
                    <td><?= $order['operating_system'] ?></td>
                    <td><?= $order['admin_name'] ?? '-' ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="15" class="text-center">Tidak ada data yang tersedia</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">TOTAL BUDGET</th>
                <th class="text-right">Rp <?= number_format($total_budget, 0, ',', '.') ?></th>
                <th colspan="8"></th>
            </tr>
        </tfoot>
    </table>

    <?php if ($total_orders > 0): ?>
    <!-- Ringkasan Spesifikasi Populer -->
    <h3 style="margin-top:30px;">RINGKASAN SPESIFIKASI</h3>

    <!-- Processor Populer -->
    <?php
    $queryProcessor = "SELECT processor, COUNT(*) as total, AVG(budget) as avg_budget, MAX(budget) as max_budget 
                      FROM tb_custom_orders " . (empty($where) ? "" : str_replace('co.', '', $where)) . "
                      GROUP BY processor ORDER BY total DESC LIMIT 10";
    $resultProcessor = mysqli_query($conn, $queryProcessor);
    $processors = [];
    while ($row = mysqli_fetch_assoc($resultProcessor)) {
        $processors[] = $row;
    }
    ?>
    <h4>Processor Populer</h4>
    <table>
        <thead>
            <tr>
                <th>Processor</th>
                <th>Total Order</th>
                <th>Persentase</th>
                <th>Budget Rata-rata</th>
                <th>Budget Tertinggi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($processors as $proc) : ?>
            <tr>
                <td><?= $proc['processor'] ?></td>
                <td class="text-center"><?= $proc['total'] ?></td>
                <td class="text-center"><?= $total_orders > 0 ? round(($proc['total'] / $total_orders) * 100, 2) : 0 ?>%</td>
                <td class="text-right">Rp <?= number_format($proc['avg_budget'], 0, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($proc['max_budget'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- RAM Populer -->
    <?php
    $queryRam = "SELECT ram, COUNT(*) as total, AVG(budget) as avg_budget, MAX(budget) as max_budget 
                FROM tb_custom_orders " . (empty($where) ? "" : str_replace('co.', '', $where)) . "
                GROUP BY ram ORDER BY total DESC LIMIT 10";
    $resultRam = mysqli_query($conn, $queryRam);
    $rams = [];
    while ($row = mysqli_fetch_assoc($resultRam)) {
        $rams[] = $row;
    }
    ?>
    <h4>RAM Populer</h4>
    <table>
        <thead>
            <tr>
                <th>RAM</th>
                <th>Total Order</th>
                <th>Persentase</th>
                <th>Budget Rata-rata</th>
                <th>Budget Tertinggi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rams as $ram) : ?>
            <tr>
                <td><?= $ram['ram'] ?></td>
                <td class="text-center"><?= $ram['total'] ?></td>
                <td class="text-center"><?= $total_orders > 0 ? round(($ram['total'] / $total_orders) * 100, 2) : 0 ?>%</td>
                <td class="text-right">Rp <?= number_format($ram['avg_budget'], 0, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($ram['max_budget'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Storage Populer -->
    <?php
    $queryStorage = "SELECT storage, COUNT(*) as total, AVG(budget) as avg_budget, MAX(budget) as max_budget 
                    FROM tb_custom_orders " . (empty($where) ? "" : str_replace('co.', '', $where)) . "
                    GROUP BY storage ORDER BY total DESC LIMIT 10";
    $resultStorage = mysqli_query($conn, $queryStorage);
    $storages = [];
    while ($row = mysqli_fetch_assoc($resultStorage)) {
        $storages[] = $row;
    }
    ?>
    <h4>Storage Populer</h4>
    <table>
        <thead>
            <tr>
                <th>Storage</th>
                <th>Total Order</th>
                <th>Persentase</th>
                <th>Budget Rata-rata</th>
                <th>Budget Tertinggi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($storages as $storage) : ?>
            <tr>
                <td><?= $storage['storage'] ?></td>
                <td class="text-center"><?= $storage['total'] ?></td>
                <td class="text-center"><?= $total_orders > 0 ? round(($storage['total'] / $total_orders) * 100, 2) : 0 ?>%</td>
                <td class="text-right">Rp <?= number_format($storage['avg_budget'], 0, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($storage['max_budget'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <!-- Tanda Tangan -->
    <div style="margin-top: 30px; text-align: right;">
        <p>........................., <?= date('d F Y') ?><br>
        Dibuat oleh,<br><br><br><br>
        <?= $_SESSION['nama'] ?? 'Admin' ?></p>
    </div>
</body>
</html>