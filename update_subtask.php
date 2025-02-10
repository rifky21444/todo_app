<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subtask_id = $_POST['subtask_id'];
    $parent_id = $_POST['parent_id'];

    // Periksa status subtask
    $check_status = $conn->query("SELECT status FROM subtasks WHERE id='$subtask_id'")->fetch_assoc();
    if ($check_status['status'] == '0') {
        // Update status subtask ke selesai (1)
        $conn->query("UPDATE subtasks SET status='1' WHERE id='$subtask_id'");

        // Cek apakah semua subtasks selesai
        $all_done = $conn->query("SELECT COUNT(*) AS count FROM subtasks WHERE parent_id='$parent_id' AND status='0'")->fetch_assoc()['count'] == 0;

        // Jika semua subtasks selesai, set task utama ke selesai
        if ($all_done) {
            $conn->query("UPDATE tasks SET status='1' WHERE id='$parent_id'");
        }

        echo "success";
    } else {
        echo "failed";
    }
}
?>
