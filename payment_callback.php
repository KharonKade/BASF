<?php
session_start();

require_once 'secrets.php'; 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['db_id']) || !is_numeric($_GET['db_id'])) {
    die("Error: Missing Database ID in URL.");
}

$db_id = (int)$_GET['db_id'];

$stmt = $conn->prepare("
    SELECT r.paymongo_id, r.token, r.event_id, r.status, r.name, r.email, e.event_name 
    FROM event_registrations r 
    JOIN upcoming_events e ON r.event_id = e.id 
    WHERE r.id = ?
");
$stmt->bind_param("i", $db_id);
$stmt->execute();
$result = $stmt->get_result();
$registration = $result->fetch_assoc();

if (!$registration) {
    die("Error: Registration record not found in database.");
}

$session_id = $registration['paymongo_id'];
$token = $registration['token'];
$event_id = $registration['event_id'];
$event_name = $registration['event_name'];
$user_name = $registration['name'];
$user_email = $registration['email'];
$current_status = $registration['status'];

if ($current_status === 'paid') {
    header("Location: eventPages.php?id=" . $event_id . "&success_token=" . $token);
    exit;
}

$ch = curl_init('https://api.paymongo.com/v1/checkout_sessions/' . $session_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
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

if ($is_paid) {
    if ($current_status !== 'paid') {
        $update_stmt = $conn->prepare("UPDATE event_registrations SET status = 'paid' WHERE id = ?");
        $update_stmt->bind_param("i", $db_id);
        $update_stmt->execute();

        $to = $user_email;
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
                    <p><strong>Participant:</strong> ' . htmlspecialchars($user_name) . '</p>
                    <p><strong>Date Paid:</strong> ' . date("F j, Y, g:i a") . '</p>
                    <p><strong>Amount Paid:</strong> PHP 100.00</p>
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
    }

    header("Location: eventPages.php?id=" . $event_id . "&success_token=" . $token);
    exit;
} else {
    echo "<div style='font-family: Poppins, sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1>Payment Verification Failed</h1>";
    echo "<p>We found the session, but the payment status is: <strong>" . htmlspecialchars($status_found) . "</strong></p>";
    echo "<p>Please ensure you completed the payment in the popup window.</p>";
    echo "<br><a href='eventPages.php?id=$event_id'>Return to Event Page</a>";
    echo "</div>";
}

$conn->close();
?>