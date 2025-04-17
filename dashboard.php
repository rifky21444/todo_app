<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
taskDeadlineUpdate($conn, $user_id);

function taskDeadlineUpdate($conn, $user_id) {
    $conn->query("UPDATE tasks SET status = 2 WHERE status = 0 AND deadline < NOW() AND user_id = '$user_id'");
}

$search_query = isset($_POST['search_query']) ? $conn->real_escape_string($_POST['search_query']) : '';
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';

$filter_condition = "";
if ($filter == "active") {
    $filter_condition = "AND status = 0 AND deadline >= NOW()";
} elseif ($filter == "completed") {
    $filter_condition = "AND status = 1";
} elseif ($filter == "overdue") {
    $filter_condition = "AND status = 0 AND deadline < NOW()";
}

$search_order = "";
if (!empty($search_query)) {
    $search_order = "(name LIKE '%$search_query%') DESC,";
}

$tasks = $conn->query("
    SELECT * FROM tasks 
    WHERE user_id = '$user_id' $filter_condition
    ORDER BY $search_order 
        priority DESC, 
        (SELECT MIN(status) FROM subtasks WHERE parent_id = tasks.id) ASC, 
        status ASC, 
        id ASC
");

$subtasks = $conn->query("SELECT * FROM subtasks WHERE parent_id IN (SELECT id FROM tasks WHERE user_id = '$user_id') ORDER BY status ASC, parent_id ASC, id ASC");
$subtasks_by_parent = [];
while ($subtask = $subtasks->fetch_assoc()) {
    $subtasks_by_parent[$subtask['parent_id']][] = $subtask;
}

if (isset($_POST['update_subtask_status'])) {
    $subtask_id = $_POST['subtask_id'];
    $parent_id = $_POST['parent_id'];

    $check_status = $conn->query("SELECT status FROM subtasks WHERE id='$subtask_id'")->fetch_assoc();
    if ($check_status['status'] == '0') {
        $conn->query("UPDATE subtasks SET status='1' WHERE id='$subtask_id'");

        $all_done = $conn->query("SELECT COUNT(*) AS count FROM subtasks WHERE parent_id='$parent_id' AND status='0'")->fetch_assoc()['count'] == 0;

        if ($all_done) {
            $conn->query("UPDATE tasks SET status='1' WHERE id='$parent_id'");
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];
    $conn->query("DELETE FROM subtasks WHERE parent_id = '$task_id'");
    $conn->query("DELETE FROM tasks WHERE id = '$task_id'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
<script>
$(document).ready(function () {
    // Minta izin notifikasi
    if ("Notification" in window && Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    // Panggil fungsi notifikasi pertama kali saat halaman dimuat
    checkTasks();

    // Cek setiap 5 menit
  let notificationInterval = setInterval(checkTasks, 300000); 

    function checkTasks() {
        $.ajax({
            url: "notifikasi.php",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.upcoming.length > 0) {
                    response.upcoming.forEach(task => {
                        showNotification("Tugas Akan Jatuh Tempo", `${task.name} (deadline: ${task.deadline})`);
                    });
                }

                if (response.overdue.length > 0) {
                    response.overdue.forEach(task => {
                        showNotification("Tugas TERLAMBAT!", `${task.name} (deadline: ${task.deadline})`);
                    });
                }

                if (response.upcoming.length + response.overdue.length > 0) {
                    Swal.fire({
                        title: "Notifikasi Tugas!",
                        html: "KAMU TERLAMBAT MENGERJAKAN TUGAS!",
                        icon: "info",
                        confirmButtonText: "OK"
                    });
                }
            }
        });
    }

    function showNotification(title, message) {
        if (Notification.permission === "granted") {
            new Notification(title, {
                body: message,
                icon: "https://cdn-icons-png.flaticon.com/512/786/786205.png"
            });
        }
    }
});
</script>


    <script>
        $(document).ready(function() {
            $(".subtask-checkbox").change(function() {
                let subtask_id = $(this).data("id");
                let parent_id = $(this).data("parent");

                $.ajax({
                    url: "update_subtask.php",
                    type: "POST",
                    data: { subtask_id: subtask_id, parent_id: parent_id },
                    success: function(response) {
                        if (response == "success") {
                            location.reload(); // Reload otomatis setelah update
                        } else {
                            alert("Gagal memperbarui subtask.");
                        }
                    }
                });
            });
        });
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f8f9fa;
        text-align: center;
    }
    
    h1 {
        color: #343a40;
    }

    form {
        margin-bottom: 15px;
    }

    input[type="text"] {
        padding: 10px;
        width: 60%;
        border: 2px solid #ced4da;
        border-radius: 5px;
        font-size: 16px;
    }

    .button-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        background-color: #28a745;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background-color: #218838;
    }

    table {
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        border-collapse: collapse;
        background: white;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    th, td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #007bff;
        color: white;
    }

    .completed {
        text-decoration: line-through;
        color: gray;
    }

    .overdue { 
        color: red; 
        font-weight: bold; 
    }

    ul {
        list-style-type: none;
        padding: 0;
    }

    li {
        background: #f8f9fa;
        padding: 8px;
        margin-bottom: 5px;
        border-radius: 5px;
        display: flex;
        align-items: center;
    }

    li input[type="checkbox"] {
        margin-right: 10px;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background-color: #007bff;
        border-radius: 10px;
        color: white;
        flex-wrap: wrap;
    }

    .logout-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        margin-top: 17px;
        margin-right: 10px;
        padding: 9px 12px;
        border-radius: 5px;
        cursor: pointer;
    }

    .logout-btn:hover {
        background-color: #c82333;
    }

    form.inline {
        display: inline;
    }

    .delete-btn {
        background-color: #dc3545;
        margin-left: 5px;
    }

    .delete-btn:hover {
        background-color: #c82333;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .top-bar {
            flex-direction: column;
            align-items: flex-start;
        }

        input[type="text"] {
            width: 100%;
            margin-bottom: 10px;
        }

        .button-container {
            flex-direction: column;
            align-items: stretch;
        }

        form.inline,
        form {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        button {
            width: 100%;
            font-size: 14px;
        }

        table {
            font-size: 14px;
            width: 100%;
            display: block;
            overflow-x: auto;
        }

        .logout-btn {
            margin-top: 10px;
            width: 100%;
        }
    }
