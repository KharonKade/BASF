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
    <div class="admin-wrapper">
    <div class="page-header">
        <h2>Edit Event</h2>
        <p>Update your event details, schedule, and media below.</p>
    </div>

    <form action="update_event.php" method="post" enctype="multipart/form-data" class="main-form">
        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">

        <div class="form-card">
            <div class="card-header">
                <h3>Event Details</h3>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Event Name</label>
                        <input type="text" name="event_name" value="<?php echo $event['event_name']; ?>" placeholder="Enter event name" required>
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo $event['location']; ?>" placeholder="Enter location" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <div class="select-wrapper">
                        <select name="category">
                            <option value="skateboard" <?php if ($event['category'] == "skateboard") echo "selected"; ?>>Skateboard</option>
                            <option value="inline" <?php if ($event['category'] == "inline") echo "selected"; ?>>Inline</option>
                            <option value="bmx" <?php if ($event['category'] == "bmx") echo "selected"; ?>>BMX</option>
                            <option value="all" <?php if ($event['category'] == "all") echo "selected"; ?>>All</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description"><?php echo $event['description']; ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h3>Registration Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group toggle-group">
                    <label class="switch">
                        <input type="checkbox" name="registration" id="registration" <?php if ($event['registration']) echo "checked"; ?>>
                        <span class="slider round"></span>
                    </label>
                    <span class="toggle-label">Enable Registration</span>
                </div>

                <div id="registration-options" style="display: <?php echo $event['registration'] ? 'block' : 'none'; ?>;" class="fade-in-section">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Registration Limit</label>
                            <input type="number" name="registration_limit" value="<?php echo isset($event['registration_limit']) ? $event['registration_limit'] : ''; ?>" min="1" placeholder="Max participants">
                        </div>

                        <div class="form-group">
                            <label>Registration Type</label>
                            <div class="select-wrapper">
                                <select name="registration_type" id="registration_type">
                                    <option value="free" <?php echo (!$is_paid) ? 'selected' : ''; ?>>Free</option>
                                    <option value="paid" <?php echo ($is_paid) ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="fee-container" style="display: <?php echo ($is_paid) ? 'block' : 'none'; ?>;">
                        <div class="form-group">
                            <label>Registration Fee (PHP)</label>
                            <input type="number" name="registration_fee" id="registration_fee" min="1" step="0.01" value="<?php echo $event['registration_fee']; ?>" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header flex-header">
                <h3>Schedule</h3>
                <button type="button" id="add-schedule" class="btn-secondary small-btn">+ Add Date</button>
            </div>
            <div class="card-body">
                <div id="schedule-container">
                    <?php while ($schedule = $schedules->fetch_assoc()): ?>
                    <div class="schedule-row">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="event_date[]" value="<?php echo $schedule['event_date']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Start</label>
                            <input type="time" name="start_time[]" value="<?php echo $schedule['start_time']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>End</label>
                            <input type="time" name="end_time[]" value="<?php echo $schedule['end_time']; ?>" required>
                        </div>
                        <button type="button" class="btn-icon-danger" onclick="this.closest('.schedule-row').remove()">
                            &times;
                        </button>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <h3>Media Gallery</h3>
            </div>
            <div class="card-body">
                <div class="media-section">
                    <h4>Event Posters</h4>
                    <div class="image-grid" id="posters-container">
                        <?php while ($image = $images->fetch_assoc()): ?>
                        <div class="media-item">
                            <img src="<?php echo $image['image_path']; ?>" alt="Poster">
                            <input type="hidden" name="existing_posters[]" value="<?php echo $image['image_path']; ?>">
                            <button type="button" class="btn-overlay-remove" onclick="removeElement(this)">REMOVE</button>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="upload-box">
                        <label>Upload New Posters</label>
                        <input type="file" name="posters[]" multiple class="file-input">
                    </div>
                </div>

                <div class="media-section">
                    <h4>Sponsors</h4>
                    <div class="image-grid" id="sponsors-container">
                        <?php while ($sponsor = $sponsors->fetch_assoc()): ?>
                        <div class="media-item">
                            <img src="<?php echo $sponsor['logo_path']; ?>" alt="Sponsor">
                            <input type="hidden" name="existing_sponsors[]" value="<?php echo $sponsor['logo_path']; ?>">
                            <button type="button" class="btn-overlay-remove" onclick="removeElement(this)">REMOVE</button>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="upload-box">
                        <label>Upload New Sponsors</label>
                        <input type="file" name="sponsors[]" multiple class="file-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary-large">Update Event</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('add-schedule').addEventListener('click', function () {
        const container = document.getElementById('schedule-container');
        const scheduleDiv = document.createElement('div');
        scheduleDiv.className = 'schedule-row slide-in';
        scheduleDiv.innerHTML = `
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="event_date[]" required>
            </div>
            <div class="form-group">
                <label>Start</label>
                <input type="time" name="start_time[]" required>
            </div>
            <div class="form-group">
                <label>End</label>
                <input type="time" name="end_time[]" required>
            </div>
            <button type="button" class="btn-icon-danger" onclick="this.closest('.schedule-row').remove()">
                &times;
            </button>
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

    function removeElement(button) {
        button.closest('.media-item').remove();
    }

    let editorInstance;
    ClassicEditor
    .create(document.querySelector('#description'))
    .then(editor => {
        editorInstance = editor;
    })
    .catch(error => {
        console.error(error);
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        if (editorInstance) {
            document.querySelector('#description').value = editorInstance.getData();
        }
    });
</script>
</body>
</html>

<?php $conn->close(); ?>