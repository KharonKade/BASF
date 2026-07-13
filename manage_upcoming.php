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

$auto_archive_sql = "
    UPDATE upcoming_events ue
    JOIN (
        SELECT event_id, MAX(CONCAT(event_date, ' ', end_time)) as last_schedule
        FROM event_schedules
        GROUP BY event_id
    ) s ON ue.id = s.event_id
    SET ue.status = 'archived'
    WHERE ue.status = 'active' AND s.last_schedule < NOW()
";
$conn->query($auto_archive_sql);

$paid_regs = 0;
$free_regs = 0;
$fee_insight = "Waiting for registration data to populate.";

$fee_result = $conn->query("
    SELECT 
        IF((SELECT COUNT(*) FROM event_categories c WHERE c.event_id = e.id AND c.fee > 0) > 0, 'Paid', 'Free') AS fee_type,
        COUNT(r.id) AS total_regs
    FROM event_registrations r
    JOIN upcoming_events e ON r.event_id = e.id
    GROUP BY fee_type
");

if ($fee_result) {
    while($row = $fee_result->fetch_assoc()) {
        if($row['fee_type'] == 'Paid') {
            $paid_regs = (int)$row['total_regs'];
        } else {
            $free_regs = (int)$row['total_regs'];
        }
    }
}

$total_fee_regs = $paid_regs + $free_regs;
$free_percentage = $total_fee_regs > 0 ? round(($free_regs / $total_fee_regs) * 100) : 0;

if ($total_fee_regs > 0) {
    if ($free_percentage >= 75) {
        $fee_insight = "<strong>$free_percentage%</strong> of registrations are for Free events. The community is highly price-sensitive. Consider lowering fees or relying on sponsorships for revenue.";
    } elseif ($free_percentage >= 50) {
        $fee_insight = "Registrations are fairly balanced, leaning slightly towards Free events ($free_percentage%).";
    } else {
        $fee_insight = "Paid events are performing exceptionally well, capturing " . (100 - $free_percentage) . "% of registrations! Your premium offerings are highly valued.";
    }
}

$fee_labels_json = json_encode(['Free Events', 'Paid Events']);
$fee_data_json = json_encode([$free_regs, $paid_regs]);

$buckets = [
    'Last Minute (0-3 Days)' => 0,
    'Late (4-7 Days)' => 0,
    'Proactive (8-14 Days)' => 0,
    'Early Bird (15+ Days)' => 0
];
$lead_insight = "Waiting for registration data to establish lead time trends.";

$lead_result = $conn->query("
    SELECT 
        DATEDIFF(MIN(s.event_date), r.registration_time) AS days_before
    FROM event_registrations r
    JOIN event_schedules s ON r.event_id = s.event_id
    GROUP BY r.id
");

if ($lead_result) {
    while($row = $lead_result->fetch_assoc()) {
        $days = (int)$row['days_before'];
        if ($days <= 3) {
            $buckets['Last Minute (0-3 Days)']++;
        } elseif ($days <= 7) {
            $buckets['Late (4-7 Days)']++;
        } elseif ($days <= 14) {
            $buckets['Proactive (8-14 Days)']++;
        } else {
            $buckets['Early Bird (15+ Days)']++;
        }
    }
}

$sorted_buckets = $buckets;
arsort($sorted_buckets);
$top_bucket = key($sorted_buckets);
$top_bucket_count = current($sorted_buckets);
$total_lead_regs = array_sum($buckets);
$top_bucket_percent = $total_lead_regs > 0 ? round(($top_bucket_count / $total_lead_regs) * 100) : 0;

if ($total_lead_regs > 0) {
    if (strpos($top_bucket, 'Last Minute') !== false) {
        $lead_insight = "<strong>$top_bucket_percent%</strong> of users are <strong>Last Minute</strong> registrants. Don't panic if numbers look low a week out—push your heaviest marketing in the final 72 hours.";
    } elseif (strpos($top_bucket, 'Early Bird') !== false) {
        $lead_insight = "<strong>$top_bucket_percent%</strong> of users are <strong>Early Birds</strong>! Your audience plans ahead. Capitalize on this by opening registrations even earlier.";
    } else {
        $lead_insight = "The majority of users (<strong>$top_bucket_percent%</strong>) register in the <strong>$top_bucket</strong> window. Focus your marketing pushes during this timeframe.";
    }
}

$lead_labels_json = json_encode(array_keys($buckets));
$lead_data_json = json_encode(array_values($buckets));

$filter_category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "
    SELECT 
        id, event_name, location, category, registration, registration_limit
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
                $fee_check = $conn->query("SELECT MIN(fee) as min_fee, MAX(fee) as max_fee FROM event_categories WHERE event_id = " . $row['id']);
                $fee_data = $fee_check->fetch_assoc();
                
                if ($fee_data && $fee_data['max_fee'] > 0) {
                    $display_fee = ($fee_data['min_fee'] == $fee_data['max_fee']) ? "₱" . number_format($fee_data['min_fee'], 2) : "₱" . number_format($fee_data['min_fee'], 2) . " - ₱" . number_format($fee_data['max_fee'], 2);
                    echo "<span style='color:green; font-weight:600;'>Paid</span><br><small>" . $display_fee . "</small>";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        .chart-header {
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #333;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
        }
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
            flex-grow: 1;
        }
        .ai-insight {
            background: #f8faff;
            border-left: 4px solid #4a90e2;
            padding: 12px 15px;
            margin-top: 15px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #333;
            line-height: 1.5;
        }
        .ai-insight i {
            color: #4a90e2;
            margin-right: 8px;
        }
    </style>
</head>
<body style="font-family: 'Poppins', sans-serif;">
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

            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <i class="fas fa-clock"></i> Registration Lead Time
                    </div>
                    <div class="chart-container">
                        <canvas id="leadTimeChart"></canvas>
                    </div>
                    <div class="ai-insight">
                        <i class="fas fa-magic"></i> <strong>AI Insight:</strong> <?php echo $lead_insight; ?>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <i class="fas fa-ticket-alt"></i> Free vs. Paid Conversions
                    </div>
                    <div class="chart-container">
                        <canvas id="feeChart"></canvas>
                    </div>
                    <div class="ai-insight">
                        <i class="fas fa-magic"></i> <strong>AI Insight:</strong> <?php echo $fee_insight; ?>
                    </div>
                </div>
            </div>

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
                                        $fee_check = $conn->query("SELECT MIN(fee) as min_fee, MAX(fee) as max_fee FROM event_categories WHERE event_id = " . $row['id']);
                                        $fee_data = $fee_check->fetch_assoc();
                                        
                                        if ($fee_data && $fee_data['max_fee'] > 0) {
                                            $display_fee = ($fee_data['min_fee'] == $fee_data['max_fee']) ? "₱" . number_format($fee_data['min_fee'], 2) : "₱" . number_format($fee_data['min_fee'], 2) . " - ₱" . number_format($fee_data['max_fee'], 2);
                                            echo "<span style='color:green; font-weight:600;'>Paid</span><br><small>" . $display_fee . "</small>";
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
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = "#555";

        document.addEventListener('DOMContentLoaded', function () {
            
            const leadCtx = document.getElementById('leadTimeChart').getContext('2d');
            new Chart(leadCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $lead_labels_json; ?>,
                    datasets: [{
                        label: 'Registrations',
                        data: <?php echo $lead_data_json; ?>,
                        backgroundColor: ['#e74c3c', '#f39c12', '#3498db', '#2ecc71'],
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#333',
                            titleFont: { family: "'Poppins', sans-serif", size: 14 },
                            bodyFont: { family: "'Poppins', sans-serif", size: 13 },
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });

            const feeCtx = document.getElementById('feeChart').getContext('2d');
            new Chart(feeCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $fee_labels_json; ?>,
                    datasets: [{
                        data: <?php echo $fee_data_json; ?>,
                        backgroundColor: ['#3498db', '#9b59b6'],
                        hoverOffset: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } },
                        tooltip: {
                            backgroundColor: '#333',
                            titleFont: { family: "'Poppins', sans-serif", size: 14 },
                            bodyFont: { family: "'Poppins', sans-serif", size: 13 },
                            padding: 10,
                            cornerRadius: 8
                        }
                    }
                }
            });
        });

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