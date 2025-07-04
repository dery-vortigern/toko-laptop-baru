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

$merk_id = $_GET['id'];

// Cek apakah merk masih digunakan di tabel barang
$cek = query("SELECT * FROM tb_barang WHERE merk_id = $merk_id");

if ($cek) {
    $_SESSION['error'] = "Merk tidak dapat dihapus karena masih digunakan oleh beberapa produk!";
    header("Location: index.php");
    exit;
}

// Proses hapus
if (hapus('tb_merk', "merk_id = $merk_id")) {
    $_SESSION['success'] = "Merk berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus merk!";
}

header("Location: index.php");
exit;
?>