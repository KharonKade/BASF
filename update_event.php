<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

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

$stmt = $conn->prepare("UPDATE upcoming_events SET event_name=?, location=?, description=?, category=?, registration=?, registration_limit=? WHERE id=?");
$stmt->bind_param("ssssiii", $event_name, $location, $description, $category, $registration, $registration_limit, $event_id);

if (!$stmt->execute()) {
    die("Error updating event.");
}
$stmt->close();

$conn->query("DELETE FROM event_categories WHERE event_id = $event_id");

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

if (!empty($_FILES['posters']['name'])) {
    $stmt_img = $conn->prepare("INSERT INTO event_images (event_id, image_path) VALUES (?, ?)");
    foreach ($_FILES['posters']['tmp_name'] as $index => $tmp_name) {
        if ($_FILES['posters']['error'][$index] === 0 && !empty($tmp_name)) {
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

if (!empty($_FILES['sponsors']['name'])) {
    $stmt_sponsor = $conn->prepare("INSERT INTO sponsor_logos (event_id, logo_path) VALUES (?, ?)");
    foreach ($_FILES['sponsors']['tmp_name'] as $index => $tmp_name) {
        if ($_FILES['sponsors']['error'][$index] === 0 && !empty($tmp_name)) {
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

$conn->query("CREATE TABLE IF NOT EXISTS event_leaderboards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    rank VARCHAR(50) NOT NULL,
    player_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    score VARCHAR(100) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES upcoming_events(id) ON DELETE CASCADE
)");

if (isset($_POST['lb_rank']) && is_array($_POST['lb_rank'])) {
    $conn->query("DELETE FROM event_leaderboards WHERE event_id = $event_id");
    $stmt_lb = $conn->prepare("INSERT INTO event_leaderboards (event_id, rank, player_name, category, score) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($_POST['lb_rank'] as $index => $rank) {
        $name = $_POST['lb_name'][$index] ?? '';
        $lb_category = $_POST['lb_category'][$index] ?? '';
        $score = $_POST['lb_score'][$index] ?? '';
        
        if (!empty($rank) && !empty($name)) {
            $stmt_lb->bind_param("issss", $event_id, $rank, $name, $lb_category, $score);
            $stmt_lb->execute();
        }
    }
    $stmt_lb->close();
}

$status_query = $conn->query("SELECT status FROM upcoming_events WHERE id = $event_id");
$event_status = $status_query->fetch_assoc()['status'];
$redirect_url = ($event_status === 'archived') ? 'archived_events.php' : 'manage_upcoming.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Updated</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html, * {
            font-family: 'Poppins', sans-serif !important;
        }
        body {
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
                text: 'Event updated successfully.',
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
                    window.location.href = '<?php echo $redirect_url; ?>';
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>