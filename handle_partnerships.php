<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O2";
$password = "0^Yf>YXE/C";
$dbname = "u142318015_db_vf0t87O3";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["logo"])) {
    $uploadDir = "images/uploads/"; 
    $fileName = basename($_FILES["logo"]["name"]);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $filePath)) {
        $stmt = $conn->prepare("INSERT INTO partnerships (logo) VALUES (?)");
        $stmt->bind_param("s", $filePath);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "⚠️ Image upload failed!";
        exit;
    }
}

if (isset($_POST["delete"])) {
    $id = $_POST["id"];
    $result = $conn->query("SELECT logo FROM partnerships WHERE id='$id'");
    if ($row = $result->fetch_assoc()) {
        $filePath = $row["logo"];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $conn->query("DELETE FROM partnerships WHERE id='$id'");
    }
}

$conn->close();
header("Location: editInlinePage.php");
?>
