<?php
$servername = "localhost"; 
$username = "u142318015_usr_vf0t87O1";        
$password = "W1xz8gB^";            
$dbname = "u142318015_db_vf0t87O1";   

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = $conn->query("
    SELECT ue.event_name, es.event_date 
    FROM upcoming_events ue
    INNER JOIN event_schedules es ON ue.id = es.event_id
    WHERE ue.status = 'active'
");

$events = [];

while ($row = $query->fetch_assoc()) {
    $events[] = [
        'title' => $row['event_name'],
        'start' => date('Y-m-d\TH:i:s', strtotime($row['event_date']))
    ];
}

echo json_encode($events);

$conn->close();  
?>
