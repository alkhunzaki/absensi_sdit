<?php
include 'config.php';
header('Content-Type: application/json');

// Jika belum login, jangan kasih data
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$cache_time = 300; // 5 menit cache
$now = time();

// Cek Cache di Session
if (isset($_SESSION['dashboard_cache']) && ($now - $_SESSION['dashboard_cache_time'] < $cache_time)) {
    echo $_SESSION['dashboard_cache'];
    exit;
}

// --- LOGIKA DATA ---
$data = [];

// 1. Menghitung Jumlah Siswa
$result_jumlah_siswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM siswa");
$data_jumlah_siswa = mysqli_fetch_assoc($result_jumlah_siswa);
$data['jumlah_siswa'] = (int)$data_jumlah_siswa['total'];

// 2. Data Pie Chart (Hari Ini)
$tanggal_hari_ini = date('Y-m-d');
$query_pie = "SELECT status, COUNT(*) as jumlah FROM absensi WHERE tanggal = '$tanggal_hari_ini' GROUP BY status";
$result_pie = mysqli_query($koneksi, $query_pie);
$pie_stats = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
while ($row = mysqli_fetch_assoc($result_pie)) {
    $pie_stats[$row['status']] = (int)$row['jumlah'];
}
$data['pie'] = [
    'labels' => array_keys($pie_stats),
    'values' => array_values($pie_stats)
];
$data['hadir_hari_ini'] = $pie_stats['Hadir'];
$data['tidak_hadir_hari_ini'] = $pie_stats['Izin'] + $pie_stats['Sakit'] + $pie_stats['Alfa'];

// 3. Data Bar Chart (12 Bulan Terakhir)
$query_bar = "
    SELECT 
        DATE_FORMAT(tanggal, '%M %Y') as bulan, 
        COUNT(CASE WHEN status = 'Hadir' THEN 1 END) as hadir,
        COUNT(CASE WHEN status = 'Izin' THEN 1 END) as izin,
        COUNT(CASE WHEN status = 'Sakit' THEN 1 END) as sakit,
        COUNT(CASE WHEN status = 'Alfa' THEN 1 END) as alfa
    FROM absensi 
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(tanggal, '%M %Y'), YEAR(tanggal), MONTH(tanggal)
    ORDER BY YEAR(tanggal), MONTH(tanggal)
";
$result_bar = mysqli_query($koneksi, $query_bar);
$bar_data = ['labels' => [], 'hadir' => [], 'izin' => [], 'sakit' => [], 'alfa' => []];
while ($row = mysqli_fetch_assoc($result_bar)) {
    $bar_data['labels'][] = $row['bulan'];
    $bar_data['hadir'][] = (int)$row['hadir'];
    $bar_data['izin'][] = (int)$row['izin'];
    $bar_data['sakit'][] = (int)$row['sakit'];
    $bar_data['alfa'][] = (int)$row['alfa'];
}
$data['bar'] = $bar_data;

$json_result = json_encode($data);

// Simpan Cache
$_SESSION['dashboard_cache'] = $json_result;
$_SESSION['dashboard_cache_time'] = $now;

echo $json_result;
?>
