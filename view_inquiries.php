<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inquiries</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/view_inquiries.css?v=1.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            <?php
            $servername = "localhost";
            $username = "u142318015_usr_vf0t87O1";
            $password = "W1xz8gB^";
            $dbname = "u142318015_db_vf0t87O1";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            echo "<div class='content-wrapper'>";
            echo "<h2>Contact Inquiries</h2>";

            $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
            $concernResult = $conn->query("SELECT DISTINCT concerns FROM contact_inquiries");
            $concernOptions = '';
            while ($cRow = $concernResult->fetch_assoc()) {
                $selected = ($filter === $cRow['concerns']) ? 'selected' : '';
                $concernOptions .= "<option value='" . htmlspecialchars($cRow['concerns']) . "' $selected>" . htmlspecialchars($cRow['concerns']) . "</option>";
            }

            echo "
            <div class='filter-action-container'>
                <div class='search-filters'>
                    <form method='get' id='filterForm' style='margin:0;'>
                        <select name='filter' id='filter' onchange='this.form.submit()'>
                            <option value=''>All</option>
                            $concernOptions
                        </select>
                    </form>
                    
                    <input type='text' id='live_search' placeholder='Search name, email, or message...'>
                </div>

                <div class='action-buttons'>
                    <a href='archived_inquiries.php' class='btn btn-secondary'><i class='fas fa-archive'></i> Archived Inquiries</a>
                </div>
            </div>";

            echo "<div class='table-responsive'>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Concerns</th>
                                <th>Message</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id='inquiries_table_body'>";

            $sql = "SELECT id, full_name, email, contact_number, concerns, message, submitted_at, archived FROM contact_inquiries WHERE archived = 0";
            if (!empty($filter)) {
                $sql .= " AND concerns = '" . $conn->real_escape_string($filter) . "'";
            }
            $sql .= " ORDER BY id DESC";
            
            $result = $conn->query($sql);
            $counter = 1;

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $shortMessage = strlen($row["message"]) > 25 ? substr($row["message"], 0, 25) . '...' : $row["message"];
                    
                    echo "<tr>
                            <td>" . $counter . "</td>
                            <td>" . htmlspecialchars($row["full_name"]) . "</td>
                            <td>" . htmlspecialchars($row["email"]) . "</td>
                            <td>" . htmlspecialchars($row["contact_number"]) . "</td>
                            <td>" . htmlspecialchars($row["concerns"]) . "</td>
                            <td>" . htmlspecialchars($shortMessage) . "</td>
                            <td>" . htmlspecialchars($row["submitted_at"]) . "</td>
                            <td>
                                <a href='view_message.php?id=" . $row["id"] . "' title='View'><i class='fas fa-eye'></i></a> |
                                <a href='javascript:void(0);' onclick='confirmArchive(" . $row["id"] . ")' title='Archive'><i class='fas fa-box-archive'></i></a> |
                                <a href='javascript:void(0);' onclick='confirmDelete(" . $row["id"] . ")' title='Delete'><i class='fas fa-trash'></i></a>
                            </td>
                          </tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='8' style='text-align:center;'>No inquiries found.</td></tr>";
            }

            echo "</tbody></table></div>";
            echo "</div>";

            $conn->close();
            ?>
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

        document.getElementById('live_search').addEventListener('keyup', function() {
            var searchTerm = this.value;
            var filterValue = document.getElementById('filter').value;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'search_inquiries.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('inquiries_table_body').innerHTML = xhr.responseText;
                }
            };
            
            xhr.send('search=' + encodeURIComponent(searchTerm) + '&filter=' + encodeURIComponent(filterValue));
        });

        function confirmArchive(id) {
            Swal.fire({
                title: 'Archive Inquiry?',
                text: "This inquiry will be moved to the archives.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'archive_inquiry.php?id=' + id;
                }
            });
        }

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
                    window.location.href = 'delete_inquiry.php?id=' + id;
                }
            });
        }
    </script>

    <?php if (isset($_GET['status'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($_GET['status'] == 'archived'): ?>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Inquiry has been archived.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'view_inquiries.php';
                        }
                    });
                <?php elseif ($_GET['status'] == 'deleted'): ?>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Inquiry has been deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'view_inquiries.php';
                        }
                    });
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>