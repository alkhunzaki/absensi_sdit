<?php
/* ==================================================
File: master_pengumuman.php
Fungsi: Manajemen pengumuman untuk walimurid.
==================================================
*/
include 'config.php';
check_login();

$pesan_sukses = '';
$pesan_error = '';

// Logika Hapus
if (isset($_GET['hapus'])) {
    check_csrf($_GET['csrf_token'] ?? '');
    $id = (int)$_GET['hapus'];
    
    $stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM pengumuman WHERE id_pengumuman = ?");
    mysqli_stmt_bind_param($stmt_hapus, "i", $id);
    
    if (mysqli_stmt_execute($stmt_hapus)) {
        $pesan_sukses = "Pengumuman berhasil dihapus!";
    } else {
        $pesan_error = "Gagal menghapus: " . mysqli_error($koneksi);
    }
}

// Logika Tambah
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    check_csrf($_POST['csrf_token'] ?? '');
    
    $judul = $_POST['judul'];
    $isi = $_POST['isi'];
    $kategori = $_POST['kategori'];
    $tanggal = date('Y-m-d');

    $stmt_tambah = mysqli_prepare($koneksi, "INSERT INTO pengumuman (judul, isi, kategori, tanggal) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_tambah, "ssss", $judul, $isi, $kategori, $tanggal);
    
    if (mysqli_stmt_execute($stmt_tambah)) {
        $pesan_sukses = "Pengumuman baru telah diterbitkan!";
    } else {
        $pesan_error = "Gagal menerbitkan: " . mysqli_error($koneksi);
    }
}

$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'pengumuman'");
if (mysqli_num_rows($check_table) == 0) {
    echo "<div class='bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6'>";
    echo "<p class='font-bold text-yellow-700'>Update Database Diperlukan</p>";
    echo "<p class='text-yellow-600 mb-2'>Tabel 'pengumuman' belum tersedia di database Anda.</p>";
    echo "<a href='setup_db_new.php' class='underline font-bold text-yellow-800'>Klik untuk inisialisasi tabel sekarang</a>";
    echo "</div>";
    template_footer();
    exit;
}

$result = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY tanggal DESC");

template_header('Manajemen Pengumuman');
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Pengumuman</h1>

<!-- Form Tambah -->
<div class="bg-white p-6 rounded-lg shadow-md mb-8 border-l-4 border-blue-600">
    <h2 class="text-xl font-semibold mb-4 text-blue-800">Buat Pengumuman Baru</h2>
    <form action="master_pengumuman.php" method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Pengumuman</label>
                <input type="text" name="judul" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Jadwal Libur Semester">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="kategori" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="Info">Info</option>
                    <option value="Agenda">Agenda</option>
                    <option value="Libur">Libur</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Isi Pengumuman</label>
            <textarea name="isi" rows="4" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Tuliskan detail pengumuman di sini..."></textarea>
        </div>
        <button type="submit" name="tambah" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-colors">
            <i class="fas fa-paper-plane mr-2"></i> Terbitkan Pengumuman
        </button>
    </form>
</div>

<!-- Daftar Pengumuman -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-6 text-gray-800">Daftar Pengumuman Terbit</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Terbit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $row['tanggal'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $badge = "bg-blue-100 text-blue-800";
                            if($row['kategori'] == 'Agenda') $badge = "bg-yellow-100 text-yellow-800";
                            if($row['kategori'] == 'Libur') $badge = "bg-red-100 text-red-800";
                            ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase <?= $badge ?>">
                                <?= $row['kategori'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= e($row['judul']) ?></td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="showDeleteModal('master_pengumuman.php?hapus=<?= $row['id_pengumuman'] ?>&csrf_token=<?= get_csrf_token() ?>')" class="text-red-500 hover:text-red-700 font-medium text-sm">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">Belum ada pengumuman.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pesan_sukses): ?>
<script>
    Swal.fire({ title: 'Berhasil!', text: '<?= $pesan_sukses ?>', icon: 'success', confirmButtonText: 'OK' });
</script>
<?php endif; ?>

<?php if ($pesan_error): ?>
<script>
    Swal.fire({ title: 'Error!', text: '<?= $pesan_error ?>', icon: 'error', confirmButtonText: 'OK' });
</script>
<?php endif; ?>

<?php template_footer(); ?>
