<?php
$conn = new mysqli("localhost", "root", "", "contact_us");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "DELETE FROM contact_inquiries WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: view_inquiries.php?status=deleted");
} else {
    echo "Error deleting inquiry: " . $conn->error;
}

$conn->close();
?>