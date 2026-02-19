<?php
session_start();
$admin_username = "admin";
$admin_hashed_password = '$2y$10$63CGLTs10sIm7AeZ/gcmzOlCJewedw0o9Iykk.1sAuXA0yq5GFk.2';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $admin_username) {
        if (password_verify($password, $admin_hashed_password)) {
            $_SESSION['is_admin'] = true;
            header("Location: admin.php");
            exit();
        } else {
            $error = "Invalid login credentials.";
        }
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background-color: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #2c3e50;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #ccd1d9;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }
        input[type="submit"] {
            padding: 12px;
            width: 100%;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #1a252f;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 16px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>