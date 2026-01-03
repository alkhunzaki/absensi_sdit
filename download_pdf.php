<?php
// download_pdf.php
require('fpdf/fpdf.php'); // Panggil pustaka FPDF
include 'config.php';

// Ambil filter dari URL
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_siswa = isset($_GET['id_siswa']) ? (int)$_GET['id_siswa'] : '';

// Query untuk mengambil data rekap
$query_rekap = "
    SELECT a.tanggal, a.status, a.catatan, s.nama_lengkap, s.nis 
    FROM absensi a 
    JOIN siswa s ON a.id_siswa = s.id_siswa
";
$where_clauses = [];
if ($filter_bulan) {
    $where_clauses[] = "DATE_FORMAT(a.tanggal, '%Y-%m') = '$filter_bulan'";
}
if ($filter_siswa) {
    $where_clauses[] = "a.id_siswa = $filter_siswa";
}
if (!empty($where_clauses)) {
    $query_rekap .= " WHERE " . implode(' AND ', $where_clauses);
}
$query_rekap .= " ORDER BY a.tanggal DESC, s.nama_lengkap ASC";
$result_rekap = mysqli_query($koneksi, $query_rekap);

// Ambil nama siswa jika difilter
$nama_siswa_filter = "Semua Siswa";
if ($filter_siswa) {
    $result_nama = mysqli_query($koneksi, "SELECT nama_lengkap FROM siswa WHERE id_siswa = $filter_siswa");
    if ($row_nama = mysqli_fetch_assoc($result_nama)) {
        $nama_siswa_filter = $row_nama['nama_lengkap'];
    }
}

// Buat objek PDF
class PDF extends FPDF
{
    // Header halaman
    function Header()
    {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,5,'REKAPITULASI KEHADIRAN SISWA',0,1,'C');
        $this->SetFont('Arial','B',12);
        $this->Cell(0,7,'SDIT Akhyar International Islamic School',0,1,'C');
        $this->Ln(5);
    }

    // Footer halaman
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Inisialisasi PDF
$pdf = new PDF('L','mm','A4'); // 'L' untuk Landscape
$pdf->AliasNbPages();
$pdf->AddPage();

// Informasi Filter
$pdf->SetFont('Arial','B',10);
$pdf->Cell(20,6,'Bulan',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6,date("F Y", strtotime($filter_bulan)),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(20,6,'Siswa',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6,htmlspecialchars($nama_siswa_filter),0,1);
$pdf->Ln(10); // Jarak

// Header Tabel
$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'NIS', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Nama Lengkap', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(117, 10, 'Catatan', 1, 1, 'C', true);

// Isi Tabel
$pdf->SetFont('Arial','',9);
if (mysqli_num_rows($result_rekap) > 0) {
    $no = 1;
    while($row = mysqli_fetch_assoc($result_rekap)) {
        $pdf->Cell(10, 8, $no++, 1, 0, 'C');
        $pdf->Cell(25, 8, date("d-m-Y", strtotime($row['tanggal'])), 1, 0, 'C');
        $pdf->Cell(30, 8, htmlspecialchars($row['nis']), 1, 0, 'L');
        $pdf->Cell(70, 8, htmlspecialchars($row['nama_lengkap']), 1, 0, 'L');
        $pdf->Cell(25, 8, htmlspecialchars($row['status']), 1, 0, 'C');
        $pdf->Cell(117, 8, htmlspecialchars($row['catatan']), 1, 1, 'L');
    }
} else {
    $pdf->Cell(277, 10, 'Tidak ada data untuk filter yang dipilih.', 1, 1, 'C');
}

// Output PDF untuk diunduh
$nama_file_pdf = "rekap-absensi-" . $filter_bulan . ".pdf";
$pdf->Output('D', $nama_file_pdf);
?>
