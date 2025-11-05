<?php
session_start();
include '../database/config.php';

// Cek login & role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teknisi') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil id_teknisi
$query_teknisi = $conn->prepare("SELECT id_teknisi FROM teknisi WHERE id_pengguna = ?");
$query_teknisi->bind_param("i", $id_pengguna);
$query_teknisi->execute();
$id_teknisi = $query_teknisi->get_result()->fetch_assoc()['id_teknisi'];

// Proses jika teknisi menekan tombol “Mulai”
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mulai'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $update = $conn->prepare("UPDATE jadwal SET status='proses' WHERE id_jadwal=?");
    $update->bind_param("i", $id_jadwal);
    $update->execute();
    header("Location: dashboard.teknisi.php?status=proses");
    exit;
}

// Ambil semua jadwal teknisi
$query_jadwal = "
    SELECT j.id_jadwal, p.nama_pelanggan, j.deskripsi_pekerjaan, j.status, j.tanggal_jadwal
    FROM jadwal j
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    WHERE j.id_teknisi = ?
    ORDER BY j.tanggal_jadwal DESC
";
$stmt = $conn->prepare($query_jadwal);
$stmt->bind_param("i", $id_teknisi);
$stmt->execute();
$jadwal = $stmt->get_result();

// Hitung status ringkasan
$count_dijadwalkan = $conn->query("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi=$id_teknisi AND status='dijadwalkan'")->fetch_assoc()['jml'];
$count_proses = $conn->query("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi=$id_teknisi AND status='proses'")->fetch_assoc()['jml'];
$count_selesai = $conn->query("SELECT COUNT(*) AS jml FROM jadwal WHERE id_teknisi=$id_teknisi AND status='selesai'")->fetch_assoc()['jml'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Teknisi | Sismontek</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    margin: 0;
}
.header {
    background: #3f72af;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header a {
    background: #d32f2f;
    color: white;
    text-decoration: none;
    padding: 8px 14px;
    border-radius: 6px;
}
.container { padding: 20px; }
h1 { color: #3f72af; }

.status-cards {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.status-card {
    flex: 1;
    min-width: 150px;
    background: white;
    border-radius: 10px;
    text-align: center;
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.status-card h2 { color: #3f72af; margin: 0; }

.card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-top: 25px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #3f72af;
    color: white;
}
.status {
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    text-transform: capitalize;
}
.status.dijadwalkan { background-color: #f9a825; }
.status.proses { background-color: #29b6f6; }
.status.selesai { background-color: #66bb6a; }

button, .btn {
    padding: 7px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: white;
    font-weight: 600;
}
.btn-proses { background-color: #29b6f6; }
.btn-selesai { background-color: #66bb6a; text-decoration:none; display:inline-block; text-align:center; }
.btn-proses:hover { background-color: #0288d1; }
.btn-selesai:hover { background-color: #388e3c; }

@media (max-width: 768px) {
    .status-cards { flex-direction: column; }
    table { font-size: 14px; }
}
</style>
</head>
<body>

<div class="header">
    <h2>🔧 Dashboard Teknisi</h2>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="container">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>

    <div class="status-cards">
        <div class="status-card"><h3>Dijadwalkan</h3><h2><?= $count_dijadwalkan; ?></h2></div>
        <div class="status-card"><h3>Proses</h3><h2><?= $count_proses; ?></h2></div>
        <div class="status-card"><h3>Selesai</h3><h2><?= $count_selesai; ?></h2></div>
    </div>

    <div class="card">
        <h3>📋 Jadwal Kerja</h3>
        <table>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
            <?php
            if ($jadwal->num_rows > 0) {
                $no = 1;
                while ($row = $jadwal->fetch_assoc()) {
                    echo "<tr>
                        <td>$no</td>
                        <td>{$row['nama_pelanggan']}</td>
                        <td>{$row['deskripsi_pekerjaan']}</td>
                        <td><span class='status ".strtolower($row['status'])."'>{$row['status']}</span></td>
                        <td>{$row['tanggal_jadwal']}</td>
                        <td>";
                    
                    // Tombol aksi
                    if ($row['status'] == 'dijadwalkan') {
                        echo "<form method='POST'>
                                <input type='hidden' name='id_jadwal' value='{$row['id_jadwal']}'>
                                <button type='submit' name='mulai' class='btn btn-proses'>Mulai Pekerjaan</button>
                              </form>";
                    } elseif ($row['status'] == 'proses') {
                        echo "<a href='form_laporan.php?id_jadwal={$row['id_jadwal']}' class='btn btn-selesai'>Buat Laporan</a>";
                    } elseif ($row['status'] == 'selesai') {
                        echo "<a href='edit_laporan.php?id_jadwal={$row['id_jadwal']}' class='btn btn-proses'>Edit Laporan</a>";
                    }

                    echo "</td></tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6'>Belum ada jadwal.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>
