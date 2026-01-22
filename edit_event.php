<?php
$conn = new mysqli("localhost", "root", "", "basf_events");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_GET['id'];

$event = $conn->query("SELECT * FROM upcoming_events WHERE id = $event_id")->fetch_assoc();

$schedules = $conn->query("SELECT * FROM event_schedules WHERE event_id = $event_id");

$images = $conn->query("SELECT * FROM event_images WHERE event_id = $event_id");

$sponsors = $conn->query("SELECT * FROM sponsor_logos WHERE event_id = $event_id");

$is_paid = ($event['registration_fee'] > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="Css/edit_event.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
    <div class="admin-container">
        <form action="update_event.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">

            <label>Event Name:</label>
            <input type="text" name="event_name" value="<?php echo $event['event_name']; ?>" required>

            <label>Location:</label>
            <input type="text" name="location" value="<?php echo $event['location']; ?>" required>

            <label>Category:</label>
            <select name="category">
                <option value="skateboard" <?php if ($event['category'] == "skateboard") echo "selected"; ?>>Skateboard</option>
                <option value="inline" <?php if ($event['category'] == "inline") echo "selected"; ?>>Inline</option>
                <option value="bmx" <?php if ($event['category'] == "bmx") echo "selected"; ?>>BMX</option>
                <option value="all" <?php if ($event['category'] == "all") echo "selected"; ?>>All</option>
            </select>

            <label>Description:</label>
            <textarea name="description" id="description"><?php echo $event['description']; ?></textarea>

            <label>Registration:
            <input type="checkbox" name="registration" id="registration" <?php if ($event['registration']) echo "checked"; ?>>
            <span>Yes</span>
            </label>

            <div id="registration-options" style="display: <?php echo $event['registration'] ? 'block' : 'none'; ?>;">
                <label>Registration Limit:</label>
                <input type="number" name="registration_limit" value="<?php echo isset($event['registration_limit']) ? $event['registration_limit'] : ''; ?>" min="1">

                <label>Registration Type:</label>
                <select name="registration_type" id="registration_type">
                    <option value="free" <?php echo (!$is_paid) ? 'selected' : ''; ?>>Free</option>
                    <option value="paid" <?php echo ($is_paid) ? 'selected' : ''; ?>>Paid</option>
                </select>

                <div id="fee-container" style="display: <?php echo ($is_paid) ? 'block' : 'none'; ?>; margin-top: 10px;">
                    <label>Registration Fee (PHP):</label>
                    <input type="number" name="registration_fee" id="registration_fee" min="1" step="0.01" value="<?php echo $event['registration_fee']; ?>">
                </div>
            </div>

            <h3>Schedules:</h3>
            <div id="schedule-container">
                <?php while ($schedule = $schedules->fetch_assoc()): ?>
                <div>
                    <label>Date:</label>
                    <input type="date" name="event_date[]" value="<?php echo $schedule['event_date']; ?>" required>
                    <label>Start Time:</label>
                    <input type="time" name="start_time[]" value="<?php echo $schedule['start_time']; ?>" required>
                    <label>End Time:</label>
                    <input type="time" name="end_time[]" value="<?php echo $schedule['end_time']; ?>" required>
                    <button type="button" onclick="this.parentElement.remove()">Remove</button>
                </div>
                <?php endwhile; ?>
            </div>
            <button type="button" id="add-schedule">Add Schedule</button>

            <h3>Posters:</h3>
                <div id="posters-container">
                    <?php while ($image = $images->fetch_assoc()): ?>
                    <div class="poster-item">
                        <img src="<?php echo $image['image_path']; ?>" alt="Poster" style="width: 100px; height: auto;">
                        <input type="hidden" name="existing_posters[]" value="<?php echo $image['image_path']; ?>">
                        <button type="button" onclick="removeElement(this)">Remove</button>
                    </div>
                    <?php endwhile; ?>
                </div>
                <label>Upload New Posters:</label>
                <input type="file" name="posters[]" multiple>

                <h3>Sponsor Logos:</h3>
                <div id="sponsors-container">
                    <?php while ($sponsor = $sponsors->fetch_assoc()): ?>
                    <div class="sponsor-item">
                        <img src="<?php echo $sponsor['logo_path']; ?>" alt="Sponsor" style="width: 100px; height: auto;">
                        <input type="hidden" name="existing_sponsors[]" value="<?php echo $sponsor['logo_path']; ?>">
                        <button type="button" onclick="removeElement(this)">Remove</button>
                    </div>
                    <?php endwhile; ?>
                </div>
                <label>Upload New Sponsors:</label>
                <input type="file" name="sponsors[]" multiple>

            <button type="submit">Update Event</button>
        </form>
    </div>
    <script>
        document.getElementById('add-schedule').addEventListener('click', function () {
            const container = document.getElementById('schedule-container');
            const scheduleDiv = document.createElement('div');
            scheduleDiv.innerHTML = `
                <label>Date:</label>
                <input type="date" name="event_date[]" required>
                <label>Start Time:</label>
                <input type="time" name="start_time[]" required>
                <label>End Time:</label>
                <input type="time" name="end_time[]" required>
                <button type="button" onclick="this.parentElement.remove()">Remove</button>
            `;
            container.appendChild(scheduleDiv);
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
    <script>
        function removeElement(button) {
            button.parentElement.remove();
        }

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
    </script>
</body>
</html>

<?php $conn->close(); ?>