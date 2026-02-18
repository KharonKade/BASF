<?php
session_start();

if (!isset($_POST['id']) || !isset($_POST['source'])) {
    echo "Invalid request";
    exit;
}

$videoId = (int) $_POST['id'];
$source = $_POST['source'];

$allowedSources = [
    'bmx' => [
        'dbname' => 'u142318015_db_vf0t87O5',
        'username' => 'u142318015_usr_vf0t87O4',
        'password' => 'dmBI2c5QdB4*'
    ],
    'inline' => [
        'dbname' => 'u142318015_db_vf0t87O3',
        'username' => 'u142318015_usr_vf0t87O2',
        'password' => '0^Yf>YXE/C'
    ],
    'skateboard' => [
        'dbname' => 'u142318015_db_vf0t87O7',
        'username' => 'u142318015_usr_vf0t87O6',
        'password' => 'B^vC=ErJ@7'
    ]
];

if (!isset($allowedSources[$source]) || $videoId <= 0) {
    echo "Invalid request";
    exit;
}

$dbConfig = $allowedSources[$source];
$servername = "localhost";
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "Connection failed";
    exit;
}

$sessionKey = 'viewed_' . $source;

if (!isset($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = [];
}

if (!in_array($videoId, $_SESSION[$sessionKey])) {
    $conn->query("UPDATE highlight_carousel SET views = views + 1 WHERE id = $videoId");
    $_SESSION[$sessionKey][] = $videoId;
}

$result = $conn->query("SELECT views FROM highlight_carousel WHERE id = $videoId");

if ($row = $result->fetch_assoc()) {
    echo $row['views'];
} else {
    echo "0";
}

$conn->close();
?>