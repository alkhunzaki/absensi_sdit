<?php
// download_excel.php (Versi Sederhana - Diperbaiki & Lebih Aman)

// Langkah 0: Mulai output buffering dan aktifkan pelaporan error
// Ini akan menangkap output yang tidak diinginkan dan menampilkan semua error PHP.
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Langkah 1: Sertakan file konfigurasi dan fungsi login check
include 'config.php';
check_login();

// Langkah 2: Ambil semua parameter filter dari URL
$filter_tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'bulanan';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_pekan = isset($_GET['pekan']) ? $_GET['pekan'] : date('Y-\WW');
$filter_siswa = isset($_GET['id_siswa']) ? (int)$_GET['id_siswa'] : '';

// Langkah 3: Buat nama file dinamis
$nama_file = "rekap-absensi-" . ($filter_tipe == 'bulanan' ? $filter_bulan : $filter_pekan) . ".xls";

// Langkah 4: Bangun query SQL dengan pengamanan dasar
$query_rekap = "
    SELECT a.tanggal, a.status, a.catatan, s.nama_lengkap, s.nis 
    FROM absensi a 
    JOIN siswa s ON a.id_siswa = s.id_siswa
";
$where_clauses = [];
if ($filter_tipe == 'bulanan') {
    $where_clauses[] = "DATE_FORMAT(a.tanggal, '%Y-%m') = '" . mysqli_real_escape_string($koneksi, $filter_bulan) . "'";
} else {
    $tahun = substr($filter_pekan, 0, 4);
    $pekan = substr($filter_pekan, 6, 2);
    $where_clauses[] = "YEARWEEK(a.tanggal, 1) = '" . mysqli_real_escape_string($koneksi, $tahun . $pekan) . "'";
}
if ($filter_siswa) {
    $where_clauses[] = "a.id_siswa = " . $filter_siswa;
}
if (!empty($where_clauses)) {
    $query_rekap .= " WHERE " . implode(' AND ', $where_clauses);
}
$query_rekap .= " ORDER BY a.tanggal DESC, s.nama_lengkap ASC";

// Langkah 5: Eksekusi query dengan penanganan error yang jelas
$result_rekap = mysqli_query($koneksi, $query_rekap);
if (!$result_rekap) {
    // Jika query gagal, hentikan proses dan tampilkan pesan error yang jelas.
    ob_end_clean(); // Hapus buffer sebelum menampilkan error.
    die("Terjadi kesalahan pada query database: " . mysqli_error($koneksi));
}

// Langkah 6: Atur header HTTP untuk download.
// ob_end_clean() membersihkan semua output yang mungkin sudah ada, memastikan header bisa dikirim.
ob_end_clean();
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$nama_file\"");
header("Pragma: no-cache");
header("Expires: 0");

// Langkah 7: Buat output dalam format tabel HTML yang akan menjadi isi file Excel
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h3>Rekapitulasi Kehadiran Siswa</h3>
    <p>Periode: <?= htmlspecialchars($filter_tipe == 'bulanan' ? date("F Y", strtotime($filter_bulan)) : 'Pekan ke-' . substr($filter_pekan, 6, 2) . ' Tahun ' . substr($filter_pekan, 0, 4)) ?></p>
    <br>
    <table>
        <thead>
            <tr>
                <th>TANGGAL</th>
                <th>NIS</th>
                <th>NAMA LENGKAP</th>
                <th>STATUS KEHADIRAN</th>
                <th>CATATAN</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result_rekap) > 0): ?>
                <?php while($rekap = mysqli_fetch_assoc($result_rekap)): ?>
                <tr>
                    <td><?= date("d-m-Y", strtotime($rekap['tanggal'])) ?></td>
                    <td>'<?= htmlspecialchars($rekap['nis']) ?></td> <!-- Petik satu di depan NIS mencegah Excel mengubahnya jadi angka aneh -->
                    <td><?= htmlspecialchars($rekap['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($rekap['status']) ?></td>
                    <td><?= htmlspecialchars($rekap['catatan']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Tidak ada data untuk filter yang dipilih.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
// Langkah 8: Hentikan eksekusi skrip untuk memastikan tidak ada output lain
exit;
?>
