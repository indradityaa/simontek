<?php
$host = "localhost";
$username = "root";
$password = "Sicepat888*";
$database = "simontek";

$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("<h3 style='color:red;'>Gagal terkoneksi ke server MySQL: " . $conn->connect_error . "</h3>");
}

// Buat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!$conn->query($sql)) {
    die("<p style='color:red;'>Gagal membuat database: " . $conn->error . "</p>");
}
$conn->select_db($database);

// ==================== TABEL PENGGUNA ====================
$conn->query("
CREATE TABLE IF NOT EXISTS pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teknisi','manajer') DEFAULT 'teknisi',
    email VARCHAR(100)
) ENGINE=InnoDB;
");

// ==================== TABEL TEKNISI ====================
// Setiap teknisi terkait ke pengguna (akun login)
$conn->query("
CREATE TABLE IF NOT EXISTS teknisi (
    id_teknisi INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT NOT NULL,
    nama_teknisi VARCHAR(100),
    no_hp VARCHAR(15),
    alamat TEXT,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
");

// ==================== TABEL PELANGGAN ====================
$conn->query("
CREATE TABLE IF NOT EXISTS pelanggan (
    id_pelanggan INT(6) AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    alamat TEXT,
    nomer_telepon VARCHAR(15),
    email VARCHAR(100),
    paket VARCHAR(50)
) ENGINE=InnoDB;
");

// ==================== TABEL JADWAL ====================
$conn->query("
CREATE TABLE IF NOT EXISTS jadwal (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT(6),
    id_teknisi INT(11),
    tanggal_jadwal DATE,
    deskripsi_pekerjaan TEXT,
    status ENUM('dijadwalkan','proses','selesai') DEFAULT 'dijadwalkan',
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_teknisi) REFERENCES teknisi(id_teknisi)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
");

// ==================== TABEL DETAIL_JADWAL ====================
$conn->query("
CREATE TABLE IF NOT EXISTS detail_jadwal (
    id_jadwal INT NOT NULL,
    id_teknisi INT NOT NULL,
    PRIMARY KEY (id_jadwal, id_teknisi),
    FOREIGN KEY (id_jadwal) REFERENCES jadwal(id_jadwal)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_teknisi) REFERENCES teknisi(id_teknisi)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
");

// ==================== TABEL LAPORAN ====================
$conn->query("
CREATE TABLE IF NOT EXISTS laporan (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    id_jadwal INT NOT NULL,
    tanggal_laporan DATE DEFAULT (CURRENT_DATE),
    kendala TEXT,
    foto_bukti VARCHAR(255),
    FOREIGN KEY (id_jadwal) REFERENCES jadwal(id_jadwal)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
");

// ==================== BUAT ADMIN DEFAULT ====================
$cekAdmin = $conn->query("SELECT * FROM pengguna WHERE username='admin'");
if ($cekAdmin->num_rows === 0) {
    $conn->query("
        INSERT INTO pengguna (nama, username, password, role, email)
        VALUES ('Administrator', 'admin', 'admin', 'admin', 'admin@sismontek.com')
    ");
}

$conn->set_charset("utf8mb4");
?>