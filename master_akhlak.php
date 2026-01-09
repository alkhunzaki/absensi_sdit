<?php
// master_akhlak.php
include 'config.php';
check_login();

$pesan_sukses = '';
$pesan_error = '';

// Logika untuk menghapus aspek
if (isset($_GET['hapus'])) {
    check_csrf($_GET['csrf_token'] ?? '');
    $id_aspek_hapus = (int)$_GET['hapus'];
    
    $stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM master_aspek WHERE id_aspek = ?");
    mysqli_stmt_bind_param($stmt_hapus, "i", $id_aspek_hapus);
    
    if (mysqli_stmt_execute($stmt_hapus)) {
        $pesan_sukses = "Aspek penilaian berhasil dihapus!";
    } else {
        $pesan_error = "Gagal menghapus aspek: " . mysqli_error($koneksi);
    }
}

// Logika untuk menambah aspek baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nama_aspek'])) {
    check_csrf($_POST['csrf_token'] ?? '');
    $nama_aspek = trim($_POST['nama_aspek']);

    if (!empty($nama_aspek)) {
        $stmt_tambah = mysqli_prepare($koneksi, "INSERT INTO master_aspek (nama_aspek) VALUES (?)");
        mysqli_stmt_bind_param($stmt_tambah, "s", $nama_aspek);
        
        if (mysqli_stmt_execute($stmt_tambah)) {
            $pesan_sukses = "Aspek penilaian baru berhasil ditambahkan!";
        } else {
            // Cek jika error karena duplikat
            if(mysqli_errno($koneksi) == 1062) {
                $pesan_error = "Gagal menambahkan: Aspek '" . e($nama_aspek) . "' sudah ada.";
            } else {
                $pesan_error = "Gagal menambahkan aspek: " . mysqli_error($koneksi);
            }
        }
    } else {
        $pesan_error = "Nama aspek tidak boleh kosong.";
    }
}

// Mengambil semua data aspek untuk ditampilkan
$result_aspek = mysqli_query($koneksi, "SELECT * FROM master_aspek ORDER BY nama_aspek ASC");

template_header('Master Penilaian Akhlak');
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Master Aspek Penilaian Akhlak</h1>

<!-- Form Input Aspek Baru -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-semibold mb-4">Tambah Aspek Penilaian Baru</h2>
    <form action="master_akhlak.php" method="post" class="flex items-end gap-4">
        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
        <div class="flex-grow">
            <label for="nama_aspek" class="block text-sm font-medium text-gray-700">Nama Aspek</label>
            <input type="text" name="nama_aspek" id="nama_aspek" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Contoh: Adab Berbicara" required>
        </div>
        <div>
            <button type="submit" class="py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Tambah Aspek
            </button>
        </div>
    </form>
</div>

<!-- Tabel Daftar Aspek -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-4">Daftar Aspek Penilaian</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aspek</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $no = 1; while($aspek = mysqli_fetch_assoc($result_aspek)): ?>
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $no++ ?></td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= e($aspek['nama_aspek']) ?></td>
                    <td class="px-6 py-4 text-sm text-center">
                        <a href="master_akhlak.php?hapus=<?= $aspek['id_aspek'] ?>&csrf_token=<?= get_csrf_token() ?>" class="text-red-600 hover:text-red-900" onclick="event.preventDefault(); showDeleteModal(this.href);">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result_aspek) == 0): ?>
                <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data aspek penilaian.</td></tr>
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
