<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Ambil data produk
$barang = query("SELECT b.*, k.nama_kategori, m.nama_merk 
                FROM tb_barang b 
                LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
                LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
                WHERE b.stok > 0
                ORDER BY b.nama_barang ASC");

// Proses tambah penjualan
if (isset($_POST['tambah'])) {
    $admin_id = $_SESSION['admin_id'];
    $barang_id = $_POST['barang_id'];
    $jumlah = $_POST['jumlah'];
    $bayar = str_replace(['Rp', '.', ','], '', $_POST['bayar']);
    
    // Validasi
    $error = false;
    
    // Cek stok
    $stok_query = query("SELECT stok, harga_jual FROM tb_barang WHERE barang_id = $barang_id")[0];
    if ($jumlah > $stok_query['stok']) {
        $error = true;
        $error_msg = "Stok tidak mencukupi! Stok tersedia: " . $stok_query['stok'];
    }
    
    // Hitung total dan kembalian
    $subtotal = $stok_query['harga_jual'] * $jumlah;
    $kembalian = $bayar - $subtotal;
    
    if ($kembalian < 0) {
        $error = true;
        $error_msg = "Pembayaran kurang!";
    }

    if (!$error) {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert ke tb_penjualan
            $data_penjualan = [
                'admin_id' => $admin_id,
                'tanggal' => date('Y-m-d H:i:s'),
                'bayar' => $bayar,
                'total' => $subtotal,
                'kembalian' => $kembalian
            ];
            
            if (tambah('tb_penjualan', $data_penjualan)) {
                $penjualan_id = mysqli_insert_id($conn);
                
                // Insert ke tb_detail_penjualan
                $data_detail = [
                    'penjualan_id' => $penjualan_id,
                    'barang_id' => $barang_id,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal
                ];
                
                if (tambah('tb_detail_penjualan', $data_detail)) {
                    // Update stok
                    $stok_baru = $stok_query['stok'] - $jumlah;
                    $data_stok = ['stok' => $stok_baru];
                    
                    if (ubah('tb_barang', $data_stok, "barang_id = $barang_id")) {
                        mysqli_commit($conn);
                        $_SESSION['success'] = "Penjualan berhasil ditambahkan!";
                        header("Location: index.php");
                        exit;
                    }
                }
            }
            
            // Rollback jika ada error
            mysqli_rollback($conn);
            $error_msg = "Gagal menambahkan penjualan!";
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Penjualan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Penjualan</a></li>
        <li class="breadcrumb-item active">Tambah Penjualan</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-1"></i>
                    Form Tambah Penjualan
                </div>
                <div class="card-body">
                    <?php if (isset($error_msg)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="barang_id" class="form-label">Pilih Produk</label>
                                    <select class="form-select" id="barang_id" name="barang_id" required>
                                        <option value="">Pilih Produk</option>
                                        <?php foreach ($barang as $item) : ?>
                                            <option value="<?= $item['barang_id']; ?>" 
                                                    data-harga="<?= $item['harga_jual']; ?>"
                                                    data-stok="<?= $item['stok']; ?>">
                                                <?= $item['nama_barang']; ?> - 
                                                <?= $item['nama_merk']; ?> 
                                                (Stok: <?= $item['stok']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Pilih produk!
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="jumlah" class="form-label">Jumlah</label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" 
                                           min="1" required>
                                    <div class="invalid-feedback">
                                        Masukkan jumlah!
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Harga Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="harga" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Total</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="total" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bayar" class="form-label">Bayar</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control rupiah-input" id="bayar" 
                                               name="bayar" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        Masukkan jumlah pembayaran!
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Kembalian</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input<input type="text" class="form-control" id="kembalian" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" name="tambah" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informasi Produk -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle me-1"></i>
                    Informasi Produk
                </div>
                <div class="card-body">
                    <div id="produkInfo">
                        <p class="text-muted text-center mb-0">Pilih produk untuk melihat detail</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk kalkulasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const barangSelect = document.getElementById('barang_id');
    const jumlahInput = document.getElementById('jumlah');
    const hargaInput = document.getElementById('harga');
    const totalInput = document.getElementById('total');
    const bayarInput = document.getElementById('bayar');
    const kembalianInput = document.getElementById('kembalian');
    const produkInfo = document.getElementById('produkInfo');

    // Format number to currency
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Calculate total and change
    function hitungTotal() {
        const harga = parseInt(hargaInput.value.replace(/[^0-9]/g, '')) || 0;
        const jumlah = parseInt(jumlahInput.value) || 0;
        const bayar = parseInt(bayarInput.value.replace(/[^0-9]/g, '')) || 0;

        const total = harga * jumlah;
        const kembalian = bayar - total;

        totalInput.value = formatRupiah(total);
        kembalianInput.value = formatRupiah(Math.max(0, kembalian));

        // Validate payment
        if (bayar > 0 && bayar < total) {
            bayarInput.classList.add('is-invalid');
        } else {
            bayarInput.classList.remove('is-invalid');
        }
    }

    // Update product info when product is selected
    barangSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const harga = parseInt(selectedOption.dataset.harga) || 0;
        const stok = parseInt(selectedOption.dataset.stok) || 0;

        hargaInput.value = formatRupiah(harga);
        jumlahInput.max = stok;

        // Update product info display
        if (this.value) {
            produkInfo.innerHTML = `
                <div class="text-center mb-3">
                    <h5 class="mb-0">${selectedOption.text.split('-')[0].trim()}</h5>
                    <small class="text-muted">Stok tersedia: ${stok}</small>
                </div>
                <div class="d-grid gap-2">
                    <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Harga Satuan</small>
                        <strong>Rp ${formatRupiah(harga)}</strong>
                    </div>
                </div>
            `;
        } else {
            produkInfo.innerHTML = '<p class="text-muted text-center mb-0">Pilih produk untuk melihat detail</p>';
        }

        hitungTotal();
    });

    // Calculate on input change
    jumlahInput.addEventListener('input', hitungTotal);
    bayarInput.addEventListener('input', hitungTotal);

    // Format bayar input as rupiah
    bayarInput.addEventListener('keyup', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        this.value = formatRupiah(value);
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>