<?php
header('Content-Type: application/json');
ob_start();

require_once 'secrets.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

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
$event_id = (int)($_POST['event_id'] ?? 0);

if (empty($name) || empty($email) || empty($phone) || $age <= 0 || empty($gender) || empty($submitted_category) || $event_id <= 0) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields correctly."]);
    exit;
}

$event_stmt = $conn->prepare("SELECT registration_fee, category FROM upcoming_events WHERE id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event_data = $event_result->fetch_assoc();
$event_stmt->close();

if (!$event_data) {
    echo json_encode(["success" => false, "message" => "Event not found."]);
    exit;
}

$registration_fee = (float)$event_data['registration_fee'];
$db_category = $event_data['category'];

if ($db_category === 'all') {
    $valid_categories = ['Skateboard', 'Inline', 'BMX'];
    if (!in_array($submitted_category, $valid_categories)) {
        echo json_encode(["success" => false, "message" => "Invalid category selected."]);
        exit;
    }
    $final_category = $submitted_category;
} else {
    $final_category = $db_category;
}

function generateToken($length = 6) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $token;
}

$token = generateToken();
$status = ($registration_fee > 0) ? 'pending' : 'paid';

$stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email, phone, age, gender, category, token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssissss", $event_id, $name, $email, $phone, $age, $gender, $final_category, $token, $status);

if ($stmt->execute()) {
    $db_id = $stmt->insert_id;
    
    if ($registration_fee > 0) {
        $amount = $registration_fee * 100; 
        $description = "Registration Fee for " . $name;
        
        $domain = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
        $success_url = $domain . "/payment_callback.php?db_id=" . $db_id;
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
                        'db_id' => $db_id,
                        'token' => $token,
                        'event_id' => $event_id
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

            $update_stmt = $conn->prepare("UPDATE event_registrations SET paymongo_id = ? WHERE id = ?");
            $update_stmt->bind_param("si", $checkout_id, $db_id);
            $update_stmt->execute();

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
        ob_end_clean();
        echo json_encode(["success" => true, "is_paid_event" => false, "token" => $token]);
        exit;
    }

} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    exit;
}

$stmt->close();
$conn->close();
?>