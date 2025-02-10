<?php
include 'koneksi.php';
// Pastikan ID task diberikan
if (!isset($_GET['id'])) {
    die("Task tidak ditemukan.");
}

$task_id = $_GET['id'];

// Ambil task dari database
$task = $conn->query("SELECT * FROM tasks WHERE id='$task_id'")->fetch_assoc();
if (!$task) {
    die("Task tidak ditemukan.");
}

// Ambil subtasks terkait
$subtasks = $conn->query("SELECT * FROM subtasks WHERE parent_id='$task_id' ORDER BY id ASC");

$update_success = false;

// Proses update task
if (isset($_POST['update_task'])) {
    $new_task_name = $_POST['new_task_name'];
    $conn->query("UPDATE tasks SET name='$new_task_name' WHERE id='$task_id'");
    $update_success = true;
}

// Proses update subtask
if (isset($_POST['update_subtask'])) {
    $subtask_id = $_POST['subtask_id'];
    $new_subtask_name = $_POST['new_subtask_name'];

    // Gunakan Prepared Statement
    $stmt = $conn->prepare("UPDATE subtasks SET name=? WHERE id=?");
    $stmt->bind_param("si", $new_subtask_name, $subtask_id);

    if ($stmt->execute()) {
        $update_success = true;
    } else {
        echo "Gagal memperbarui subtask: " . $conn->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Task</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            width: 50%;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h2 {
            color: #343a40;
        }

        form {
            margin-bottom: 15px;
        }

        input[type="text"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php if ($update_success): ?>
                alert("Update berhasil!");
            <?php endif; ?>
        });
    </script>
</head>
<body>

    <div class="container">
        <h2>Update Task</h2>

        <!-- Form Update Task -->
        <form method="POST">
            <input type="text" name="new_task_name" value="<?= htmlspecialchars($task['name']) ?>" required>
            <button type="submit" name="update_task">Update Task</button>
        </form>

        <h2>Update Subtasks</h2>

        <ul>
            <?php while ($subtask = $subtasks->fetch_assoc()): ?>
                <li>
                <form method="POST">
                    <input type="hidden" name="subtask_id" value="<?= htmlspecialchars($subtask['id']) ?>">
                    <input type="text" name="new_subtask_name" value="<?= htmlspecialchars($subtask['name']) ?>" required>
                    <button type="submit" name="update_subtask">Update Subtask</button>
                </form>
                </li>
            <?php endwhile; ?>
        </ul>

        <a href="dashboard.php" class="back-btn">Kembali</a>
    </div>

</body>
</html>
