<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
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