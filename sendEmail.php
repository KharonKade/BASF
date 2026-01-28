<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contact_us";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = htmlspecialchars($_POST['fullName']);
    $email = htmlspecialchars($_POST['email']);
    $contactNumber = htmlspecialchars($_POST['contactNumber']);
    $concerns = htmlspecialchars($_POST['concerns']);
    $message = htmlspecialchars($_POST['message']);
    $companyName = isset($_POST['companyName']) ? htmlspecialchars($_POST['companyName']) : null;

    $stmt = $conn->prepare("INSERT INTO contact_inquiries (full_name, email, contact_number, concerns, message, company_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullName, $email, $contactNumber, $concerns, $message, $companyName);

    if ($stmt->execute()) {
        header("Location: contactUs.html?status=success");
        exit();
    } else {
        header("Location: contactUs.html?status=error");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>