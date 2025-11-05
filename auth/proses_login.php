<?php
session_start();
include '../database/config.php'; // pastikan path sesuai struktur folder kamu

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=empty");
        exit;
    }

    // Cek user di database
    $sql = "SELECT * FROM pengguna WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Karena password belum di-hash, kita bandingkan langsung
        if ($password === $data['password']) {

            // Simpan data ke session
            $_SESSION['id_pengguna'] = $data['id_pengguna'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];

            // Arahkan berdasarkan role
            if ($data['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit;
            } elseif ($data['role'] === 'manajer') {
                header("Location: ../manajer/dashboard.manajer.php");
                exit;
            } elseif ($data['role'] === 'teknisi') {
                header("Location: ../teknisi/dashboard.teknisi.php");
                exit;
            } else {
                header("Location: login.php?error=invalidrole");
                exit;
            }

        } else {
            header("Location: login.php?error=wrongpass");
            exit;
        }
    } else {
        header("Location: login.php?error=nouser");
        exit;
    }
} else {
    header("Location: login.php?error=invalid");
    exit;
}
?>
