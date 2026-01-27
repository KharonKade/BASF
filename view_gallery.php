<?php
// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Gallery Item
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM gallery WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Gallery item not found.");
}

$gallery = $result->fetch_assoc();
$stmt->close(); // Close the first statement after use

// Get Multiple Images
$images_sql = "SELECT image_path FROM gallery_images WHERE gallery_id = ?";
$stmt = $conn->prepare($images_sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$images_result = $stmt->get_result();

$images = [];
while ($image = $images_result->fetch_assoc()) {
    $images[] = $image['image_path'];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Gallery Item</title>
    <link rel="stylesheet" href="Css/admin_gallery.css">
</head>
<body>

    <div class="admin-wrapper">
    <div class="page-header">
        <div class="header-content">
            <h1><?php echo htmlspecialchars($gallery['title']); ?></h1>
            <p class="view-description"><?php echo nl2br(htmlspecialchars($gallery['description'])); ?></p>
        </div>
        <a href="admin_gallery.php" class="btn-secondary">Return</a>
    </div>

    <div class="gallery-hero-section">
        <div class="card thumbnail-card">
            <h3>Primary Thumbnail</h3>
            <div class="thumbnail-wrapper">
                <img src="<?php echo 'images/uploads/' . basename($gallery['thumbnail']); ?>" alt="Thumbnail" class="view-thumbnail">
            </div>
        </div>

        <div class="stats-card">
            <div class="stat-item">
                <span class="stat-label">Total Images</span>
                <span class="stat-value"><?php echo count($images); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Status</span>
                <span class="status-pill active">Published</span>
            </div>
        </div>
    </div>

    <div class="gallery-content">
        <div class="content-header">
            <h3>Gallery Collection</h3>
        </div>
        
        <div class="image-grid">
            <?php if (!empty($images)) { 
                foreach ($images as $image_path) { ?>
                    <div class="grid-item" onclick="openLightbox(this)">
                        <img src="<?php echo 'images/uploads/' . basename($image_path); ?>" alt="Gallery Image">
                        <div class="image-overlay">
                            <span>View Full Size</span>
                        </div>
                    </div>
                <?php } 
            } else { ?>
                <div class="empty-state">
                    <p>No images found for this gallery.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox">&times;</span>
    <img id="lightbox-img" src="" alt="Full Size Image">
</div>

<script>
function openLightbox(element) {
    const imgSrc = element.querySelector('img').src;
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    
    lightboxImg.src = imgSrc;
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
});
</script>

</body>
</html>
