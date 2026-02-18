<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O4";
$password = "dmBI2c5QdB4*";
$dbname = "u142318015_db_vf0t87O5";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DELETE athlete logic - Place this at the top
if (isset($_GET['delete_id'])) {
    $athlete_id = intval($_GET['delete_id']);

    // Delete related achievements
    $conn->query("DELETE FROM achievements WHERE athlete_id = $athlete_id");

    // Delete related gallery images
    $conn->query("DELETE FROM athlete_gallery WHERE athlete_id = $athlete_id");

    // Delete the athlete
    $delete_query = $conn->query("DELETE FROM top_athletes WHERE id = $athlete_id");

    if ($delete_query) {
        echo "<script>alert('Athlete deleted successfully!'); window.location.href = 'editBmxPage.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting athlete. Please try again.'); window.location.href = 'editBmxPage.php';</script>";
        exit();
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST["edit_id"]) ? $_POST["edit_id"] : null;
    $name = $conn->real_escape_string($_POST["name"]);
    $bio = $conn->real_escape_string($_POST["bio"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $wins = $conn->real_escape_string($_POST["wins"]);
    $podium_finishes = isset($_POST["podium_finishes"]) ? $conn->real_escape_string($_POST["podium_finishes"]) : "0";
    $years_active = $conn->real_escape_string($_POST["years_active"]);
    $specialty = $conn->real_escape_string($_POST["specialty"]);

    // Handle achievements
    $achievements = !empty($_POST["achievements"]) ? $_POST["achievements"] : [];
    $descriptions = isset($_POST["achievements_descriptions"]) ? $_POST["achievements_descriptions"] : [];

    $existing_image = "";

    // If editing, fetch existing image
    if (!empty($id) && is_numeric($id)) {
        $result = $conn->query("SELECT image FROM top_athletes WHERE id = '$id'");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $existing_image = $row["image"];
        }
    }

    // Handle profile image upload
    if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] == 0) {
        $image = "images/uploads/" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    } else {
        $image = $existing_image; // Keep existing image if no new upload
    }

    // Check if updating or inserting new record
    if (!empty($id) && is_numeric($id) && $id > 0)  {
        // Update athlete record
        $query = "UPDATE top_athletes SET 
                    name='$name', bio='$bio', description='$description', wins='$wins', 
                    podium_finishes='$podium_finishes', years_active='$years_active', specialty='$specialty', image='$image' 
                  WHERE id='$id'";

        if ($conn->query($query) === TRUE) {
            // Update achievements
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
            

            // Handle gallery images update
            if (!empty($_POST["gallery_image_ids"])) {
                foreach ($_POST["gallery_image_ids"] as $key => $gallery_id) {
                    if (!empty($gallery_id) && is_numeric($gallery_id)) { // Ensure ID is valid
                        $existing_image = $_POST["gallery_existing_images"][$key]; // Default to existing image
                        
                        // Check if a new image was uploaded
                        if (!empty($_FILES["athlete_gallery"]["name"][$key]) && $_FILES["athlete_gallery"]["error"][$key] == 0) {
                            $new_image_path = "images/uploads/" . basename($_FILES["athlete_gallery"]["name"][$key]);
                            move_uploaded_file($_FILES["athlete_gallery"]["tmp_name"][$key], $new_image_path);
                            $existing_image = $new_image_path;
                        }
            
                        $updated_desc = $conn->real_escape_string($_POST["gallery_descriptions"][$key]);
            
                        // Check if the current image is actually changing
                        $result = $conn->query("SELECT * FROM athlete_gallery WHERE id='$gallery_id'");
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            if ($row["image"] != $existing_image || $row["description"] != $updated_desc) {
                                // Only update if something has changed
                                $conn->query("UPDATE athlete_gallery SET image='$existing_image', description='$updated_desc' WHERE id='$gallery_id'");
                            }
                        }
                    }
                }
            }
            

            // Append new images only if they are actually uploaded
            if (!empty($_FILES["athlete_gallery"]["name"])) {
                foreach ($_FILES["athlete_gallery"]["tmp_name"] as $key => $tmp_name) {
                    if (!empty($_FILES["athlete_gallery"]["name"][$key]) && $_FILES["athlete_gallery"]["error"][$key] == 0) {
                        $gallery_image = "images/uploads/" . basename($_FILES["athlete_gallery"]["name"][$key]);
                        move_uploaded_file($tmp_name, $gallery_image);

                        $gallery_description = isset($_POST["gallery_descriptions"][$key]) ? $conn->real_escape_string($_POST["gallery_descriptions"][$key]) : '';

                        // Ensure we insert only if the image does not already exist
                        $check_existing = $conn->query("SELECT id FROM athlete_gallery WHERE athlete_id='$id' AND image='$gallery_image'");
                        if ($check_existing->num_rows == 0) {  
                            $conn->query("INSERT INTO athlete_gallery (athlete_id, image, description) VALUES ('$id', '$gallery_image', '$gallery_description')");
                        }
                    }
                }
            }

            $page = $_POST['page'] ?? 1;
            header("Location: editBmxPage.php?page=" . $page);
            exit();
        } else {
            echo "Error updating athlete: " . $conn->error;
        }
    } else {
        // New athlete entry
        $image = "";
        if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] == 0) {
            $image = "images/uploads/" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        // Insert into `top_athletes`
        $query = "INSERT INTO top_athletes (name, bio, description, wins, podium_finishes, years_active, specialty, image) 
                  VALUES ('$name', '$bio', '$description', '$wins', '$podium_finishes', '$years_active', '$specialty', '$image')";

        if ($conn->query($query) === TRUE) {
            $athlete_id = $conn->insert_id;

            // Insert achievements
            foreach ($achievements as $index => $achievement) {
                $achievement_title = $conn->real_escape_string($achievement);
                $achievement_desc = isset($descriptions[$index]) ? $conn->real_escape_string($descriptions[$index]) : "";
                $conn->query("INSERT INTO achievements (athlete_id, title, description) VALUES ('$athlete_id', '$achievement_title', '$achievement_desc')");
            }

            // Ensure we only insert new images, not duplicate existing ones
            if (!empty($_FILES["athlete_gallery"]["name"][0])) {
                foreach ($_FILES["athlete_gallery"]["tmp_name"] as $key => $tmp_name) {
                    if ($_FILES["athlete_gallery"]["error"][$key] == 0) {
                        $gallery_image = "images/uploads/" . basename($_FILES["athlete_gallery"]["name"][$key]);
                        move_uploaded_file($tmp_name, $gallery_image);

                        $gallery_description = isset($_POST["gallery_descriptions"][$key]) ? $conn->real_escape_string($_POST["gallery_descriptions"][$key]) : '';

                        // Ensure we are adding only **NEW** gallery images
                        $check_existing = $conn->query("SELECT id FROM athlete_gallery WHERE athlete_id='$id' AND image='$gallery_image'");
                        if ($check_existing->num_rows == 0) {  
                            // Only insert if this image does not already exist
                            $conn->query("INSERT INTO athlete_gallery (athlete_id, image, description) VALUES ('$athlete_id', '$gallery_image', '$gallery_description')");
                        }
                    }
                }
            }

            
            header("Location: editBmxPage.php");
            exit();
        } else {
            echo "Error inserting athlete: " . $conn->error;
        }
    }
}

$conn->close();
?>
