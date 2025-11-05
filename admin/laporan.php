<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// ===================== AMBIL DATA UNTUK CHART =====================
$status_data = [];
$count_data = [];

$sql_chart = "SELECT status, COUNT(*) as jumlah FROM jadwal GROUP BY status";
$result_chart = $conn->query($sql_chart);

if ($result_chart && $result_chart->num_rows > 0) {
    while ($row = $result_chart->fetch_assoc()) {
        $status_data[] = ucfirst($row['status']);
        $count_data[] = $row['jumlah'];
    }
}

// ===================== AMBIL SEMUA LAPORAN =====================
$sql_laporan = "
    SELECT 
        l.id_laporan, 
        p.nama_pelanggan, 
        u.nama AS nama_teknisi, 
        j.deskripsi_pekerjaan AS deskripsi, 
        j.status, 
        l.tanggal_laporan
    FROM laporan l
    JOIN jadwal j ON l.id_jadwal = j.id_jadwal
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    JOIN teknisi t ON j.id_teknisi = t.id_teknisi
    JOIN pengguna u ON t.id_pengguna = u.id_pengguna
    ORDER BY l.tanggal_laporan DESC
";
$laporan = $conn->query($sql_laporan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Kinerja Teknisi | Sismontek</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    .card {
        background-color: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    canvas {
        max-width: 100%;
        height: 350px;
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

    .status {
        font-weight: bold;
        text-transform: capitalize;
        padding: 6px 10px;
        border-radius: 8px;
        color: white;
    }

    .status.dijadwalkan { background-color: #f9a825; }
    .status.proses { background-color: #29b6f6; }
    .status.selesai { background-color: #66bb6a; }
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
    <a href="teknisi.php">🧑‍🔧 Teknisi</a>
    <a href="laporan.php" class="active">📊 Laporan Kinerja</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Laporan Kinerja Teknisi</h1>

    <!-- Grafik Batang -->
    <div class="card">
        <h3>📊 Grafik Status Laporan Teknisi</h3>
        <canvas id="statusChart"></canvas>
    </div>

    <!-- Tabel Laporan -->
    <div class="card">
        <h3>📋 Daftar Semua Laporan Teknisi</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama Pelanggan</th>
                <th>Nama Teknisi</th>
                <th>Deskripsi Pekerjaan</th>
                <th>Status</th>
                <th>Tanggal Laporan</th>
            </tr>
            <?php if ($laporan && $laporan->num_rows > 0): ?>
                <?php while ($row = $laporan->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_laporan']; ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td><?= htmlspecialchars($row['nama_teknisi']); ?></td>
                    <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                    <td><span class="status <?= strtolower($row['status']); ?>"><?= ucfirst($row['status']); ?></span></td>
                    <td><?= $row['tanggal_laporan']; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Belum ada laporan dari teknisi.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Script Chart -->
<script>
const ctx = document.getElementById('statusChart');
const statusChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($status_data); ?>,
        datasets: [{
            label: 'Jumlah Jadwal',
            data: <?= json_encode($count_data); ?>,
            backgroundColor: ['#f9a825', '#29b6f6', '#66bb6a'],
            borderWidth: 1,
            borderColor: '#3f72af'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Jumlah' }
            },
            x: {
                title: { display: true, text: 'Status Jadwal' }
            }
        }
    }
});

// Auto-refresh grafik tiap 10 detik
setInterval(() => {
    fetch('laporan_chart_data.php')
        .then(res => res.json())
        .then(data => {
            statusChart.data.labels = data.labels;
            statusChart.data.datasets[0].data = data.counts;
            statusChart.update();
        });
}, 10000);
</script>

</body>
</html>
