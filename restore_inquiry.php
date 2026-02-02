<?php
$conn = new mysqli("localhost", "root", "", "contact_us");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];

$sql = "UPDATE contact_inquiries SET archived = 0 WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: archived_inquiries.php?status=restored");
    exit();
} else {
    echo "Error restoring inquiry: " . $conn->error;
}

$conn->close();
?>