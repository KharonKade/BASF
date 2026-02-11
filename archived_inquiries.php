<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "contact_us");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Logic to determine which row to highlight
$highlight_id = 0;

// 1. Check if ID is in Session (Persistent)
if (isset($_SESSION['highlight_archive_id'])) {
    $highlight_id = $_SESSION['highlight_archive_id'];
}

// 2. Check if ID is in URL (Overrides session if present)
if (isset($_GET['highlight_id'])) {
    $highlight_id = intval($_GET['highlight_id']);
    $_SESSION['highlight_archive_id'] = $highlight_id; // Update session
}

$sql = "SELECT id, full_name, email, contact_number, concerns, message, submitted_at, archived FROM contact_inquiries WHERE archived = 1 ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Inquiries</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/archived_inquiries.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .highlight-row {
            background-color: #d1e7dd !important; 
            border-left: 5px solid #0f5132;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manage_upcoming.php"><i class="fas fa-calendar-check"></i>Events</a></li>
                <li><a href="manage_news.php"><i class="fas fa-edit"></i>News & Announcements</a></li>
                <li><a href="admin_gallery.php"><i class="fas fa-images"></i>Gallery Page</a></li>
                <li><a href="editInlinePage.php"><i class="fas fa-skating"></i>Inline Page</a></li>
                <li><a href="editBmxPage.php"><i class="fas fa-bicycle"></i>BMX Page</a></li>
                <li><a href="editSkateboardPage.php"><i class="fas fa-snowboarding"></i>Skateboard Page</a></li>
                <li><a href="view_inquiries.php"><i class="fas fa-question-circle"></i> Inquiries</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <div class="content">
            <h2>Archived Inquiries</h2>

            <div style="margin-bottom: 20px;">
                <button type="button" class="delete-all-btn" onclick="confirmDeleteAll()">Delete All</button>
            </div>

            <?php
            if ($result->num_rows > 0) {
                $counter = 1; 
                echo "<table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Concerns</th>
                                <th>Message</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";

                while($row = $result->fetch_assoc()) {
                    $shortMessage = strlen($row["message"]) > 25 ? substr($row["message"], 0, 25) . '...' : $row["message"];
                    
                    // Check if this row matches the highlight ID
                    $rowClass = ($row['id'] == $highlight_id) ? 'highlight-row' : '';
                    
                    echo "<tr class='$rowClass' id='inquiry-" . $row['id'] . "'>
                            <td>" . $counter++ . "</td> 
                            <td>" . htmlspecialchars($row["full_name"]) . "</td>
                            <td>" . htmlspecialchars($row["email"]) . "</td>
                            <td>" . htmlspecialchars($row["contact_number"]) . "</td>
                            <td>" . htmlspecialchars($row["concerns"]) . "</td>
                            <td>" . $shortMessage . "</td>
                            <td>" . htmlspecialchars($row["submitted_at"]) . "</td>
                                <td>
                                    <a href='view_message.php?id=" . $row["id"] . "' title='View'>
                                        <i class='fas fa-eye'></i>
                                    </a> |
                                    <a href='javascript:void(0);' onclick='confirmRestore(" . $row["id"] . ")' title='Restore'>
                                        <i class='fas fa-undo'></i>
                                    </a> |
                                    <a href='javascript:void(0);' onclick='confirmDelete(" . $row["id"] . ")' title='Delete'>
                                        <i class='fas fa-trash'></i>
                                    </a>
                                </td>
                          </tr>";
                }

                echo "</tbody>
                      </table>";
            } else {
                echo "<p>No archived inquiries found.</p>";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var highlightedRow = document.querySelector(".highlight-row");
            if (highlightedRow) {
                highlightedRow.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        });

        function confirmDeleteAll() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete ALL archived inquiries. You cannot revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete all!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_all_inquiries.php';
                }
            });
        }

        function confirmRestore(id) {
            Swal.fire({
                title: 'Restore Inquiry?',
                text: "This inquiry will be moved back to the active list.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'restore_inquiry.php?id=' + id;
                }
            });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Permanently Delete?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_inquiry.php?id=' + id + '&redirect=archive';
                }
            });
        }

        <?php if (isset($_GET['status'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($_GET['status'] == 'replied'): ?>
                    Swal.fire({
                        title: 'Sent & Archived!',
                        text: 'The reply was sent and the inquiry has been moved to archives.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                <?php elseif ($_GET['status'] == 'restored'): ?>
                    Swal.fire({
                        title: 'Restored!',
                        text: 'Inquiry has been restored successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'archived_inquiries.php';
                        }
                    });
                <?php elseif ($_GET['status'] == 'deleted_all'): ?>
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'All archived inquiries have been deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'archived_inquiries.php';
                        }
                    });
                <?php elseif ($_GET['status'] == 'deleted'): ?>
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Inquiry has been permanently deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'archived_inquiries.php';
                        }
                    });
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>