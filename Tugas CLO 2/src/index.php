<?php
session_start();
require 'config.php';

$error_login = "";

// --- LOGIKA 1: PROSES LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Mencegah SQL Injection dengan Prepared Statement
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $id = 0; $hashed_password = '';
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        
        // Verifikasi Hash Password
        if (password_verify($pass, (string)$hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error_login = "Password salah!";
        }
    } else {
        $error_login = "User tidak ditemukan!";
    }
    $stmt->close();
}

// --- LOGIKA 2: PROSES INPUT KOMENTAR ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_submit']) && isset($_SESSION['user_id'])) {
    $comment = $_POST['comment'];
    
    // Mitigasi Buffer Overflow (Validasi Panjang Input)
    if(strlen($comment) > 500) {
        die("<div class='container'><h3 style='color:red;'>Komentar terlalu panjang! (Maks 500 karakter)</h3><a href='index.php'>Kembali</a></div>");
    }

    // Mitigasi SQL Injection
    $stmt = $conn->prepare("INSERT INTO comments (user_id, comment) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $comment);
    $stmt->execute();
    $stmt->close();
    
    header("Location: index.php");
    exit;
}

// --- LOGIKA 3: AMBIL DATA KOMENTAR ---
$result = $conn->query("SELECT users.username, comments.comment, comments.created_at FROM comments JOIN users ON comments.user_id = users.id ORDER BY comments.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas CLO 2 - Zidan Faizan Ramadhan</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        
        /* Card UI */
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 25px; border: 1px solid #ddd; }
        h2, h3 { margin-top: 0; color: #1c1e21; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        /* Form Elements */
        input[type="text"], input[type="password"], textarea { 
            width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px;
        }
        button { border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; padding: 12px 20px; }
        .btn-primary { background-color: #0866ff; color: white; width: 100%; font-size: 16px; margin-top: 10px; }
        .btn-primary:hover { background-color: #0055d4; }
        .btn-comment { background-color: #42b72a; color: white; width: auto; }
        .btn-comment:hover { background-color: #36a420; }
        .btn-logout { background-color: #f02849; color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 13px; }

        /* User Header */
        .user-header { display: flex; justify-content: space-between; align-items: center; background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbdefb; }

        /* Admin Table */
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        .admin-table th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #ddd; text-align: left; }
        .admin-table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: top; }
        .hash-text { font-family: 'Courier New', Courier, monospace; color: #d32f2f; word-break: break-all; background: #fff5f5; padding: 5px; border-radius: 4px; border: 1px solid #ffcdd2; }

        /* Comment List */
        .comment-item { background: #fff; padding: 15px; border-bottom: 1px solid #eee; margin-bottom: 10px; border-left: 5px solid #0866ff; border-radius: 0 6px 6px 0; }
        .comment-user { font-weight: bold; color: #0866ff; font-size: 1.1em; }
        .comment-date { font-size: 12px; color: #90949c; margin-left: 10px; }
        .comment-text { margin-top: 8px; line-height: 1.6; white-space: pre-wrap; }
        
        .error-msg { color: #d32f2f; background: #fdecea; padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c2c7; font-weight: bold; }
        .auth-footer { text-align: center; margin-top: 15px; font-size: 14px; color: #65676b; }
        .auth-footer a { color: #0866ff; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <div class="card">
            <h2>Tugas CLO 2 Keamanan Sistem</h2>
            <?php if($error_login) echo "<div class='error-msg'>$error_login</div>"; ?>
            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
                <button type="submit" name="login_submit" class="btn-primary">Login</button>
            </form>
            <div class="auth-footer">
                Belum punya akun? <a href="signup.php">Registrasi Akun Baru</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="user-header">
                <span>Selamat Datang, <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></strong>!</span>
                <a href="logout.php" class="btn-logout">Keluar (Logout)</a>
            </div>
            <h3>Kirim Komentar Baru</h3>
            <form method="POST">
                <textarea name="comment" rows="3" placeholder="Apa yang ingin Anda sampaikan hari ini?" required></textarea>
                <button type="submit" name="comment_submit" class="btn-comment">Posting Komentar</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['username']) && $_SESSION['username'] === 'zidanfaizan'): ?>
        <div class="card" style="border-top: 5px solid #d32f2f;">
            <h3 style="color: #d32f2f;">🛡️ Dashboard Keamanan Admin</h3>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                Sebagai Admin, Anda dapat memantau kredensial pengguna. Password di bawah ini dilindungi dengan <strong>Hash Bcrypt + Salt</strong>.
            </p>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Password Hash (Bukti Pengamanan)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $users_list = $conn->query("SELECT id, username, password_hash FROM users");
                    while($u = $users_list->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><strong><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td><div class="hash-text"><?= htmlspecialchars($u['password_hash'], ENT_QUOTES, 'UTF-8') ?></div></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Diskusi Publik</h3>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="comment-item">
                    <span class="comment-user"><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="comment-date"><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="comment-text"><?= nl2br(htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #999; text-align: center; padding: 20px;">Belum ada diskusi. Jadilah yang pertama berkomentar!</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>