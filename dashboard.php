<?php
include 'koneksi.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data to-do list user ini
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Pencarian task
$search_query = "";
$search_condition = ""; // Variabel untuk menampung kondisi pencarian

if (isset($_POST['search'])) {
    $search_query = $conn->real_escape_string($_POST['search_query']); // Hindari SQL Injection
    $search_condition = "AND name LIKE '%$search_query%'";
}

// Ambil task dari database, pastikan pencarian tetap berlaku
$tasks = $conn->query("
    SELECT * FROM tasks 
    WHERE user_id = '$user_id' $search_condition
    ORDER BY priority DESC, (SELECT MIN(status) FROM subtasks WHERE parent_id = tasks.id) ASC, status ASC, id ASC
");



$subtasks = $conn->query("
    SELECT * FROM subtasks 
    WHERE parent_id IN (SELECT id FROM tasks WHERE user_id = '$user_id')
    ORDER BY status ASC, parent_id ASC, id ASC
");

$subtasks_by_parent = []; 
while ($subtask = $subtasks->fetch_assoc()) {
    $subtasks_by_parent[$subtask['parent_id']][] = $subtask;
}
// Update status subtask (hanya bisa dicentang sekali)
if (isset($_POST['update_subtask_status'])) {
    $subtask_id = $_POST['subtask_id'];
    $parent_id = $_POST['parent_id'];
    
    // Pastikan subtask belum selesai sebelumnya
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

        echo "<script>alert('Subtask diperbarui!');</script>";
    }
}


// Proses hapus task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];

    // Hapus subtasks yang terkait dengan task ini
    $conn->query("DELETE FROM subtasks WHERE parent_id = '$task_id'");

    // Hapus task utama
    $conn->query("DELETE FROM tasks WHERE id = '$task_id'");

    // Redirect agar perubahan langsung terlihat
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        ul {
            list-style-type: none;
            padding: 0;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #007bff;
            border-radius: 10px;
            color: white;
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

        @media (max-width: 600px) {
            input[type="text"] {
                width: 80%;
            }
            
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="top-bar">
    <h2>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <form method="POST" action="logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>
    <br>
        <!-- Form Pencarian -->
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

    <table border="1">
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
                    <tr>
                        <td class="<?= $task['status'] == '1' ? 'completed' : '' ?>">
                            <?= htmlspecialchars($task['name']) ?>
                        </td>
                        <td><?= $task['status'] == '1' ? 'Selesai' : 'Belum Selesai' ?></td>
                        <td>
                            <?php if (isset($subtasks_by_parent[$task['id']])): ?>
                                <ul>
                                    <?php foreach ($subtasks_by_parent[$task['id']] as $subtask): ?>
                                        <li>
                                        <form method="POST" style="display: inline;" onsubmit="setTimeout(() => location.reload(), 100);">
                                            <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">
                                            <input type="hidden" name="parent_id" value="<?= $task['id'] ?>">
                                            <input type="checkbox" class="subtask-checkbox" 
                                                data-id="<?= $subtask['id'] ?>" 
                                                data-parent="<?= $task['id'] ?>"
                                                <?= $subtask['status'] == '1' ? 'checked disabled' : '' ?>>
                                        </form>
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
                            <a href="update.php?id=<?= $task['id'] ?>">
                                <button type="button" class="edit-btn">Edit</button>
                            </a>
                            <form method="POST" class="inline">
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
</body>
</html>
