<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// ==================== DATA UNTUK TABEL JADWAL =====================
$jadwalQuery = $conn->query("
    SELECT deskripsi_pekerjaan, status, tanggal_jadwal
    FROM jadwal ORDER BY tanggal_jadwal DESC LIMIT 5
");

// ==================== DATA UNTUK CHART STATUS JADWAL =====================
$statusQuery = $conn->query("SELECT status, COUNT(*) AS jumlah FROM jadwal GROUP BY status");
$status_labels = [];
$status_values = [];
while ($row = $statusQuery->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_values[] = $row['jumlah'];
}

// ==================== DATA UNTUK CHART JUMLAH LAPORAN PER HARI =====================
$laporanQuery = $conn->query("
    SELECT tanggal_laporan, COUNT(*) AS total
    FROM laporan
    GROUP BY tanggal_laporan
    ORDER BY tanggal_laporan ASC
");
$laporan_tanggal = [];
$laporan_total = [];
while ($row = $laporanQuery->fetch_assoc()) {
    $laporan_tanggal[] = $row['tanggal_laporan'];
    $laporan_total[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin | Sismontek</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #3f72af;
    --dark-blue: #2b4d75;
    --light-bg: #f4f6fb;
    --white: #ffffff;
}

* { box-sizing: border-box; }

body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background-color: var(--light-bg);
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* Sidebar */
.sidebar {
    width: 240px;
    background-color: var(--primary);
    color: white;
    height: 100vh;
    padding-top: 20px;
    position: fixed;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
    font-weight: 600;
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

/* Main Area */
.main {
    margin-left: 240px; /* penting agar tidak tertutup sidebar */
    width: calc(100% - 240px);
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: var(--light-bg);
}

/* Header */
header {
    background-color: var(--white);
    padding: 15px 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

header h2 {
    color: var(--primary);
    margin: 0;
    font-weight: 600;
}

.logout {
    background: #e74c3c;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
}
.logout:hover { background: #c0392b; }

/* Content */
.content {
    padding: 30px;
    overflow-y: auto;
    flex: 1;
    animation: fadeIn 0.7s ease;
}

.card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(63,114,175,0.15);
    margin-bottom: 25px;
    transition: transform 0.3s ease;
}
.card:hover { transform: translateY(-3px); }

.welcome {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.welcome h3 {
    color: var(--primary);
    font-weight: 600;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: center;
}

th {
    background-color: var(--primary);
    color: white;
}

.chart-container {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(63,114,175,0.15);
    margin-bottom: 20px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Sidebar */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        flex-direction: row;
        justify-content: space-around;
    }
    .main {
        margin-left: 0;
        width: 100%;
    }
    header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <h2>🔧 Sismontek</h2>
    <a href="dashboard.php" class="active">🏠 Home</a>
    <a href="jadwal.php">🗓 Jadwal</a>
    <a href="tambah_pengguna.php">➕ Tambah Pengguna</a>
    <a href="pelanggan.php">👥 Pelanggan</a>
    <a href="teknisi.php">🧑‍🔧 Teknisi</a>
    <a href="laporan.php">📊 Laporan Kinerja</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main">
    <header>
        <h2>Dashboard Admin</h2>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </header>

    <div class="content">
        <div class="card">
            <div class="welcome">
                <h3>Selamat Datang, <?= $_SESSION['nama']; ?> 👋</h3>
                <p>Anda login sebagai <b><?= ucfirst($_SESSION['role']); ?></b></p>
            </div>
        </div>

        <!-- Tabel Jadwal -->
        <div class="card">
            <h3>Tugas Teknisi Terbaru</h3>
            <table>
                <tr>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
                <?php while ($row = $jadwalQuery->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['deskripsi_pekerjaan']); ?></td>
                    <td><?= ucfirst($row['status']); ?></td>
                    <td><?= $row['tanggal_jadwal']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Chart Status Jadwal -->
        <div class="chart-container">
            <h3>📊 Status Jadwal Teknisi</h3>
            <canvas id="statusChart"></canvas>
        </div>

        <!-- Chart Jumlah Laporan -->
        <div class="chart-container">
            <h3>📈 Jumlah Laporan per Hari</h3>
            <canvas id="laporanChart"></canvas>
        </div>
    </div>
</div>

<!-- SCRIPT CHART -->
<script>
// === Chart Batang (Status Jadwal) ===
new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($status_labels); ?>,
        datasets: [{
            label: 'Jumlah Jadwal',
            data: <?= json_encode($status_values); ?>,
            backgroundColor: ['#f9a825', '#29b6f6', '#66bb6a'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Jumlah Jadwal' } }
        }
    }
});

// === Chart Garis (Jumlah Laporan per Hari) ===
new Chart(document.getElementById('laporanChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($laporan_tanggal); ?>,
        datasets: [{
            label: 'Jumlah Laporan',
            data: <?= json_encode($laporan_total); ?>,
            borderColor: '#3f72af',
            backgroundColor: 'rgba(63,114,175,0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Jumlah Laporan' } },
            x: { title: { display: true, text: 'Tanggal' } }
        }
    }
});
</script>

</body>
</html>
