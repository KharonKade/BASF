<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn_events = new mysqli("localhost", "root", "", "basf_events");
$conn_news = new mysqli("localhost", "root", "", "basf_news");
$conn_gallery = new mysqli("localhost", "root", "", "basf_gallery");
$conn_contact = new mysqli("localhost", "root", "", "contact_us");

$events_result = $conn_events->query("SELECT COUNT(*) as total FROM upcoming_events WHERE status = 'active'");
$events_count = $events_result->fetch_assoc()['total'];

$news_result = $conn_news->query("SELECT COUNT(*) as total FROM news_announcements WHERE status = 'active'");
$news_count = $news_result->fetch_assoc()['total'];

$gallery_result = $conn_gallery->query("SELECT COUNT(*) as total FROM gallery");
$gallery_count = $gallery_result->fetch_assoc()['total'];

$inquiry_result = $conn_contact->query("SELECT COUNT(*) as total FROM contact_inquiries WHERE archived = 0");
$inquiry_count = $inquiry_result->fetch_assoc()['total'];

$registration_result = $conn_events->query("SELECT COUNT(*) as total FROM event_registrations");
$registration_count = $registration_result->fetch_assoc()['total'];

$days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$months_order = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

$day_freq = array_fill_keys($days_order, 0);
$month_freq = array_fill_keys($months_order, 0);

$day_query = $conn_events->query("SELECT registration_date FROM event_registrations");
while ($row = $day_query->fetch_assoc()) {
    $date = new DateTime($row['registration_date']);
    $day = $date->format('l');
    $month = $date->format('F');

    if (isset($day_freq[$day])) {
        $day_freq[$day]++;
    }
    if (isset($month_freq[$month])) {
        $month_freq[$month]++;
    }
}

$chart_days_json = json_encode(array_keys($day_freq));
$chart_day_counts_json = json_encode(array_values($day_freq));

$chart_months_json = json_encode(array_keys($month_freq));
$chart_month_counts_json = json_encode(array_values($month_freq));

$visit_conn = new mysqli("localhost", "root", "", "basf_visits");

if ($visit_conn->connect_error) {
    die("Connection failed: " . $visit_conn->connect_error);
}

$visit_result = $visit_conn->query("SELECT COUNT(*) as total FROM visit_counter");
$visit_count = $visit_result->fetch_assoc()['total'];

$visit_conn->close();

$monday = new DateTime('monday this week');
$monday->setTime(0, 0, 0);
$sunday = new DateTime('sunday this week');
$sunday->setTime(23, 59, 59);

$start_date = $monday->format('Y-m-d H:i:s');
$end_date = $sunday->format('Y-m-d H:i:s');

$activities = [];

$result_events = $conn_events->query("SELECT event_name AS title, created_at AS time FROM upcoming_events WHERE status = 'active' AND created_at BETWEEN '$start_date' AND '$end_date'");
while ($row = $result_events->fetch_assoc()) {
    $activities[] = ['type' => 'Event', 'title' => $row['title'], 'time' => $row['time'], 'emoji' => '✅'];
}

$result_news = $conn_news->query("SELECT news_title AS title, created_at AS time FROM news_announcements WHERE status = 'active' AND created_at BETWEEN '$start_date' AND '$end_date'");
while ($row = $result_news->fetch_assoc()) {
    $activities[] = ['type' => 'News', 'title' => $row['title'], 'time' => $row['time'], 'emoji' => '📰'];
}

$result_gallery = $conn_gallery->query("SELECT title, uploaded_at AS time FROM gallery WHERE uploaded_at BETWEEN '$start_date' AND '$end_date'");
while ($row = $result_gallery->fetch_assoc()) {
    $activities[] = ['type' => 'Gallery', 'title' => $row['title'], 'time' => $row['time'], 'emoji' => '📸'];
}

$result_inquiry = $conn_contact->query("SELECT full_name AS title, submitted_at AS time FROM contact_inquiries WHERE archived = 0 AND submitted_at BETWEEN '$start_date' AND '$end_date'");
while ($row = $result_inquiry->fetch_assoc()) {
    $activities[] = ['type' => 'Inquiry', 'title' => $row['title'], 'time' => $row['time'], 'emoji' => '❓'];
}

