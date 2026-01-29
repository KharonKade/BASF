<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM gallery";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/admin_gallery.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .filter-action-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-filters {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-filters input[type="text"] {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-search {
            padding: 12px 12px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .right-actions {
            display: flex;
            gap: 10px;
        }

        .btn-export {
            background-color: #27ae60;
            color: #fff;
        }

        .btn-export:hover {
            background-color: #219150;
            color: #fff;
        }
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
            <h2>Manage Gallery</h2>
            
            <div class="filter-action-container">
                <div class="search-filters">
                    <input type="text" id="live_search" placeholder="Search title or description...">
                    <button class="btn-search"><i class="fas fa-search"></i></button>
                </div>
                
                <div class="right-actions">
                    <a href="export_gallery.php" class="btn btn btn-export">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                    <a href="add_gallery.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Gallery Item
                    </a>
                </div>
            </div>

            <table border="1">
                <thead>
                    <tr>
                        <th class="col-thumb">Thumbnail</th>
                        <th class="col-title">Title</th>
                        <th class="col-desc">Description</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="gallery_table_body">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?php echo 'images/uploads/' . basename($row['thumbnail']); ?>" width="100"></td>
                    <td>
                        <div class="text-limit" title="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php echo $row['title']; ?>
                        </div>
                    </td>
                    <td>
                        <div class="text-limit" title="<?php echo htmlspecialchars($row['description']); ?>">
                            <?php echo $row['description']; ?> 
                        </div>
                    </td>
                    <td>
                        <a href="view_gallery.php?id=<?php echo $row['id']; ?>" title="View">
                            <i class="fas fa-eye"></i>
                        </a> |
                        <a href="edit_gallery.php?id=<?php echo $row['id']; ?>" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a> |
                        <a href="delete_gallery.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        $("#live_search").on("keyup", function(){
            var input = $(this).val();
            
            $.ajax({
                url: "search_gallery.php",
                method: "POST",
                data: {input: input},
                success: function(data){
                    $("#gallery_table_body").html(data);
                }
            });
        });
    });
</script>

</body>
</html>

<?php $conn->close(); ?>