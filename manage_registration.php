<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

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
    <title>Your Registration</title>
    <link rel="stylesheet" href="Css/manage_registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
    <div class="registration-container">
        <?php if ($registration): ?>
            <h3>Your Registration</h3>
            <table border="1" cellspacing="0" cellpadding="8">
                <thead>
                    <tr>
                        <th>Name</th><th>Email</th><th>Phone</th>
                        <th>Age</th><th>Gender</th><th>Category</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($registration['name']); ?></td>
                        <td><?= htmlspecialchars($registration['email']); ?></td>
                        <td><?= htmlspecialchars($registration['phone']); ?></td>
                        <td><?= htmlspecialchars($registration['age']); ?></td>
                        <td><?= htmlspecialchars($registration['gender']); ?></td>
                        <td><?= htmlspecialchars($registration['category']); ?></td>
                        <td>
                            <a href="edit_registration.php?id=<?= $registration['id']; ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            |
                            <a href="delete_registration.php?id=<?= $registration['id']; ?>"
                            onclick="return confirm('Are you sure you want to remove your registration?');" 
                            title="Remove Registration">
                                <i class="fas fa-trash-alt" style="color: red;"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: red; text-align: center;">Invalid token or no registration found for this specific event.</p>
        <?php endif; ?>
        
        <?php if (isset($event_id)): ?>
            <div class="return-btn">
                <a href="eventPages.php?id=<?= $event_id; ?>">
                    Return to Event Page
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>