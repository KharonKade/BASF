<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $news_id = intval($_GET['id']);

    $sql = "SELECT * FROM news_announcements WHERE news_id = $news_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
        
        $image_sql = "SELECT * FROM news_images WHERE news_id = $news_id";
        $image_result = $conn->query($image_sql);
        $images = [];
        while ($image = $image_result->fetch_assoc()) {
            $images[] = $image; 
        }
    } else {
        die("Error: News item not found.");
    }
} else {
    header("Location: manage_news.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $news_title = $_POST['news_title'] ?? '';
    $news_content = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $publish_date = $_POST['news_date'] ?? '';

    if (empty($news_title) || empty($news_content) || empty($publish_date)) {
        die("Error: Please fill all the required fields.");
    }

    $update_sql = "UPDATE news_announcements SET 
                    news_title = ?, 
                    news_content = ?, 
                    category = ?, 
                    publish_date = ?
                    WHERE news_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $news_title, $news_content, $category, $publish_date, $news_id);

    if ($stmt->execute()) {
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

        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'][0] != UPLOAD_ERR_NO_FILE) {
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

        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                }
            </style>
        </head>
        <body>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success!',
                        text: 'News item updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'manage_news.php';
                        }
                    });
                });
            </script>
        </body>
        </html>";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/edit_news.css?v=1.1">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
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
                                <input type="text" id="news_title" name="news_title" value="<?php echo htmlspecialchars($news['news_title']); ?>" placeholder="Enter headline" required>
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
                                    <input type="date" id="news_date" name="news_date" value="<?php echo htmlspecialchars($news['publish_date']); ?>" required>
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
                                <textarea id="description" name="description"><?php echo htmlspecialchars($news['news_content']); ?></textarea>
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
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="News Image">
                                        <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($image['image_id']); ?>">
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
                        <button type="button" class="btn-secondary-large" style="margin-right: 15px;" onclick="window.location.href='manage_news.php';">Cancel</button>
                        <button type="submit" class="btn-primary-large">Update News</button>
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