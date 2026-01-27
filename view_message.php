<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "contact_us");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the inquiry by ID
$id = $_GET['id'];

// Fetch all inquiries to calculate sequential numbering
$sql_all_inquiries = "SELECT id FROM contact_inquiries ORDER BY id DESC";
$result_all = $conn->query($sql_all_inquiries);

// Find the position of the current inquiry
$counter = 1;
$inquiry_position = 0;
while($row_all = $result_all->fetch_assoc()) {
    if ($row_all['id'] == $id) {
        $inquiry_position = $counter;
        break;
    }
    $counter++;
}

// Now fetch the detailed inquiry based on the ID
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
    <link rel="stylesheet" href="Css/view_message.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
    <div class="page-header">
        <div class="header-content">
            <div class="inquiry-id">Inquiry #<?php echo $inquiry_position; ?></div>
            <h1>Message Details</h1>
        </div>
        <button onclick="history.back()" class="btn-secondary">Return</button>
    </div>

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
            </div>
            
            <div class="action-footer">
                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>?subject=Re: <?php echo htmlspecialchars($row['concerns']); ?>" class="btn-primary">
                    Reply via Email
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
