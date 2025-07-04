<?php
session_start();
require_once '../../config/koneksi.php';

// Cek autentikasi admin
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Include library FPDF
require_once '../../libs/fpdf/fpdf.php';

// Buat class untuk custom PDF
class PDF extends FPDF
{
    // Header halaman
    function Header()
    {
        // Logo (opsional - sesuaikan path logo Anda)
        // $this->Image('logo.png', 10, 6, 30);
        
        // Background header dengan gradient effect
        $this->SetFillColor(248, 249, 250);
        $this->Rect(0, 0, 297, 45, 'F');
        
        // Arial bold 20 - Judul lebih besar dan mencolok
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(25, 135, 84); // Warna hijau profesional
        $this->SetXY(0, 8);
        $this->Cell(0, 10, 'LAPORAN DATA PENJUALAN', 0, 1, 'C');
        
        // Nama perusahaan/toko dengan accent color
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(52, 58, 64);
        $this->Cell(0, 8, 'WARINGIN-IT', 0, 1, 'C');
        
        // Alamat dan kontak dengan spacing yang lebih baik
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(108, 117, 125);
        $this->Cell(0, 5, 'Jl. Contoh No. 123, Kota, Provinsi', 0, 1, 'C');
        $this->Cell(0, 5, 'Telp: (021) 12345678 | Email: info@waringinit.com', 0, 1, 'C');
        
        // Garis horizontal dengan style yang lebih elegan
        $this->Ln(8);
        $this->SetDrawColor(25, 135, 84);
        $this->SetLineWidth(1.2);
        $this->Line(20, $this->GetY(), 277, $this->GetY());
        
        // Garis tipis kedua
        $this->SetDrawColor(108, 117, 125);
        $this->SetLineWidth(0.3);
        $this->Line(20, $this->GetY() + 2, 277, $this->GetY() + 2);
        
        $this->SetLineWidth(0.2); // Reset line width
        $this->Ln(15);
        
        // Reset warna teks
        $this->SetTextColor(0, 0, 0);
    }

    // Footer halaman dengan perbaikan
    function Footer()
    {
        // Pindah ke posisi footer yang tepat
        $this->SetY(-30);
        
        // Reset semua properti
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(108, 117, 125);
        $this->SetDrawColor(108, 117, 125);
        $this->SetLineWidth(0.5);
        
        // Garis horizontal dengan margin yang benar untuk landscape
        $this->Line(20, $this->GetY(), 277, $this->GetY());
        $this->Ln(3);
        
        // Background footer ringan
        $this->SetFillColor(248, 249, 250);
        $this->Rect(0, $this->GetY(), 297, 20, 'F');
        
        // Baris pertama: Tanggal cetak di kiri, Halaman di kanan
        $this->SetXY(20, $this->GetY() + 2);
        $this->Cell(120, 5, 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB', 0, 0, 'L');
        $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' dari {nb}', 0, 1, 'R');
        
        // Baris kedua: Info sistem di tengah
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(25, 135, 84);
        $this->Cell(0, 5, 'Laporan Sistem Penjualan WARINGIN-IT', 0, 0, 'C');
        
        // Reset warna
        $this->SetTextColor(0, 0, 0);
    }
    
    // Fungsi untuk membuat tabel header dengan design yang lebih modern
    function TableHeader()
    {
        $this->SetFont('Arial', 'B', 9);
        
        // Gradient header effect
        $this->SetFillColor(25, 135, 84); // Hijau gelap
        $this->SetTextColor(255, 255, 255); // Teks putih
        $this->SetDrawColor(255, 255, 255); // Border putih
        $this->SetLineWidth(0.8);
        
        // Header kolom dengan tinggi yang lebih proporsional
        $headerHeight = 12;
        $this->Cell(15, $headerHeight, 'No', 1, 0, 'C', true);
        $this->Cell(28, $headerHeight, 'Tanggal', 1, 0, 'C', true);
        $this->Cell(40, $headerHeight, 'Nama Pembeli', 1, 0, 'C', true);
        $this->Cell(30, $headerHeight, 'No. Telepon', 1, 0, 'C', true);
        $this->Cell(25, $headerHeight, 'Merk', 1, 0, 'C', true);
        $this->Cell(32, $headerHeight, 'Jenis Bayar', 1, 0, 'C', true);
        $this->Cell(28, $headerHeight, 'Total (Rp)', 1, 0, 'C', true);
        $this->Cell(28, $headerHeight, 'Bayar (Rp)', 1, 0, 'C', true);
        $this->Cell(28, $headerHeight, 'Kembali (Rp)', 1, 0, 'C', true);
        $this->Cell(26, $headerHeight, 'Admin', 1, 1, 'C', true);
        
        // Sub-header dengan warna lebih terang
        $this->SetFillColor(40, 167, 69); // Hijau sedang
        $this->SetFont('Arial', '', 8);
        $this->Cell(280, 3, '', 1, 1, 'C', true);
        
        // Reset warna dan line width
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(220, 220, 220); // Border abu-abu muda untuk data
        $this->SetLineWidth(0.3);
    }
    
