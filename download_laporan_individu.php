<?php
// download_laporan_individu.php (Dengan Logika Pratinjau)

require('fpdf/fpdf.php');
include 'config.php';
check_login();

// PERUBAHAN: Memeriksa tombol mana yang ditekan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_siswa']) || empty($_POST['action'])) {
    die('Akses tidak sah atau data tidak lengkap.');
}

// Ambil data dari form
$action = $_POST['action']; // 'preview' atau 'download'
$id_siswa = (int)$_POST['id_siswa'];
$bulan_laporan = $_POST['bulan'];
$teks_kop = $_POST['teks_kop'];
$nama_walikelas = $_POST['nama_walikelas'];
$catatan_kehadiran = $_POST['catatan_kehadiran'];

// --- (Semua kode pemrosesan data, upload gambar, dan URL grafik sama seperti sebelumnya) ---
$query_siswa = "SELECT * FROM siswa WHERE id_siswa = $id_siswa";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$data_siswa = mysqli_fetch_assoc($result_siswa);
$bulan_laporan_safe = mysqli_real_escape_string($koneksi, $bulan_laporan);
$query_absensi = "SELECT * FROM absensi WHERE id_siswa = $id_siswa AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_laporan_safe' ORDER BY tanggal ASC";
$result_absensi = mysqli_query($koneksi, $query_absensi);
$summary = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
while($row = mysqli_fetch_assoc($result_absensi)) {
    if (isset($summary[$row['status']])) $summary[$row['status']]++;
}
$query_penilaian = "SELECT * FROM penilaian_akhlak WHERE id_siswa = $id_siswa AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_laporan_safe' ORDER BY tanggal ASC";
$result_penilaian = mysqli_query($koneksi, $query_penilaian);
$rekap_penilaian = [];
while($row = mysqli_fetch_assoc($result_penilaian)) {
    $rekap_penilaian[] = $row;
}
$uploads_dir = "uploads/";
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}
$logo_path = null;
if (isset($_FILES['logo_kop']) && $_FILES['logo_kop']['error'] == UPLOAD_ERR_OK) {
    $logo_path = $uploads_dir . uniqid() . '-' . basename($_FILES["logo_kop"]["name"]);
    move_uploaded_file($_FILES["logo_kop"]["tmp_name"], $logo_path);
}
$ttd_path = null;
if (isset($_FILES['tanda_tangan_digital']) && $_FILES['tanda_tangan_digital']['error'] == UPLOAD_ERR_OK) {
    $ttd_path = $uploads_dir . uniqid() . '-' . basename($_FILES["tanda_tangan_digital"]["name"]);
    move_uploaded_file($_FILES["tanda_tangan_digital"]["tmp_name"], $ttd_path);
}
$chart_config = [
    'type' => 'doughnut',
    'data' => [ 'labels' => ['Hadir', 'Izin', 'Sakit', 'Alfa'], 'datasets' => [['data' => array_values($summary), 'backgroundColor' => ['#22c55e', '#facc15', '#3b82f6', '#ef4444']]]],
    'options' => [ 'plugins' => [ 'legend' => ['position' => 'right'], 'title' => ['display' => true, 'text' => 'Grafik Kehadiran']]]
];
$chart_url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chart_config)) . '&width=400&height=250';


