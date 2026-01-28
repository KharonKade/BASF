<?php
$conn = new mysqli("localhost", "root", "", "basf_events");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = intval($_POST['event_id']);
$event_name = $_POST['event_name'];
$location = $_POST['location'];
$description = $_POST['description'];
$category = $_POST['category'];
$registration = isset($_POST['registration']) ? 1 : 0;

$registration_limit = (!empty($_POST['registration_limit'])) ? intval($_POST['registration_limit']) : NULL;

$registration_fee = 0.00;
if ($registration == 1 && isset($_POST['registration_type']) && $_POST['registration_type'] === 'paid') {
    $registration_fee = !empty($_POST['registration_fee']) ? floatval($_POST['registration_fee']) : 0.00;
}

$stmt = $conn->prepare("UPDATE upcoming_events SET event_name=?, location=?, description=?, category=?, registration=?, registration_limit=?, registration_fee=? WHERE id=?");
$stmt->bind_param("ssssiidi", $event_name, $location, $description, $category, $registration, $registration_limit, $registration_fee, $event_id);

if (!$stmt->execute()) {
    error_log("Error updating event: " . $stmt->error);
    die("Error updating event. Please try again later.");
}
$stmt->close();

$conn->query("DELETE FROM event_schedules WHERE event_id = $event_id");

if (!empty($_POST['event_date'])) {
    $stmt_schedule = $conn->prepare("INSERT INTO event_schedules (event_id, event_date, start_time, end_time) VALUES (?, ?, ?, ?)");
    
    foreach ($_POST['event_date'] as $index => $event_date) {
        $start_time = $_POST['start_time'][$index];
        $end_time = $_POST['end_time'][$index];
        
        $stmt_schedule->bind_param("isss", $event_id, $event_date, $start_time, $end_time);
        $stmt_schedule->execute();
    }
    $stmt_schedule->close();
}

$existing_posters = !empty($_POST['existing_posters']) ? $_POST['existing_posters'] : [];
if (!empty($existing_posters)) {
    $placeholders = implode(',', array_fill(0, count($existing_posters), '?'));
    $types = str_repeat('s', count($existing_posters));
    $stmt_del_img = $conn->prepare("DELETE FROM event_images WHERE event_id = ? AND image_path NOT IN ($placeholders)");
    
    $params = array_merge([$event_id], $existing_posters);
    $stmt_del_img->bind_param("i" . $types, ...$params);
    $stmt_del_img->execute();
    $stmt_del_img->close();
} else {
    $conn->query("DELETE FROM event_images WHERE event_id = $event_id");
}

if (!empty($_FILES['posters']['name'][0])) {
    $stmt_img = $conn->prepare("INSERT INTO event_images (event_id, image_path) VALUES (?, ?)");
    foreach ($_FILES['posters']['tmp_name'] as $index => $tmp_name) {
        if (!empty($tmp_name)) {
            $poster_name = uniqid() . "_" . basename($_FILES['posters']['name'][$index]);
            $poster_path = "images/" . $poster_name;
            if (move_uploaded_file($tmp_name, $poster_path)) {
                $stmt_img->bind_param("is", $event_id, $poster_path);
                $stmt_img->execute();
            }
        }
    }
    $stmt_img->close();
}

$existing_sponsors = !empty($_POST['existing_sponsors']) ? $_POST['existing_sponsors'] : [];
if (!empty($existing_sponsors)) {
    $placeholders = implode(',', array_fill(0, count($existing_sponsors), '?'));
    $types = str_repeat('s', count($existing_sponsors));
    $stmt_del_sponsor = $conn->prepare("DELETE FROM sponsor_logos WHERE event_id = ? AND logo_path NOT IN ($placeholders)");
    
    $params = array_merge([$event_id], $existing_sponsors);
    $stmt_del_sponsor->bind_param("i" . $types, ...$params);
    $stmt_del_sponsor->execute();
    $stmt_del_sponsor->close();
} else {
    $conn->query("DELETE FROM sponsor_logos WHERE event_id = $event_id");
}

if (!empty($_FILES['sponsors']['name'][0])) {
    $stmt_sponsor = $conn->prepare("INSERT INTO sponsor_logos (event_id, logo_path) VALUES (?, ?)");
    foreach ($_FILES['sponsors']['tmp_name'] as $index => $tmp_name) {
        if (!empty($tmp_name)) {
            $sponsor_name = uniqid() . "_" . basename($_FILES['sponsors']['name'][$index]);
            $sponsor_path = "images/" . $sponsor_name;
            if (move_uploaded_file($tmp_name, $sponsor_path)) {
                $stmt_sponsor->bind_param("is", $event_id, $sponsor_path);
                $stmt_sponsor->execute();
            }
        }
    }
    $stmt_sponsor->close();
}

$conn->close();
header("Location: manage_upcoming.php");
exit();
?>