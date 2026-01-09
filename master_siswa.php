<?php
/* ==================================================
File: master_siswa.php
Fungsi: Manajemen data siswa (tambah, lihat, hapus, impor, ekspor).
==================================================
*/
include 'config.php';
check_login();

$pesan_sukses = '';
$pesan_error = '';

// Menampilkan pesan dari proses impor
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses_impor') {
        $jumlah = isset($_GET['jumlah']) ? (int)$_GET['jumlah'] : 0;
        $pesan_sukses = "$jumlah data siswa berhasil diimpor!";
    } elseif ($_GET['status'] == 'gagal_impor') {
        $pesan_error = "Gagal mengimpor data. Pastikan format file CSV benar dan tidak ada data duplikat.";
    } elseif ($_GET['status'] == 'file_salah') {
        $pesan_error = "Tipe file salah. Harap unggah file CSV (.csv).";
    }
}

// Logika untuk menghapus data siswa
if (isset($_GET['hapus'])) {
    check_csrf($_GET['csrf_token'] ?? '');
    $id_siswa_hapus = (int)$_GET['hapus'];
    
    $stmt = mysqli_prepare($koneksi, "DELETE FROM siswa WHERE id_siswa = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_siswa_hapus);
    
    if (mysqli_stmt_execute($stmt)) {
        $pesan_sukses = "Data siswa berhasil dihapus!";
    } else {
        $pesan_error = "Gagal menghapus data siswa.";
    }
}

// Logika untuk memproses form input siswa manual
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_manual'])) {
    check_csrf($_POST['csrf_token'] ?? '');
    
    $nama_lengkap = $_POST['nama_lengkap'];
    $nis = $_POST['nis'];
    $nisn = $_POST['nisn'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $kelas = $_POST['kelas'];

    $stmt = mysqli_prepare($koneksi, "INSERT INTO siswa (nama_lengkap, nis, nisn, jenis_kelamin, kelas) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $nama_lengkap, $nis, $nisn, $jenis_kelamin, $kelas);
    
    if (mysqli_stmt_execute($stmt)) {
        $pesan_sukses = "Data siswa berhasil ditambahkan!";
    } else {
        $pesan_error = "Gagal menambahkan data.";
    }
}

$result_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama_lengkap ASC");

template_header('Data Master Siswa');
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Data Master Siswa</h1>

<!-- Bagian Impor & Ekspor -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-semibold mb-4">Impor & Ekspor Data Siswa</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Form Impor CSV -->
        <div>
            <h3 class="font-medium text-gray-800 mb-2">Impor dari CSV</h3>
            <form action="import_csv.php" method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="file_csv" class="block text-sm font-medium text-gray-700">Pilih File CSV</label>
                    <input type="file" name="file_csv" id="file_csv" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required accept=".csv">
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" name="impor" class="inline-flex items-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-file-upload mr-2"></i>Impor Data
                    </button>
                    <a href="download_format_csv.php" class="text-sm text-blue-600 hover:underline">
                        <i class="fas fa-file-download mr-1"></i>Unduh Format
                    </a>
                </div>
            </form>
        </div>
        <!-- Tombol Ekspor -->
        <div>
             <h3 class="font-medium text-gray-800 mb-2">Ekspor ke Excel</h3>
             <p class="text-sm text-gray-600 mb-4">Unduh semua data siswa yang ada di dalam database ke dalam satu file Excel.</p>
             <a href="export_siswa.php" class="inline-flex items-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-file-export mr-2"></i>Ekspor Semua Siswa
             </a>
        </div>
    </div>
</div>

<!-- Form Input Manual -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-semibold mb-4">Input Data Siswa Baru (Manual)</h2>
    <form action="master_siswa.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
                <input type="text" name="nis" id="nis" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label for="nisn" class="block text-sm font-medium text-gray-700">NISN</label>
                <input type="text" name="nisn" id="nisn" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md" required>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
             <div class="md:col-span-2">
                <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                <input type="text" name="kelas" id="kelas" value="3A" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" name="tambah_manual" class="inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Tambah Siswa</button>
        </div>
    </form>
</div>

<!-- Tabel Data Siswa -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-4">Daftar Siswa Kelas 3A</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NISN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $no = 1; while($siswa = mysqli_fetch_assoc($result_siswa)): ?>
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $no++ ?></td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= e($siswa['nama_lengkap']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= e($siswa['nis']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= e($siswa['nisn']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= e($siswa['jenis_kelamin']) ?></td>
                    <td class="px-6 py-4 text-sm text-center">
                        <button onclick="showDeleteModal('master_siswa.php?hapus=<?= $siswa['id_siswa'] ?>&csrf_token=<?= get_csrf_token() ?>')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i> Hapus</button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result_siswa) == 0): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data siswa.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (!empty($pesan_sukses)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ title: 'Berhasil!', text: '<?= addslashes($pesan_sukses); ?>', icon: 'success', confirmButtonText: 'OK' });
});
</script>
<?php endif; ?>
<?php if (!empty($pesan_error)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ title: 'Gagal!', text: '<?= addslashes($pesan_error); ?>', icon: 'error', confirmButtonText: 'OK' });
});
</script>
<?php endif; ?>
<?php
template_footer();
?>
