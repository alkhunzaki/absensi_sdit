<?php
/* ==================================================
File: penilaian_akhlak.php
Fungsi: Halaman untuk input penilaian akhlak dan adab.
==================================================
*/
include 'config.php';
check_login();

$pesan = '';
$status_pesan = '';
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['penilaian'])) {
    check_csrf($_POST['csrf_token'] ?? '');
    
    $penilaian_data = $_POST['penilaian'];
    $tanggal_penilaian = $_POST['tanggal_penilaian'];
    $berhasil_disimpan = 0;

    $stmt_save = mysqli_prepare($koneksi, "INSERT INTO penilaian_akhlak (id_siswa, tanggal, aspek_penilaian, nilai, catatan) VALUES (?, ?, ?, ?, ?)");

    foreach ($penilaian_data as $id_siswa => $data) {
        $aspek_dropdown = $data['aspek_dropdown'] ?? '';
        $aspek_lainnya = $data['aspek_lainnya'] ?? '';
        $nilai = $data['nilai'] ?? '';
        $catatan = $data['catatan'] ?? '';
        $id_siswa_safe = (int)$id_siswa;
        
        $aspek_final = ($aspek_dropdown === 'Lainnya') ? $aspek_lainnya : $aspek_dropdown;
        
        if (!empty($aspek_final) && $aspek_dropdown !== '') {
            mysqli_stmt_bind_param($stmt_save, "issss", $id_siswa_safe, $tanggal_penilaian, $aspek_final, $nilai, $catatan);
            if(mysqli_stmt_execute($stmt_save)) { 
                $berhasil_disimpan++; 
            }
        }
    }
    
    if ($berhasil_disimpan > 0) {
        $pesan = "Sebanyak " . $berhasil_disimpan . " data penilaian berhasil disimpan!";
        $status_pesan = "sukses";
    } else {
        $pesan = "Tidak ada data baru untuk disimpan.";
        $status_pesan = "gagal";
    }
}

$result_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama_lengkap ASC");
// PERBAIKAN: Mengambil daftar aspek dari database
$result_aspek = mysqli_query($koneksi, "SELECT * FROM master_aspek ORDER BY nama_aspek ASC");
$aspek_options = [];
while($row = mysqli_fetch_assoc($result_aspek)) {
    $aspek_options[] = $row;
}

template_header('Penilaian Akhlak & Adab');
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Penilaian Akhlak & Adab Siswa</h1>
<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="penilaian_akhlak.php" method="get" class="flex flex-wrap items-end gap-4 mb-4">
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700">Pilih Tanggal Penilaian</label>
            <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>" class="mt-1 px-3 py-2 border border-gray-300 rounded-md">
        </div>
        <div><button type="submit" class="py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i class="fas fa-search mr-2"></i>Tampilkan</button></div>
    </form>
    <hr class="my-4">
    <form action="penilaian_akhlak.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
        <input type="hidden" name="tanggal_penilaian" value="<?= htmlspecialchars($tanggal_filter) ?>">
        <h2 class="text-xl font-semibold mb-4">Form Penilaian untuk: <?= date("d F Y", strtotime($tanggal_filter)) ?></h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aspek yang Dinilai</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penilaian</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($siswa = mysqli_fetch_assoc($result_siswa)): $id_siswa = $siswa['id_siswa']; ?>
                    <tr>
                        <td class="px-4 py-4 text-sm font-medium text-gray-900 align-top"><?= htmlspecialchars($siswa['nama_lengkap']) ?></td>
                        <td class="px-4 py-4 align-top">
                            <!-- PERBAIKAN: Opsi dropdown diambil dari database -->
                            <select name="penilaian[<?= $id_siswa ?>][aspek_dropdown]" class="aspek-dropdown w-full px-2 py-1 border border-gray-300 rounded-md text-sm bg-white" data-target="aspek-lainnya-<?= $id_siswa ?>">
                                <option value="">-- Pilih Aspek --</option>
                                <?php foreach($aspek_options as $aspek): ?>
                                <option value="<?= htmlspecialchars($aspek['nama_aspek']) ?>"><?= htmlspecialchars($aspek['nama_aspek']) ?></option>
                                <?php endforeach; ?>
                                <option value="Lainnya">Lainnya...</option>
                            </select>
                            <input type="text" name="penilaian[<?= $id_siswa ?>][aspek_lainnya]" id="aspek-lainnya-<?= $id_siswa ?>" class="aspek-lainnya-input mt-2 w-full px-2 py-1 border border-gray-300 rounded-md text-sm hidden" placeholder="Tuliskan aspek lainnya">
                        </td>
                        <td class="px-4 py-4 align-top">
                            <input type="text" name="penilaian[<?= $id_siswa ?>][nilai]" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="Contoh: Sangat Baik">
                        </td>
                        <td class="px-4 py-4 align-top"><input type="text" name="penilaian[<?= $id_siswa ?>][catatan]" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="Catatan spesifik..."></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-6"><button type="submit" class="py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"><i class="fas fa-save mr-2"></i>Simpan Penilaian</button></div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.aspek-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            const targetInput = document.getElementById(this.getAttribute('data-target'));
            if (this.value === 'Lainnya') { targetInput.classList.remove('hidden'); } else { targetInput.classList.add('hidden'); }
        });
    });
    <?php if (!empty($pesan) && !empty($status_pesan)): ?>
    Swal.fire({
        title: '<?= ($status_pesan == 'sukses') ? 'Berhasil!' : 'Info'; ?>',
        text: '<?= addslashes(htmlspecialchars_decode($pesan)); ?>',
        icon: '<?= ($status_pesan == 'sukses') ? 'success' : 'info'; ?>',
        confirmButtonText: 'OK'
    });
    <?php endif; ?>
});
</script>
<?php
template_footer();
?>
