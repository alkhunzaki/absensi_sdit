<?php
include 'config.php';
session_start(); // session_start() must be called before any session operations
// Hapus semua session
session_unset();
session_destroy();
// Hapus cookies (legacy)
setcookie('auth_token', '', time() - 3600, "/");
header('Location: login.php');
exit;
?>
