<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../database/config.php';

// Proses form tambah pengguna
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (!empty($nama) && !empty($username) && !empty($password) && !empty($role)) {
        $sql = "INSERT INTO pengguna (nama, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nama, $username, $password, $role);
        if ($stmt->execute()) {
            header("Location: tambah_pengguna.php?success=1");
            exit;
        } else {
            $error = "Gagal menambahkan pengguna.";
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Pengguna | Sismontek</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6fb;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 450px;
        margin: 60px auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 30px;
    }

    h2 {
        text-align: center;
        color: #3f72af;
        margin-bottom: 20px;
    }

    label {
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    input, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    button {
        width: 100%;
        background-color: #3f72af;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 15px;
        transition: background 0.3s;
    }

    button:hover {
        background-color: #2b4d75;
    }

    .success {
        background-color: #38b000;
        color: white;
        padding: 10px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 15px;
    }

    .error {
        background-color: #e74c3c;
        color: white;
        padding: 10px;
        border-radius: 6px;
        text-align: center;
        margin-bottom: 15px;
    }

    .back {
        display: inline-block;
        text-decoration: none;
        color: #3f72af;
        font-weight: 600;
        margin-top: 10px;
        text-align: center;
        width: 100%;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Tambah Pengguna Baru</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">✅ Pengguna berhasil ditambahkan!</div>
    <?php elseif (isset($error)): ?>
        <div class="error">❌ <?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Masukkan nama pengguna" required>

        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="">-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="teknisi">Teknisi</option>
            <option value="manajer">Manajer</option>
        </select>

        <button type="submit">+ Tambah Pengguna</button>
    </form>

    <a href="dashboard.php" class="back">← Kembali ke Dashboard</a>
</div>
</body>
</html>
