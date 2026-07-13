<?php
require_once 'secrets.php'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Error: Missing Registration Token in URL.");
}

$token = $_GET['token'];

// 1. Fetch the registration and session ID directly from the database
$stmt = $conn->prepare("SELECT paymongo_id, status, event_id, name, email, category FROM event_registrations WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$reg_result = $stmt->get_result();
$reg_data = $reg_result->fetch_assoc();
$stmt->close();

if (!$reg_data || empty($reg_data['paymongo_id'])) {
    echo "<div style='font-family: Poppins, sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1>Verification Error</h1>";
    echo "<p>We couldn't find a pending registration for this token.</p>";
    echo "<p>If your payment was successful, please contact support with your Token: <strong>" . htmlspecialchars($token) . "</strong></p>";
    echo "</div>";
    exit;
}

$session_id = $reg_data['paymongo_id'];
$event_id = $reg_data['event_id'];

// If it's already marked paid, just send them to the success screen
if ($reg_data['status'] === 'paid') {
    header("Location: eventPages.php?id=" . $event_id . "&success_token=" . $token);
    exit;
}

// 2. Ask PayMongo if the session was paid
$ch = curl_init('https://api.paymongo.com/v1/checkout_sessions/' . $session_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($paymongo_secret_key)
]);

$response = curl_exec($ch);
curl_close($ch);
$api_result = json_decode($response, true);

$is_paid = false;
$status_found = "unknown";

if (isset($api_result['data']['attributes']['payment_intent']['attributes']['status'])) {
    $status_found = $api_result['data']['attributes']['payment_intent']['attributes']['status'];
    if ($status_found === 'succeeded') {
        $is_paid = true;
    }
}

if (!$is_paid && isset($api_result['data']['attributes']['payments'])) {
    foreach ($api_result['data']['attributes']['payments'] as $payment) {
        if (isset($payment['attributes']['status']) && $payment['attributes']['status'] === 'paid') {
            $is_paid = true;
            $status_found = 'paid';
            break;
        }
    }
}

// 3. Update DB and send Email if paid
if ($is_paid) {
    // Update the pending status to paid
    $update_stmt = $conn->prepare("UPDATE event_registrations SET status = 'paid' WHERE token = ?");
    $update_stmt->bind_param("s", $token);
    $update_stmt->execute();
    $update_stmt->close();

    // Fetch details for the email receipt
    $evt_stmt = $conn->prepare("SELECT event_name FROM upcoming_events WHERE id = ?");
    $evt_stmt->bind_param("i", $event_id);
    $evt_stmt->execute();
    $evt_result = $evt_stmt->get_result();
    $event_data = $evt_result->fetch_assoc();
    $evt_stmt->close();
    
    $event_name = $event_data['event_name'] ?? 'Event';
    $registration_fee = 0.00;
    
    $cat_parts = explode(' - ', (string)$reg_data['category']);
    if (count($cat_parts) === 2) {
        $sport_type = trim(strtolower((string)$cat_parts));
        $sub_category = trim((string)$cat_parts);
        
        $fee_stmt = $conn->prepare("SELECT fee FROM event_categories WHERE event_id = ? AND LOWER(sport_type) = ? AND category_name = ?");
        $fee_stmt->bind_param("iss", $event_id, $sport_type, $sub_category);
        $fee_stmt->execute();
        $fee_result = $fee_stmt->get_result();
        if ($fee_data = $fee_result->fetch_assoc()) {
            $registration_fee = (float)$fee_data['fee'];
        }
        $fee_stmt->close();
    }

    $to = $reg_data['email'];
    $subject = "Payment Receipt - BASF Event Registration";
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap");
            body { font-family: "Poppins", sans-serif; background-color: #f4f4f4; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
            .details { margin-bottom: 20px; }
            .details p { margin: 5px 0; font-size: 14px; color: #555; }
            .token-box { background: #e8f0fe; color: #1a73e8; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; letter-spacing: 2px; }
            .footer { text-align: center; font-size: 12px; color: #aaa; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>OFFICIAL RECEIPT</h2>
                <p>Thank you for your payment!</p>
            </div>
            <div class="details">
                <p><strong>Event:</strong> ' . htmlspecialchars($event_name) . '</p>
                <p><strong>Participant:</strong> ' . htmlspecialchars($reg_data['name']) . '</p>
                <p><strong>Date Paid:</strong> ' . date("F j, Y, g:i a") . '</p>
                <p><strong>Amount Paid:</strong> PHP ' . number_format($registration_fee, 2) . '</p>
                <p><strong>Reference ID:</strong> ' . htmlspecialchars($session_id) . '</p>
            </div>
            <p style="text-align: center;">Here is your unique registration token. Please present this at the event entry.</p>
            <div class="token-box">' . htmlspecialchars($token) . '</div>
            <div class="footer">
                <p>BASF Events Team</p>
                <p>This is an automated message. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@basfevents.com" . "\r\n";

    mail($to, $subject, $message, $headers);

    header("Location: eventPages.php?id=" . $event_id . "&success_token=" . $token);
    exit;
} else {
    echo "<div style='font-family: Poppins, sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1>Payment Verification Failed</h1>";
    echo "<p>The payment status is currently: <strong>" . htmlspecialchars($status_found) . "</strong></p>";
    echo "<p>Please ensure you completed the payment in the popup window. If you did, it may take a minute to process.</p>";
    echo "<br><a href='eventPages.php?id=$event_id'>Return to Event Page</a>";
    echo "</div>";
}

$conn->close();
?>