// --- (Semua kode inisialisasi PDF dan pembuatan konten sama seperti sebelumnya) ---
class PDF extends FPDF {
    public $logo; public $kop; public $footer_text;
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw']; if($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize; $s = str_replace("\r", '', $txt);
        $nb = strlen($s); if($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i < $nb) {
            $c = $s[$i]; if($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if($c == ' ') $sep = $i; $l += $cw[$c];
            if($l > $wmax) {
                if($sep == -1) { if($i == $j) $i++; } else $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        } return $nl;
    }
    function CheckPageBreak($h) { if($this->GetY() + $h > $this->PageBreakTrigger) $this->AddPage($this->CurOrientation); }
    function Header() {
        if ($this->PageNo() == 1) {
            $this->SetFont('Arial', 'B', 12);
            if ($this->logo && file_exists($this->logo)) { $this->Image($this->logo, 10, 8, 25); $this->SetX(40); $this->MultiCell(160, 6, $this->kop, 0, 'C');
            } else { $this->SetX(10); $this->MultiCell(190, 6, $this->kop, 0, 'C'); }
            $this->Ln(5); $this->SetLineWidth(1); $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->SetLineWidth(0.2); $this->Line(10, $this->GetY()+1, 200, $this->GetY()+1); $this->Ln(10);
        } else { $this->Ln(20); }
    }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, $this->footer_text, 0, 0, 'C'); }
}
$pdf = new PDF('P', 'mm', 'A4');
$pdf->logo = $logo_path; $pdf->kop = $teks_kop; $pdf->footer_text = "Laporan Kehadiran Siswa SDIT Akhyar International Islamic School - Kelas 3A Al A'raf";
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12); $pdf->Cell(0, 7, 'LAPORAN PERKEMBANGAN SISWA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11); $pdf->Cell(0, 6, 'Periode: ' . date('F Y', strtotime($bulan_laporan)), 0, 1, 'C'); $pdf->Ln(8);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'Nama Siswa', 0, 0); $pdf->Cell(5, 6, ':', 0, 0); $pdf->Cell(0, 6, $data_siswa['nama_lengkap'], 0, 1);
$pdf->Cell(30, 6, 'NIS / NISN', 0, 0); $pdf->Cell(5, 6, ':', 0, 0); $pdf->Cell(0, 6, $data_siswa['nis'] . ' / ' . $data_siswa['nisn'], 0, 1);
$pdf->Cell(30, 6, 'Kelas', 0, 0); $pdf->Cell(5, 6, ':', 0, 0); $pdf->Cell(0, 6, $data_siswa['kelas'], 0, 1);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, 'A. Ringkasan Kehadiran', 0, 1);
$y_pos_start = $pdf->GetY();
$pdf->SetXY(15, $y_pos_start);
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(40, 8, 'Hadir', 1, 0, 'L', true); $pdf->Cell(40, 8, $summary['Hadir'] . ' hari', 1, 1, 'C');
$pdf->SetX(15);
$pdf->Cell(40, 8, 'Izin', 1, 0, 'L', true); $pdf->Cell(40, 8, $summary['Izin'] . ' hari', 1, 1, 'C');
$pdf->SetX(15);
$pdf->Cell(40, 8, 'Sakit', 1, 0, 'L', true); $pdf->Cell(40, 8, $summary['Sakit'] . ' hari', 1, 1, 'C');
$pdf->SetX(15);
$pdf->Cell(40, 8, 'Alfa', 1, 0, 'L', true); $pdf->Cell(40, 8, $summary['Alfa'] . ' hari', 1, 1, 'C');
$pdf->Image($chart_url, 105, $y_pos_start, 80, 0, 'PNG');
$pdf->SetY($y_pos_start + 55);
if (!empty($catatan_kehadiran)) {
    $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(0, 7, 'Catatan Kehadiran:', 0, 1);
    $pdf->SetFont('Arial', '', 10); $pdf->MultiCell(190, 6, htmlspecialchars($catatan_kehadiran), 1, 'L'); $pdf->Ln(5);
}
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, 'B. Catatan Perkembangan Akhlak dan Adab', 0, 1);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(25, 7, 'Tanggal', 1, 0, 'C', true); $pdf->Cell(50, 7, 'Aspek yang Dinilai', 1, 0, 'C', true); $pdf->Cell(35, 7, 'Penilaian', 1, 0, 'C', true); $pdf->Cell(80, 7, 'Catatan Guru', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 9);
if (count($rekap_penilaian) > 0) {
    foreach ($rekap_penilaian as $nilai) {
        $line_height = 6;
        $nb_tanggal = $pdf->NbLines(25, date('d-m-Y', strtotime($nilai['tanggal'])));
        $nb_aspek = $pdf->NbLines(50, htmlspecialchars($nilai['aspek_penilaian']));
        $nb_penilaian = $pdf->NbLines(35, htmlspecialchars($nilai['nilai']));
        $nb_catatan = $pdf->NbLines(80, htmlspecialchars($nilai['catatan']));
        $nb = max($nb_tanggal, $nb_aspek, $nb_penilaian, $nb_catatan);
        $h = $line_height * $nb;
        $pdf->CheckPageBreak($h);
        $x = $pdf->GetX(); $y = $pdf->GetY();
        $pdf->Rect($x, $y, 25, $h); $pdf->Rect($x + 25, $y, 50, $h); $pdf->Rect($x + 25 + 50, $y, 35, $h); $pdf->Rect($x + 25 + 50 + 35, $y, 80, $h);
        $pdf->MultiCell(25, $line_height, date('d-m-Y', strtotime($nilai['tanggal'])), 0, 'C');
        $pdf->SetXY($x + 25, $y); $pdf->MultiCell(50, $line_height, htmlspecialchars($nilai['aspek_penilaian']), 0, 'L');
        $pdf->SetXY($x + 25 + 50, $y); $pdf->MultiCell(35, $line_height, htmlspecialchars($nilai['nilai']), 0, 'C');
        $pdf->SetXY($x + 25 + 50 + 35, $y); $pdf->MultiCell(80, $line_height, htmlspecialchars($nilai['catatan']), 0, 'L');
        $pdf->SetY($y + $h);
    }
} else { $pdf->Cell(190, 10, 'Tidak ada catatan penilaian pada periode ini.', 1, 1, 'C'); }
$pdf->Ln(10);
$current_y = $pdf->GetY();
if ($current_y > 220 && count($rekap_penilaian) > 0) { $pdf->AddPage(); }
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(120); $pdf->Cell(0, 6, 'Jakarta, ' . date('d F Y'), 0, 1, 'C');
$pdf->Cell(120); $pdf->Cell(0, 6, 'Wali Kelas 3A', 0, 1, 'C');
$y_pos_before_ttd = $pdf->GetY();
if ($ttd_path && file_exists($ttd_path)) { $pdf->Image($ttd_path, 145, $y_pos_before_ttd, 40, 20, 'PNG'); }
$pdf->SetY($y_pos_before_ttd + 25);
$pdf->Cell(120); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(0, 6, $nama_walikelas, 0, 1, 'C');
$pdf->SetFont('Arial', '', 10); $pdf->Cell(120); $pdf->Cell(0, 0, '____________________', 0, 1, 'C');


// Hapus File Sementara
if ($logo_path && file_exists($logo_path)) unlink($logo_path);
if ($ttd_path && file_exists($ttd_path)) unlink($ttd_path);

// --- PERUBAHAN: Logika untuk Pratinjau atau Download ---
$nama_file_laporan = 'Laporan Perkembangan - ' . $data_siswa['nama_lengkap'] . ' - ' . $bulan_laporan . '.pdf';

if ($action == 'preview') {
    // Tampilkan di browser
    $pdf->Output('I', $nama_file_laporan);
} else {
    // Paksa download
    $pdf->Output('D', $nama_file_laporan);
}
exit;
?>
