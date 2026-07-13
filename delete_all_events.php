<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';
$request_delete = isset($_POST['delete_all']);

if ($confirm) {
    $delete_sql = "DELETE FROM upcoming_events WHERE status = 'archived'";
    
    if ($conn->query($delete_sql) === TRUE) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Deleted All</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <style> body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; } </style>
        </head>
        <body>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'All archived events have been permanently deleted.',
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
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
} elseif ($request_delete) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirm Delete All</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style> body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; } </style>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to delete ALL archived events. This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete everything!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'delete_all_events.php?confirm=yes';
                    } else {
                        window.location.href = 'archived_events.php';
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php
    exit();
} else {
    header("Location: archived_events.php");
    exit();
}

$conn->close();
?>