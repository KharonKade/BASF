<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link rel="stylesheet" href="Css/admin.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
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
        <main class="content" id="create_event">
            <h2>Create Event</h2>
            <div class="form-container">
            <form action="store_event.php" method="post" enctype="multipart/form-data">
                <label for="event_name">Event Name:</label>
                <input type="text" name="event_name" id="event_name" required>

                <label for="location">Location:</label>
                <input type="text" name="location" id="location" required>

                <label for="description">Event Description:</label>
                <textarea name="description" id="description" style="display:none;"></textarea>

                <label for="category">Event For:</label>
                <select name="category" id="category" required>
                    <option value="skateboard">Skateboard</option>
                    <option value="inline">Inline</option>
                    <option value="bmx">BMX</option>
                    <option value="All">All</option>
                </select>

                <label for="registration">
                    Event Registration:
                    <input type="checkbox" name="registration" id="registration" value="1">
                    <span>Yes</span>
                </label>

                <div id="registration-options" style="display: none;">
                    <label for="registration_limit">Registration Limit:</label>
                    <input type="number" name="registration_limit" id="registration_limit" min="1">
                    
                    <label for="registration_type">Registration Type:</label>
                    <select name="registration_type" id="registration_type">
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>

                    <div id="fee-container" style="display: none; margin-top: 10px;">
                        <label for="registration_fee">Registration Fee (PHP):</label>
                        <input type="number" name="registration_fee" id="registration_fee" min="1" step="0.01">
                    </div>
                </div>

                <div id="schedule-container">
                    <div class="schedule-item">
                        <label for="event_date">Event Date:</label>
                        <input type="date" name="event_date[]" required>
                        <label for="start_time">Start Time:</label>
                        <input type="time" name="start_time[]" required>
                        <label for="end_time">End Time:</label>
                        <input type="time" name="end_time[]" required>
                    </div>
                </div>
                <button type="button" id="add_schedule">Add Another Schedule</button>

                <label for="posters">Event Posters:</label>
                <input type="file" name="posters[]" id="posters" multiple required>

                <label for="sponsors">Sponsor Logos:</label>
                <input type="file" name="sponsors[]" id="sponsors" multiple required>

                <button type="submit">Create Event</button>
            </form>
            </div>
        </main>
    </div>

    <script>
        let editorInstance;

        ClassicEditor
        .create(document.querySelector('#description'))
        .then(editor => {
            editor.ui.view.editable.element.parentElement.style.display = 'block';
            editorInstance = editor;
        })
        .catch(error => {
            console.error(error);
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            try {
                if (editorInstance) {
                    document.querySelector('#description').value = editorInstance.getData();
                }
            } catch (error) {
                console.error("CKEditor content sync failed:", error);
            }
        });

        const addScheduleButton = document.getElementById('add_schedule');
        const scheduleContainer = document.getElementById('schedule-container');

        addScheduleButton.addEventListener('click', () => {
            const newSchedule = document.createElement('div');
            newSchedule.classList.add('schedule-item');

            newSchedule.innerHTML = `
                <label for="event_date">Event Date:</label>
                <input type="date" name="event_date[]" required>
                <label for="start_time">Start Time:</label>
                <input type="time" name="start_time[]" required>
                <label for="end_time">End Time:</label>
                <input type="time" name="end_time[]" required>
                <button type="button" class="remove-schedule">Remove</button>
            `;

            scheduleContainer.appendChild(newSchedule);

            newSchedule.querySelector('.remove-schedule').addEventListener('click', () => {
                newSchedule.remove();
            });
        });

        document.getElementById("registration").addEventListener("change", function () {
            var registrationOptions = document.getElementById("registration-options");
            registrationOptions.style.display = this.checked ? "block" : "none";
        });

        document.getElementById("registration_type").addEventListener("change", function () {
            var feeContainer = document.getElementById("fee-container");
            var feeInput = document.getElementById("registration_fee");
            
            if (this.value === "paid") {
                feeContainer.style.display = "block";
                feeInput.required = true;
            } else {
                feeContainer.style.display = "none";
                feeInput.required = false;
                feeInput.value = "";
            }
        });
    </script>
</body>
</html>