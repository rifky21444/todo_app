<?php
include 'koneksi.php';
session_start();

$error = '';

// Batasi percobaan login (opsional, bisa disimpan di DB jika ingin lebih kompleks)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cegah brute force (maksimal 5 kali percobaan)
    if ($_SESSION['login_attempts'] >= 5) {
        $error = 'Terlalu banyak percobaan login. Coba lagi nanti.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Validasi form
        if (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi.';
        } else {
            // Periksa apakah email terdaftar
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Reset login attempts
                    $_SESSION['login_attempts'] = 0;

                    // Regenerasi session untuk keamanan
                    session_regenerate_id(true);

                    // Simpan data ke session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];  // **DIPINDAHKAN KE SINI**
                    $_SESSION['email'] = $user['email'];

                    // Redirect ke halaman dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $_SESSION['login_attempts']++;
                    $error = 'Password salah.';
                }
            } else {
                $_SESSION['login_attempts']++;
                $error = 'Email tidak ditemukan.';
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
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
            width: 100%;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px rgb(0, 164, 241);
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
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            background: linear-gradient(to bottom, rgb(29, 32, 218), #2575fc);
            color: #fff;
            cursor: pointer;
        }
        .message {
            text-align: center;
            margin-top: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button> 
        </form>
        <p class="message">Belum punya akun? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
