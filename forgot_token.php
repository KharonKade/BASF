<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_events";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    if ($event_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid event context.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT name, token FROM event_registrations WHERE email = ? AND event_id = ? LIMIT 1");
    $stmt->bind_param("si", $email, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['name'];
        $user_token = $row['token'];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kharontogana371@gmail.com'; 
            $mail->Password   = 'mdub rwug jftk eqah'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email_here@gmail.com', 'BASF Events');
            $mail->addAddress($email, $user_name);

            $mail->isHTML(true);
            $mail->Subject = 'Your Event Registration Token';
            $mail->Body    = "<div style='font-family: Poppins, sans-serif; color: #333;'>
                                <h3>Hello, {$user_name}</h3>
                                <p>You requested to retrieve your registration token for this event.</p>
                                <p><strong>Your Token:</strong> <span style='color: #25523B; font-size: 1.2em; font-weight: 600;'>{$user_token}</span></p>
                                <p>Use this token to manage your registration details.</p>
                                <br>
                                <small>If you did not request this, please ignore this email.</small>
                              </div>";
            $mail->AltBody = "Hello {$user_name}, Your registration token is: {$user_token}";

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Token has been sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'This email is not registered for this specific event.']);
    }

    $stmt->close();
}
$conn->close();
?>