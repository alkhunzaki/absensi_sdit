<?php
include 'config.php';
check_login();

// Ambil daftar siswa untuk dropdown
$result_siswa = mysqli_query($koneksi, "SELECT id_siswa, nama_lengkap FROM siswa ORDER BY nama_lengkap ASC");

template_header('Cetak Laporan Siswa');
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Cetak Laporan Individu Siswa</h1>

<div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
    <p class="mb-6 text-gray-600">Silakan isi form di bawah ini untuk membuat laporan kehadiran PDF per siswa. Laporan akan dibuat dalam format kertas A4.</p>
    
    <!-- PERUBAHAN: Menghapus target="_blank" agar pratinjau lebih baik -->
    <form action="download_laporan_individu.php" method="post" enctype="multipart/form-data">
        <div class="space-y-6">
            <!-- ... (semua kolom isian sama seperti sebelumnya) ... -->
            <!-- Pengaturan Laporan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Pengaturan Laporan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="id_siswa" class="block text-sm font-medium text-gray-700">Pilih Siswa</label>
                        <select id="id_siswa" name="id_siswa" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php while($siswa = mysqli_fetch_assoc($result_siswa)): ?>
                            <option value="<?= $siswa['id_siswa'] ?>"><?= htmlspecialchars($siswa['nama_lengkap']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="bulan" class="block text-sm font-medium text-gray-700">Pilih Bulan Laporan</label>
                        <input type="month" id="bulan" name="bulan" value="<?= date('Y-m') ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md" required>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Header -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Pengaturan Header</h3>
                 <div>
                    <label for="logo_kop" class="block text-sm font-medium text-gray-700">1. Logo Sekolah (Opsional)</label>
                    <input type="file" id="logo_kop" name="logo_kop" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Gunakan format PNG transparan untuk hasil terbaik.</p>
                </div>
                <div class="mt-4">
                    <label for="teks_kop" class="block text-sm font-medium text-gray-700">2. Teks Kop Surat (Bisa Diedit)</label>
                    <textarea id="teks_kop" name="teks_kop" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm" placeholder="Contoh:&#10;YAYASAN PENDIDIKAN AKHYAR&#10;SDIT AKHYAR INTERNATIONAL ISLAMIC SCHOOL&#10;Jl. Sekolah No. 1, Jakarta"><?= "YAYASAN PENDIDIKAN AKHYAR\nSDIT AKHYAR INTERNATIONAL ISLAMIC SCHOOL\nJl. Sekolah No. 1, Jakarta" ?></textarea>
                </div>
            </div>

            <!-- Catatan Kehadiran -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Catatan Tambahan</h3>
                <div>
                    <label for="catatan_kehadiran" class="block text-sm font-medium text-gray-700">Catatan Rekap Kehadiran (Opsional)</label>
                    <textarea id="catatan_kehadiran" name="catatan_kehadiran" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm" placeholder="Contoh: Ananda menunjukkan peningkatan dalam kedisiplinan waktu."></textarea>
                </div>
            </div>

            <!-- Pengaturan Tanda Tangan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Pengaturan Tanda Tangan</h3>
                <div class="mb-4">
                    <label for="tanda_tangan_digital" class="block text-sm font-medium text-gray-700">1. Tanda Tangan Digital (Opsional)</label>
                    <input type="file" id="tanda_tangan_digital" name="tanda_tangan_digital" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="text-xs text-gray-500 mt-1">Unggah gambar tanda tangan (format PNG transparan direkomendasikan).</p>
                </div>
                <div>
                    <label for="nama_walikelas" class="block text-sm font-medium text-gray-700">2. Nama Wali Kelas (Bisa Diedit)</label>
                    <input type="text" id="nama_walikelas" name="nama_walikelas" value="Ardianto Bagas" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md" required>
                </div>
            </div>
        </div>

        <!-- PERUBAHAN: Menambahkan dua tombol (Pratinjau dan Download) -->
        <div class="mt-8 pt-5 border-t">
            <div class="flex justify-end gap-4">
                <button type="submit" name="action" value="preview" class="py-2 px-6 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                    <i class="fas fa-eye mr-2"></i>Tampilkan Pratinjau
                </button>
                <button type="submit" name="action" value="download" class="py-2 px-6 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700">
                    <i class="fas fa-download mr-2"></i>Download PDF
                </button>
            </div>
        </div>
    </form>
</div>

<?php
template_footer();
?>
