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
$event_id = $registration['event_id'];

$evt_stmt = $conn->prepare("SELECT category FROM upcoming_events WHERE id = ?");
$evt_stmt->bind_param("i", $event_id);
$evt_stmt->execute();
$evt_res = $evt_stmt->get_result();
$event_data = $evt_res->fetch_assoc();
$event_base_category = strtolower($event_data['category'] ?? 'all');
$evt_stmt->close();

$categories_sql = "SELECT sport_type, category_name FROM event_categories WHERE event_id = $event_id";
$categories_result = $conn->query($categories_sql);
$dynamic_categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $sport = strtolower($row['sport_type']);
        $dynamic_categories[$sport][] = $row['category_name'];
    }
}
$dynamic_categories_json = json_encode($dynamic_categories);

$current_category = (string)$registration['category'];
$cat_parts = explode(' - ', $current_category);
$current_sport = '';
$current_sub = '';

if (count($cat_parts) === 2) {
    $current_sport = strtolower(trim((string)$cat_parts));
    $current_sub = trim((string)$cat_parts);
} else {
    $current_sport = strtolower(trim($current_category));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    $sport_category = strtolower(trim($_POST['sport_category'] ?? ''));
    $sub_category = trim($_POST['sub_category'] ?? '');
    
    if ($event_base_category !== 'all') {
        $sport_category = $event_base_category;
    }
    
    if (empty($sub_category)) {
        $category = ucfirst($sport_category); 
    } else {
        $category = ucfirst($sport_category) . " - " . $sub_category;
    }

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
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Css/edit_registration.css">
    <style>
        body, html, * {
            font-family: 'Poppins', sans-serif !important;
        }
        .readonly-input {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
    </style>
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

                <div class="form-row">
                    <div class="form-group half">
                        <label for="sportCategory">Sport Category</label>
                        <div class="select-wrapper">
                            <?php if ($event_base_category === 'all'): ?>
                                <select id="sportCategory" name="sport_category" required>
                                    <option value="">Select Sport...</option>
                                    <option value="skateboard" <?php echo ($current_sport == 'skateboard') ? 'selected' : ''; ?>>Skateboard</option>
                                    <option value="inline" <?php echo ($current_sport == 'inline') ? 'selected' : ''; ?>>Inline</option>
                                    <option value="bmx" <?php echo ($current_sport == 'bmx') ? 'selected' : ''; ?>>BMX</option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            <?php else: ?>
                                <input type="hidden" name="sport_category" id="sportCategory" value="<?php echo htmlspecialchars($event_base_category); ?>">
                                <input type="text" class="readonly-input" value="<?php echo ucfirst(htmlspecialchars($event_base_category)); ?>" readonly>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group half">
                        <label for="sub_category">Event Category</label>
                        <div class="select-wrapper">
                            <select id="sub_category" name="sub_category" required disabled>
                                <option value="">Select Event Category...</option>
                            </select>
                            <i class="fas fa-chevron-down select-icon"></i>
                        </div>
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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dynamicCategories = <?php echo $dynamic_categories_json; ?>;
            const sportCategoryInput = document.getElementById('sportCategory');
            const subCategorySelect = document.getElementById('sub_category');
            const currentSub = "<?php echo htmlspecialchars($current_sub); ?>";
            const defaultSport = "<?php echo $event_base_category === 'all' ? htmlspecialchars($current_sport) : htmlspecialchars($event_base_category); ?>";

            function updateSubCategories(sportType, preselect = '') {
                subCategorySelect.innerHTML = '<option value="">Select Event Category...</option>';
                subCategorySelect.disabled = true;

                if (sportType && dynamicCategories[sportType]) {
                    dynamicCategories[sportType].forEach(catName => {
                        const option = document.createElement('option');
                        option.value = catName;
                        option.text = catName;
                        if (catName === preselect) {
                            option.selected = true;
                        }
                        subCategorySelect.appendChild(option);
                    });
                    subCategorySelect.disabled = false;
                }
            }

            if (sportCategoryInput && sportCategoryInput.tagName === 'SELECT') {
                sportCategoryInput.addEventListener('change', function() {
                    updateSubCategories(this.value.toLowerCase());
                });
                
                if (sportCategoryInput.value) {
                    updateSubCategories(sportCategoryInput.value.toLowerCase(), currentSub);
                }
            } else if (sportCategoryInput) {
                updateSubCategories(defaultSport, currentSub);
            }
        });
    </script>
</body>
</html>