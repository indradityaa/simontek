<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// ================== PROSES TAMBAH TEKNISI ==================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pengguna = $_POST['id_pengguna'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $sql = "INSERT INTO teknisi (id_pengguna, no_hp, alamat) VALUES ('$id_pengguna', '$no_hp', '$alamat')";
    if ($conn->query($sql)) {
        $success = "✅ Teknisi baru berhasil ditambahkan!";
    } else {
        $error = "❌ Terjadi kesalahan: " . $conn->error;
    }
}

// ================== AMBIL DATA TEKNISI DAN PENGGUNA ==================
$pengguna = $conn->query("SELECT id_pengguna, nama FROM pengguna WHERE role = 'teknisi'");
$teknisi = $conn->query("
    SELECT t.id_teknisi, p.nama, t.no_hp, t.alamat
    FROM teknisi t
    JOIN pengguna p ON t.id_pengguna = p.id_pengguna
    ORDER BY t.id_teknisi DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Teknisi | Sismontek</title>
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
    <a href="pelanggan.php">👥 Pelanggan</a>
    <a href="teknisi.php" class="active">🧑‍🔧 Teknisi</a>
    <a href="laporan.php">📊 Laporan Kinerja</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Manajemen Teknisi</h1>

    <?php if (isset($success)) echo "<div class='alert success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert error'>$error</div>"; ?>

    <!-- Form Input Teknisi -->
    <div class="form-card">
        <h3>Tambah Teknisi Baru</h3>
        <form method="POST" action="">
            <label for="id_pengguna">Pilih Pengguna (Teknisi)</label>
            <select name="id_pengguna" required>
                <option value="">-- Pilih Teknisi dari Pengguna --</option>
                <?php while ($p = $pengguna->fetch_assoc()) { ?>
                    <option value="<?= $p['id_pengguna']; ?>"><?= $p['nama']; ?></option>
                <?php } ?>
            </select>

            <label for="no_hp">Nomor HP</label>
            <input type="text" name="no_hp" placeholder="Contoh: 08123456789" required>

            <label for="alamat">Alamat</label>
            <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap teknisi"></textarea>

            <button type="submit">+ Tambah Teknisi</button>
        </form>
    </div>

    <!-- Tabel Teknisi -->
    <div class="table-card">
        <h3>Daftar Teknisi</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama Teknisi</th>
                <th>No HP</th>
                <th>Alamat</th>
            </tr>
            <?php if ($teknisi && $teknisi->num_rows > 0): ?>
                <?php while ($t = $teknisi->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['id_teknisi']; ?></td>
                    <td><?= $t['nama']; ?></td>
                    <td><?= $t['no_hp']; ?></td>
                    <td><?= $t['alamat']; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">Belum ada data teknisi.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
