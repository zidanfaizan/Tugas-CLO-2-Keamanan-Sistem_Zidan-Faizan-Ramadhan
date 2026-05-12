<?php
require 'config.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Mencegah Buffer Overflow
    if (strlen($user) > 50 || strlen($pass) > 100) {
        die("<div class='container'><p class='msg-error'>Input terlalu panjang!</p></div>");
    }

    // Hash Password
    $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $hashed_password);
    
    // Menangkap Error dari Database (termasuk Duplicate Username)
    try {
        if ($stmt->execute()) {
            $message = "<div class='msg-success'>Registrasi berhasil! <a href='index.php'>Silakan Login di Halaman Utama</a></div>";
        }
    } catch (mysqli_sql_exception $e) {
        // Kode 1062 adalah error MySQL untuk Duplicate Entry (Data ganda)
        if ($e->getCode() == 1062) {
            $message = "<div class='msg-error'>Gagal mendaftar. Username '<strong>" . htmlspecialchars($user) . "</strong>' sudah terpakai! Silakan gunakan nama lain.</div>";
        } else {
            $message = "<div class='msg-error'>Terjadi kesalahan sistem. Coba lagi nanti.</div>";
        }
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Akun Baru</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; padding-top: 50px; }
        .container { background-color: white; padding: 30px 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #333; text-align: center; margin-bottom: 20px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #0866ff; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #0055d4; }
        .links { text-align: center; margin-top: 15px; font-size: 14px; }
        a { color: #0866ff; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
        .msg-success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px; }
        .msg-error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrasi Akun</h2>
        
        <?php if($message) echo $message; ?>
        
        <form method="POST">
            <label>Pilih Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>
            <label>Buat Password</label>
            <input type="password" name="password" placeholder="Buat password yang kuat" required>
            <button type="submit">Daftar Sekarang</button>
        </form>
        
        <div class="links">
            <p>Sudah punya akun?</p>
            <p><a href="index.php">Kembali ke Halaman Utama untuk Login</a></p>
        </div>
    </div>
</body>
</html>