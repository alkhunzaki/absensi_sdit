<?php
include 'config.php';

$nis = isset($_GET['nis']) ? mysqli_real_escape_string($koneksi, $_GET['nis']) : '';

if (!$nis) {
    header('Location: portal_siswa.php');
    exit;
}

// 1. Ambil Data Siswa
$query_siswa = "SELECT * FROM siswa WHERE nis = '$nis'";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

if (!$siswa) {
    echo "<script>
        alert('Data siswa dengan NIS $nis tidak ditemukan.');
        window.location.href = 'portal_siswa.php';
    </script>";
    exit;
}

$id_siswa = $siswa['id_siswa'];

// 2. Data Statistik Absensi (Pie Chart)
$query_absensi = "SELECT status, COUNT(*) as jumlah FROM absensi WHERE id_siswa = $id_siswa GROUP BY status";
$result_absensi = mysqli_query($koneksi, $query_absensi);
$data_pie = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
while ($row = mysqli_fetch_assoc($result_absensi)) {
    $data_pie[$row['status']] = $row['jumlah'];
}
$data_pie_json = json_encode(array_values($data_pie));
$label_pie_json = json_encode(array_keys($data_pie));

// 3. Riwayat Penilaian Akhlak Terbaru
$query_akhlak = "SELECT * FROM penilaian_akhlak WHERE id_siswa = $id_siswa ORDER BY tanggal DESC LIMIT 5";
$result_akhlak = mysqli_query($koneksi, $query_akhlak);

template_portal_header('Laporan ' . $siswa['nama_lengkap']);
?>

<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="portal_siswa.php" class="text-sm text-gray-500 hover:text-blue-600">Portal</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        <span class="ml-1 text-sm font-medium text-gray-700 md:ml-2">Detail Laporan</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900"><?= $siswa['nama_lengkap'] ?></h1>
        <p class="text-gray-500">Kelas <?= $siswa['kelas'] ?> | NIS: <?= $siswa['nis'] ?></p>
    </div>
    <div class="flex gap-3">
        <a href="download_laporan_individu.php?id=<?= $id_siswa ?>" target="_blank" 
           class="inline-flex items-center px-4 py-2 border border-blue-600 rounded-lg text-blue-600 font-semibold hover:bg-blue-50 transition-colors">
            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Unduh PDF
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Kolom Kiri: Profil & Statistik -->
    <div class="space-y-8">
        <!-- Card Profil -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Informasi Siswa</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">NISN</span>
                    <span class="font-medium"><?= $siswa['nisn'] ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jenis Kelamin</span>
                    <span class="font-medium"><?= $siswa['jenis_kelamin'] ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tahun Ajaran</span>
                    <span class="font-medium">2023/2024</span>
                </div>
            </div>
        </div>

        <!-- Card Statistik Absensi -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Statistik Kehadiran</h2>
            <div class="w-full max-w-[250px] mx-auto">
                <canvas id="absensiChart"></canvas>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4 text-center">
                <div class="bg-blue-50 p-2 rounded-lg">
                    <span class="block text-blue-800 font-bold text-xl"><?= $data_pie['Hadir'] ?></span>
                    <span class="text-xs text-blue-600 uppercase">Hadir</span>
                </div>
                <div class="bg-yellow-50 p-2 rounded-lg">
                    <span class="block text-yellow-800 font-bold text-xl"><?= $data_pie['Izin'] ?></span>
                    <span class="text-xs text-yellow-600 uppercase">Izin</span>
                </div>
                <div class="bg-blue-50 p-2 rounded-lg">
                    <span class="block text-blue-600 font-bold text-xl"><?= $data_pie['Sakit'] ?></span>
                    <span class="text-xs text-blue-500 uppercase">Sakit</span>
                </div>
                <div class="bg-red-50 p-2 rounded-lg">
                    <span class="block text-red-800 font-bold text-xl"><?= $data_pie['Alfa'] ?></span>
                    <span class="text-xs text-red-600 uppercase">Alfa</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Penilaian Akhlak -->
    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 min-h-full">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Capaian Akhlak & Karakter</h2>
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold uppercase tracking-wider">Terbaru</span>
            </div>

            <?php if (mysqli_num_rows($result_akhlak) > 0): ?>
                <div class="space-y-6">
                    <?php while ($row = mysqli_fetch_assoc($result_akhlak)): ?>
                        <div class="relative pl-8 border-l-2 border-blue-100 pb-2">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-blue-500 border-4 border-white"></div>
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-1">
                                <h3 class="font-bold text-gray-800 text-lg"><?= $row['aspek_penilaian'] ?></h3>
                                <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> <?= $row['tanggal'] ?></span>
                            </div>
                            <div class="flex items-center gap-4 mb-2">
                                <div class="flex text-yellow-400">
                                    <?php 
                                    $n = 0;
                                    if($row['nilai'] == 'Sangat Baik') $n = 5;
                                    elseif($row['nilai'] == 'Baik') $n = 4;
                                    elseif($row['nilai'] == 'Cukup') $n = 3;
                                    else $n = 2;
                                    for($i=1; $i<=5; $i++) echo "<i class='fas fa-star ".($i<=$n ? "" : "text-gray-200")."'></i>";
                                    ?>
                                </div>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded capitalize"><?= $row['nilai'] ?></span>
                            </div>
                            <p class="text-gray-600 text-sm italic bg-gray-50 p-3 rounded-lg border-l-4 border-gray-200">
                                "<?= $row['catatan'] ?: 'Tidak ada catatan khusus.' ?>"
                            </p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500 font-medium">Belum ada penilaian akhlak untuk periode ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const dataPie = <?= $data_pie_json ?>;
const labelsPie = <?= $label_pie_json ?>;

new Chart(document.getElementById('absensiChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: labelsPie,
        datasets: [{
            data: dataPie,
            backgroundColor: ['#1d4ed8','#facc15','#60a5fa','#ef4444'],
            hoverOffset: 10,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        cutout: '70%'
    }
});
</script>

<?php template_portal_footer(); ?>
