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

// Ambil data laporan lama
$stmt = $conn->prepare("SELECT * FROM laporan WHERE id_jadwal = ?");
$stmt->bind_param("i", $id_jadwal);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();

if (!$laporan) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location='dashboard.teknisi.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Laporan | Sismontek</title>
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
    width: 400px;
}
h2 { color: #3f72af; text-align: center; }
label { font-weight: bold; display: block; margin-top: 10px; }
textarea, input[type="file"] {
    width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;
}
img {
    width: 100%;
    margin-top: 10px;
    border-radius: 6px;
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
    <h2>✏️ Edit Laporan</h2>
    <form action="update_laporan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_laporan" value="<?= $laporan['id_laporan']; ?>">
        <input type="hidden" name="id_jadwal" value="<?= $id_jadwal; ?>">

        <label>Kendala / Catatan</label>
        <textarea name="kendala" rows="4" required><?= htmlspecialchars($laporan['kendala']); ?></textarea>

        <label>Foto Bukti Lama</label>
        <img src="<?= $laporan['foto_bukti']; ?>" alt="Bukti Lama">

        <label>Ganti Foto (opsional)</label>
        <input type="file" name="foto">

        <button type="submit" name="update">Update Laporan</button>
    </form>
</div>
</body>
</html>
