<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed");
}

if (isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $sql = "UPDATE upcoming_events SET clicks = clicks + 1 WHERE id = $event_id";
    $conn->query($sql);
}
$conn->close();
?>