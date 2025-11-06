<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'manajer') {
    header("Location: ../auth/login.php");
    exit;
}

// === DATA CHART STATUS ===
$data_status = $conn->query("SELECT status, COUNT(*) AS jumlah FROM jadwal GROUP BY status");
$status_labels = [];
$status_values = [];
while ($row = $data_status->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_values[] = $row['jumlah'];
}

// === DATA CHART LAPORAN PER HARI ===
$data_laporan = $conn->query("
    SELECT tanggal_jadwal, COUNT(*) AS total 
    FROM jadwal GROUP BY tanggal_jadwal ORDER BY tanggal_jadwal ASC
");
$laporan_tanggal = [];
$laporan_total = [];
while ($row = $data_laporan->fetch_assoc()) {
    $laporan_tanggal[] = $row['tanggal_jadwal'];
    $laporan_total[] = $row['total'];
}

// === LAPORAN TERBARU ===
$laporan_terbaru = $conn->query("
    SELECT l.*, p.nama_pelanggan, j.deskripsi_pekerjaan 
    FROM laporan l
    JOIN jadwal j ON l.id_jadwal = j.id_jadwal
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    ORDER BY l.tanggal_laporan DESC LIMIT 5
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
:root {
    --primary: #3f72af;
    --secondary: #dbe2ef;
    --accent: #112d4e;
    --bg: #f9fbfd;
    --white: #ffffff;
}
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
body { background-color: var(--bg); color: #333; }

/* Navbar */
.navbar {
    background: linear-gradient(90deg, var(--primary), var(--accent));
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.navbar h1 { font-size: 20px; font-weight: 600; }
.navbar a {
    color: white;
    text-decoration: none;
    background-color: rgba(255,255,255,0.2);
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 500;
    transition: 0.3s;
}
.navbar a:hover { background-color: rgba(255,255,255,0.4); }

/* Container */
.container {
    padding: 25px 40px;
    animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header Text */
.header-text {
    margin-bottom: 25px;
}
.header-text h2 {
    color: var(--accent);
    font-size: 24px;
    font-weight: 600;
}
.header-text p {
    color: #666;
    font-size: 15px;
}

/* Cards Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

/* Card */
.card {
    background: var(--white);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(63,114,175,0.2);
}
.card h3 {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 15px;
}

/* Table */
.table-box {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    margin-top: 30px;
    padding: 20px;
}
.table-box h3 {
    margin-bottom: 15px;
    color: var(--primary);
}
table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 10px;
}
th, td {
    padding: 12px 10px;
    text-align: center;
}
th {
    background-color: var(--primary);
    color: white;
}
tr:nth-child(even) { background-color: #f2f5fa; }

/* Buttons */
.buttons {
    margin-top: 25px;
}
button {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    background-color: var(--primary);
    color: white;
    font-weight: 500;
    cursor: pointer;
    margin-right: 10px;
    transition: 0.3s;
}
button:hover {
    background-color: var(--accent);
}
@media(max-width:768px) {
    .container { padding: 20px; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <h1>📊 Dashboard Manajer</h1>
    <a href="../auth/logout.php">Logout</a>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="header-text">
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?> 👋</h2>
        <p>Berikut rangkuman performa tim teknisi minggu ini.</p>
    </div>

    <!-- CHARTS -->
    <div class="dashboard-grid">
        <div class="card">
            <h3>📊 Status Jadwal Teknisi</h3>
            <canvas id="chartStatus"></canvas>
        </div>

        <div class="card">
            <h3>📈 Jumlah Kerusakan per Tanggal</h3>
            <canvas id="chartLaporan"></canvas>
        </div>
    </div>

    <!-- TABEL LAPORAN TERBARU -->
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
                        <td>{$no}</td>
                        <td>{$row['nama_pelanggan']}</td>
                        <td>{$row['deskripsi_pekerjaan']}</td>
                        <td>{$row['kendala']}</td>
                        <td>{$row['tanggal_laporan']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='5'>Belum ada laporan terbaru.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Tombol Cetak -->
    <div style="margin-top:20px;">
        <button onclick="cetakLaporan('minggu')">🗓 Cetak Laporan Mingguan</button>
        <button onclick="cetakLaporan('bulan')">📅 Cetak Laporan Bulanan</button>
    </div>

    <!-- Iframe tersembunyi -->
    <iframe id="printFrame" style="display:none;"></iframe>

</div>

<!-- CHART JS -->
<script>
new Chart(document.getElementById('chartStatus'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($status_labels); ?>,
        datasets: [{
            label: 'Jumlah Jadwal',
            data: <?= json_encode($status_values); ?>,
            backgroundColor: ['#3f72af', '#f9a825', '#66bb6a'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Jumlah' } }
        },
        plugins: { legend: { display: false } }
    }
});

new Chart(document.getElementById('chartLaporan'), {
    type: 'line',
    data: {
        labels: <?= json_encode($laporan_tanggal); ?>,
        datasets: [{
            label: 'Jumlah Kerusakan',
            data: <?= json_encode($laporan_total); ?>,
            borderColor: '#3f72af',
            backgroundColor: 'rgba(63,114,175,0.2)',
            fill: true,
            tension: 0.3,
            pointStyle: 'circle',
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Jumlah Kerusakan' } },
            x: { title: { display: true, text: 'Tanggal' } }
        }
    }
});
function cetakLaporan(periode) {
    const iframe = document.getElementById('printFrame');
    iframe.src = 'cetak_laporan.php?periode=' + periode;

    iframe.onload = () => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}
</script>

</body>
</html>
