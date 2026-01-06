<?php
/* ==================================================
File: config.php
Fungsi: Konfigurasi utama, koneksi database, dan template.
==================================================
*/
session_start();

// Pengaturan Database (Support Local Laragon & Vercel/Railway)
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'db_absensi_sdit';
$db_port = getenv('DB_PORT') ?: 3306; // Default MySQL port

$koneksi = mysqli_init();

if (!$koneksi) {
    die("mysqli_init failed");
}

// Konfigurasi SSL untuk TiDB
if (getenv('DB_HOST')) {
    // Coba path CA umum di Linux/Vercel
    $ca_path = NULL;
    if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
        $ca_path = '/etc/ssl/certs/ca-certificates.crt';
    }
    
    mysqli_ssl_set($koneksi, NULL, NULL, $ca_path, NULL, NULL); 
    $flags = MYSQLI_CLIENT_SSL;
} else {
    $flags = 0;
}

// Pastikan Port berupa Integer
$port_int = intval($db_port);

if (!@mysqli_real_connect($koneksi, $db_host, $db_user, $db_pass, $db_name, $port_int, NULL, $flags)) {
    // Tampilkan pesan error detail untuk debugging (Hapus ini nanti jika production!)
    $error_msg = mysqli_connect_error();
    echo "<h1>Koneksi Database Gagal</h1>";
    echo "<p>Error: $error_msg</p>";
    echo "<hr>";
    echo "<h3>Debug Info:</h3>";
    echo "<ul>";
    echo "<li>Host: $db_host</li>";
    echo "<li>User: " . substr($db_user, 0, 3) . "***</li>";
    echo "<li>Port: $port_int</li>";
    echo "<li>SSL Flag: " . ($flags ? "Active" : "None") . "</li>";
    echo "<li>CA Path: " . ($ca_path ? $ca_path : "System Default (NULL)") . "</li>";
    echo "</ul>";
    die();
}

date_default_timezone_set('Asia/Jakarta');

function check_login() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function template_header($title) {
    $nama_sekolah = "SDIT Akhyar International Islamic School";
    echo <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>$title - $nama_sekolah</title>
		<script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
        <style> body { font-family: 'Inter', sans-serif; } .sidebar { transition: transform 0.3s ease-in-out; } </style>
	</head>
	<body class="bg-gray-100">
    <div class="relative min-h-screen md:flex">
        <div class="bg-blue-800 text-gray-100 flex justify-between md:hidden">
            <a href="index.php" class="block p-4 text-white font-bold">Absensi AIIS</a>
            <button id="hamburger-btn" class="p-4 focus:outline-none focus:bg-blue-700"><i class="fas fa-bars"></i></button>
        </div>
        <aside id="sidebar" class="sidebar bg-blue-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0">
            <div class="px-4 text-center">
                <center> <img src="img/aiis.png" style="width: 50%;" alt="Logo Sekolah" onerror="this.style.display='none'"> </center>
                <h2 class="text-xl font-bold mt-4">Absensi Kelas 3A</h2>
                <p class="text-sm">$nama_sekolah</p>
            </div>
            <nav class="flex flex-col justify-between h-[85%]">
                <div>
                    <a href="index.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-700"><i class="fas fa-tachometer-alt w-6"></i><span class="ml-3">Dashboard</span></a>
                    <a href="master_siswa.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-users w-6"></i><span class="ml-3">Data Master Siswa</span></a>
                    <!-- MENU BARU DITAMBAHKAN DI SINI -->
                    <a href="master_akhlak.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-cogs w-6"></i><span class="ml-3">Master Penilaian</span></a>
                    <a href="absen.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-user-check w-6"></i><span class="ml-3">Absen Kehadiran</span></a>
                    <a href="penilaian_akhlak.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-award w-6"></i><span class="ml-3">Penilaian Akhlak</span></a>
                    <a href="rekap.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-file-alt w-6"></i><span class="ml-3">Rekap Kehadiran</span></a>
                    <a href="rekap_akhlak.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-book-open w-6"></i><span class="ml-3">Rekap Akhlak</span></a>
                    <a href="laporan_individu.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-print w-6"></i><span class="ml-3">Cetak Laporan Siswa</span></a>
                </div>
                <div>
                    <a href="logout.php" class="flex items-center px-4 py-3 mt-2 rounded-lg bg-red-600 hover:bg-red-700"><i class="fas fa-sign-out-alt w-6"></i><span class="ml-3">Logout</span></a>
                </div>
            </nav>
        </aside>
        <main class="flex-1 p-4 md:p-8">
EOT;
}

function template_footer() {
echo <<<EOT
            <footer class="text-center mt-8 text-sm text-gray-500">Copyright &copy; 
EOT;
echo date('Y');
echo <<<EOT
 - Ardianto.Bagas</footer>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const sidebar = document.getElementById('sidebar');
            if(hamburgerBtn && sidebar) {
                hamburgerBtn.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); });
            }
        });
        function showDeleteModal(deleteUrl) {
            Swal.fire({
                title: 'Apakah Anda yakin?', text: "Data yang dihapus tidak dapat dikembalikan!", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal'
            }).then((result) => { if (result.isConfirmed) { window.location.href = deleteUrl; } })
        }
    </script>
	</body>
</html>
EOT;
}
?>
