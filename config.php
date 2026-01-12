<?php
/* ==================================================
File: config.php
Fungsi: Konfigurasi utama, koneksi database, dan template.
==================================================
*/
// Deteksi HTTPS di balik Proxy (seperti Vercel)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Pengaturan Sesi untuk Vercel (Menggunakan /tmp jika filesystem read-only)
if (getenv('VERCEL') || getenv('DB_HOST')) {
    ini_set('session.save_path', '/tmp');
}

// Pengaturan Cookie yang lebih ketat tapi kompatibel
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

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

// Tambahkan timeout koneksi (5 detik) untuk menghindari hanging
mysqli_options($koneksi, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

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

try {
    if (!mysqli_real_connect($koneksi, $db_host, $db_user, $db_pass, $db_name, $port_int, NULL, $flags)) {
        throw new Exception(mysqli_connect_error());
    }
} catch (mysqli_sql_exception $e) {
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        die("Database '" . e($db_name) . "' tidak ditemukan. Silakan jalankan <a href='setup_db.php'>Setup Database</a>.");
    }
    die("Koneksi ke database gagal: " . $e->getMessage());
} catch (Exception $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

date_default_timezone_set('Asia/Jakarta');

// Helper untuk mencegah XSS (Cross-Site Scripting)
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function check_csrf($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF Token validation failed.");
    }
}

function get_csrf_token() {
    return $_SESSION['csrf_token'];
}

function check_login() {
    global $koneksi;
    
    // Cek apakah tabel 'users' ada (Cache hasil di SESSION agar tidak query terus)
    if (!isset($_SESSION['table_exists']['users'])) {
        $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'users'");
        $_SESSION['table_exists']['users'] = (mysqli_num_rows($check_table) > 0);
    }

    if (!$_SESSION['table_exists']['users']) {
        die("Tabel 'users' tidak ditemukan. Silakan jalankan <a href='update_db_security.php'>Update Security (Database)</a> terlebih dahulu untuk membuat akun admin.");
    }

    if (!isset($_SESSION['admin_id'])) {
        header('Location: /login.php');
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
                    <a href="master_pengumuman.php" class="flex items-center px-4 py-3 mt-2 rounded-lg hover:bg-blue-700"><i class="fas fa-bullhorn w-6"></i><span class="ml-3">Input Pengumuman</span></a>
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

function template_portal_header($title) {
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
        <style> body { font-family: 'Inter', sans-serif; } </style>
    </head>
    <body class="bg-gray-50 text-gray-800">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <img src="img/aiis.png" class="h-10 w-auto mr-3" alt="Logo" onerror="this.style.display='none'">
                    <div>
                        <span class="text-xl font-bold text-blue-800 leading-none block">AIIS Portal</span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-wider">Walimurid Information System</span>
                    </div>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="portal_siswa.php" class="text-gray-600 hover:text-blue-600 font-medium">Cek Laporan Siswa</a>
                    <a href="informasi.php" class="text-gray-600 hover:text-blue-600 font-medium">Pengumuman Sekolah</a>
                    <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold border-l pl-6 border-gray-200">Login Admin</a>
                </div>
                <!-- Mobile Navigation -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-600 p-2"><i class="fas fa-bars text-xl"></i></button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-3">
            <a href="portal_siswa.php" class="block text-gray-600 hover:text-blue-600 font-medium">Cek Laporan Siswa</a>
            <a href="informasi.php" class="block text-gray-600 hover:text-blue-600 font-medium">Pengumuman Sekolah</a>
            <a href="login.php" class="block text-blue-600 hover:text-blue-800 font-semibold pt-2 border-t border-gray-100">Login Admin</a>
        </div>
    </nav>
    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
EOT;
}

function template_portal_footer() {
echo <<<EOT
    </main>
    <footer class="bg-white border-t border-gray-200 py-10">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-500 mb-2">SDIT Akhyar International Islamic School</p>
            <p class="text-xs text-gray-400">&copy; 
EOT;
echo date('Y');
echo <<<EOT
 - Dikembangkan oleh Ardianto Bagas</p>
        </div>
    </footer>
    </body>
</html>
EOT;
}
?>
