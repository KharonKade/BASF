<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT 
        @rownum := @rownum + 1 AS row_num, 
        id, event_name, location, category, registration 
    FROM upcoming_events, (SELECT @rownum := 0) r 
    WHERE status = 'archived'
    ORDER BY id DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Events</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/archived_events.css?v=1.1">
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
                <h2>Archived Events</h2>
            </div>

            <form method="post" action="delete_all_events.php">
                <button type="submit" name="delete_all" class="delete-all-btn">Delete All</button>
            </form>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Name</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Registration</th>
                                <th>Schedules</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $row_num = 1;
                            while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row_num++; ?></td>
                                <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo ucfirst($row['category']); ?></td>
                                <td><?php echo $row['registration'] == 1 ? 'Enabled' : 'Disabled'; ?></td>
                                <td>
                                    <?php
                                    $schedule_sql = "SELECT * FROM event_schedules WHERE event_id = " . $row['id'];
                                    $schedule_result = $conn->query($schedule_sql);
                                    if ($schedule_result->num_rows > 0) {
                                        while ($schedule = $schedule_result->fetch_assoc()) {
                                            echo "Date: " . $schedule['event_date'] . "<br>";
                                            echo "Start: " . $schedule['start_time'] . "<br>";
                                            echo "End: " . $schedule['end_time'] . "<br><hr>";
                                        }
                                    } else {
                                        echo "No schedules found.";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="view_event.php?id=<?php echo $row['id']; ?>" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a> |
                                    <a href="edit_event.php?id=<?php echo $row['id']; ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a> |
                                    <a href="delete_archiveEvent.php?id=<?php echo $row['id']; ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a> |
                                    <a href="restore_event.php?id=<?php echo $row['id']; ?>" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No archived events found.</p>
            <?php endif; ?>
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