    // Fungsi untuk format rupiah
    function formatRupiah($angka)
    {
        return number_format($angka, 0, ',', '.');
    }
    
    // Fungsi untuk memotong teks jika terlalu panjang
    function truncateText($text, $maxLength = 15)
    {
        if (strlen($text) > $maxLength) {
            return substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }
    
    // Fungsi untuk membuat box statistik dengan design modern
    function StatBox($title, $value, $x, $y, $width = 65, $height = 28)
    {
        $this->SetXY($x, $y);
        
        // Shadow effect
        $this->SetFillColor(200, 200, 200);
        $this->Rect($x + 1, $y + 1, $width, $height, 'F');
        
        // Main box dengan gradient
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(25, 135, 84);
        $this->SetLineWidth(0.8);
        $this->Rect($x, $y, $width, $height, 'FD');
        
        // Accent line di atas
        $this->SetFillColor(25, 135, 84);
        $this->Rect($x, $y, $width, 3, 'F');
        
        // Title
        $this->SetXY($x + 2, $y + 6);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(108, 117, 125);
        $this->Cell($width - 4, 6, $title, 0, 1, 'C');
        
        // Value dengan font yang lebih besar
        $this->SetXY($x + 2, $y + 15);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(25, 135, 84);
        $this->Cell($width - 4, 8, $value, 0, 1, 'C');
        
        // Reset
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(0.2);
    }
    
    // Fungsi untuk section header
    function SectionHeader($title, $color = null)
    {
        if ($color === null) {
            $color = [25, 135, 84];
        }
        
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor($color[0], $color[1], $color[2]);
        
        // Background section
        $this->SetFillColor(248, 249, 250);
        $this->Cell(0, 10, $title, 0, 1, 'L', true);
        
        // Accent line
        $this->SetDrawColor($color[0], $color[1], $color[2]);
        $this->SetLineWidth(2);
        $this->Line(20, $this->GetY() - 8, 80, $this->GetY() - 8);
        
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->Ln(2);
    }
}

// Inisialisasi variabel filtering
$where = "";
$dari = "";
$sampai = "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'p.tanggal';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validasi kolom sorting untuk keamanan
$allowed_sort_columns = [
    'p.tanggal', 'u.nama', 'u.telepon', 'm.nama_merk', 
    'pb.jenis_pembayaran', 'p.total', 'p.bayar', 'p.kembalian', 'a.nama'
];

if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'p.tanggal';
}

if (!in_array($order, ['ASC', 'DESC'])) {
    $order = 'DESC';
}

// Filter berdasarkan tanggal jika ada
if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    if (!empty($dari) && !empty($sampai)) {
        $where = "WHERE DATE(p.tanggal) BETWEEN '$dari' AND '$sampai'";
    }
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

// Hitung total statistik
$total_transaksi = count($penjualan);
$total_pendapatan = array_sum(array_column($penjualan, 'total'));

// Query untuk total produk terjual
$query_produk = "SELECT COALESCE(SUM(dp.jumlah), 0) as total 
                FROM tb_detail_penjualan dp 
                JOIN tb_penjualan p ON dp.penjualan_id = p.penjualan_id 
                " . (empty($where) ? "" : str_replace('WHERE', 'WHERE', $where));
$result_produk = query($query_produk);
$total_produk = $result_produk[0]['total'] ?? 0;

// Query untuk total customer
$query_customer = "SELECT COUNT(DISTINCT pmb.user_id) as total 
                  FROM tb_pembelian pmb 
                  JOIN tb_penjualan p ON pmb.id_pembelian = p.id_pembelian
                  " . (empty($where) ? "" : str_replace('WHERE', 'WHERE', $where));
$result_customer = query($query_customer);
$total_customer = $result_customer[0]['total'] ?? 0;

// Buat instance PDF
$pdf = new PDF('L'); // L untuk Landscape
$pdf->AliasNbPages();
$pdf->AddPage();

// Informasi periode laporan dengan design yang lebih menarik
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(25, 135, 84);

// Background untuk periode
$pdf->SetFillColor(240, 248, 255);
$pdf->SetDrawColor(25, 135, 84);
$pdf->SetLineWidth(0.5);

if (!empty($dari) && !empty($sampai)) {
    $periodeText = 'PERIODE: ' . strtoupper(date('d F Y', strtotime($dari))) . ' s/d ' . strtoupper(date('d F Y', strtotime($sampai)));
} else {
    $periodeText = 'PERIODE: SEMUA DATA';
}

$pdf->Cell(0, 12, $periodeText, 1, 1, 'C', true);
$pdf->Ln(10);

// Ringkasan statistik dengan section header
$pdf->SectionHeader('RINGKASAN LAPORAN');
$pdf->Ln(5);

// Buat 4 box statistik dalam satu baris dengan spacing yang lebih baik
$startX = 20;
$startY = $pdf->GetY();
$boxWidth = 63;
$boxHeight = 28;
$spacing = 6;

$pdf->StatBox('Total Transaksi', number_format($total_transaksi, 0, ',', '.'), 
              $startX, $startY, $boxWidth, $boxHeight);

