<?php
$host = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$database = "u142318015_db_vf0t87O1";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "SELECT * FROM gallery WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$images_sql = "SELECT * FROM gallery_images WHERE gallery_id = $id";
$images_result = $conn->query($images_sql);
$gallery_images = [];
while ($image = $images_result->fetch_assoc()) {
    $gallery_images[] = $image;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $conn->query("UPDATE gallery SET title='$title', description='$description' WHERE id=$id");

    if (!empty($_POST['delete_ids'])) {
        foreach ($_POST['delete_ids'] as $delete_id) {
            $delete_id = intval($delete_id); 
            $conn->query("DELETE FROM gallery_images WHERE id=$delete_id");
        }
    }

    if (!empty($_FILES["thumbnail"]["name"])) {
        $thumbnail = "images/uploads/" . basename($_FILES["thumbnail"]["name"]);
        move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $thumbnail);
        $conn->query("UPDATE gallery SET thumbnail='$thumbnail' WHERE id=$id");
    }

    if (!empty($_FILES["gallery_images"]["name"][0])) {
        foreach ($_FILES["gallery_images"]["tmp_name"] as $key => $tmp_name) {
            $image_path = "images/uploads/" . basename($_FILES["gallery_images"]["name"][$key]);
            move_uploaded_file($_FILES["gallery_images"]["tmp_name"][$key], $image_path);
            $conn->query("INSERT INTO gallery_images (gallery_id, image_path) VALUES ($id, '$image_path')");
        }
    }

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: 'Gallery item updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'admin_gallery.php?id=$id';
                }
            });
        });
    </script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gallery Item</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/edit_gallery.css?v=1.1">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="admin-container">
        
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <nav class="sidebar" id="sidebar">
            <button class="close-sidebar" id="closeSidebar"><i class="fas fa-times"></i></button>
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
            <div class="top-header">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            </div>

            <div class="admin-wrapper">
                <div class="page-header">
                    <h2>Edit Gallery Album</h2>
                    <p>Update album details, change the thumbnail, or manage gallery images.</p>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" class="main-form">
                    
                    <div class="form-card">
                        <div class="card-header">
                            <h3>Album Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Album Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" placeholder="Enter album title" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea id="description" name="description"><?php echo htmlspecialchars($row['description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="card-header">
                            <h3>Thumbnail Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Update Thumbnail Image</label>
                                <input type="file" name="thumbnail" class="file-input-bordered">
                                <p class="help-text">Leave empty to keep the current thumbnail.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="card-header">
                            <h3>Gallery Images</h3>
                        </div>
                        <div class="card-body">
                            <div class="media-section">
                                <h4>Current Images</h4>
                                <?php if (!empty($gallery_images)): ?>
                                <div class="image-grid">
                                    <?php foreach ($gallery_images as $image) { ?>
                                    <div class="media-item" id="image-row-<?php echo $image['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery Image">
                                        <button type="button" class="btn-overlay-remove" onclick="removeGalleryImage(this, '<?php echo $image['id']; ?>')">REMOVE</button>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php else: ?>
                                    <p class="no-data">No images in this album yet.</p>
                                <?php endif; ?>

                                <div class="upload-box">
                                    <label>Add New Images</label>
                                    <input type="file" name="gallery_images[]" multiple class="file-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 15px; align-items: center; justify-content: flex-end;">
                        <button type="button" class="btn-secondary-large" onclick="window.location.href='admin_gallery.php';">Cancel</button>
                        <button type="submit" class="btn-primary-large">Update Gallery Item</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

<script>
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.add('active');
        document.getElementById('sidebarOverlay').classList.add('active');
    });

    document.getElementById('closeSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('sidebarOverlay').classList.remove('active');
    });

    document.getElementById('sidebarOverlay').addEventListener('click', function() {
        document.getElementById('sidebar').classList.remove('active');
        this.classList.remove('active');
    });

    let editorInstance;

    ClassicEditor
    .create(document.querySelector('#description'))
    .then(editor => {
        editorInstance = editor;
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

    function removeGalleryImage(button, imageId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This image will be removed when you click Update. This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.querySelector('form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_ids[]'; 
                input.value = imageId;
                form.appendChild(input);
                
                button.closest('.media-item').style.display = 'none';
                
                Swal.fire(
                    'Marked for Removal!',
                    'Click "Update Gallery Item" to save changes.',
                    'success'
                )
            }
        })
    }
</script>

</body>
</html>