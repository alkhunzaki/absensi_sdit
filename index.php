<?php
include 'config.php';
check_login();

// --- LOGIKA UNTUK DATA DASHBOARD ---
// 1. Menghitung Jumlah Siswa
$result_jumlah_siswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM siswa");
$data_jumlah_siswa = mysqli_fetch_assoc($result_jumlah_siswa);
$jumlah_siswa = $data_jumlah_siswa['total'];

// 2. Data untuk Diagram Lingkaran (Presensi Hari Ini)
$tanggal_hari_ini = date('Y-m-d');
$query_pie_chart = "SELECT status, COUNT(*) as jumlah FROM absensi WHERE tanggal = '$tanggal_hari_ini' GROUP BY status";
$result_pie_chart = mysqli_query($koneksi, $query_pie_chart);

$data_pie = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
while ($row = mysqli_fetch_assoc($result_pie_chart)) {
    $data_pie[$row['status']] = $row['jumlah'];
}
$data_pie_json = json_encode(array_values($data_pie));
$label_pie_json = json_encode(array_keys($data_pie));

// 3. Data untuk Grafik Batang (Kehadiran per Bulan dalam 1 Tahun Terakhir)
// PERBAIKAN: Query disesuaikan untuk sql_mode=only_full_group_by
$query_bar_chart = "
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
$result_bar_chart = mysqli_query($koneksi, $query_bar_chart);

$label_bulan = [];
$data_hadir = [];
$data_izin = [];
$data_sakit = [];
$data_alfa = [];

if ($result_bar_chart) {
    while ($row = mysqli_fetch_assoc($result_bar_chart)) {
        $label_bulan[] = $row['bulan'];
        $data_hadir[] = $row['hadir'];
        $data_izin[] = $row['izin'];
        $data_sakit[] = $row['sakit'];
        $data_alfa[] = $row['alfa'];
    }
}

$label_bulan_json = json_encode($label_bulan);
$data_hadir_json = json_encode($data_hadir);
$data_izin_json = json_encode($data_izin);
$data_sakit_json = json_encode($data_sakit);
$data_alfa_json = json_encode($data_alfa);

// --- TAMPILAN HTML ---
template_header('Dashboard');
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Kehadiran</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
        <i class="fas fa-users text-4xl text-blue-500"></i>
        <div class="ml-4">
            <h2 class="text-gray-600 text-lg">Jumlah Siswa</h2>
            <p class="text-2xl font-bold"><?= $jumlah_siswa ?> Siswa</p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
        <i class="fas fa-user-check text-4xl text-green-500"></i>
        <div class="ml-4">
            <h2 class="text-gray-600 text-lg">Hadir Hari Ini</h2>
            <p class="text-2xl font-bold"><?= $data_pie['Hadir'] ?> Siswa</p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
        <i class="fas fa-user-times text-4xl text-red-500"></i>
        <div class="ml-4">
            <h2 class="text-gray-600 text-lg">Tidak Hadir Hari Ini</h2>
            <p class="text-2xl font-bold"><?= $data_pie['Izin'] + $data_pie['Sakit'] + $data_pie['Alfa'] ?> Siswa</p>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-gray-700 mb-4">Rekap Presensi Hari Ini</h3>
        <canvas id="pieChart"></canvas>
    </div>
    <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-gray-700 mb-4">Grafik Kehadiran per Bulan</h3>
        <canvas id="barChart"></canvas>
    </div>
</div>
<script>
const dataPie = <?= $data_pie_json ?>;
const labelsPie = <?= $label_pie_json ?>;
const labelsBar = <?= $label_bulan_json ?>;
const dataHadir = <?= $data_hadir_json ?>;
const dataIzin = <?= $data_izin_json ?>;
const dataSakit = <?= $data_sakit_json ?>;
const dataAlfa = <?= $data_alfa_json ?>;

new Chart(document.getElementById('pieChart').getContext('2d'), {
    type: 'pie', data: { labels: labelsPie, datasets: [{ label: 'Jumlah Siswa', data: dataPie,
    backgroundColor: ['rgba(75, 192, 192, 0.7)','rgba(255, 206, 86, 0.7)','rgba(54, 162, 235, 0.7)','rgba(255, 99, 132, 0.7)'],
    borderColor: ['rgba(75, 192, 192, 1)','rgba(255, 206, 86, 1)','rgba(54, 162, 235, 1)','rgba(255, 99, 132, 1)'], borderWidth: 1 }] },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
});
new Chart(document.getElementById('barChart').getContext('2d'), {
    type: 'bar', data: { labels: labelsBar, datasets: [
    { label: 'Hadir', data: dataHadir, backgroundColor: 'rgba(75, 192, 192, 0.7)' },
    { label: 'Izin', data: dataIzin, backgroundColor: 'rgba(255, 206, 86, 0.7)' },
    { label: 'Sakit', data: dataSakit, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
    { label: 'Alfa', data: dataAlfa, backgroundColor: 'rgba(255, 99, 132, 0.7)' } ] },
    options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }, plugins: { legend: { position: 'top' } } }
});
</script>
<?php
template_footer();
?>
