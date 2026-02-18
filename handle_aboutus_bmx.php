<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O4";
$password = "dmBI2c5QdB4*";
$dbname = "u142318015_db_vf0t87O5";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $about_us = $conn->real_escape_string($_POST["about_us"]);
    $conn->query("UPDATE content SET content='$about_us' WHERE section='about_us'");
}
$conn->close();
header("Location: editBmxPage.php");
?>
