<?php
$conn = new mysqli("localhost", "root", "", "basf_events");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

if (!$event_id) {
    header("Location: archived_events.php?status=invalid_id");
    exit();
}

if ($confirm) {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM event_schedules WHERE event_id = $event_id");
        $conn->query("DELETE FROM event_images WHERE event_id = $event_id");
        $conn->query("DELETE FROM sponsor_logos WHERE event_id = $event_id");
        $conn->query("DELETE FROM upcoming_events WHERE id = $event_id");
        $conn->commit();
        $conn->close();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Deleted</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <style> body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; } </style>
        </head>
        <body>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The archived event has been permanently deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    }).then((result) => {
                        window.location.href = 'archived_events.php';
                    });
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        header("Location: archived_events.php?status=error");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delete</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style> body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; } </style>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Permanently Delete?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_archiveEvent.php?id=<?php echo $event_id; ?>&confirm=yes';
                } else {
                    window.location.href = 'archived_events.php';
                }
            });
        });
    </script>
</body>
</html>