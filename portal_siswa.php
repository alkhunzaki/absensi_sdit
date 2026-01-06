<?php
include 'config.php';
template_portal_header('Portal Walimurid');
?>

<div class="max-w-2xl mx-auto py-12">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-blue-900 mb-4">Laporan Perkembangan Siswa</h1>
        <p class="text-lg text-gray-600">Silakan masukkan Nama Siswa, NIS, atau NISN untuk melihat laporan absensi dan akhlak anak Anda secara *real-time*.</p>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-xl border border-blue-50">
        <form action="info_siswa.php" method="GET" class="space-y-6">
            <div>
                <label for="nis" class="block text-sm font-semibold text-gray-700 mb-2">Nama, NIS atau NISN Siswa</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="nis" id="nis" required
                        class="block w-full pl-10 pr-3 py-4 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-lg transition duration-150 ease-in-out"
                        placeholder="Contoh: Ahmad Zaky atau 123456">
                </div>
            </div>
            
            <button type="submit" 
                class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-lg font-bold text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition-all duration-200 transform hover:scale-[1.02]">
                <i class="fas fa-search mr-2 mt-1"></i> Lihat Laporan Siswa
            </button>
        </form>
    </div>

    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex items-start">
            <div class="bg-blue-600 p-2 rounded-lg text-white mr-4">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-blue-900">Kehadiran Real-time</h3>
                <p class="text-sm text-blue-800 opacity-80">Pantau kehadiran harian anak Anda setiap hari.</p>
            </div>
        </div>
        <div class="bg-green-50 p-6 rounded-xl border border-green-100 flex items-start">
            <div class="bg-green-600 p-2 rounded-lg text-white mr-4">
                <i class="fas fa-medal"></i>
            </div>
            <div>
                <h3 class="font-bold text-green-900">Penilaian Akhlak</h3>
                <p class="text-sm text-green-800 opacity-80">Melihat perkembangan karakter dan perilaku siswa.</p>
            </div>
        </div>
    </div>
</div>

<?php template_portal_footer(); ?>
