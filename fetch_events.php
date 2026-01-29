<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';
$category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : 'all';
$dateFilter = isset($_POST['date']) ? $conn->real_escape_string($_POST['date']) : 'all';

$sql = "
SELECT 
    e.id,
    e.event_name, 
    e.category, 
    s.event_date, 
    MIN(i.image_path) AS image_path
FROM 
    upcoming_events e
JOIN 
    event_schedules s ON e.id = s.event_id
JOIN 
    event_images i ON e.id = i.event_id
WHERE 
    e.status = 'active'
";

if (!empty($search)) {
    $sql .= " AND e.event_name LIKE '%$search%'";
}

if ($category !== 'all') {
    $sql .= " AND e.category = '$category'";
}

if ($dateFilter !== 'all') {
    if ($dateFilter == 'upcoming') {
        $sql .= " AND s.event_date >= CURDATE()";
    } elseif ($dateFilter == 'this-week') {
        $sql .= " AND YEARWEEK(s.event_date, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($dateFilter == 'this-month') {
        $sql .= " AND MONTH(s.event_date) = MONTH(CURDATE()) AND YEAR(s.event_date) = YEAR(CURDATE())";
    }
}

$sql .= " GROUP BY e.id ORDER BY e.id DESC";

$result = $conn->query($sql);

$trending_events = [];
$regular_events = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $trend_sql = "
            SELECT COUNT(r.id) AS recent_registrations
            FROM event_registrations r
            WHERE r.event_id = " . $row['id'] . "
            AND r.registration_time > NOW() - INTERVAL 7 DAY
        ";
        $trend_result = $conn->query($trend_sql);
        $trend_row = $trend_result->fetch_assoc();
        $recent_registrations = $trend_row['recent_registrations'];
        $is_trending = $recent_registrations > 5;

        if ($is_trending) {
            $trending_events[] = ['data' => $row, 'trending' => true];
        } else {
            $regular_events[] = ['data' => $row, 'trending' => false];
        }
    }

    $all_events = array_merge($trending_events, $regular_events);

    foreach ($all_events as $event) {
        $row = $event['data'];
        $is_trending = $event['trending'];

        $event_date = new DateTime($row["event_date"]);
        $formatted_date = $event_date->format('l, F j, Y');

        echo '<div class="event-item animate-on-scroll" 
                data-category="' . htmlspecialchars($row['category']) . '" 
                data-date="' . htmlspecialchars($row['event_date']) . '">
                <a href="eventPages.php?id=' . $row['id'] . '">
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            ' . ($is_trending ? '<span class="trending-tag">Trending Now</span>' : '') . '
                            <div class="flip-card-front">
                                <img src="' . $row["image_path"] . '" alt="' . $row["event_name"] . '">
                            </div>
                            <div class="flip-card-back" style="background-image: url(' . "'" . $row["image_path"] . "'" . ');">
                                <div class="back-content">
                                    <p>' . $row["event_name"] . '</p>
                                    <p>Category: ' . $row["category"] . '</p>
                                    <p>Date: ' . $formatted_date . '</p>
                                    <br><p>Click for more...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>';
    }
} else {
    echo "<p style='font-family: Poppins, sans-serif; padding: 20px; color: #fff;'>No events found matching your criteria.</p>";
}

$conn->close();
?>