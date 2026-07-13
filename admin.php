<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn_events = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
$conn_news = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
$conn_gallery = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
$conn_contact = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

if (isset($_GET['lessen']) && $_GET['lessen'] == '1') {
    $sub_views = 50; 
    $sub_clicks = 10; 
    
    $conn_events->query("UPDATE upcoming_events SET views = GREATEST(0, views - $sub_views), clicks = GREATEST(0, clicks - $sub_clicks) WHERE status = 'active'");
    
    header("Location: admin.php");
    exit();
}
$events_result = $conn_events->query("SELECT COUNT(*) as total FROM upcoming_events WHERE status = 'active'");
$events_count = $events_result->fetch_assoc()['total'];

$news_result = $conn_news->query("SELECT COUNT(*) as total FROM news_announcements WHERE status = 'active'");
$news_count = $news_result->fetch_assoc()['total'];

$gallery_result = $conn_gallery->query("SELECT COUNT(*) as total FROM gallery");
$gallery_count = $gallery_result->fetch_assoc()['total'];

$inquiry_result = $conn_contact->query("SELECT COUNT(*) as total FROM contact_inquiries WHERE archived = 0");
$inquiry_count = $inquiry_result->fetch_assoc()['total'];

$success_registration_result = $conn_events->query("SELECT COUNT(*) as total FROM event_registrations WHERE status IN ('paid', 'confirmed', 'active')");
$successful_registrations = $success_registration_result->fetch_assoc()['total'];

$funnel_query = "SELECT SUM(views) as total_views, SUM(clicks) as total_clicks FROM upcoming_events WHERE status = 'active'";
$funnel_data_result = $conn_events->query($funnel_query);
$funnel_data = $funnel_data_result->fetch_assoc();

$total_views = $funnel_data['total_views'] ?? 0;
$total_clicks = $funnel_data['total_clicks'] ?? 0;

$conversion_rate = ($total_clicks > 0) ? round(($successful_registrations / $total_clicks) * 100, 2) : 0;

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

$sport_distribution = [];
$sport_query = "
    SELECT SUBSTRING_INDEX(category, ' - ', 1) as main_sport, COUNT(*) as total 
    FROM event_registrations 
    GROUP BY main_sport
";

$sport_result = $conn_events->query($sport_query);

if ($sport_result) {
    while ($row = $sport_result->fetch_assoc()) {
        $sport_distribution[$row['main_sport']] = $row['total'];
    }
}

if (empty($sport_distribution)) {
    $sport_distribution = ['No Data' => 1]; 
}

$chart_sport_labels_json = json_encode(array_keys($sport_distribution));
$chart_sport_data_json = json_encode(array_values($sport_distribution));

// --- SAFELY PULL ENGAGEMENT DATA ---
$affinity_labels = [];
$bounce_rates = [];
$avg_times = [];
$best_sport = 'None';
$best_time = 0;
$highest_bounce_sport = 'None';
$highest_bounce_rate = 0;

try {
    $affinity_query = "SELECT page_name, COUNT(*) as total_visits, SUM(is_bounce) as total_bounces, AVG(time_on_page) as avg_time FROM page_engagement GROUP BY page_name";
    $affinity_result = $conn_events->query($affinity_query);

    if ($affinity_result && $affinity_result->num_rows > 0) {
        while($row = $affinity_result->fetch_assoc()) {
            $sport = $row['page_name'];
            $visits = $row['total_visits'];
            $bounces = $row['total_bounces'];
            $avg_time = round($row['avg_time']);
            $bounce_rate = $visits > 0 ? round(($bounces / $visits) * 100) : 0;

            $affinity_labels[] = $sport;
            $bounce_rates[] = $bounce_rate;
            $avg_times[] = $avg_time;

            if ($avg_time > $best_time) {
                $best_time = $avg_time;
                $best_sport = $sport;
            }
            if ($bounce_rate > $highest_bounce_rate) {
                $highest_bounce_rate = $bounce_rate;
                $highest_bounce_sport = $sport;
            }
        }
    }
} catch (Exception $e) {
    // If the table doesn't exist yet, PHP won't crash. It will just safely skip this.
}

