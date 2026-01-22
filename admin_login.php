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
<html>
<head>
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        form {
            max-width: 300px;
            margin: auto;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }
        input[type="submit"] {
            padding: 10px;
            width: 100%;
            background-color: #2c3e50;
            color: white;
            border: none;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Admin Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
</body>
</html>
