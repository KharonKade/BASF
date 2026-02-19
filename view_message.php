<?php
session_start(); 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'secrets.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$statusMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_reply'])) {
    $recipient_email = $_POST['recipient_email'];
    $subject_line = "Re: " . $_POST['original_subject'];
    $reply_body = $_POST['reply_message'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER; 
        $mail->Password   = SMTP_PASS; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom($mail->Username, 'BASF Team');
        $mail->addAddress($recipient_email);

        $mail->isHTML(true);
        $mail->Subject = $subject_line;
        $mail->Body    = nl2br($reply_body);
        $mail->AltBody = $reply_body;

        $mail->send();

        $update_sql = "UPDATE contact_inquiries SET archived = 1 WHERE id = $id";
        if ($conn->query($update_sql) === TRUE) {
            $_SESSION['highlight_archive_id'] = $id;
            
            header("Location: archived_inquiries.php?status=replied");
            exit();
        } else {
            $statusMsg = "<div class='alert error'>Reply sent, but failed to archive inquiry: " . $conn->error . "</div>";
        }

    } catch (Exception $e) {
        $statusMsg = "<div class='alert error'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
    }
}

$sql_all_inquiries = "SELECT id FROM contact_inquiries ORDER BY id DESC";
$result_all = $conn->query($sql_all_inquiries);

$counter = 1;
$inquiry_position = 0;
while($row_all = $result_all->fetch_assoc()) {
    if ($row_all['id'] == $id) {
        $inquiry_position = $counter;
        break;
    }
    $counter++;
}

$sql = "SELECT * FROM contact_inquiries WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inquiry Message</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/view_message.css?v=1.1">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .reply-section { margin-top: 30px; border-top: 2px solid #eee; padding-top: 20px; }
        .reply-form textarea { width: 95%; height: 150px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; resize: vertical; margin-bottom: 15px; }
        .reply-form label { font-weight: 600; display: block; margin-bottom: 8px; color: #333; }
        .btn-send { background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 16px; transition: 0.3s; }
        .btn-send:hover { background-color: #218838; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="admin-container">
        
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <nav class="sidebar" id="sidebar">
            <button class="close-sidebar" id="closeSidebar"><i class="fas fa-times"></i></button>
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manage_upcoming.php"><i class="fas fa-calendar-check"></i> Events</a></li>
                <li><a href="manage_news.php"><i class="fas fa-edit"></i> News & Announcements</a></li>
                <li><a href="admin_gallery.php"><i class="fas fa-images"></i> Gallery Page</a></li>
                <li><a href="editInlinePage.php"><i class="fas fa-skating"></i> Inline Page</a></li>
                <li><a href="editBmxPage.php"><i class="fas fa-bicycle"></i> BMX Page</a></li>
                <li><a href="editSkateboardPage.php"><i class="fas fa-snowboarding"></i> Skateboard Page</a></li>
                <li><a href="view_inquiries.php"><i class="fas fa-question-circle"></i> Inquiries</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="top-header">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            </div>

            <div class="admin-wrapper">
                <div class="page-header">
                    <div class="header-content">
                        <div class="inquiry-id">Inquiry #<?php echo $inquiry_position; ?></div>
                        <h1>Message Details</h1>
                    </div>
                    <button onclick="history.back()" class="btn-secondary">Return</button>
                </div>

                <?php echo $statusMsg; ?>

                <div class="message-grid">
                    <div class="sidebar-details">
                        <div class="card sender-card">
                            <h3>Sender Information</h3>
                            <div class="detail-item">
                                <label>Full Name</label>
                                <p class="sender-name"><?php echo htmlspecialchars($row['full_name']); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Email Address</label>
                                <p><a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="email-link"><?php echo htmlspecialchars($row['email']); ?></a></p>
                            </div>
                            <div class="detail-item">
                                <label>Phone Number</label>
                                <p><?php echo htmlspecialchars($row['contact_number']); ?></p>
                            </div>
                        </div>

                        <div class="card meta-card">
                            <div class="detail-item">
                                <label>Submitted On</label>
                                <p><?php echo date('F j, Y, g:i a', strtotime($row['submitted_at'])); ?></p>
                            </div>
                            <div class="detail-item">
                                <label>Concern Category</label>
                                <span class="badge-pill"><?php echo htmlspecialchars($row['concerns']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="main-message">
                        <?php if (strtolower(trim($row['concerns'])) === 'sponsorship inquiry'): ?>
                        <div class="card sponsorship-alert">
                            <div class="alert-icon">🏢</div>
                            <div>
                                <label>Company Represented</label>
                                <p><strong><?php echo htmlspecialchars($row['company_name']); ?></strong></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="card message-body-card">
                            <h3>Message Content</h3>
                            <div class="message-text">
                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                            </div>

                            <div class="reply-section">
                                <h3>Reply to User</h3>
                                <form method="POST" class="reply-form">
                                    <input type="hidden" name="recipient_email" value="<?php echo htmlspecialchars($row['email']); ?>">
                                    <input type="hidden" name="original_subject" value="<?php echo htmlspecialchars($row['concerns']); ?>">
                                    
                                    <label>Your Response</label>
                                    <textarea name="reply_message" placeholder="Type your reply here..." required></textarea>
                                    
                                    <button type="submit" name="send_reply" class="btn-send">Send Reply & Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('active');
            document.getElementById('sidebarOverlay').classList.add('active');
        });

        document.getElementById('closeSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            document.getElementById('sidebarOverlay').classList.remove('active');
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            this.classList.remove('active');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>