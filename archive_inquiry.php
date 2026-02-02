<?php
$conn = new mysqli("localhost", "root", "", "contact_us");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "UPDATE contact_inquiries SET archived = 1 WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: view_inquiries.php?status=archived");
} else {
    echo "Error archiving inquiry: " . $conn->error;
}

$conn->close();
?>