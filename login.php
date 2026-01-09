<?php
include 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error_msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Gunakan Prepared Statement untuk cek user
    $stmt = mysqli_prepare($koneksi, "SELECT id, password FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $username;
            
            header('Location: index.php');
            exit;
        } else {
            $error_msg = 'Username atau password salah!';
        }
    } else {
        $error_msg = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">Selamat Datang</h1>
                <p class="text-gray-600">Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error_msg): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error_msg) ?></span>
            </div>
            <?php endif; ?>

            <form class="space-y-6" action="login.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                <div>
                    <label for="username" class="text-sm font-bold text-gray-600 block">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-2 border border-gray-300 rounded-md mt-1 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="password" class="text-sm font-bold text-gray-600 block">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300 rounded-md mt-1 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <button type="submit" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
