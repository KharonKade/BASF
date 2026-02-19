<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$host = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$database = "u142318015_db_vf0t87O1";

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
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/admin_gallery.css?v=1.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <h2>Manage Gallery</h2>
            </div>
            
            <div class="filter-action-container">
                <div class="search-filters">
                    <input type="text" id="live_search" placeholder="Search title or description...">
                    <button class="btn-search"><i class="fas fa-search"></i></button>
                </div>
                
                <div class="right-actions">
                    <a href="export_gallery.php" class="btn btn-export">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                    <a href="add_gallery.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Gallery Item
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table>
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
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-limit" title="<?php echo htmlspecialchars($row['description']); ?>">
                                    <?php echo htmlspecialchars($row['description']); ?> 
                                </div>
                            </td>
                            <td>
                                <a href="view_gallery.php?id=<?php echo $row['id']; ?>" title="View">
                                    <i class="fas fa-eye"></i>
                                </a> |
                                <a href="edit_gallery.php?id=<?php echo $row['id']; ?>" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a> |
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
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

    function confirmDelete(id) {
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
                window.location.href = 'delete_gallery.php?id=' + id;
            }
        });
    }
</script>

</body>
</html>

<?php $conn->close(); ?>