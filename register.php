<?php
include 'koneksi.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi form
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $error = 'Username hanya boleh berisi huruf dan angka.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!preg_match('/^[a-zA-Z0-9]{8,}$/', $password)) { // Minimal 8 karakter
        $error = 'Password harus terdiri dari minimal 8 karakter huruf dan angka.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama.';
    } else {
        // Periksa apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        } else {
            // Simpan data pengguna baru ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(to bottom, rgb(29, 32, 218), #2575fc);
    }

    .container {
        max-width: 400px;
        width: 90%;
        background: #fff;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 164, 241, 0.5);
        border-radius: 10px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    input {
        margin-bottom: 10px;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    button {
        padding: 12px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        background: linear-gradient(to bottom, rgb(29, 32, 218), #2575fc);
        color: #fff;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: linear-gradient(to bottom, #1a1dcf, #1a5cf7);
    }

    .message {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }

    .error {
        color: red;
    }

    .success {
        color: green;
    }

    a {
        color: #007bff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .container {
            padding: 15px;
        }

        input,
        button {
            font-size: 14px;
            padding: 10px;
        }

        h2 {
            font-size: 20px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if ($error): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($success): ?>
            <p class="message success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required pattern="[a-zA-Z0-9]+" title="Username hanya boleh berisi huruf dan angka.">
            <input type="email" name="email" placeholder="Email address" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            <input type="password" name="password" placeholder="Password" required minlength="8" pattern="[a-zA-Z0-9]+" title="Password harus terdiri dari minimal 8 karakter huruf dan angka.">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="8" pattern="[a-zA-Z0-9]+" title="Password harus terdiri dari minimal 8 karakter huruf dan angka.">
            <button type="submit">Register</button>
        </form>
        <p class="message">Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
