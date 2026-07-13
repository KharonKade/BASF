<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O6";
$password = "B^vC=ErJ@7";
$dbname = "u142318015_db_vf0t87O7";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['delete_id'])) {
    $athlete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM achievements WHERE athlete_id = $athlete_id");
    $conn->query("DELETE FROM athlete_gallery WHERE athlete_id = $athlete_id");
    $delete_query = $conn->query("DELETE FROM top_athletes WHERE id = $athlete_id");

    if ($delete_query) {
        echo "<script>alert('Athlete deleted successfully!'); window.location.href = 'editSkateboardPage.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting athlete. Please try again.'); window.location.href = 'editSkateboardPage.php';</script>";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST["edit_id"]) ? $_POST["edit_id"] : null;
    $name = $conn->real_escape_string($_POST["name"]);
    $bio = $conn->real_escape_string($_POST["bio"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $wins = $conn->real_escape_string($_POST["wins"]);
    $podium_finishes = isset($_POST["podium_finishes"]) ? $conn->real_escape_string($_POST["podium_finishes"]) : "0";
    $years_active = $conn->real_escape_string($_POST["years_active"]);
    $specialty = $conn->real_escape_string($_POST["specialty"]);
    $achievements = !empty($_POST["achievements"]) ? $_POST["achievements"] : [];
    $descriptions = isset($_POST["achievements_descriptions"]) ? $_POST["achievements_descriptions"] : [];

    $existing_image = "";
    if (!empty($id) && is_numeric($id)) {
        $result = $conn->query("SELECT image FROM top_athletes WHERE id = '$id'");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $existing_image = $row["image"];
        }
    }

    if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] == 0) {
        $image = "images/uploads/" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    } else {
        $image = $existing_image; 
    }

    if (!empty($id) && is_numeric($id) && $id > 0)  {
        $query = "UPDATE top_athletes SET 
                    name='$name', bio='$bio', description='$description', wins='$wins', 
                    podium_finishes='$podium_finishes', years_active='$years_active', specialty='$specialty', image='$image' 
                  WHERE id='$id'";

        if ($conn->query($query) === TRUE) {
            $conn->query("DELETE FROM achievements WHERE athlete_id='$id'");
            foreach ($achievements as $index => $achievement) {
                $achievement_title = $conn->real_escape_string($achievement);
                $achievement_desc = isset($descriptions[$index]) ? $conn->real_escape_string($descriptions[$index]) : "";
                $conn->query("INSERT INTO achievements (athlete_id, title, description) VALUES ('$id', '$achievement_title', '$achievement_desc')");
            }

            if (!empty($_POST['deleted_images'])) {
                $deletedImages = explode(',', $_POST['deleted_images']);
                foreach ($deletedImages as $imageId) {
                    $stmt = $conn->prepare("DELETE FROM athlete_gallery WHERE id = ?");
                    $stmt->bind_param("i", $imageId);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            if (!empty($_POST["gallery_image_ids"])) {
                foreach ($_POST["gallery_image_ids"] as $key => $gallery_id) {
                    if (!empty($gallery_id) && is_numeric($gallery_id)) { 
                        $existing_image = $_POST["gallery_existing_images"][$key];
                        if (!empty($_FILES["athlete_gallery"]["name"][$key]) && $_FILES["athlete_gallery"]["error"][$key] == 0) {
                            $new_image_path = "images/uploads/" . basename($_FILES["athlete_gallery"]["name"][$key]);
                            move_uploaded_file($_FILES["athlete_gallery"]["tmp_name"][$key], $new_image_path);
                            $existing_image = $new_image_path;
                        }
            
                        $updated_desc = $conn->real_escape_string($_POST["gallery_descriptions"][$key]);
                        $result = $conn->query("SELECT * FROM athlete_gallery WHERE id='$gallery_id'");
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            if ($row["image"] != $existing_image || $row["description"] != $updated_desc) {
                                $conn->query("UPDATE athlete_gallery SET image='$existing_image', description='$updated_desc' WHERE id='$gallery_id'");
                            }
                        }
                    }
                }
            }
            
            if (!empty($_FILES["athlete_gallery"]["name"])) {
                foreach ($_FILES["athlete_gallery"]["tmp_name"] as $key => $tmp_name) {
                    if (!empty($_FILES["athlete_gallery"]["name"][$key]) && $_FILES["athlete_gallery"]["error"][$key] == 0) {
                        $gallery_image = "images/uploads/" . basename($_FILES["athlete_gallery"]["name"][$key]);
                        move_uploaded_file($tmp_name, $gallery_image);

                        $gallery_description = isset($_POST["gallery_descriptions"][$key]) ? $conn->real_escape_string($_POST["gallery_descriptions"][$key]) : '';
                        $check_existing = $conn->query("SELECT id FROM athlete_gallery WHERE athlete_id='$id' AND image='$gallery_image'");
                        if ($check_existing->num_rows == 0) {  
                            $conn->query("INSERT INTO athlete_gallery (athlete_id, image, description) VALUES ('$id', '$gallery_image', '$gallery_description')");
                        }
                    }
                }
            }

            $page = $_POST['page'] ?? 1;
            header("Location: editSkateboardPage.php?page=" . $page);
            exit();
        } else {
            echo "Error updating athlete: " . $conn->error;
        }
    } else {
        $image = "";
        if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] == 0) {
            $image = "images/uploads/" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        $query = "INSERT INTO top_athletes (name, bio, description, wins, podium_finishes, years_active, specialty, image) 
                  VALUES ('$name', '$bio', '$description', '$wins', '$podium_finishes', '$years_active', '$specialty', '$image')";

        if ($conn->query($query) === TRUE) {
            $athlete_id = $conn->insert_id;
            foreach ($achievements as $index => $achievement) {
                $achievement_title = $conn->real_escape_string($achievement);
                $achievement_desc = isset($descriptions[$index]) ? $conn->real_escape_string($descriptions[$index]) : "";
                $conn->query("INSERT INTO achievements (athlete_id, title, description) VALUES ('$athlete_id', '$achievement_title', '$achievement_desc')");
            }

            if (!empty($_FILES["athlete_gallery"]["name"][0])) {
                foreach ($_FILES["athlete_gallery"]["tmp_name"] as $key => $tmp_name) {
                    if ($_FILES["athlete_gallery"]["error"][$key] == 0) {
                        $gallery_image = "images/uploads/" . basename($_FILES["athlete_gallery"]["name"][$key]);
                        move_uploaded_file($tmp_name, $gallery_image);

                        $gallery_description = isset($_POST["gallery_descriptions"][$key]) ? $conn->real_escape_string($_POST["gallery_descriptions"][$key]) : '';
                        $check_existing = $conn->query("SELECT id FROM athlete_gallery WHERE athlete_id='$id' AND image='$gallery_image'");
                        if ($check_existing->num_rows == 0) {  
                            $conn->query("INSERT INTO athlete_gallery (athlete_id, image, description) VALUES ('$athlete_id', '$gallery_image', '$gallery_description')");
                        }
                    }
                }
            }

            
            header("Location: editSkateboardPage.php");
            exit();
        } else {
            echo "Error inserting athlete: " . $conn->error;
        }
    }
}

$conn->close();
?>
