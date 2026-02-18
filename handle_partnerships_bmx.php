<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O4";
$password = "dmBI2c5QdB4*";
$dbname = "u142318015_db_vf0t87O5";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["logo"])) {
    $uploadDir = "images/uploads/"; // Ensure this folder exists
    $fileName = basename($_FILES["logo"]["name"]);
    $filePath = $uploadDir . $fileName;

    // Check if file upload is successful
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

// Delete image when requested
if (isset($_POST["delete"])) {
    $id = $_POST["id"];

    // Retrieve file path before deleting
    $result = $conn->query("SELECT logo FROM partnerships WHERE id='$id'");
    if ($row = $result->fetch_assoc()) {
        $filePath = $row["logo"];

        // Delete the file from the server
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete the record from the database
        $conn->query("DELETE FROM partnerships WHERE id='$id'");
    }
}

$conn->close();
header("Location: editBmxPage.php");
?>