$pdf->StatBox('Total Pendapatan', 'Rp ' . number_format($total_pendapatan, 0, ',', '.'), 
              $startX + $boxWidth + $spacing, $startY, $boxWidth, $boxHeight);

$pdf->StatBox('Produk Terjual', number_format($total_produk, 0, ',', '.'), 
              $startX + ($boxWidth + $spacing) * 2, $startY, $boxWidth, $boxHeight);

$pdf->StatBox('Total Customer', number_format($total_customer, 0, ',', '.'), 
              $startX + ($boxWidth + $spacing) * 3, $startY, $boxWidth, $boxHeight);

$pdf->SetY($startY + $boxHeight + 15);

// Header tabel dengan section header
$pdf->SectionHeader('DETAIL TRANSAKSI PENJUALAN');
$pdf->Ln(5);

$pdf->TableHeader();

// Data tabel dengan row styling yang lebih baik
$pdf->SetFont('Arial', '', 8);
$no = 1;
$total_keseluruhan = 0;

foreach ($penjualan as $row) {
    // Cek jika perlu halaman baru
    if ($pdf->GetY() > 175) {
        $pdf->AddPage();
        $pdf->TableHeader();
    }
    
    // Warna baris bergantian dengan nuansa yang lebih lembut
    $fill = ($no % 2 == 0);
    if ($fill) {
        $pdf->SetFillColor(252, 253, 253); // Abu-abu sangat muda
    } else {
        $pdf->SetFillColor(255, 255, 255); // Putih
    }
    
    $rowHeight = 10;
    
    // Data baris dengan padding yang lebih baik
    $pdf->Cell(15, $rowHeight, $no, 1, 0, 'C', $fill);
    $pdf->Cell(28, $rowHeight, date('d/m/Y', strtotime($row['tanggal'])), 1, 0, 'C', $fill);
    $pdf->Cell(40, $rowHeight, $pdf->truncateText($row['nama_user'] ?? 'N/A', 25), 1, 0, 'L', $fill);
    $pdf->Cell(30, $rowHeight, $pdf->truncateText($row['telepon'] ?? '-', 18), 1, 0, 'C', $fill);
    $pdf->Cell(25, $rowHeight, $pdf->truncateText($row['merk'] ?? '-', 15), 1, 0, 'C', $fill);
    $pdf->Cell(32, $rowHeight, $pdf->truncateText($row['jenis_pembayaran'] ?? '-', 20), 1, 0, 'C', $fill);
    
    // Format mata uang dengan alignment kanan dan padding
    $pdf->Cell(28, $rowHeight, $pdf->formatRupiah($row['total']), 1, 0, 'R', $fill);
    $pdf->Cell(28, $rowHeight, $pdf->formatRupiah($row['bayar']), 1, 0, 'R', $fill);
    $pdf->Cell(28, $rowHeight, $pdf->formatRupiah($row['kembalian']), 1, 0, 'R', $fill);
    $pdf->Cell(26, $rowHeight, $pdf->truncateText($row['admin_name'], 15), 1, 1, 'C', $fill);
    
    $total_keseluruhan += $row['total'];
    $no++;
}

// Baris total dengan design yang lebih premium
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(25, 135, 84);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(255, 255, 255);
$pdf->SetLineWidth(0.8);

$totalRowHeight = 14;
$pdf->Cell(198, $totalRowHeight, 'TOTAL KESELURUHAN', 1, 0, 'C', true);
$pdf->Cell(28, $totalRowHeight, $pdf->formatRupiah($total_keseluruhan), 1, 0, 'R', true);
$pdf->Cell(54, $totalRowHeight, '', 1, 1, 'C', true);

// Reset warna
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.3);

// Tanda tangan dengan layout yang lebih profesional
$pdf->Ln(25);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(52, 58, 64);

// Dua kolom untuk tanda tangan
$pdf->Cell(140, 6, 'Disiapkan oleh:', 0, 0, 'L');
$pdf->Cell(0, 6, 'Mengetahui:', 0, 1, 'C');

$pdf->Cell(140, 6, date('d F Y'), 0, 0, 'L');
$pdf->Cell(0, 6, date('d F Y'), 0, 1, 'C');

$pdf->Ln(25);

// Nama dan garis tanda tangan
$pdf->SetDrawColor(52, 58, 64);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 100, $pdf->GetY()); // Garis kiri
$pdf->Line(197, $pdf->GetY(), 277, $pdf->GetY()); // Garis kanan

$pdf->Ln(8);
$pdf->Cell(140, 6, 'Staff Admin', 0, 0, 'L');
$pdf->Cell(0, 6, 'Manager/Supervisor', 0, 1, 'C');

// Generate nama file
$filename = 'Laporan_Penjualan_' . date('Y-m-d_H-i-s');
if (!empty($dari) && !empty($sampai)) {
    $filename .= '_' . $dari . '_to_' . $sampai;
}
$filename .= '.pdf';

// Output PDF
$pdf->Output('D', $filename); // 'D' untuk download, 'I' untuk tampil di browser
?>