<?php
session_start();
require 'config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $id = 0;
        $hashed_password = ''; 
        
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        
        if (password_verify($pass, (string)$hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit;
        } else {
            sleep(2); 
            $error = "Password salah!";
        }
    } else {
        sleep(2);
        $error = "User tidak ditemukan!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Akun</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; padding-top: 50px; }
        .container { background-color: white; padding: 30px 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #333; text-align: center; margin-bottom: 20px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #198754; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #157347; }
        .links { text-align: center; margin-top: 15px; font-size: 14px; }
        a { color: #0d6efd; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .msg-error { color: #dc3545; text-align: center; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Sistem</h2>
        <?php if($error) echo "<div class='msg-error'>$error</div>"; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
            <button type="submit">Login</button>
        </form>
        <div class="links">
            <p>Belum punya akun? <a href="signup.php">Register di sini</a>.</p>
            <p><a href="index.php">Kembali ke Halaman Komentar</a></p>
        </div>
    </div>
</body>
</html>