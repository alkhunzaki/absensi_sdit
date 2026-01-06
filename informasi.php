<?php
include 'config.php';
template_portal_header('Pengumuman Sekolah');

// Ambil pengumuman
$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'pengumuman'");
if (mysqli_num_rows($check_table) == 0) {
    echo "<div class='bg-red-50 p-10 rounded-3xl border border-red-100 text-center'>";
    echo "<i class='fas fa-database text-5xl text-red-200 mb-4 block'></i>";
    echo "<h2 class='text-2xl font-bold text-red-800 mb-2'>Database Update Diperlukan</h2>";
    echo "<p class='text-red-700 mb-6'>Tabel informasi belum terpasang. Silakan jalankan setup database terlebih dahulu.</p>";
    echo "<a href='setup_db_new.php' class='bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all inline-block'>Mulai Setup Database</a>";
    echo "</div>";
    template_portal_footer();
    exit;
}

$query = "SELECT * FROM pengumuman ORDER BY tanggal DESC";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    echo "<div class='bg-yellow-50 p-6 rounded-xl border border-yellow-200 text-yellow-800'>";
    echo "Gagal memuat pengumuman: " . mysqli_error($koneksi);
    echo "</div>";
    template_portal_footer();
    exit;
}
?>

<div class="mb-10 text-center">
    <h1 class="text-4xl font-extrabold text-blue-900 mb-2">Informasi & Pengumuman</h1>
    <p class="text-gray-600">Dapatkan informasi terbaru mengenai kegiatan dan agenda sekolah.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php 
            $color = 'blue';
            $icon = 'info-circle';
            if ($row['kategori'] == 'Agenda') { $color = 'yellow'; $icon = 'calendar-alt'; }
            elseif ($row['kategori'] == 'Libur') { $color = 'red'; $icon = 'home'; }
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="h-2 bg-<?= $color ?>-500"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 bg-<?= $color ?>-50 text-<?= $color ?>-700 text-xs font-bold rounded-full uppercase border border-<?= $color ?>-100">
                            <i class="fas fa-<?= $icon ?> mr-1"></i> <?= $row['kategori'] ?>
                        </span>
                        <span class="text-xs text-gray-400 font-medium"><?= date('d M Y', strtotime($row['tanggal'])) ?></span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-3 leading-tight"><?= $row['judul'] ?></h2>
                    <p class="text-gray-600 text-sm mb-6 line-clamp-3">
                        <?= nl2br($row['isi']) ?>
                    </p>
                    <button onclick="Swal.fire({
                        title: '<?= addslashes($row['judul']) ?>',
                        html: '<div class=\'text-left text-sm\'><?= str_replace(["\r", "\n"], '', nl2br(addslashes($row['isi']))) ?></div>',
                        icon: 'info',
                        confirmButtonText: 'Tutup'
                    })" class="text-<?= $color ?>-600 font-bold text-sm flex items-center hover:underline focus:outline-none">
                        Baca Selengkapnya <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-span-full py-20 text-center">
            <i class="fas fa-bullhorn text-6xl text-gray-200 mb-4"></i>
            <p class="text-gray-500">Belum ada pengumuman untuk saat ini.</p>
        </div>
    <?php endif; ?>
</div>

<div class="mt-16 bg-blue-900 rounded-3xl p-8 text-white relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="text-center md:text-left">
            <h2 class="text-2xl font-bold mb-2">Butuh bantuan atau informasi lanjut?</h2>
            <p class="text-blue-100 opacity-80">Hubungi Front Office atau Wali Kelas melalui WhatsApp resmi sekolah.</p>
        </div>
        <a href="#" class="px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-lg transition-all flex items-center">
            <i class="fab fa-whatsapp mr-3 text-2xl"></i> Hubungi Kami
        </a>
    </div>
    <!-- Dekorasi Background -->
    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-800 rounded-full opacity-50"></div>
    <div class="absolute -left-10 -top-10 w-32 h-32 bg-blue-800 rounded-full opacity-50"></div>
</div>

<?php template_portal_footer(); ?>