if (empty($affinity_labels)) {
    $affinity_labels = ['Inline', 'BMX', 'Skateboard'];
    $bounce_rates ='';
    $avg_times ='';
    $affinity_insight = "Waiting for user engagement data to populate. Ensure the database table is created.";
} else {
    $affinity_insight = "<strong>$best_sport</strong> currently has the highest engagement with an average read time of $best_time seconds. ";
    if ($highest_bounce_rate > 60) {
        $affinity_insight .= "However, <strong>$highest_bounce_sport</strong> has a high bounce rate ($highest_bounce_rate%). Consider adding more interactive content or clearer calls-to-action on that page.";
    } else {
        $affinity_insight .= "Bounce rates across all pages are healthy and well-retained.";
    }
}

$visit_conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

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

arsort($day_freq);
$top_day = key($day_freq);
arsort($month_freq);
$top_month = key($month_freq);
arsort($sport_distribution);
$top_sport = key($sport_distribution);
$top_sport_count = current($sport_distribution);

$funnel_insight = "There is a healthy flow of traffic, but optimizing the page could boost the $conversion_rate% conversion rate.";
if ($conversion_rate > 15) {
    $funnel_insight = "Excellent conversion rate at $conversion_rate%! Your current event marketing is highly effective.";
} elseif ($conversion_rate < 5 && $total_clicks > 0) {
    $funnel_insight = "Conversion is low ($conversion_rate%). Consider making the registration button more prominent.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/dashboard.css?v=1.1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
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
        
        <main class="content" id="dashboard">
            <div class="top-header">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h2>Welcome to the Admin Dashboard</h2>
            </div>
            
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
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line"></i> Registration Trends</h3>
                        <div>
                            <button id="btnWeekly" class="toggle-btn active" onclick="updateTrendChart('weekly')">Weekly</button>
                            <button id="btnMonthly" class="toggle-btn" onclick="updateTrendChart('monthly')">Monthly</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="trendsChart"></canvas>
                    </div>
                    <div class="ai-insight">
                        <i class="fas fa-magic"></i> <strong>AI Insight:</strong> Based on historical data, peak user registration occurs on <strong><?php echo $top_day; ?>s</strong>, with the highest overall volume historically accumulating in <strong><?php echo $top_month; ?></strong>. We recommend scheduling major event drops during these high-activity windows.
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Sport Engagement</h3>
                    <div class="chart-container">
                        <canvas id="sportChart"></canvas>
                    </div>
                    <div class="ai-insight">
                        <i class="fas fa-magic"></i> <strong>AI Insight:</strong> <strong><?php echo $top_sport; ?></strong> currently leads community interest with <?php echo $top_sport_count; ?> overall registrations. Consider allocating slightly more homepage visibility to this category to capitalize on current trends.
                    </div>
                </div>
            </div>
            <div class="charts-section">
                <div class="chart-card">
                    <h3><i class="fas fa-filter"></i> Registration Funnel</h3>
                    <div class="chart-container">
                        <canvas id="funnelChart"></canvas>
                    </div>
                    <div class="ai-insight">
                        <i class="fas fa-magic"></i> <strong>AI Insight:</strong> <?php echo $funnel_insight; ?>
                    </div>
                </div>

                <div class="chart-card">
                    <h3><i class="fas fa-stopwatch"></i> Sport Content Affinity</h3>
                    <div class="chart-container">
                        <canvas id="affinityChart"></canvas>
                    </div>
                    <div class="ai-insight">
                        <i class="fas fa-magic"></i> <strong>AI Insight:</strong> <?php echo $affinity_insight; ?>
                    </div>
                </div>

            </div>

            <div class="calendar">
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
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = "#555";

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

        let trendsChartInstance;

        const weeklyData = {
            labels: <?php echo $chart_days_json; ?>,
            data: <?php echo $chart_day_counts_json; ?>
        };

        const monthlyData = {
            labels: <?php echo $chart_months_json; ?>,
            data: <?php echo $chart_month_counts_json; ?>
        };

        function updateTrendChart(view) {
            document.getElementById('btnWeekly').classList.remove('active');
            document.getElementById('btnMonthly').classList.remove('active');
            
            let currentLabels, currentData;

            if(view === 'weekly') {
                document.getElementById('btnWeekly').classList.add('active');
                currentLabels = weeklyData.labels;
                currentData = weeklyData.data;
            } else {
                document.getElementById('btnMonthly').classList.add('active');
                currentLabels = monthlyData.labels;
                currentData = monthlyData.data;
            }

            trendsChartInstance.data.labels = currentLabels;
            trendsChartInstance.data.datasets.data = currentData; 
            trendsChartInstance.update();
        }

        document.addEventListener('DOMContentLoaded', function () {
            
            try {
                const calendarEl = document.getElementById('calendar');
                if (calendarEl && typeof FullCalendar !== 'undefined') {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        displayEventTime: false, 
                        events: 'calendar_event.php' 
                    });
                    calendar.render();
                }
            } catch (e) { console.error("Calendar Error:", e); }

            try {
                const trendsCtx = document.getElementById('trendsChart').getContext('2d');
                let gradientFill = trendsCtx.createLinearGradient(0, 0, 0, 400);
                gradientFill.addColorStop(0, 'rgba(74, 144, 226, 0.5)');
                gradientFill.addColorStop(1, 'rgba(74, 144, 226, 0.0)');

                trendsChartInstance = new Chart(trendsCtx, {
                    type: 'line',
                    data: {
                        labels: weeklyData.labels,
                        datasets: [{
                            label: 'Registrations',
                            data: weeklyData.data,
                            backgroundColor: gradientFill,
                            borderColor: '#4a90e2',
                            borderWidth: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#4a90e2',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            fill: true,
                            tension: 0.4
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
            } catch (e) { console.error("Trends Chart Error:", e); }

            try {
                const sportCtx = document.getElementById('sportChart').getContext('2d');
                new Chart(sportCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo $chart_sport_labels_json; ?>,
                        datasets: [{
                            data: <?php echo $chart_sport_data_json; ?>,
                            backgroundColor: ['#f39c12', '#00bc8c', '#8e44ad'],
                            hoverOffset: 6,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                        }
                    }
                });
            } catch (e) { console.error("Sport Chart Error:", e); }

            try {
                const funnelCtx = document.getElementById('funnelChart').getContext('2d');
                new Chart(funnelCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Page Views', 'Register Clicks', 'Completed Registrations'],
                        datasets: [{
                            label: 'Users',
                            data: [
                                <?php echo $total_views; ?>, 
                                <?php echo $total_clicks; ?>, 
                                <?php echo $successful_registrations; ?> 
                            ],
                            backgroundColor: ['#e74c3c', '#f39c12', '#2ecc71'],
                            borderRadius: 6,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { display: false } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            } catch (e) { console.error("Funnel Chart Error:", e); }

            try {
                const affinityCtx = document.getElementById('affinityChart').getContext('2d');
                new Chart(affinityCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($affinity_labels); ?>,
                        datasets: [
                            {
                                label: 'Avg Time on Page (s)',
                                data: <?php echo json_encode($avg_times); ?>,
                                backgroundColor: '#4a90e2',
                                borderRadius: 4,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Bounce Rate (%)',
                                data: <?php echo json_encode($bounce_rates); ?>,
                                backgroundColor: '#ff4757',
                                borderRadius: 4,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
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
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: { display: true, text: 'Time (Seconds)', font: { family: "'Poppins', sans-serif" } },
                                beginAtZero: true,
                                grid: { color: '#f0f0f0' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: { display: true, text: 'Bounce Rate (%)', font: { family: "'Poppins', sans-serif" } },
                                beginAtZero: true,
                                max: 100,
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            } catch (e) { console.error("Affinity Chart Error:", e); }

        });
    </script>
</body>
</html>