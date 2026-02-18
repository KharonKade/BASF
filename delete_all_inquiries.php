<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
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