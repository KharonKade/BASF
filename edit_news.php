<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "basf_news");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the news ID is set in the URL
if (isset($_GET['id'])) {
    $news_id = intval($_GET['id']); // Get the news ID from the URL

    // Fetch the current news data from the database
    $sql = "SELECT * FROM news_announcements WHERE news_id = $news_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc(); // Fetch the news data
        
        // Fetch the associated images
        $image_sql = "SELECT * FROM news_images WHERE news_id = $news_id";
        $image_result = $conn->query($image_sql);
        $images = [];
        while ($image = $image_result->fetch_assoc()) {
            $images[] = $image; // Store all the image data in an array
        }
    } else {
        die("Error: News item not found.");
    }
} else {
    // If no news ID is provided, redirect back to manage news page
    header("Location: manage_news.php");
    exit();
}

// Handle form submission (update news)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $news_title = $_POST['news_title'] ?? '';
    $news_content = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $publish_date = $_POST['news_date'] ?? '';

    // Validate form inputs
    if (empty($news_title) || empty($news_content) || empty($publish_date)) {
        die("Error: Please fill all the required fields.");
    }

    // Update news data in the database
        // Update news data in the database using prepared statement
    $update_sql = "UPDATE news_announcements SET 
                    news_title = ?, 
                    news_content = ?, 
                    category = ?, 
                    publish_date = ?
                    WHERE news_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $news_title, $news_content, $category, $publish_date, $news_id);

    if ($stmt->execute()) {
    // Image deletion logic...
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $image_id) {
            $delete_image_sql = "SELECT image_path FROM news_images WHERE image_id = ?";
            $stmt_img = $conn->prepare($delete_image_sql);
            $stmt_img->bind_param("i", $image_id);
            $stmt_img->execute();
            $result = $stmt_img->get_result();

            if ($result->num_rows > 0) {
                $image = $result->fetch_assoc();
                if (file_exists($image['image_path'])) {
                    unlink($image['image_path']);
                }

                $delete_sql = "DELETE FROM news_images WHERE image_id = ?";
                $stmt_del = $conn->prepare($delete_sql);
                $stmt_del->bind_param("i", $image_id);
                $stmt_del->execute();
            }
        }
    }

    // Image upload logic...
    if (!empty($_FILES['image']['tmp_name'])) {
        $upload_dir = "images/uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (is_array($_FILES['image']['tmp_name'])) {
            foreach ($_FILES['image']['tmp_name'] as $index => $tmp_name) {
                $image_name = basename($_FILES['image']['name'][$index]);
                $image_path = $upload_dir . $image_name;
                if (move_uploaded_file($tmp_name, $image_path)) {
                    $image_sql = "INSERT INTO news_images (news_id, image_path) VALUES (?, ?)";
                    $stmt_img = $conn->prepare($image_sql);
                    $stmt_img->bind_param("is", $news_id, $image_path);
                    $stmt_img->execute();
                }
            }
        } else {
            $image_name = basename($_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $image_sql = "INSERT INTO news_images (news_id, image_path) VALUES (?, ?)";
                $stmt_img = $conn->prepare($image_sql);
                $stmt_img->bind_param("is", $news_id, $image_path);
                $stmt_img->execute();
            }
        }
    }

    header("Location: manage_news.php?message=News item updated successfully");
    exit();
} else {
    die("Error updating news: " . $stmt->error);
}
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News & Announcements</title>
    <link rel="stylesheet" href="Css/edit_news.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
</head>
<body>
    <div class="admin-wrapper">
    <div class="page-header">
        <h2>Edit News & Announcements</h2>
        <p>Update news content, categorize entries, and manage media.</p>
    </div>

    <form action="edit_news.php?id=<?php echo $news_id; ?>" method="POST" enctype="multipart/form-data" class="main-form">
        
        <div class="form-card">
            <div class="card-header">
                <h3>News Details</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="news_title">News Title</label>
                    <input type="text" id="news_title" name="news_title" value="<?php echo $news['news_title']; ?>" placeholder="Enter headline" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <div class="select-wrapper">
                            <select id="category" name="category">
                                <option value="All" <?php echo ($news['category'] == 'All') ? 'selected' : ''; ?>>All</option>
                                <option value="Skateboard" <?php echo ($news['category'] == 'Skateboard') ? 'selected' : ''; ?>>Skateboard</option>
                                <option value="Inline" <?php echo ($news['category'] == 'Inline') ? 'selected' : ''; ?>>Inline</option>
                                <option value="BMX" <?php echo ($news['category'] == 'BMX') ? 'selected' : ''; ?>>BMX</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="news_date">Publish Date</label>
                        <input type="date" id="news_date" name="news_date" value="<?php echo $news['publish_date']; ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h3>Content</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <textarea id="description" name="description"><?php echo $news['news_content']; ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h3>Media Gallery</h3>
            </div>
            <div class="card-body">
                <div class="media-section">
                    <h4>Existing Images</h4>
                    <?php if (!empty($images)): ?>
                    <div class="image-grid">
                        <?php foreach ($images as $image): ?>
                        <div class="media-item">
                            <img src="<?php echo $image['image_path']; ?>" alt="News Image">
                            <input type="hidden" name="existing_images[]" value="<?php echo $image['image_id']; ?>">
                            <button type="button" class="btn-overlay-remove" onclick="removeElement(this)">REMOVE</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                        <p class="no-data">No images uploaded yet.</p>
                    <?php endif; ?>
                    
                    <div class="upload-box">
                        <label for="image">Upload New Images</label>
                        <input type="file" id="image" name="image[]" multiple class="file-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary-large">Update News</button>
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
    
    function removeElement(button) {
        let mediaItem = button.closest(".media-item");
        let idInput = mediaItem.querySelector("input[name='existing_images[]']");
        
        if (idInput) {
            let imageId = idInput.value;
            let removeInput = document.createElement("input");
            removeInput.type = "hidden";
            removeInput.name = "delete_images[]"; 
            removeInput.value = imageId;
            document.querySelector("form").appendChild(removeInput);
        }

        mediaItem.remove();
    }
</script>
    
</body>
</html>
