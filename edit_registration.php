<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registration_id = $_GET['id'] ?? null;

if (!$registration_id) {
    die("Invalid registration ID.");
}

$registration_query = "SELECT * FROM event_registrations WHERE id = ?";
$stmt = $conn->prepare($registration_query);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Registration not found.");
}

$registration = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $category = $_POST['category'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($age) || empty($gender) || empty($category)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        $update_query = "UPDATE event_registrations SET name=?, email=?, phone=?, age=?, gender=?, category=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssi", $name, $email, $phone, $age, $gender, $category, $registration_id);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Registration updated successfully!');
                    window.location.href = 'manage_registration.php?id=" . $registration_id . "';
                  </script>";
        } else {
            echo "Error updating record: " . $conn->error;
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
    <title>Edit Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Css/edit_registration.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="edit-card">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Edit Registration</h3>
            </div>
            
            <form method="post" class="card-body">
                <div class="form-group">
                    <label for="name">Name</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($registration['name']); ?>" required placeholder="Full Name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($registration['email']); ?>" required placeholder="Email Address">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($registration['phone']); ?>" required placeholder="Phone Number">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($registration['age']); ?>" required>
                    </div>

                    <div class="form-group half">
                        <label for="gender">Gender</label>
                        <div class="select-wrapper">
                            <select id="gender" name="gender" required>
                                <option value="Male" <?php echo ($registration['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($registration['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                            <i class="fas fa-chevron-down select-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <div class="select-wrapper">
                        <select id="category" name="category" required>
                            <option value="Skateboard" <?php echo ($registration['category'] == 'Skateboard') ? 'selected' : ''; ?>>Skateboard</option>
                            <option value="Inline" <?php echo ($registration['category'] == 'Inline') ? 'selected' : ''; ?>>Inline</option>
                            <option value="BMX" <?php echo ($registration['category'] == 'BMX') ? 'selected' : ''; ?>>BMX</option>
                        </select>
                        <i class="fas fa-chevron-down select-icon"></i>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="javascript:void(0);" onclick="history.back();" class="btn btn-cancel">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-update">
                        Update Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>