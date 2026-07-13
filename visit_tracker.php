<?php
session_start(); 

if (!isset($_SESSION['has_visited'])) {
    $visit_conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
    if ($visit_conn->connect_error) {
        die("Connection failed: " . $visit_conn->connect_error);
    }
    $visit_conn->query("INSERT INTO visit_counter (visited_at) VALUES (NOW())");
    $_SESSION['has_visited'] = true;

    $visit_conn->close();
}
?>
