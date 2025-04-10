<?php
include 'koneksi.php';

header('Content-Type: application/json');

// Waktu saat ini
$now = new DateTime();
$nowFormatted = $now->format('Y-m-d H:i:s');

// Waktu H-1 hari
$oneDayBefore = new DateTime();
$oneDayBefore->modify('+1 day');
$oneDayBeforeFormatted = $oneDayBefore->format('Y-m-d H:i:s');

// Waktu H-1 jam
$oneHourBefore = new DateTime();
$oneHourBefore->modify('+1 hour');
$oneHourBeforeFormatted = $oneHourBefore->format('Y-m-d H:i:s');

// Query untuk mengambil tugas yang akan jatuh tempo dalam H-1 hari atau H-1 jam
$query = $conn->query("SELECT tasks.id, tasks.name, tasks.deadline FROM tasks 
WHERE ((tasks.deadline <= '$oneDayBeforeFormatted' AND tasks.deadline > '$nowFormatted')
   OR (tasks.deadline <= '$oneHourBeforeFormatted' AND tasks.deadline > '$nowFormatted'))
   AND tasks.status = 0");

$tasks = [];

while ($task = $query->fetch_assoc()) {
    $tasks[] = [
        'id' => $task['id'],
        'name' => $task['name'],
        'deadline' => $task['deadline']
    ];
}

// Jika tidak ada tugas yang belum selesai, kirim array kosong
echo json_encode($tasks);
?>
