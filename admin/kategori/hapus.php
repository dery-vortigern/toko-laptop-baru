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

$kategori_id = $_GET['id'];

// Cek apakah kategori masih digunakan di tabel barang
$cek = query("SELECT * FROM tb_barang WHERE kategori_id = $kategori_id");

if ($cek) {
    $_SESSION['error'] = "Kategori tidak dapat dihapus karena masih digunakan!";
    header("Location: index.php");
    exit;
}

// Proses hapus
if (hapus('tb_kategori', "kategori_id = $kategori_id")) {
    $_SESSION['success'] = "Kategori berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus kategori!";
}

header("Location: index.php");
exit;
?>