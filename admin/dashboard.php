<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// ambil data jadwal untuk tabel dan chart
$jadwalQuery = $conn->query("SELECT deskripsi_pekerjaan, status, tanggal_jadwal 
                             FROM jadwal ORDER BY tanggal_jadwal DESC LIMIT 5");
$statusQuery = $conn->query("SELECT status, COUNT(*) AS jumlah FROM jadwal GROUP BY status");
$statusData = [];
while ($row = $statusQuery->fetch_assoc()) {
    $statusData[$row['status']] = $row['jumlah'];
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
        width: 250px;
        background: linear-gradient(180deg, var(--primary), var(--dark-blue));
        color: white;
        display: flex;
        flex-direction: column;
        padding-top: 30px;
        transition: all 0.3s ease;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        margin: 5px 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .sidebar a:hover, .sidebar a.active {
        background-color: rgba(255,255,255,0.15);
        transform: translateX(5px);
    }

    /* Main Area */
    .main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Header */
    header {
        background-color: var(--white);
        padding: 15px 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        height: 100%;
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

    .card:hover {
        transform: translateY(-3px);
    }

    .welcome {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>🔧 Sismontek</h2>
        <a href="dashboard.php" class="active">🏠 Home</a>
        <a href="jadwal.php">🗓 Jadwal</a>
        <a href="tambah_pengguna.php">➕ Tambah Pengguna</a> <!-- Tambahan -->
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

            <div class="chart-container">
                <h3>Status Jadwal Teknisi</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

<script>
const ctx = document.getElementById('statusChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($statusData)); ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusData)); ?>,
            backgroundColor: ['#3f72af', '#f6b93b', '#38b000'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { color: '#333', font: { size: 14 } } },
        }
    }
});
</script>
</body>
</html>
