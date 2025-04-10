<?php
include 'koneksi.php';
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $name = $conn->real_escape_string($_POST['task_name']);
    $deadline = strtotime($_POST['deadline']); // Konversi deadline ke timestamp
    $current_time = time(); // Waktu saat ini

    // Validasi deadline tidak boleh mundur
    if ($deadline < $current_time) {
        echo "<script>alert('Tenggat waktu tidak boleh di masa lalu!'); window.location.href='tambah_task.php';</script>";
        exit();
    }

    $deadline_formatted = date("Y-m-d H:i:s", $deadline); // Format deadline ke MySQL format
    $priority = isset($_POST['priority']) ? 1 : 0; // Jika checkbox dicentang, priority = 1
    $status = 0; // Default status belum selesai

    $sql = "INSERT INTO tasks (user_id, name, deadline, priority, status) 
            VALUES ('$user_id', '$name', '$deadline_formatted', '$priority', '$status')";

    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Task</title>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            let localDatetime = now.toISOString().slice(0, 16); // Format untuk datetime-local
            document.getElementById("deadline").min = localDatetime;
        });
    </script>
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
        input[type="text"], input[type="datetime-local"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        label {
            display: block;
            margin-top: 10px;
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
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #007bff;
            border-radius: 10px;
            color: white;
        }
        button:hover {
            background-color: #218838;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px;
            background-color: rgba(251, 17, 0, 0.9);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <h1>Tambah Task</h1>
        <form method="POST" action="dashboard.php">
            <button type="submit" class="back-btn">Kembali</button>
        </form>
    </div>
    <br>

    <form method="POST">
        <input type="text" name="task_name" placeholder="Nama Task" required>
        <label for="deadline">Tenggat Waktu:</label>
        <input type="datetime-local" id="deadline" name="deadline" required>
        <label>
            <input type="checkbox" name="priority"> Tandai sebagai prioritas
        </label>
        <br>
        <button type="submit">Tambah</button>
    </form>
    <br>

</body>
</html>
