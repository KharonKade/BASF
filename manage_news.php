<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "basf_news");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['archive_id'])) {
    $archive_id = intval($_GET['archive_id']);
    $archive_sql = "UPDATE news_announcements SET status = 'archived' WHERE news_id = $archive_id";

    if ($conn->query($archive_sql)) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
            <style>body { font-family: 'Poppins', sans-serif; }</style>
        </head>
        <body>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Archived!',
                        text: 'News item has been moved to archives.',
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
        die("Error archiving news: " . $conn->error);
    }
}

$filter_category = $_GET['category'] ?? '';

$sql = "
    SELECT 
        @rownum := @rownum + 1 AS row_num, 
        news_id, news_title, category, publish_date 
    FROM news_announcements, (SELECT @rownum := 0) r 
    WHERE status = 'active'
";
if (!empty($filter_category) && strtolower($filter_category) !== 'all') {
    $filter_category_safe = $conn->real_escape_string($filter_category);
    $sql .= " AND category = '$filter_category_safe'";
}

$sql .= " ORDER BY news_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News & Announcements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/manage_event.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manage_upcoming.php"><i class="fas fa-calendar-check"></i>Events</a></li>
                <li><a href="manage_news.php"><i class="fas fa-edit"></i>News & Announcements</a></li>
                <li><a href="admin_gallery.php"><i class="fas fa-images"></i>Gallery Page</a></li>
                <li><a href="editInlinePage.php"><i class="fas fa-skating"></i>Inline Page</a></li>
                <li><a href="editBmxPage.php"><i class="fas fa-bicycle"></i>BMX Page</a></li>
                <li><a href="editSkateboardPage.php"><i class="fas fa-snowboarding"></i>Skateboard Page</a></li>
                <li><a href="view_inquiries.php"><i class="fas fa-question-circle"></i> Inquiries</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        <main class="content">
            <h2>Manage News & Announcements</h2>
             
            <div class="filter-action-container">
                <div class="search-filters">
                    <form method="GET" style="display:flex; align-items:center; gap:10px; margin:0;">
                        <label for="category">Filter by Category:</label>
                        <select name="category" id="categoryFilter" onchange="this.form.submit()">
                            <option value="All" <?php if ($filter_category === 'All' || empty($filter_category)) echo 'selected'; ?>>All</option>
                            <option value="Skateboard" <?php if ($filter_category === 'Skateboard') echo 'selected'; ?>>Skateboard</option>
                            <option value="Inline" <?php if ($filter_category === 'Inline') echo 'selected'; ?>>Inline</option>
                            <option value="BMX" <?php if ($filter_category === 'BMX') echo 'selected'; ?>>BMX</option>
                        </select>
                    </form>
                    <input type="text" id="liveSearchInput" placeholder="Search news...">
                </div>

                <div class="action-buttons">
                    <a href="export_news.php?category=<?php echo urlencode($filter_category); ?>" class="btn btn-export"><i class="fas fa-file-export"></i> Export CSV</a>
                    <a href="create_news.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create News</a>
                    <a href="archived_news.php" class="btn btn-secondary"><i class="fas fa-archive"></i> Archived News</a>
                </div>
            </div>

            <table id="newsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>News Title</th>
                        <th>Category</th>
                        <th>Publish Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="newsTableBody">
                    <?php 
                    if ($result->num_rows > 0):
                        $row_num = 1;
                        while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row_num++; ?></td> 
                            <td><?php echo $row['news_title']; ?></td>
                            <td><?php echo ucfirst($row['category']); ?></td>
                            <td><?php echo $row['publish_date']; ?></td>
                            <td>
                                <a href="view_news.php?id=<?php echo $row['news_id']; ?>" title="View">
                                    <i class="fas fa-eye"></i>
                                </a> |
                                <a href="edit_news.php?id=<?php echo $row['news_id']; ?>" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a> |
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['news_id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a> |
                                <a href="javascript:void(0);" onclick="confirmArchive(<?php echo $row['news_id']; ?>)" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; 
                    else: ?>
                        <tr><td colspan="5">No news found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        document.getElementById('liveSearchInput').addEventListener('keyup', function() {
            let query = this.value;
            let category = document.getElementById('categoryFilter').value;

            let xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_news.php?q=' + encodeURIComponent(query) + '&category=' + encodeURIComponent(category), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('newsTableBody').innerHTML = this.responseText;
                }
            };
            xhr.send();
        });

        function confirmDelete(newsId) {
            Swal.fire({
                title: 'Permanently Delete?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_news.php?id=' + newsId;
                }
            });
        }

        function confirmArchive(newsId) {
            Swal.fire({
                title: 'Archive News?',
                text: "This will move the item to the archives.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'manage_news.php?archive_id=' + newsId;
                }
            });
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>