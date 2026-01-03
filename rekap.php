<?php
// rekap.php
include 'config.php';
check_login();

// Filter
$filter_tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'bulanan';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_pekan = isset($_GET['pekan']) ? $_GET['pekan'] : date('Y-\WW');
$filter_siswa = isset($_GET['id_siswa']) ? (int)$_GET['id_siswa'] : '';

// Query dasar
$query_rekap = "
    SELECT a.tanggal, a.status, a.catatan, s.nama_lengkap, s.nis 
    FROM absensi a 
    JOIN siswa s ON a.id_siswa = s.id_siswa
";

$where_clauses = [];
if ($filter_tipe == 'bulanan') {
    $where_clauses[] = "DATE_FORMAT(a.tanggal, '%Y-%m') = '$filter_bulan'";
} else { // pekanan
    $tahun = substr($filter_pekan, 0, 4);
    $pekan = substr($filter_pekan, 6, 2);
    $where_clauses[] = "YEARWEEK(a.tanggal, 1) = '$tahun$pekan'";
}

if ($filter_siswa) {
    $where_clauses[] = "a.id_siswa = $filter_siswa";
}

if (!empty($where_clauses)) {
    $query_rekap .= " WHERE " . implode(' AND ', $where_clauses);
}
$query_rekap .= " ORDER BY a.tanggal DESC, s.nama_lengkap ASC";
$result_rekap = mysqli_query($koneksi, $query_rekap);

$result_siswa_filter = mysqli_query($koneksi, "SELECT id_siswa, nama_lengkap FROM siswa ORDER BY nama_lengkap ASC");

template_header('Rekapitulasi Absensi');
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Rekapitulasi Absensi</h1>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <form action="rekap.php" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
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
                <?php mysqli_data_seek($result_siswa_filter, 0); while($siswa = mysqli_fetch_assoc($result_siswa_filter)): ?>
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
     <!-- Tombol Download -->
    <div class="mt-4 pt-4 border-t flex flex-wrap gap-3">
        <a id="downloadExcel" href="#" class="inline-flex items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
            <i class="fas fa-file-excel mr-2"></i>Download Excel
        </a>
        <a id="downloadPdf" href="#" class="inline-flex items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
            <i class="fas fa-file-pdf mr-2"></i>Download PDF
        </a>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (mysqli_num_rows($result_rekap) > 0): ?>
                    <?php mysqli_data_seek($result_rekap, 0); while($rekap = mysqli_fetch_assoc($result_rekap)): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date("d M Y", strtotime($rekap['tanggal'])) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($rekap['nis']) ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($rekap['nama_lengkap']) ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?php 
                                $status = $rekap['status'];
                                $color = 'gray';
                                if ($status == 'Hadir') $color = 'green';
                                if ($status == 'Izin') $color = 'yellow';
                                if ($status == 'Sakit') $color = 'blue';
                                if ($status == 'Alfa') $color = 'red';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?= $color ?>-100 text-<?= $color ?>-800">
                                <?= $status ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($rekap['catatan']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data rekapitulasi untuk filter yang dipilih.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateDownloadLinks() {
    const tipe = document.getElementById('tipe').value;
    const bulan = document.getElementById('bulan').value;
    const pekan = document.getElementById('pekan').value;
    const id_siswa = document.getElementById('id_siswa').value;
    
    const params = new URLSearchParams({
        tipe: tipe,
        bulan: bulan,
        pekan: pekan,
        id_siswa: id_siswa
    }).toString();

    document.getElementById('downloadExcel').href = `download_excel.php?${params}`;
    document.getElementById('downloadPdf').href = `download_pdf.php?${params}`;
}

document.addEventListener('DOMContentLoaded', function() {
    updateDownloadLinks(); // Panggil saat halaman dimuat

    // Event listener untuk semua elemen filter
    document.getElementById('tipe').addEventListener('change', function() {
        if (this.value === 'bulanan') {
            document.getElementById('input-bulan').classList.remove('hidden');
            document.getElementById('input-pekan').classList.add('hidden');
        } else {
            document.getElementById('input-bulan').classList.add('hidden');
            document.getElementById('input-pekan').classList.remove('hidden');
        }
        updateDownloadLinks();
    });
    document.getElementById('bulan').addEventListener('change', updateDownloadLinks);
    document.getElementById('pekan').addEventListener('change', updateDownloadLinks);
    document.getElementById('id_siswa').addEventListener('change', updateDownloadLinks);
});
</script>

<?php
template_footer();
?>
