<?php
$host = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$database = "u142318015_db_vf0t87O1";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];

$conn->query("DELETE FROM gallery_images WHERE gallery_id = $id");

$conn->query("DELETE FROM gallery WHERE id = $id");

header("Location: admin_gallery.php");
exit();

$conn->close();
?>