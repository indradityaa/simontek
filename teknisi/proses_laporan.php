<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teknisi') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_jadwal = $_POST['id_jadwal'];
    $kendala = $_POST['kendala'];
    $tanggal_laporan = date('Y-m-d');

    // Upload foto bukti
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    $target_file = $target_dir . time() . "_" . basename($foto_name);
    move_uploaded_file($foto_tmp, $target_file);

    // Simpan laporan
    $stmt = $conn->prepare("INSERT INTO laporan (id_jadwal, tanggal_laporan, kendala, foto_bukti) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $id_jadwal, $tanggal_laporan, $kendala, $target_file);
    $stmt->execute();

    // Update status jadwal
    $update = $conn->prepare("UPDATE jadwal SET status='selesai' WHERE id_jadwal=?");
    $update->bind_param("i", $id_jadwal);
    $update->execute();

    header("Location: dashboard.teknisi.php?status=selesai");
    exit;
}
?>
