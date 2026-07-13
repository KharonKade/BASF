<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $news_id = intval($_GET['id']); 
    $archive_sql = "UPDATE news_announcements SET status = 'archived' WHERE news_id = $news_id";

    if ($conn->query($archive_sql)) {
        header("Location: manage_news.php?message=News archived successfully");
        exit();
    } else {
        die("Error archiving news: " . $conn->error);
    }
} else {
    die("No news ID provided.");
}

$conn->close();
?>