usort($activities, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));

$grouped = [];
foreach ($activities as $activity) {
    $date = date('l, F j', strtotime($activity['time']));
    $grouped[$date][] = $activity;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Css/dashboard.css?v=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="fullcalendar-6.1.17/dist/index.global.min.js" rel="stylesheet">
    <script src="fullcalendar-6.1.17/dist/index.global.js"></script>

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #333;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
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
        <main class="content" id="dashboard">
        <h2>Welcome to the Admin Dashboard</h2>
        <div class="dashboard-cards">
        <div class="card">
            <i class="fas fa-calendar-plus"></i>
            <h3>Total Events</h3>
            <p><?php echo $events_count; ?></p>
        </div>
        <div class="card">
            <i class="fas fa-newspaper"></i>
            <h3>News Articles</h3>
            <p><?php echo $news_count; ?></p>
        </div>
        <div class="card">
            <i class="fas fa-images"></i>
            <h3>Gallery Items</h3>
            <p><?php echo $gallery_count; ?></p>
        </div>
        <div class="card">
            <i class="fas fa-question-circle"></i>
            <h3>New Inquiries</h3>
            <p><?php echo $inquiry_count; ?></p>
        </div>
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Total Registration</h3>
            <p><?php echo $registration_count; ?></p>
        </div>
        <div class="card">
            <i class="fas fa-eye"></i>
            <h3>Total Visits</h3>
            <p><?php echo $visit_count; ?></p>
        </div>
        </div>

        <div class="charts-section">
            <div class="chart-card">
                <h3><i class="fas fa-calendar-day"></i> Weekly Activity</h3>
                <canvas id="daysChart"></canvas>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-calendar-alt"></i> Monthly Trends</h3>
                <canvas id="monthsChart"></canvas>
            </div>
        </div>

        <div class=calendar>
            <h3>Events Calendar</h3>
            <div id="calendar"></div>
        </div>

        <div class="recent-activity">
            <h3>Recent Activity (<?= $monday->format('F j') ?> – <?= $sunday->format('F j, Y') ?>)</h3>
            <ul>
                <?php foreach ($grouped as $date => $activities): ?>
                    <li>
                        <ul>
                            <?php for ($i = 0; $i < min(5, count($activities)); $i++): ?>
                                <li>
                                    <?php
                                        $a = $activities[$i];
                                        echo $a['emoji'] . ' ' . $a['type'] . ' "' . htmlspecialchars($a['title']) . '" was added on ' . date("M d, Y", strtotime($a['time']));
                                    ?>
                                </li>
                            <?php endfor; ?>

                            <?php if (count($activities) > 5): ?>
                                <div class="toggle-container">
                                    <button onclick="toggleActivities(this)">⬇ Show More</button>
                                    <ul class="extra-activities" style="display: none;">
                                        <?php for ($i = 5; $i < count($activities); $i++): ?>
                                            <li>
                                                <?php
                                                    $a = $activities[$i];
                                                    echo $a['emoji'] . ' ' . $a['type'] . ' "' . htmlspecialchars($a['title']) . '" was added on ' . date("M d, Y", strtotime($a['time']));
                                                ?>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        </main>
    </div>
    <script>
        function toggleActivities(button) {
            const extra = button.nextElementSibling;
            if (extra.style.display === "none") {
                extra.style.display = "block";
                button.textContent = "⬆ Show Less";
            } else {
                extra.style.display = "none";
                button.textContent = "⬇ Show More";
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                displayEventTime: false, 
                events: 'calendar_event.php' 
            });
            calendar.render();

            const daysCtx = document.getElementById('daysChart').getContext('2d');
            new Chart(daysCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chart_days_json; ?>,
                    datasets: [{
                        label: 'Registrations',
                        data: <?php echo $chart_day_counts_json; ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

            const monthsCtx = document.getElementById('monthsChart').getContext('2d');
            new Chart(monthsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo $chart_months_json; ?>,
                    datasets: [{
                        label: 'Registrations',
                        data: <?php echo $chart_month_counts_json; ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        });
    </script>
</body>
</html>