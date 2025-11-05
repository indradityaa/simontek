<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'manajer') {
    header("Location: ../auth/login.php");
    exit;
}

// === Ambil Data untuk Chart Bar (Status Jadwal) ===
$data_status = $conn->query("
    SELECT status, COUNT(*) AS jumlah 
    FROM jadwal 
    GROUP BY status
");
$status_labels = [];
$status_values = [];
while ($row = $data_status->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_values[] = $row['jumlah'];
}

// === Ambil Data untuk Chart Line (Laporan per Tanggal) ===
$data_laporan = $conn->query("
    SELECT tanggal_jadwal, COUNT(*) AS total 
    FROM jadwal
    GROUP BY tanggal_jadwal 
    ORDER BY tanggal_jadwal ASC
");
$laporan_tanggal = [];
$laporan_total = [];
while ($row = $data_laporan->fetch_assoc()) {
    $laporan_tanggal[] = $row['tanggal_jadwal'];
    $laporan_total[] = $row['total'];
}

// === Daftar Laporan Terbaru ===
$laporan_terbaru = $conn->query("
    SELECT l.*, p.nama_pelanggan, j.deskripsi_pekerjaan 
    FROM laporan l
    JOIN jadwal j ON l.id_jadwal = j.id_jadwal
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    ORDER BY l.tanggal_laporan DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Manajer | Sismontek</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f6f9;
    margin: 0;
}
.sidebar {
    background-color: #3f72af;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sidebar h2 {
    margin: 0;
}
.sidebar a {
    color: white;
    text-decoration: none;
    background-color: #d32f2f;
    padding: 10px 15px;
    border-radius: 6px;
}
.sidebar a:hover {
    background-color: #b71c1c;
}
.container {
    padding: 20px;
}
h1 { color: #3f72af; }
.chart-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.chart-box {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    flex: 1;
    min-width: 300px;
}
.table-box {
    background: white;
    margin-top: 20px;
    padding: 15px;
    border-radius: 10px;
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
button {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    background-color: #3f72af;
    color: white;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background-color: #2b5d9c;
}
@media (max-width: 768px) {
    .chart-container { flex-direction: column; }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>📈 Dashboard Manajer</h2>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="container">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>

    <div class="chart-container">
        <!-- Diagram Batang Status Jadwal -->
        <div class="chart-box">
            <h3>📊 Status Jadwal</h3>
            <canvas id="chartStatus"></canvas>
        </div>

        <!-- Diagram Garis Jumlah Laporan -->
        <div class="chart-box">
            <h3>📈 Jumlah Kerusakan per Tanggal</h3>
            <canvas id="chartLaporan"></canvas>
        </div>
    </div>

    <!-- Daftar Laporan Terbaru -->
    <div class="table-box">
        <h3>🧾 Laporan Terbaru</h3>
        <table>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Deskripsi</th>
                <th>Kendala</th>
                <th>Tanggal</th>
            </tr>
            <?php
            if ($laporan_terbaru->num_rows > 0) {
                $no = 1;
                while ($row = $laporan_terbaru->fetch_assoc()) {
                    echo "<tr>
                        <td>$no</td>
                        <td>{$row['nama_pelanggan']}</td>
                        <td>{$row['deskripsi_pekerjaan']}</td>
                        <td>{$row['kendala']}</td>
                        <td>{$row['tanggal_laporan']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='5'>Belum ada laporan.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Tombol Cetak -->
    <div style="margin-top:20px;">
        <form method="GET" action="cetak_laporan.php" style="display:inline;">
            <input type="hidden" name="periode" value="minggu">
            <button type="submit">🗓 Cetak Laporan Mingguan</button>
        </form>
        <form method="GET" action="cetak_laporan.php" style="display:inline; margin-left:10px;">
            <input type="hidden" name="periode" value="bulan">
            <button type="submit">📅 Cetak Laporan Bulanan</button>
        </form>
    </div>
</div>

<script>
// === Chart Batang Status Jadwal ===
new Chart(document.getElementById('chartStatus'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($status_labels); ?>,
        datasets: [{
            label: 'Jumlah Jadwal',
            data: <?= json_encode($status_values); ?>,
            backgroundColor: ['#f9a825','#29b6f6','#66bb6a']
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

// === Chart Garis Laporan ===
new Chart(document.getElementById('chartLaporan'), {
    type: 'line',
    data: {
        labels: <?= json_encode($laporan_tanggal); ?>,
        datasets: [{
            label: 'Jumlah Laporan',
            data: <?= json_encode($laporan_total); ?>,
            borderColor: '#3f72af',
            fill: false,
            tension: 0.3
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
