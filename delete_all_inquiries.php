<?php
$conn = new mysqli("localhost", "root", "", "contact_us");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "DELETE FROM contact_inquiries WHERE archived = 1";

if ($conn->query($sql) === TRUE) {
    header("Location: archived_inquiries.php?status=deleted_all");
    exit();
} else {
    echo "Error deleting inquiries: " . $conn->error;
}

$conn->close();
?>