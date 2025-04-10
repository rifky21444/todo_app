<?php
include 'koneksi.php';

// Update status task yang sudah melewati deadline
$conn->query("UPDATE tasks SET status = 2 WHERE status = 0 AND deadline < NOW()");

echo "Success";
?>
