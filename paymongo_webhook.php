<?php
$input = @file_get_contents("php://input");
$event = json_decode($input, true);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

$conn = new mysqli($servername, $username, $password, $dbname);

if (!$event || !isset($event['data']['attributes']['type'])) {
    http_response_code(400);
    exit;
}

$type = $event['data']['attributes']['type'];

if ($type === 'checkout_session.payment.paid') {
    $data = $event['data']['attributes']['data']['attributes'];
    $metadata = $data['metadata'] ?? [];
    
    $db_id = $metadata['db_id'] ?? null;
    $token = $metadata['token'] ?? null;
    $paymongo_id = $event['data']['attributes']['data']['id'];
    
    if ($db_id) {
        $stmt = $conn->prepare("UPDATE event_registrations SET status = 'paid' WHERE id = ?");
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $stmt->close();

        $user_sql = "SELECT r.email, r.name, e.event_name FROM event_registrations r JOIN upcoming_events e ON r.event_id = e.id WHERE r.id = $db_id";
        $result = $conn->query($user_sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $to = $user['email'];
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
                        <p><strong>Event:</strong> ' . htmlspecialchars($user['event_name']) . '</p>
                        <p><strong>Participant:</strong> ' . htmlspecialchars($user['name']) . '</p>
                        <p><strong>Date Paid:</strong> ' . date("F j, Y, g:i a") . '</p>
                        <p><strong>Amount Paid:</strong> PHP 100.00</p>
                        <p><strong>Reference ID:</strong> ' . htmlspecialchars($paymongo_id) . '</p>
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
    }
}

http_response_code(200);
$conn->close();
?>