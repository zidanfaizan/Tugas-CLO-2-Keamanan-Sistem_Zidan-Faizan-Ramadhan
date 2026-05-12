<?php
$host = 'db'; 
$db   = 'tugas_clo2'; 
$user = 'zidan';
$pass = 'zidanpassword';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- OTOMATIS MEMBUAT AKUN ADMIN JIKA BELUM ADA ---
$admin_user = 'zidanfaizan';
$admin_pass = 'zidan123';

$check_admin = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check_admin->bind_param("s", $admin_user);
$check_admin->execute();
$check_admin->store_result();

// Jika admin belum ada di database, buat sekarang
if ($check_admin->num_rows == 0) {
    // Hash password admin sebelum disimpan
    $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
    $insert_admin = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $insert_admin->bind_param("ss", $admin_user, $hash);
    $insert_admin->execute();
    $insert_admin->close();
}
$check_admin->close();
?>