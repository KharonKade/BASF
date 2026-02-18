<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$news_id = $_GET['id'] ?? 0;  

if ($news_id == 0) {
    die("Invalid news ID.");
}

$news_query = "SELECT * FROM news_announcements WHERE news_id = $news_id";
$news = $conn->query($news_query);

if (!$news || $news->num_rows === 0) {
    die("News not found.");
}
$news = $news->fetch_assoc();

$images_query = "SELECT * FROM news_images WHERE news_id = $news_id";
$images = $conn->query($images_query);
if (!$images) {
    die("Error fetching images: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View News & Announcement</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/view_news.css?v=1.1">
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
                        <span class="category-badge"><?php echo ucfirst(htmlspecialchars($news['category'])); ?></span>
                        <h1><?php echo htmlspecialchars($news['news_title']); ?></h1>
                        <div class="meta-info">
                            <span class="publish-date">Published on: <?php echo htmlspecialchars($news['publish_date']); ?></span>
                        </div>
                    </div>
                    <button onclick="window.location.href='manage_news.php';" class="btn-secondary">Return</button>
                </div>

                <div class="news-grid">
                    <div class="card news-main-content">
                        <h3>Content</h3>
                        <div class="article-body">
                            <?php echo nl2br(htmlspecialchars($news['news_content'])); ?>
                        </div>
                    </div>

                    <div class="card news-media">
                        <h3>Associated Posters</h3>
                        <div class="poster-gallery">
                            <?php if ($images->num_rows > 0): ?>
                                <?php while ($image = $images->fetch_assoc()): ?>
                                    <div class="poster-item">
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="News Poster">
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <p>No poster images uploaded for this news announcement.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>