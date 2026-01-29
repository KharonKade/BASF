<?php
session_start();

// Check admin session
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // Handle Thumbnail
    $target_dir = "images/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $thumbnail_name = basename($_FILES["thumbnail"]["name"]);
    $thumbnail_path = $target_dir . time() . "_thumb_" . $thumbnail_name; // unique name
    
    if(move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $thumbnail_path)) {
        $sql = "INSERT INTO gallery (title, description, thumbnail) VALUES ('$title', '$description', '$thumbnail_path')";
        
        if ($conn->query($sql) === TRUE) {
            $gallery_id = $conn->insert_id;

            // Handle Multiple Images
            if(isset($_FILES['images']['name']) && count($_FILES['images']['name']) > 0) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] == 0) {
                        $img_name = basename($_FILES['images']['name'][$key]);
                        $image_path = $target_dir . time() . "_" . $key . "_" . $img_name;
                        
                        if(move_uploaded_file($tmp_name, $image_path)) {
                            $conn->query("INSERT INTO gallery_images (gallery_id, image_path) VALUES ('$gallery_id', '$image_path')");
                        }
                    }
                }
            }

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Gallery item added successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'admin_gallery.php';
                        }
                    });
                });
            </script>";
        } else {
             echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', '" . $conn->error . "', 'error');
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Upload Failed', 'Failed to upload thumbnail.', 'error');
            });
        </script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gallery Item</title>
    <link rel="stylesheet" href="Css/add_gallery.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
</head>
<body>

<div class="admin-container">
    <nav class="sidebar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_upcoming.php"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="manage_news.php"><i class="fas fa-edit"></i> News & Announcements</a></li>
            <li><a href="admin_gallery.php"><i class="fas fa-images"></i> Gallery Page</a></li>
            <li><a href="editInlinePage.php"><i class="fas fa-skating"></i> Inline Page</a></li>
            <li><a href="editBmxPage.php"><i class="fas fa-bicycle"></i> BMX Page</a></li>
            <li><a href="editSkateboardPage.php"><i class="fas fa-snowboarding"></i> Skateboard Page</a></li>
            <li><a href="view_inquiries.php"><i class="fas fa-question-circle"></i> Inquiries</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <main class="content">
        <div class="admin-wrapper">
            <div class="page-header">
                <h2>Add New Gallery Item</h2>
                <p>Create a new album or collection for the gallery.</p>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="main-form">
                
                <div class="form-card">
                    <div class="card-header">
                        <h3>Gallery Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" placeholder="Enter gallery title" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="card-header">
                        <h3>Media Uploads</h3>
                    </div>
                    <div class="card-body">
                        <div class="media-section">
                            <h4>Thumbnail Image</h4>
                            <div class="upload-box" id="thumbnail-box">
                                <label for="thumbnail" class="upload-label">
                                    <i class="fas fa-image"></i>
                                    <span>Click to upload main thumbnail</span>
                                </label>
                                <input type="file" name="thumbnail" id="thumbnail" required class="file-input">
                                <p class="file-name" id="thumbnail-name"></p>
                            </div>
                        </div>

                        <div class="media-section">
                            <h4>Gallery Images</h4>
                            <div class="upload-box" id="images-box">
                                <label for="images" class="upload-label">
                                    <i class="fas fa-images"></i>
                                    <span>Click to upload multiple images</span>
                                </label>
                                <input type="file" name="images[]" id="images" multiple required class="file-input">
                                <p class="file-name" id="images-name"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="history.back();">Cancel</button>
                    <button type="submit" class="btn-primary-large">Publish Gallery</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    let editorInstance;

    ClassicEditor
    .create(document.querySelector('#description'))
    .then(editor => {
        editorInstance = editor;
        editor.ui.view.editable.element.parentElement.style.display = 'block';
    })
    .catch(error => {
        console.error(error);
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        try {
            if (editorInstance) {
                document.querySelector('#description').value = editorInstance.getData();
            }
        } catch (error) {
            console.error("CKEditor content sync failed:", error);
        }
    });

    // File Input UI Logic
    function handleFileSelect(inputId, displayId) {
        const input = document.getElementById(inputId);
        const display = document.getElementById(displayId);
        
        input.addEventListener('change', function(e) {
            if (this.files && this.files.length > 1) {
                display.textContent = this.files.length + " files selected";
            } else if (this.files && this.files.length === 1) {
                display.textContent = this.files[0].name;
            } else {
                display.textContent = "";
            }
            
            // Visual feedback on the box
            input.parentElement.classList.add('has-file');
        });
    }

    handleFileSelect('thumbnail', 'thumbnail-name');
    handleFileSelect('images', 'images-name');
</script>
</body>
</html>