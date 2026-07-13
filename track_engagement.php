<?php
session_start();

$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Receive JSON data from the browser
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['page']) && isset($data['time'])) {
    $page_name = $conn->real_escape_string($data['page']);
    $time_on_page = (int)$data['time'];
    $session_id = session_id(); // Group visits by user session
    
    // If they stayed longer than 10 seconds, it is NOT a bounce
    $is_bounce = ($time_on_page < 10) ? 1 : 0; 

    // Check if we already have a record for this session and page today
    $check_sql = "SELECT id FROM page_engagement WHERE session_id = '$session_id' AND page_name = '$page_name' AND DATE(created_at) = CURDATE()";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Update existing record with the final time
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $update_sql = "UPDATE page_engagement SET time_on_page = $time_on_page, is_bounce = $is_bounce WHERE id = $id";
        $conn->query($update_sql);
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO page_engagement (page_name, session_id, time_on_page, is_bounce) VALUES ('$page_name', '$session_id', $time_on_page, $is_bounce)";
        $conn->query($insert_sql);
    }
}

$conn->close();
?>