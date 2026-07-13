<?php
session_start();

$servername = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$dbname = "u142318015_db_vf0t87O1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security token validation failed.");
    }

    if (!empty($_POST['website_url'])) {
        die("Spam detected.");
    }

    $time_limit = 30;
    if (isset($_SESSION['last_submission_time']) && (time() - $_SESSION['last_submission_time']) < $time_limit) {
        header("Location: contactUs.php?status=error");
        exit();
    }
    $_SESSION['last_submission_time'] = time();

    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $contactNumber = trim($_POST['contactNumber']);
    $concerns = trim($_POST['concerns']);
    $message = trim($_POST['message']);
    $companyName = isset($_POST['companyName']) ? trim($_POST['companyName']) : null;

    if (empty($fullName) || empty($email) || empty($contactNumber) || empty($concerns) || empty($message)) {
        header("Location: contactUs.php?status=error");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: contactUs.php?status=error");
        exit();
    }

    $fullName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $contactNumber = htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8');
    $concerns = htmlspecialchars($concerns, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $companyName = $companyName ? htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') : null;

    $stmt = $conn->prepare("INSERT INTO contact_inquiries (full_name, email, contact_number, concerns, message, company_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullName, $email, $contactNumber, $concerns, $message, $companyName);

    if ($stmt->execute()) {
        header("Location: contactUs.php?status=success");
        exit();
    } else {
        header("Location: contactUs.php?status=error");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>