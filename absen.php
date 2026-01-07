<?php
// absen.php
include 'config.php';
check_login();

$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$status_pesan = isset($_GET['status']) ? $_GET['status'] : '';
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Logika untuk menyimpan data absensi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['absensi'])) {
    check_csrf($_POST['csrf_token'] ?? '');
    
    $absensi_data = $_POST['absensi'];
    $tanggal_absen = $_POST['tanggal_absen'];

    // Siapkan statement di luar loop untuk efisiensi
    $stmt_cek = mysqli_prepare($koneksi, "SELECT id_absensi FROM absensi WHERE id_siswa = ? AND tanggal = ?");
    $stmt_update = mysqli_prepare($koneksi, "UPDATE absensi SET status = ?, catatan = ? WHERE id_absensi = ?");
    $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO absensi (id_siswa, tanggal, status, catatan) VALUES (?, ?, ?, ?)");

    foreach ($absensi_data as $id_siswa => $data) {
        $status = $data['status'];
        $catatan = $data['catatan'];
        $id_siswa_safe = (int)$id_siswa;

        mysqli_stmt_bind_param($stmt_cek, "is", $id_siswa_safe, $tanggal_absen);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if ($row_cek = mysqli_fetch_assoc($result_cek)) {
            $id_absensi = $row_cek['id_absensi'];
            mysqli_stmt_bind_param($stmt_update, "ssi", $status, $catatan, $id_absensi);
            mysqli_stmt_execute($stmt_update);
        } else {
            mysqli_stmt_bind_param($stmt_insert, "isss", $id_siswa_safe, $tanggal_absen, $status, $catatan);
            mysqli_stmt_execute($stmt_insert);
        }
    }
    
    $pesan_sukses = "Absensi untuk tanggal " . date("d-m-Y", strtotime($tanggal_absen)) . " berhasil disimpan!";
    $tanggal_selanjutnya = date('Y-m-d', strtotime($tanggal_absen . ' +1 day'));

    // Redirect dengan status dan pesan untuk SweetAlert
    header("Location: absen.php?tanggal=" . $tanggal_selanjutnya . "&status=sukses&pesan=" . urlencode($pesan_sukses));
    exit;
}

// Mengambil data siswa
$result_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama_lengkap ASC");
$absensi_hari_ini = [];
$query_absensi_hari_ini = "SELECT * FROM absensi WHERE tanggal = '$tanggal_filter'";
$result_absensi_hari_ini = mysqli_query($koneksi, $query_absensi_hari_ini);
while($row = mysqli_fetch_assoc($result_absensi_hari_ini)) {
    $absensi_hari_ini[$row['id_siswa']] = $row;
}

template_header('Absen Kehadiran');
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Absen Kehadiran Siswa</h1>

<!-- HAPUS BLOK NOTIFIKASI LAMA -->

<div class="bg-white p-6 rounded-lg shadow-md">
    <!-- ... (kode form pilih tanggal sama seperti sebelumnya) ... -->
    <form action="absen.php" method="get" class="flex flex-wrap items-end gap-4 mb-4">
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700">Pilih Tanggal Absen</label>
            <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>" class="mt-1 px-3 py-2 border border-gray-300 rounded-md">
        </div>
        <div>
            <button type="submit" class="py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i>Tampilkan
            </button>
        </div>
    </form>
    
    <hr class="my-4">

    <!-- ... (kode form absensi sama seperti sebelumnya) ... -->
    <form action="absen.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
        <input type="hidden" name="tanggal_absen" value="<?= e($tanggal_filter) ?>">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Absensi untuk: <?= date("d F Y", strtotime($tanggal_filter)) ?></h2>
            <button type="button" id="hadirSemuaBtn" class="py-2 px-4 bg-green-500 text-white rounded-md hover:bg-green-600">
                <i class="fas fa-check-double mr-2"></i>Hadir Semua
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kehadiran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $no = 1; while($siswa = mysqli_fetch_assoc($result_siswa)): 
                        $id_siswa = $siswa['id_siswa'];
                        $status_tersimpan = $absensi_hari_ini[$id_siswa]['status'] ?? 'Hadir';
                        $catatan_tersimpan = $absensi_hari_ini[$id_siswa]['catatan'] ?? '';
                    ?>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= $no++ ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= e($siswa['nama_lengkap']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="flex flex-wrap gap-x-4 gap-y-2">
                                <?php foreach (['Hadir', 'Izin', 'Sakit', 'Alfa'] as $status): ?>
                                <label class="flex items-center">
                                    <input type="radio" name="absensi[<?= $id_siswa ?>][status]" value="<?= $status ?>" class="form-radio h-4 w-4 text-blue-600 radio-kehadiran" <?= ($status_tersimpan == $status) ? 'checked' : '' ?>>
                                    <span class="ml-2"><?= $status ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <input type="text" name="absensi[<?= $id_siswa ?>][catatan]" value="<?= e($catatan_tersimpan) ?>" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="Catatan...">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            <button type="submit" class="py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Simpan Absensi
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('hadirSemuaBtn').addEventListener('click', function() {
    const semuaRadio = document.querySelectorAll('.radio-kehadiran');
    semuaRadio.forEach(radio => {
        if (radio.value === 'Hadir') {
            radio.checked = true;
        }
    });
});

// TAMBAHKAN BLOK SCRIPT INI UNTUK MENAMPILKAN SWEETALERT
<?php if (!empty($pesan) && !empty($status_pesan)): ?>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: '<?= ($status_pesan == 'sukses') ? 'Berhasil!' : 'Gagal!'; ?>',
        text: '<?= addslashes(htmlspecialchars_decode($pesan)); ?>',
        icon: '<?= ($status_pesan == 'sukses') ? 'success' : 'error'; ?>',
        confirmButtonText: 'OK'
    });
});
<?php endif; ?>
</script>

<?php
template_footer();
?>
