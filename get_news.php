<?php
$conn = new mysqli("localhost", "root", "", "basf_news");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['q']) ? $_GET['q'] : '';

$sql = "SELECT * FROM news_announcements WHERE status = 'active' AND news_title LIKE ? ORDER BY publish_date DESC";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $search . "%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $news_title = $row['news_title'];
        $news_content = $row['news_content'];
        $publish_date = $row['publish_date'];
        $image_path = '';

        $publish_date_obj = new DateTime($publish_date);
        $formatted_publish_date = $publish_date_obj->format('l, F j, Y');

        $news_id = $row['news_id'];
        $image_sql = "SELECT * FROM news_images WHERE news_id = '$news_id' LIMIT 1";
        $image_result = $conn->query($image_sql);
        if ($image_result->num_rows > 0) {
            $image_row = $image_result->fetch_assoc();
            $image_path = $image_row['image_path'];
        }

        echo '
            <div class="news-item">
                <img src="' . $image_path . '" alt="' . $news_title . '">
                <div class="news-item-content">
                    <h3>' . $news_title . '</h3>
                        <p class="news-desc">' . substr(strip_tags($news_content), 0, 50) . '...</p>
                        <p class="publish-date">' . $formatted_publish_date . '</p>
                        <a class="read-more" href="newsPages.php?id=' . $news_id . '">Read More</a>
                </div>
            </div>';
    }
} else {
    echo '<p style="width:100%; text-align:center; padding: 20px;">No news found matching your search.</p>';
}

$stmt->close();
$conn->close();
?>