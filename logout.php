<?php
include 'config.php';
// Hapus semua session
session_unset();
session_destroy();
// Hapus cookies (legacy)
setcookie('auth_token', '', time() - 3600, "/");
header('Location: login.php');
exit;
?>
