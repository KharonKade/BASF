<?php
session_start(); // Start or resume the session

if (!isset($_SESSION['has_visited'])) {
    // User hasn't been counted yet in this session
    $visit_conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

    // Make sure the connection was successful
    if ($visit_conn->connect_error) {
        die("Connection failed: " . $visit_conn->connect_error);
    }

    // Log the visit
    $visit_conn->query("INSERT INTO visit_counter (visited_at) VALUES (NOW())");

    // Mark this session as counted
    $_SESSION['has_visited'] = true;

    $visit_conn->close();
}
?>
