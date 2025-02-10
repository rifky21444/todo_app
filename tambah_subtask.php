<?php
include 'koneksi.php';

if (!isset($_GET['task_id'])) {
    header("Location: dashboard.php"); // Redirect jika tidak ada task_id
    exit();
}

$task_id = $_GET['task_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subtask_name = $_POST['subtask_name'];
    if (!empty($subtask_name)) {
        $conn->query("INSERT INTO subtasks (parent_id, name, status) VALUES ('$task_id', '$subtask_name', '0')");
        header("Location: dashboard.php"); // Redirect kembali ke halaman utama
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Subtask</title>
</head>
<body>
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
            background-color:rgb(255, 0, 0);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
   <div class="top-bar">
    <h1>Tambah Subtask</h1>
    <form method="POST" action="dashboard.php">
            <button type="submit" class="back-btn">Kembali</button>
        </form>
    </div>
    <br>
    <form method="POST">
        <input type="text" name="subtask_name" placeholder="Nama Subtask" required>
        <button type="submit">Tambah</button>
    </form>
    <br>
</body>
</html>
