<?php
header('Content-Type: application/json');
ob_start();

require_once 'secrets.php';

$servername = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$dbname = "u142318015_db_vf0t87O1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
$verify_response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
$captcha_data = json_decode($verify_response, true);

if (!$captcha_data['success']) {
    echo json_encode(["success" => false, "message" => "reCAPTCHA verification failed."]);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$age = (int)($_POST['age'] ?? 0);
$gender = $_POST['gender'] ?? '';
$submitted_category = $_POST['category'] ?? ''; 
$submitted_sub_category = $_POST['sub_category'] ?? ''; 
$event_id = (int)($_POST['event_id'] ?? 0);

if (empty($name) || empty($email) || empty($phone) || $age <= 0 || empty($gender) || empty($submitted_sub_category) || $event_id <= 0) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields correctly."]);
    exit;
}

// 1. Verify main event exists
$event_stmt = $conn->prepare("SELECT category FROM upcoming_events WHERE id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event_data = $event_result->fetch_assoc();
$event_stmt->close();

if (!$event_data) {
    echo json_encode(["success" => false, "message" => "Event not found."]);
    exit;
}

$db_category = strtolower($event_data['category']);

// 2. Determine exact sport type
if ($db_category === 'all') {
    $sport_type = strtolower($submitted_category);
    $valid_categories = ['skateboard', 'inline', 'bmx'];
    if (!in_array($sport_type, $valid_categories)) {
        echo json_encode(["success" => false, "message" => "Invalid sport category selected."]);
        exit;
    }
} else {
    $sport_type = $db_category;
}

// 3. Securely fetch the exact fee for the chosen sub-category from the database
$cat_stmt = $conn->prepare("SELECT fee FROM event_categories WHERE event_id = ? AND LOWER(sport_type) = ? AND category_name = ?");
$cat_stmt->bind_param("iss", $event_id, $sport_type, $submitted_sub_category);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$cat_data = $cat_result->fetch_assoc();
$cat_stmt->close();

if (!$cat_data) {
    echo json_encode(["success" => false, "message" => "Invalid event category selected."]);
    exit;
}

// 4. Set the dynamic fee and final category string for the database
$registration_fee = (float)$cat_data['fee'];
$final_category = ucfirst($sport_type) . " - " . $submitted_sub_category;

function generateToken($length = 6) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $token;
}

$token = generateToken();

if ($registration_fee > 0) {
    $amount = $registration_fee * 100; 
    $description = "Registration Fee for " . $name;
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    
    $success_url = $domain . "/payment_callback.php?token=" . $token;
    $cancel_url = $domain . "/eventPages.php?id=" . $event_id;

    $data = [
        'data' => [
            'attributes' => [
                'billing' => [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ],
                'line_items' => [[
                    'currency' => 'PHP',
                    'amount' => $amount,
                    'description' => $description,
                    'name' => 'Event Registration',
                    'quantity' => 1
                ]],
                'payment_method_types' => ['gcash', 'card', 'paymaya'],
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'description' => $description,
                'metadata' => [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'age' => (string)$age,
                    'gender' => $gender,
                    'category' => $final_category,
                    'event_id' => (string)$event_id,
                    'token' => $token
                ]
            ]
        ]
    ];

    $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($paymongo_secret_key)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code == 200 && isset($result['data']['attributes']['checkout_url'])) {
        $checkout_url = $result['data']['attributes']['checkout_url'];
        $checkout_id = $result['data']['id'];

        // Save as pending in the database before redirecting
        $pending_status = 'pending';
        $pending_stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email, phone, age, gender, category, token, status, paymongo_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $pending_stmt->bind_param("isssisssss", $event_id, $name, $email, $phone, $age, $gender, $final_category, $token, $pending_status, $checkout_id);
        $pending_stmt->execute();
        $pending_stmt->close();

        ob_end_clean();
        echo json_encode(["success" => true, "is_paid_event" => true, "checkout_url" => $checkout_url]);
        exit;
    } else {
        ob_end_clean();
        error_log("PayMongo Error: " . $response);
        echo json_encode(["success" => false, "message" => "Failed to initiate payment."]);
        exit;
    }
} else {
    $status = 'paid';
    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email, phone, age, gender, category, token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssissss", $event_id, $name, $email, $phone, $age, $gender, $final_category, $token, $status);

    if ($stmt->execute()) {
        ob_end_clean();
        echo json_encode(["success" => true, "is_paid_event" => false, "token" => $token]);
        exit;
    } else {
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
        exit;
    }
    $stmt->close();
}

$conn->close();
?>