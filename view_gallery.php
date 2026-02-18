<?php
$host = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$database = "u142318015_db_vf0t87O1";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
$stmt->close();

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/admin_gallery.css?v=1.2">
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
        </main>
    </div>

    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="close-lightbox">&times;</span>
        <img id="lightbox-img" src="" alt="Full Size Image">
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