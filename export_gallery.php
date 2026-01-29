<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=gallery_data.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array('Title', 'Description', 'Uploaded At'));

$sql = "SELECT title, description, uploaded_at FROM gallery";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$conn->close();
exit();
?>