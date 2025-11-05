<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teknisi') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id_jadwal'])) {
    header("Location: dashboard.teknisi.php");
    exit;
}

$id_jadwal = $_GET['id_jadwal'];

// ambil info pelanggan dan pekerjaan
$query = "SELECT j.deskripsi_pekerjaan, j.tanggal_jadwal, p.nama_pelanggan 
          FROM jadwal j 
          JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan 
          WHERE j.id_jadwal = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_jadwal);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Laporan | Sismontek</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 420px;
}
h2 { color: #3f72af; text-align: center; }
label { font-weight: bold; display: block; margin-top: 10px; }
textarea, input[type="file"] {
    width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;
}
.info {
    background: #e3f2fd;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 10px;
    font-size: 14px;
}
button {
    margin-top: 15px;
    background-color: #3f72af;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    width: 100%;
    font-weight: 600;
}
button:hover { background-color: #2b5d9c; }
</style>
</head>
<body>
<div class="container">
    <h2>📝 Form Laporan</h2>
    <div class="info">
        <p><b>Pelanggan:</b> <?= htmlspecialchars($data['nama_pelanggan']); ?></p>
        <p><b>Tanggal:</b> <?= htmlspecialchars($data['tanggal_jadwal']); ?></p>
        <p><b>Pekerjaan:</b> <?= htmlspecialchars($data['deskripsi_pekerjaan']); ?></p>
    </div>

    <form action="proses_laporan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_jadwal" value="<?= $id_jadwal; ?>">
        <label>Kendala / Catatan</label>
        <textarea name="kendala" rows="4" required></textarea>
        <label>Foto Bukti</label>
        <input type="file" name="foto" required>
        <button type="submit" name="submit">Simpan Laporan & Ubah Status</button>
    </form>
</div>
</body>
</html>
