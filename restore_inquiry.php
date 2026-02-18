<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
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