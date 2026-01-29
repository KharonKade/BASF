<?php
$conn = new mysqli("localhost", "root", "", "basf_news");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$q = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "
    SELECT 
        news_id, news_title, category, publish_date 
    FROM news_announcements 
    WHERE status = 'active'
";

if (!empty($category) && strtolower($category) !== 'all') {
    $category = $conn->real_escape_string($category);
    $sql .= " AND category = '$category'";
}

if (!empty($q)) {
    $q = $conn->real_escape_string($q);
    $sql .= " AND (news_title LIKE '%$q%' OR category LIKE '%$q%')";
}

$sql .= " ORDER BY news_id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row_num = 1;
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row_num++ . '</td>';
        echo '<td>' . htmlspecialchars($row['news_title']) . '</td>';
        echo '<td>' . ucfirst(htmlspecialchars($row['category'])) . '</td>';
        echo '<td>' . htmlspecialchars($row['publish_date']) . '</td>';
        echo '<td>
                <a href="view_news.php?id=' . $row['news_id'] . '" title="View"><i class="fas fa-eye"></i></a> |
                <a href="edit_news.php?id=' . $row['news_id'] . '" title="Edit"><i class="fas fa-edit"></i></a> |
                <a href="delete_news.php?id=' . $row['news_id'] . '" onclick="return confirm(\'Are you sure you want to delete this news item?\');" title="Delete"><i class="fas fa-trash"></i></a> |
                <a href="manage_news.php?archive_id=' . $row['news_id'] . '" onclick="return confirm(\'Archive this news item?\');" title="Archive"><i class="fas fa-archive"></i></a>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">No news found matching your search.</td></tr>';
}

$conn->close();
?>