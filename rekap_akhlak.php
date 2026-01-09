<?php
// rekap_akhlak.php
include 'config.php';
check_login();

// Filter
$filter_tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'bulanan';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_pekan = isset($_GET['pekan']) ? $_GET['pekan'] : date('Y-\WW');
$filter_siswa = isset($_GET['id_siswa']) ? (int)$_GET['id_siswa'] : '';

// Query dasar untuk mengambil data penilaian
$query_rekap = "
    SELECT pa.tanggal, pa.aspek_penilaian, pa.nilai, pa.catatan, s.nama_lengkap 
    FROM penilaian_akhlak pa
    JOIN siswa s ON pa.id_siswa = s.id_siswa
";

$params = [];
$types = "";

if ($filter_tipe == 'bulanan') {
    $where_clauses[] = "DATE_FORMAT(pa.tanggal, '%Y-%m') = ?";
    $params[] = $filter_bulan;
    $types .= "s";
} else { // pekanan
    $tahun = substr($filter_pekan, 0, 4);
    $pekan = substr($filter_pekan, 6, 2);
    $where_clauses[] = "YEARWEEK(pa.tanggal, 1) = ?";
    $params[] = $tahun . $pekan;
    $types .= "s";
}

if ($filter_siswa) {
    $where_clauses[] = "pa.id_siswa = ?";
    $params[] = $filter_siswa;
    $types .= "i";
}

if (!empty($where_clauses)) {
    $query_rekap .= " WHERE " . implode(' AND ', $where_clauses);
}
$query_rekap .= " ORDER BY pa.tanggal DESC, s.nama_lengkap ASC";

$stmt_rekap = mysqli_prepare($koneksi, $query_rekap);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_rekap, $types, ...$params);
}
mysqli_stmt_execute($stmt_rekap);
$result_rekap = mysqli_stmt_get_result($stmt_rekap);

// Ambil daftar siswa untuk dropdown filter
$result_siswa_filter = mysqli_query($koneksi, "SELECT id_siswa, nama_lengkap FROM siswa ORDER BY nama_lengkap ASC");

template_header('Rekapitulasi Penilaian Akhlak');
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Rekapitulasi Penilaian Akhlak</h1>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form action="rekap_akhlak.php" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label for="tipe" class="block text-sm font-medium text-gray-700">Tipe Filter</label>
            <select id="tipe" name="tipe" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm">
                <option value="bulanan" <?= $filter_tipe == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                <option value="pekanan" <?= $filter_tipe == 'pekanan' ? 'selected' : '' ?>>Pekanan</option>
            </select>
        </div>
        <div id="input-bulan" class="<?= $filter_tipe == 'pekanan' ? 'hidden' : '' ?>">
            <label for="bulan" class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
            <input type="month" id="bulan" name="bulan" value="<?= htmlspecialchars($filter_bulan) ?>" class="mt-1 block w-full py-2 px-3 border rounded-md">
        </div>
        <div id="input-pekan" class="<?= $filter_tipe == 'bulanan' ? 'hidden' : '' ?>">
            <label for="pekan" class="block text-sm font-medium text-gray-700">Pilih Pekan</label>
            <input type="week" id="pekan" name="pekan" value="<?= htmlspecialchars($filter_pekan) ?>" class="mt-1 block w-full py-2 px-3 border rounded-md">
        </div>
        <div>
            <label for="id_siswa" class="block text-sm font-medium text-gray-700">Pilih Siswa</label>
            <select id="id_siswa" name="id_siswa" class="mt-1 block w-full py-2 pl-3 pr-10 border bg-white rounded-md">
                <option value="">Semua Siswa</option>
                <?php while($siswa = mysqli_fetch_assoc($result_siswa_filter)): ?>
                <option value="<?= $siswa['id_siswa'] ?>" <?= ($filter_siswa == $siswa['id_siswa']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($siswa['nama_lengkap']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full py-2 px-4 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aspek yang Dinilai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penilaian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan Guru</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($result_rekap && mysqli_num_rows($result_rekap) > 0): ?>
                    <?php while($rekap = mysqli_fetch_assoc($result_rekap)): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date("d M Y", strtotime($rekap['tanggal'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= e($rekap['nama_lengkap']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= e($rekap['aspek_penilaian']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= e($rekap['nilai']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= e($rekap['catatan']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data penilaian untuk filter yang dipilih.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('tipe').addEventListener('change', function() {
    if (this.value === 'bulanan') {
        document.getElementById('input-bulan').classList.remove('hidden');
        document.getElementById('input-pekan').classList.add('hidden');
    } else {
        document.getElementById('input-bulan').classList.add('hidden');
        document.getElementById('input-pekan').classList.remove('hidden');
    }
});
</script>

<?php
template_footer();
?>
