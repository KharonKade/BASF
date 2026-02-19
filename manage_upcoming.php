<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filter_category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "
    SELECT 
        id, event_name, location, category, registration, registration_limit, registration_fee
    FROM upcoming_events 
    WHERE status = 'active'
";

if (!empty($filter_category) && $filter_category !== 'All') {
    $sql .= " AND category = '$filter_category'";
}

if (!empty($search_query)) {
    $sql .= " AND event_name LIKE '%$search_query%'";
}

$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);

if (isset($_GET['ajax'])) {
    if ($result->num_rows > 0) {
        $row_num = 1;
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row_num++ . "</td>";
            echo "<td>" . htmlspecialchars($row['event_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['location']) . "</td>";
            echo "<td>" . ucfirst($row['category']) . "</td>";
            echo "<td>";
            if ($row['registration'] == 1) {
                if ($row['registration_fee'] > 0) {
                    echo "<span style='color:green; font-weight:600;'>Paid</span><br><small>₱" . number_format($row['registration_fee'], 2) . "</small>";
                } else {
                    echo "<span style='color:blue; font-weight:600;'>Free</span>";
                }
            } else {
                echo "<span style='color:gray;'>Disabled</span>";
            }
            echo "</td>";
            echo "<td>" . ($row['registration_limit'] ? $row['registration_limit'] : 'Unlimited') . "</td>";
            echo "<td>";
            $schedule_sql = "SELECT * FROM event_schedules WHERE event_id = " . $row['id'];
            $schedule_result = $conn->query($schedule_sql);
            if ($schedule_result->num_rows > 0) {
                while ($schedule = $schedule_result->fetch_assoc()) {
                    echo "<div><small><strong>" . $schedule['event_date'] . "</strong> (" . date('h:i A', strtotime($schedule['start_time'])) . " - " . date('h:i A', strtotime($schedule['end_time'])) . ")</small></div>";
                }
            } else {
                echo "No schedules found.";
            }
            echo "</td>";
            echo "<td>
                    <a href='view_event.php?id=" . $row['id'] . "' title='View'><i class='fas fa-eye'></i></a> |
                    <a href='edit_event.php?id=" . $row['id'] . "' title='Edit'><i class='fas fa-edit'></i></a> |
                    <a href='delete_event.php?id=" . $row['id'] . "' title='Delete'><i class='fas fa-trash'></i></a> |
                    <a href='archive_event.php?id=" . $row['id'] . "' title='Archive'><i class='fas fa-archive'></i></a>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No upcoming events found.</td></tr>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Upcoming Events</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/manage_event.css?v=1.1">
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
                <h2>Manage Events</h2>
            </div>
            
            <?php if(isset($_GET['message'])): ?>
                <div style="padding: 10px; background: #d4edda; color: #155724; margin-bottom: 20px; border-radius: 5px;">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <div class="filter-action-container">
                <form id="filterForm" method="GET" class="search-filters">
                    <label for="category">Filter:</label>
                    <select name="category" id="categoryFilter">
                        <option value="All" <?php if ($filter_category === 'All' || empty($filter_category)) echo 'selected'; ?>>All Categories</option>
                        <option value="Skateboard" <?php if ($filter_category === 'Skateboard') echo 'selected'; ?>>Skateboard</option>
                        <option value="BMX" <?php if ($filter_category === 'BMX') echo 'selected'; ?>>BMX</option>
                        <option value="Inline" <?php if ($filter_category === 'Inline') echo 'selected'; ?>>In-Line</option>
                    </select>
                    
                    <input type="text" name="search" id="searchInput" placeholder="Search event name..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="button" class="btn-search"><i class="fas fa-search"></i></button>
                    <a href="manage_upcoming.php" id="clearFilters" style="font-size: 12px; color: #666; display: <?php echo (!empty($search_query) || (!empty($filter_category) && $filter_category !== 'All')) ? 'inline' : 'none'; ?>;">Clear Filters</a>
                </form>
                
                <div class="action-buttons">
                    <a href="export_events.php" id="exportBtn" class="btn btn-export"><i class="fas fa-file-export"></i> Export CSV</a>
                    <a href="create_event.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Event</a>
                    <a href="archived_events.php" class="btn btn-secondary"><i class="fas fa-archive"></i> Archived Events</a>
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Name</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Registration</th>
                            <th>Limit</th>
                            <th>Schedules</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventTableBody">
                        <?php if ($result->num_rows > 0): 
                            $row_num = 1;
                            while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row_num++; ?></td> 
                            <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><?php echo ucfirst($row['category']); ?></td>
                            <td>
                                <?php 
                                    if ($row['registration'] == 1) {
                                        if ($row['registration_fee'] > 0) {
                                            echo "<span style='color:green; font-weight:600;'>Paid</span><br><small>₱" . number_format($row['registration_fee'], 2) . "</small>";
                                        } else {
                                            echo "<span style='color:blue; font-weight:600;'>Free</span>";
                                        }
                                    } else {
                                        echo "<span style='color:gray;'>Disabled</span>";
                                    }
                                ?>
                            </td>
                            <td><?php echo $row['registration_limit'] ? $row['registration_limit'] : 'Unlimited'; ?></td>
                            <td>
                                <?php
                                $schedule_sql = "SELECT * FROM event_schedules WHERE event_id = " . $row['id'];
                                $schedule_result = $conn->query($schedule_sql);
                                if ($schedule_result->num_rows > 0) {
                                    while ($schedule = $schedule_result->fetch_assoc()) {
                                        echo "<div><small><strong>" . $schedule['event_date'] . "</strong> (" . date('h:i A', strtotime($schedule['start_time'])) . " - " . date('h:i A', strtotime($schedule['end_time'])) . ")</small></div>";
                                    }
                                } else {
                                    echo "No schedules found.";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="view_event.php?id=<?php echo $row['id']; ?>" title="View"><i class="fas fa-eye"></i></a> |
                                <a href="edit_event.php?id=<?php echo $row['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a> |
                                <a href="delete_event.php?id=<?php echo $row['id']; ?>" title="Delete"><i class="fas fa-trash"></i></a> |
                                <a href="archive_event.php?id=<?php echo $row['id']; ?>" title="Archive"><i class="fas fa-archive"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; 
                        else: ?>
                        <tr><td colspan="8">No upcoming events found.</td></tr>
                        <?php endif; ?>
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

        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const eventTableBody = document.getElementById('eventTableBody');
        const exportBtn = document.getElementById('exportBtn');
        const clearFilters = document.getElementById('clearFilters');

        function fetchEvents() {
            const search = searchInput.value;
            const category = categoryFilter.value;
            
            const params = new URLSearchParams({
                ajax: 1,
                search: search,
                category: category
            });

            clearFilters.style.display = (search !== '' || category !== 'All') ? 'inline' : 'none';
            exportBtn.href = `export_events.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;

            fetch(`manage_upcoming.php?${params.toString()}`)
                .then(response => response.text())
                .then(data => {
                    eventTableBody.innerHTML = data;
                });
        }

        searchInput.addEventListener('input', fetchEvents);
        categoryFilter.addEventListener('change', fetchEvents);
    </script>
</body>
</html>
<?php $conn->close(); ?>