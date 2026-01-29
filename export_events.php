<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    exit("Unauthorized access");
}

$conn = new mysqli("localhost", "root", "", "basf_events");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filter_category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=events_list_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['Event Name', 'Location', 'Category', 'Registration Type', 'Fee', 'Limit', 'Schedules']);

$sql = "
    SELECT 
        e.event_name, 
        e.location, 
        e.category, 
        e.registration, 
        e.registration_fee, 
        e.registration_limit,
        GROUP_CONCAT(
            CONCAT(s.event_date, ' (', TIME_FORMAT(s.start_time, '%h:%i %p'), ' - ', TIME_FORMAT(s.end_time, '%h:%i %p'), ')') 
            SEPARATOR ' | '
        ) as schedules
    FROM upcoming_events e
    LEFT JOIN event_schedules s ON e.id = s.event_id
    WHERE e.status = 'active'
";

if (!empty($filter_category) && $filter_category !== 'All') {
    $sql .= " AND e.category = '$filter_category'";
}

if (!empty($search_query)) {
    $sql .= " AND e.event_name LIKE '%$search_query%'";
}

$sql .= " GROUP BY e.id ORDER BY e.id DESC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $reg_type = ($row['registration'] == 1) ? (($row['registration_fee'] > 0) ? 'Paid' : 'Free') : 'Disabled';
    $fee = ($row['registration_fee'] > 0) ? 'PHP ' . number_format($row['registration_fee'], 2) : '0.00';
    $limit = $row['registration_limit'] ? $row['registration_limit'] : 'Unlimited';

    fputcsv($output, [
        $row['event_name'],
        $row['location'],
        ucfirst($row['category']),
        $reg_type,
        $fee,
        $limit,
        $row['schedules']
    ]);
}

fclose($output);
$conn->close();
exit();
?>