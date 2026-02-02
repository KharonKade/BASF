<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

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