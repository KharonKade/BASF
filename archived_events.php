<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "basf_events");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch archived events
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/archived_events.css">
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
            <h2>Archived Events</h2>

            <form method="post" action="delete_all_events.php" onsubmit="return confirm('Are you sure you want to delete all archived events?');">
                <button type="submit" name="delete_all" class="delete-all-btn">Delete All</button>
            </form>

            <?php if ($result->num_rows > 0): ?>
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
                            // Initialize a counter for row numbers
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
                                    // Fetch schedules for the event
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
                                    <a href="delete_archiveEvent.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this event?');" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a> |
                                    <a href="restore_event.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to restore this event?');" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No archived events found.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php $conn->close(); ?>
