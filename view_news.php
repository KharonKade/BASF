<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "basf_news");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the news ID from the URL (ensure it's properly sanitized)
$news_id = $_GET['id'] ?? 0;  // Default to 0 if 'id' is not present

if ($news_id == 0) {
    die("Invalid news ID.");
}

// Fetch news details
$news_query = "SELECT * FROM news_announcements WHERE news_id = $news_id";
$news = $conn->query($news_query);

// Handle no result scenario
if (!$news || $news->num_rows === 0) {
    die("News not found.");
}
$news = $news->fetch_assoc();

// Fetch poster images for news (if any)
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
    <link rel="stylesheet" href="Css/view_news.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-wrapper">
    <div class="page-header">
        <div class="header-content">
            <span class="category-badge"><?php echo ucfirst(htmlspecialchars($news['category'])); ?></span>
            <h1><?php echo htmlspecialchars($news['news_title']); ?></h1>
            <div class="meta-info">
                <span class="publish-date">Published on: <?php echo htmlspecialchars($news['publish_date']); ?></span>
            </div>
        </div>
        <button onclick="history.back()" class="btn-secondary">Return</button>
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
</body>
</html>

<?php $conn->close(); ?>
