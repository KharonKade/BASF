<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$servername = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$dbname = "u142318015_db_vf0t87O1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$trend_query = "
    SELECT 
        DATE_FORMAT(submitted_at, '%Y-%m') as sort_date, 
        DATE_FORMAT(submitted_at, '%b %Y') as display_date, 
        concerns, 
        COUNT(*) as total_count 
    FROM contact_inquiries 
    GROUP BY sort_date, display_date, concerns 
    ORDER BY sort_date ASC
";

$trend_result = $conn->query($trend_query);

$months_array = [];
$concerns_data = [];
$latest_month_counts = [];

if ($trend_result && $trend_result->num_rows > 0) {
    while ($row = $trend_result->fetch_assoc()) {
        $month = $row['display_date'];
        $concern = $row['concerns'];
        $count = (int)$row['total_count'];

        if (!in_array($month, $months_array)) {
            $months_array[] = $month;
        }

        if (!isset($concerns_data[$concern])) {
            $concerns_data[$concern] = [];
        }

        $concerns_data[$concern][$month] = $count;
        $latest_month_counts[$concern] = $count; 
    }
}

$datasets = [];
$colors = ['#4a90e2', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#34495e'];
$color_index = 0;

foreach ($concerns_data as $concern => $data_by_month) {
    $data_points = [];
    foreach ($months_array as $month) {
        $data_points[] = isset($data_by_month[$month]) ? $data_by_month[$month] : 0;
    }

    $datasets[] = [
        'label' => $concern,
        'data' => $data_points,
        'borderColor' => $colors[$color_index % count($colors)],
        'backgroundColor' => $colors[$color_index % count($colors)] . '33', 
        'borderWidth' => 2,
        'fill' => true,
        'tension' => 0.4
    ];
    $color_index++;
}

$chart_labels_json = json_encode($months_array);
$chart_datasets_json = json_encode($datasets);

$ai_insight = "Not enough data to determine trends yet. As more inquiries come in, insights will appear here.";

if (!empty($latest_month_counts)) {
    arsort($latest_month_counts);
    $top_concern = key($latest_month_counts);
    
    $top_concern_lower = strtolower($top_concern);
    
    if (strpos($top_concern_lower, 'general') !== false) {
        $ai_insight = "<strong>General Inquiries</strong> are currently peaking. If you recently announced an event, review the description to ensure all details are perfectly clear to the public to reduce repetitive questions.";
    } elseif (strpos($top_concern_lower, 'sponsor') !== false) {
        $ai_insight = "<strong>Sponsorship Inquiries</strong> are leading the trends! This is a highly positive indicator that the federation's reach and brand visibility are actively growing.";
    } else {
        $ai_insight = "<strong>" . htmlspecialchars($top_concern) . "</strong> inquiries are currently the most common. Reviewing these specific messages can reveal immediate community needs or technical issues.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inquiries</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/view_inquiries.css?v=1.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            font-family: 'Poppins', sans-serif;
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
            height: 300px;
            width: 100%;
        }
        .ai-insight {
            background: #f8faff;
            border-left: 4px solid #4a90e2;
            padding: 12px 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #333;
            font-family: 'Poppins', sans-serif;
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
            </div>

            <?php
            $unread_stmt = $conn->query("SELECT COUNT(*) AS unread_count FROM contact_inquiries WHERE archived = 0 AND is_read = 0");
            $unread_data = $unread_stmt->fetch_assoc();
            $unread_count = $unread_data['unread_count'];

            echo "<div class='content-wrapper'>";
            echo "<h2>Contact Inquiries</h2>";

            if ($unread_count > 0) {
                echo "<div style='background-color: #d4edda; color: #155724; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-family: \"Poppins\", sans-serif;'>
                        <i class='fas fa-bell'></i> You have <strong>{$unread_count}</strong> new unread inquiry(ies).
                      </div>";
            }
            ?>

            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line"></i> Inquiry Trend Analysis</h3>
                </div>
                <div class="chart-container">
                    <canvas id="inquiryTrendChart"></canvas>
                </div>
                <div class="ai-insight">
                    <i class="fas fa-magic"></i> <strong>AI Insight:</strong> <?php echo $ai_insight; ?>
                </div>
            </div>

            <?php
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

            $sql = "SELECT id, full_name, email, contact_number, concerns, message, submitted_at, archived, is_read FROM contact_inquiries WHERE archived = 0";
            if (!empty($filter)) {
                $sql .= " AND concerns = '" . $conn->real_escape_string($filter) . "'";
            }
            $sql .= " ORDER BY id DESC";
            
            $result = $conn->query($sql);
            $counter = 1;

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $shortMessage = strlen($row["message"]) > 25 ? substr($row["message"], 0, 25) . '...' : $row["message"];
                    
                    $newBadge = ($row['is_read'] == 0) ? " <span style='background: #ff4757; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 500; font-family: \"Poppins\", sans-serif;'>New</span>" : "";
                    
                    echo "<tr>
                            <td>" . $counter . "</td>
                            <td>" . htmlspecialchars($row["full_name"]) . $newBadge . "</td>
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
        Chart.defaults.font.family = "'Poppins', sans-serif";
        
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('inquiryTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $chart_labels_json; ?>,
                    datasets: <?php echo $chart_datasets_json; ?>
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: '#333',
                            titleFont: { family: "'Poppins', sans-serif", size: 14 },
                            bodyFont: { family: "'Poppins', sans-serif", size: 13 },
                            padding: 10,
                            cornerRadius: 8,
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
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