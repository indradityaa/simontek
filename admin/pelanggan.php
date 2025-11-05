<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// ================== PROSES TAMBAH PELANGGAN ==================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['nomer_telepon'];
    $email = $_POST['email'];
    $paket = $_POST['paket'];

    $sql = "INSERT INTO pelanggan (nama_pelanggan, alamat, nomer_telepon, email, paket)
            VALUES ('$nama_pelanggan', '$alamat', '$telepon', '$email', '$paket')";

    if ($conn->query($sql)) {
        $success = "✅ Data pelanggan berhasil ditambahkan!";
    } else {
        $error = "❌ Terjadi kesalahan: " . $conn->error;
    }
}

// ================== AMBIL DATA PELANGGAN ==================
$pelanggan = $conn->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pelanggan | Sismontek</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        display: flex;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        background-color: #3f72af;
        color: white;
        height: 100vh;
        padding-top: 20px;
        position: fixed;
    }
    .sidebar h2 {
        text-align: center;
        margin-bottom: 40px;
    }
    .sidebar a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 14px 25px;
        transition: 0.3s;
        font-size: 15px;
    }
    .sidebar a:hover, .sidebar a.active {
        background-color: #2e5c8a;
    }

    /* Main content */
    .main-content {
        margin-left: 240px;
        padding: 30px;
        width: 100%;
    }

    h1 {
        color: #3f72af;
        margin-bottom: 20px;
    }

    .form-card, .table-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    .form-card h3, .table-card h3 {
        margin-top: 0;
        color: #3f72af;
    }

    form input, form select, form textarea {
        width: 100%;
        padding: 10px;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }

    form button {
        background-color: #3f72af;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 15px;
        transition: 0.3s;
    }

    form button:hover {
        background-color: #2e5c8a;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th {
        background-color: #3f72af;
        color: white;
        padding: 10px;
        text-align: left;
    }

    td {
        padding: 10px;
    }

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .alert.success { background: #e8f5e9; color: #2e7d32; }
    .alert.error { background: #ffebee; color: #c62828; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>🔧 Sismontek</h2>
    <a href="dashboard.php">🏠 Home</a>
    <a href="jadwal.php">🗓 Jadwal</a>
    <a href="tambah_pengguna.php">➕ Tambah Pengguna</a>
    <a href="pelanggan.php" class="active">👥 Pelanggan</a>
    <a href="teknisi.php">🧑‍🔧 Teknisi</a>
    <a href="laporan.php">📊 Laporan Kinerja</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Manajemen Data Pelanggan</h1>

    <?php if (isset($success)) echo "<div class='alert success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert error'>$error</div>"; ?>

    <!-- Form Input Pelanggan -->
    <div class="form-card">
        <h3>Tambah Pelanggan Baru</h3>
        <form method="POST" action="">
            <label for="nama_pelanggan">Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" required>

            <label for="alamat">Alamat</label>
            <textarea name="alamat" rows="3" required></textarea>

            <label for="nomor_telepon">Nomor Telepon</label>
            <input type="text" name="nomor_telepon" required>

            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="paket">Paket Layanan</label>
            <select name="paket" required>
                <option value="">-- Pilih Paket --</option>
                <option value="Basic">Basic</option>
                <option value="Standard">Standard</option>
                <option value="Premium">Premium</option>
            </select>

            <button type="submit">+ Tambah Pelanggan</button>
        </form>
    </div>

    <!-- Tabel Pelanggan -->
    <div class="table-card">
        <h3>Daftar Pelanggan</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Paket</th>
            </tr>
            <?php if ($pelanggan && $pelanggan->num_rows > 0): ?>
                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id_pelanggan']; ?></td>
                    <td><?= $p['nama_pelanggan']; ?></td>
                    <td><?= $p['alamat']; ?></td>
                    <td><?= $p['nomer_telepon']; ?></td>
                    <td><?= $p['email']; ?></td>
                    <td><?= $p['paket']; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Belum ada data pelanggan.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
