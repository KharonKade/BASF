<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "basf_news");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = $_GET['category'] ?? 'All';

$filename = "news_export_" . date('Y-m-d') . ".csv";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen("php://output", "w");

fputcsv($output, array('ID', 'News Title', 'Category', 'Publish Date', 'Status'));

$sql = "SELECT news_id, news_title, category, publish_date, status FROM news_announcements WHERE status = 'active'";

if (!empty($category) && strtolower($category) !== 'all') {
    $category_safe = $conn->real_escape_string($category);
    $sql .= " AND category = '$category_safe'";
}

$sql .= " ORDER BY news_id DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
$conn->close();
exit();
?>