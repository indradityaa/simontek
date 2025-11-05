<?php
include '../database/config.php';

$sql = "SELECT status, COUNT(*) AS jumlah FROM jadwal GROUP BY status";
$result = $conn->query($sql);

$labels = [];
$counts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = ucfirst($row['status']);
        $counts[] = $row['jumlah'];
    }
}

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'counts' => $counts]);
?>
