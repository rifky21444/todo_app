<?php
include 'koneksi.php';
session_start();

$error = '';

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Validasi form
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Email tidak ditemukan.';
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

    input[type="email"],
    input[type="password"] {
        margin-bottom: 10px;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .show-password {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .show-password input {
        margin-right: 5px;
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

    /* Responsive Design */
    @media (max-width: 480px) {
        .container {
            padding: 15px;
        }

        input[type="email"],
        input[type="password"],
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
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div class="show-password">
                <input type="checkbox" id="togglePassword">
                <label for="togglePassword">Tampilkan Password</label>
            </div>
            <button type="submit">Login</button> 
        </form>
        <p class="message">Belum punya akun? <a href="register.php">Register</a></p>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('change', function () {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>
