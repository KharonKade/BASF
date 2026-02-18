<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O4";
$password = "dmBI2c5QdB4*";
$dbname = "u142318015_db_vf0t87O5";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// ADD NEW RECORD (INSERT)
if (isset($_POST["title"]) && !isset($_POST["id"])) {  // Check if ID is missing to avoid duplicate inserts
    $title = $conn->real_escape_string($_POST["title"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $video = "images/uploads/" . basename($_FILES["video"]["name"]);
    move_uploaded_file($_FILES["video"]["tmp_name"], $video);
    $conn->query("INSERT INTO highlight_carousel (video, title, description) VALUES ('$video', '$title', '$description')");
}

// UPDATE EXISTING RECORD
if (isset($_POST["id"])) { 
    $id = $_POST["id"];
    $title = $conn->real_escape_string($_POST["title"]);
    $description = $conn->real_escape_string($_POST["description"]);

    // If a new video is uploaded, update it
    if (!empty($_FILES["video"]["name"])) {
        $video = "images/uploads/" . basename($_FILES["video"]["name"]);
        move_uploaded_file($_FILES["video"]["tmp_name"], $video);
        $query = "UPDATE highlight_carousel SET video='$video', title='$title', description='$description' WHERE id='$id'";
    } else {
        $query = "UPDATE highlight_carousel SET title='$title', description='$description' WHERE id='$id'";
    }

    $conn->query($query);
}

// DELETE RECORD
if (isset($_GET["delete_id"])) {
    $id = $_GET["delete_id"];
    $conn->query("DELETE FROM highlight_carousel WHERE id='$id'");
}

$conn->close();
header("Location: editBmxPage.php");
exit(); // Ensure script stops execution
?>