</style>
</head>
<body>
<div class="top-bar">
    <h2>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <form method="POST" action="logout.php" onsubmit="return confirm('Yakin ingin logout?')">
    <button type="submit" class="logout-btn">Logout</button>
</form>
</div>
    <br>

<form method="POST">
    <input type="text" name="search_query" placeholder="Cari task" value="<?= htmlspecialchars($search_query) ?>">
    <button type="submit" name="search">Cari</button>
</form>
 <div class="button-container">
        <a href="tambah_task.php">
            <button>Tambah Task</button>
        </a>
        <form method="GET" action="tambah_subtask.php">
            <select name="task_id" required>
                <option value="">Pilih Task</option>
                <?php
               $tasksDropdown = $conn->query("SELECT id, name FROM tasks WHERE user_id = '$user_id'");
                while ($row = $tasksDropdown->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Tambah Subtask</button>
        </form>
    </div>

<form method="POST">
    <label><input type="radio" name="filter" value="all" <?= $filter == "all" ? "checked" : "" ?>> Semua</label>
    <label><input type="radio" name="filter" value="active" <?= $filter == "active" ? "checked" : "" ?>> Aktif</label>
    <label><input type="radio" name="filter" value="completed" <?= $filter == "completed" ? "checked" : "" ?>> Selesai</label>
    <label><input type="radio" name="filter" value="overdue" <?= $filter == "overdue" ? "checked" : "" ?>> Terlambat</label>
    <button type="submit" name="apply_filter">Terapkan</button>
</form>

<table>
    <thead>
        <tr>
            <th>Nama Task</th>
            <th>Status</th>
            <th>Subtasks</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($tasks->num_rows > 0): ?>
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <?php
                    $isOverdue = ($task['status'] == 0 && strtotime($task['deadline']) < time());
                    $highlight = (!empty($search_query) && stripos($task['name'], $search_query) !== false) ? 'background-color: #fff3cd;' : '';
                ?>
                <tr>
                    <td class="<?= $task['status'] == 1 ? 'completed' : ($task['status'] == 2 ? 'overdue' : '') ?>" style="<?= $highlight ?>">
                        <?= htmlspecialchars($task['name']) ?>
                    </td>
                    <td><?= $task['status'] == 1 ? 'Selesai' : ($task['status'] == 2 ? 'Terlambat' : 'Belum Selesai') ?></td>
                    <td>
                        <?php if (isset($subtasks_by_parent[$task['id']])): ?>
                            <ul>
                                <?php foreach ($subtasks_by_parent[$task['id']] as $subtask): ?>
                                    <li>
                                        <input type="checkbox" 
                                               class="subtask-checkbox" 
                                               data-id="<?= $subtask['id'] ?>" 
                                               data-parent="<?= $task['id'] ?>"
                                               <?= $subtask['status'] == '1' ? 'checked disabled' : '' ?>
                                               <?= $isOverdue ? 'disabled' : '' ?> >
                                        <span class="<?= $subtask['status'] == '1' ? 'completed' : '' ?>">
                                            <?= htmlspecialchars($subtask['name']) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <em>Tidak ada subtask</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$isOverdue && $task['status'] != 1): ?>
                            <a href="update.php?id=<?= $task['id'] ?>">
                                <button type="button">Edit</button>
                            </a>
                        <?php endif; ?>
                           <form method="POST" class="inline" onsubmit="return confirm('Yakin untuk hapus?')">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <button type="submit" name="delete_task" class="delete-btn">Hapus</button>
                    </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Tidak ada task ditemukan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
