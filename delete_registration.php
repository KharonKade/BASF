<?php
$conn = new mysqli("localhost", "root", "", "basf_events");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registration_id = $_GET['id'] ?? null;
$event_id = null;
$deletion_success = false;

if ($registration_id) {
    $event_query = "SELECT event_id FROM event_registrations WHERE id = ?";
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        $event_id = $event['event_id'];

        $delete_query = "DELETE FROM event_registrations WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $registration_id);

        if ($stmt->execute()) {
            $deletion_success = true;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Deletion</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; }
        .swal2-popup { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body>
    <script>
        <?php if ($deletion_success): ?>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Registration has been removed successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'eventPages.php?id=<?= $event_id; ?>';
                    }
                });
            });
        <?php else: ?>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Unable to delete registration or ID not found.',
                    icon: 'error',
                    confirmButtonText: 'Go Back'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.history.back();
                    }
                });
            });
        <?php endif; ?>
    </script>
</body>
</html>