<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "SELECT * FROM gallery WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Fetch gallery images
$images_sql = "SELECT * FROM gallery_images WHERE gallery_id = $id";
$images_result = $conn->query($images_sql);
$gallery_images = [];
while ($image = $images_result->fetch_assoc()) {
    $gallery_images[] = $image;
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Update Title & Description
    $conn->query("UPDATE gallery SET title='$title', description='$description' WHERE id=$id");

    // Update Thumbnail if new file is uploaded
    if (!empty($_FILES["thumbnail"]["name"])) {
        $thumbnail = "images/uploads/" . basename($_FILES["thumbnail"]["name"]);
        move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $thumbnail);
        $conn->query("UPDATE gallery SET thumbnail='$thumbnail' WHERE id=$id");
    }

    // Handle New Gallery Images Upload
    if (!empty($_FILES["gallery_images"]["name"][0])) {
        foreach ($_FILES["gallery_images"]["tmp_name"] as $key => $tmp_name) {
            $image_path = "images/uploads/" . basename($_FILES["gallery_images"]["name"][$key]);
            move_uploaded_file($_FILES["gallery_images"]["tmp_name"][$key], $image_path);
            $conn->query("INSERT INTO gallery_images (gallery_id, image_path) VALUES ($id, '$image_path')");
        }
    }

    echo "<script>alert('Gallery item updated successfully!'); window.location='admin_gallery.php';</script>";
}

// Handle Deleting Individual Gallery Images
if (isset($_GET['delete_image'])) {
    $delete_id = $_GET['delete_image'];
    $conn->query("DELETE FROM gallery_images WHERE id=$delete_id");
    echo "<script>window.location='edit_gallery.php?id=$id';</script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/edit_gallery.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <title>Edit Gallery Item</title>
</head>
<body>
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
                        <div class="media-item">
                            <img src="<?php echo $image['image_path']; ?>" alt="Gallery Image">
                            <input type="hidden" name="existing_ids[]" value="<?php echo $image['id']; ?>">
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

        <div class="form-actions">
            <button type="submit" class="btn-primary-large">Update Gallery Item</button>
        </div>
    </form>
</div>

<script>
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
        if(confirm("Are you sure you want to remove this image?")) {
            const form = document.querySelector('form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_ids[]'; 
            input.value = imageId;
            form.appendChild(input);
            
            button.closest('.media-item').remove();
        }
    }
</script>

</body>
</html>
