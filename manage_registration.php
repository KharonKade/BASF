<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$dbname = "u142318015_db_vf0t87O1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$token = $_POST['token'] ?? null;
$posted_event_id = $_POST['event_id'] ?? null;
$registration_id = $_GET['id'] ?? null;

$registration = null;

if ($token && $posted_event_id) {
    $registration_sql = "SELECT * FROM event_registrations WHERE token = ? AND event_id = ?";
    $stmt = $conn->prepare($registration_sql);
    $stmt->bind_param("si", $token, $posted_event_id);
    $stmt->execute();
    $registration_result = $stmt->get_result();
    $registration = $registration_result->fetch_assoc();
} elseif ($registration_id) {
    $registration_sql = "SELECT * FROM event_registrations WHERE id = ?";
    $stmt = $conn->prepare($registration_sql);
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $registration_result = $stmt->get_result();
    $registration = $registration_result->fetch_assoc();
}

if ($registration) {
    $event_id = $registration['event_id'];
} elseif ($posted_event_id) {
    $event_id = $posted_event_id;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Registration</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="Css/manage_registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .swal2-popup { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="registration-card">
            <?php if ($registration): ?>
                <div class="card-header">
                    <h3><i class="fas fa-ticket-alt"></i> Your Registration Details</h3>
                </div>
                
                <div class="table-responsive">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="Name"><?= htmlspecialchars($registration['name']); ?></td>
                                <td data-label="Email" style="word-break: break-all;"><?= htmlspecialchars($registration['email']); ?></td>
                                <td data-label="Phone"><?= htmlspecialchars($registration['phone']); ?></td>
                                <td data-label="Age"><?= htmlspecialchars($registration['age']); ?></td>
                                <td data-label="Gender"><?= htmlspecialchars($registration['gender']); ?></td>
                                <td data-label="Category">
                                    <span class="category-badge"><?= htmlspecialchars($registration['category']); ?></span>
                                </td>
                                <td data-label="Actions" class="action-cells">
                                    <a href="edit_registration.php?id=<?= $registration['id']; ?>" class="btn-icon edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $registration['id']; ?>)" class="btn-icon delete" title="Remove Registration">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Invalid token or no registration found for this specific event.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($event_id)): ?>
                <div class="card-footer">
                    <a href="eventPages.php?id=<?= $event_id; ?>" class="return-btn">
                        <i class="fas fa-arrow-left"></i> Return to Event Page
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure you want to remove your Registration?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                font: 'Poppins'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_registration.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>