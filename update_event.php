<?php
$conn = new mysqli("localhost", "root", "", "basf_events");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = intval($_POST['event_id']);

$event_name = $conn->real_escape_string($_POST['event_name']);
$location = $conn->real_escape_string($_POST['location']);
$description = $conn->real_escape_string($_POST['description']);
$category = $conn->real_escape_string($_POST['category']);
$registration = isset($_POST['registration']) ? 1 : 0;
$registration_limit = isset($_POST['registration_limit']) ? intval($_POST['registration_limit']) : NULL;

$registration_fee = 0.00;
if ($registration == 1 && isset($_POST['registration_type']) && $_POST['registration_type'] === 'paid') {
    $registration_fee = isset($_POST['registration_fee']) ? floatval($_POST['registration_fee']) : 0.00;
}

$update_event_sql = "
    UPDATE upcoming_events 
    SET event_name = '$event_name', 
        location = '$location', 
        description = '$description', 
        category = '$category', 
        registration = '$registration',
        registration_limit = " . ($registration_limit !== NULL ? $registration_limit : "NULL") . ",
        registration_fee = $registration_fee
    WHERE id = $event_id";

if (!$conn->query($update_event_sql)) {
    error_log("Error updating event: " . $conn->error);
    die("Error updating event. Please try again later.");
}

$conn->query("DELETE FROM event_schedules WHERE event_id = $event_id");

if (!empty($_POST['event_date'])) {
    foreach ($_POST['event_date'] as $index => $event_date) {
        $start_time = $_POST['start_time'][$index];
        $end_time = $_POST['end_time'][$index];

        $event_date = $conn->real_escape_string($event_date);
        $start_time = $conn->real_escape_string($start_time);
        $end_time = $conn->real_escape_string($end_time);

        $conn->query("
            INSERT INTO event_schedules (event_id, event_date, start_time, end_time) 
            VALUES ($event_id, '$event_date', '$start_time', '$end_time')
        ");
    }
}

$existing_posters = !empty($_POST['existing_posters']) ? $_POST['existing_posters'] : [];
if (!empty($existing_posters)) {
    $existing_posters_placeholder = "'" . implode("','", array_map([$conn, 'real_escape_string'], $existing_posters)) . "'";
    $conn->query("
        DELETE FROM event_images 
        WHERE event_id = $event_id AND image_path NOT IN ($existing_posters_placeholder)
    ");
} else {
    $conn->query("DELETE FROM event_images WHERE event_id = $event_id");
}

foreach ($_FILES['posters']['tmp_name'] as $index => $tmp_name) {
    if (!empty($tmp_name)) {
        $poster_name = uniqid() . "_" . basename($_FILES['posters']['name'][$index]);
        $poster_path = "images/" . $poster_name;
        if (move_uploaded_file($tmp_name, $poster_path)) {
            $poster_path = $conn->real_escape_string($poster_path);
            $conn->query("INSERT INTO event_images (event_id, image_path) VALUES ($event_id, '$poster_path')");
        } else {
            error_log("Failed to upload poster: $tmp_name");
        }
    }
}

$existing_sponsors = !empty($_POST['existing_sponsors']) ? $_POST['existing_sponsors'] : [];
if (!empty($existing_sponsors)) {
    $existing_sponsors_placeholder = "'" . implode("','", array_map([$conn, 'real_escape_string'], $existing_sponsors)) . "'";
    $conn->query("
        DELETE FROM sponsor_logos 
        WHERE event_id = $event_id AND logo_path NOT IN ($existing_sponsors_placeholder)
    ");
} else {
    $conn->query("DELETE FROM sponsor_logos WHERE event_id = $event_id");
}

foreach ($_FILES['sponsors']['tmp_name'] as $index => $tmp_name) {
    if (!empty($tmp_name)) {
        $sponsor_name = uniqid() . "_" . basename($_FILES['sponsors']['name'][$index]);
        $sponsor_path = "images/" . $sponsor_name;
        if (move_uploaded_file($tmp_name, $sponsor_path)) {
            $sponsor_path = $conn->real_escape_string($sponsor_path);
            $conn->query("INSERT INTO sponsor_logos (event_id, logo_path) VALUES ($event_id, '$sponsor_path')");
        } else {
            error_log("Failed to upload sponsor logo: $tmp_name");
        }
    }
}

$conn->close();
header("Location: manage_upcoming.php");
exit();
?>