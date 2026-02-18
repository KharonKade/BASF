<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O4";
$password = "dmBI2c5QdB4*";
$dbname = "u142318015_db_vf0t87O5";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"]) && !isset($_POST["edit_id"])) {
    // Insert new record
    $name = $conn->real_escape_string($_POST["name"]);
    $role = $conn->real_escape_string($_POST["role"]);
    $image = "images/uploads/" . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    $conn->query("INSERT INTO community_leaders (name, role, image) VALUES ('$name', '$role', '$image')");
}

if (!empty($_POST["edit_id"])) {
    // Update existing record
    $id = $conn->real_escape_string($_POST["edit_id"]);
    $name = $conn->real_escape_string($_POST["name"]);
    $role = $conn->real_escape_string($_POST["role"]);
    $query = "UPDATE community_leaders SET name='$name', role='$role' WHERE id='$id'";

    if (!empty($_FILES["image"]["name"])) {
        $image = "images/uploads/" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        $query = "UPDATE community_leaders SET name='$name', role='$role', image='$image' WHERE id='$id'";
    }

    $conn->query($query);
}

if (isset($_POST["delete"])) {
    $id = $conn->real_escape_string($_POST["id"]);
    $conn->query("DELETE FROM community_leaders WHERE id='$id'");
}

$conn->close();
header("Location: editBmxPage.php");
exit;

?>