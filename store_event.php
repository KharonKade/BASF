<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_name = $_POST['event_name'] ?? '';
$location = $_POST['location'] ?? '';
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? 'all'; 
$registration = isset($_POST['registration']) ? 1 : 0; 
$registration_limit = isset($_POST['registration_limit']) ? (int)$_POST['registration_limit'] : NULL;

if (empty($event_name) || empty($location) || empty($description)) {
    die("Error: Please fill all the required fields.");
}

$sql = "INSERT INTO upcoming_events (event_name, location, description, category, registration, registration_limit) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssis", $event_name, $location, $description, $category, $registration, $registration_limit);
$stmt->execute();
$event_id = $stmt->insert_id;
$stmt->close();

if ($registration == 1 && isset($_POST['sport_categories']) && is_array($_POST['sport_categories'])) {
    $cat_stmt = $conn->prepare("INSERT INTO event_categories (event_id, sport_type, category_name, fee) VALUES (?, ?, ?, ?)");
    
    foreach ($_POST['sport_categories'] as $sport => $categoryData) {
        if (isset($categoryData['name']) && is_array($categoryData['name'])) {
            $count = count($categoryData['name']);
            for ($i = 0; $i < $count; $i++) {
                $cat_name = $categoryData['name'][$i];
                $cat_fee = $categoryData['fee'][$i];
                
                if (!empty($cat_name) && is_numeric($cat_fee)) {
                    $cat_stmt->bind_param("issd", $event_id, $sport, $cat_name, $cat_fee);
                    $cat_stmt->execute();
                }
            }
        }
    }
    $cat_stmt->close();
}

if (!empty($_POST['event_date'])) {
    $schedule_sql = "INSERT INTO event_schedules (event_id, event_date, start_time, end_time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($schedule_sql);
    foreach ($_POST['event_date'] as $index => $event_date) {
        $start_time = $_POST['start_time'][$index] ?? '';
        $end_time = $_POST['end_time'][$index] ?? '';
        if (!empty($event_date) && !empty($start_time) && !empty($end_time)) {
            $stmt->bind_param("isss", $event_id, $event_date, $start_time, $end_time);
            $stmt->execute();
        }
    }
    $stmt->close();
}

if (!empty($_FILES['posters']['tmp_name'])) {
    $image_sql = "INSERT INTO event_images (event_id, image_path) VALUES (?, ?)";
    $stmt = $conn->prepare($image_sql);
    foreach ($_FILES['posters']['tmp_name'] as $index => $tmp_name) {
        $poster_path = "images/uploads/" . basename($_FILES['posters']['name'][$index]);
        if (move_uploaded_file($tmp_name, $poster_path)) {
            $stmt->bind_param("is", $event_id, $poster_path);
            $stmt->execute();
        }
    }
    $stmt->close();
}

if (!empty($_FILES['sponsors']['tmp_name'])) {
    $sponsor_sql = "INSERT INTO sponsor_logos (event_id, logo_path) VALUES (?, ?)";
    $stmt = $conn->prepare($sponsor_sql);
    foreach ($_FILES['sponsors']['tmp_name'] as $index => $tmp_name) {
        $sponsor_path = "images/uploads/" . basename($_FILES['sponsors']['name'][$index]);
        if (move_uploaded_file($tmp_name, $sponsor_path)) {
            $stmt->bind_param("is", $event_id, $sponsor_path);
            $stmt->execute();
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing...</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
        }
        .swal-poppins {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: 'Event created successfully!',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                customClass: {
                    popup: 'swal-poppins',
                    title: 'swal-poppins',
                    htmlContainer: 'swal-poppins',
                    confirmButton: 'swal-poppins'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'manage_upcoming.php';
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>