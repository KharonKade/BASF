<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_GET['id'];

$event = $conn->query("SELECT * FROM upcoming_events WHERE id = $event_id")->fetch_assoc();

$schedules = $conn->query("SELECT * FROM event_schedules WHERE event_id = $event_id");

$images = $conn->query("SELECT * FROM event_images WHERE event_id = $event_id");

$sponsors = $conn->query("SELECT * FROM sponsor_logos WHERE event_id = $event_id");

$categories_query = $conn->query("SELECT * FROM event_categories WHERE event_id = $event_id");
$existing_categories = [];
while ($cat = $categories_query->fetch_assoc()) {
    $existing_categories[$cat['sport_type']][] = [
        'name' => $cat['category_name'],
        'fee' => $cat['fee']
    ];
}
$existing_categories_json = json_encode($existing_categories);

$is_paid = ($event['registration_fee'] > 0);

$leaderboards = null;
if ($event['status'] == 'archived') {
    $leaderboards_query = $conn->query("SHOW TABLES LIKE 'event_leaderboards'");
    if ($leaderboards_query->num_rows > 0) {
        $leaderboards = $conn->query("SELECT * FROM event_leaderboards WHERE event_id = $event_id ORDER BY id ASC");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/edit_event.css?v=1.2">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
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
                                    <input type="text" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" placeholder="Enter event name" required>
                                </div>

                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" placeholder="Enter location" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Category</label>
                                <div class="select-wrapper">
                                    <select name="category" id="category">
                                        <option value="skateboard" <?php if (strtolower($event['category']) == 'skateboard') echo 'selected'; ?>>Skateboard</option>
                                        <option value="inline" <?php if (strtolower($event['category']) == 'inline') echo 'selected'; ?>>Inline</option>
                                        <option value="bmx" <?php if (strtolower($event['category']) == 'bmx') echo 'selected'; ?>>BMX</option>
                                        <option value="All" <?php if (strtolower($event['category']) == 'all') echo 'selected'; ?>>All</option>
                                    </select>
                                </div>  
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" id="description"><?php echo htmlspecialchars($event['description']); ?></textarea>
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
                                        <input type="number" name="registration_limit" value="<?php echo isset($event['registration_limit']) ? htmlspecialchars($event['registration_limit']) : ''; ?>" min="1" placeholder="Max participants">
                                    </div>
                                    <div class="form-group" style="grid-column: 1 / -1; width: 100%;">
                                        <div id="dynamic-categories-wrapper" style="font-family: 'Poppins', sans-serif;"></div>
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
                                        <input type="date" name="event_date[]" value="<?php echo htmlspecialchars($schedule['event_date']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Start</label>
                                        <input type="time" name="start_time[]" value="<?php echo htmlspecialchars($schedule['start_time']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>End</label>
                                        <input type="time" name="end_time[]" value="<?php echo htmlspecialchars($schedule['end_time']); ?>" required>
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
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Poster">
                                        <input type="hidden" name="existing_posters[]" value="<?php echo htmlspecialchars($image['image_path']); ?>">
                                        <button type="button" class="btn-overlay-remove" onclick="removeElement(this)">REMOVE</button>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="upload-box">
                                    <label>Upload New Posters</label>
                                    <input type="file" name="posters[]" multiple class="file-input" accept="image/*" style="font-family: 'Poppins', sans-serif;">
                                </div>
                            </div>

                            <div class="media-section">
                                <h4>Sponsors</h4>
                                <div class="image-grid" id="sponsors-container">
                                    <?php while ($sponsor = $sponsors->fetch_assoc()): ?>
                                    <div class="media-item">
                                        <img src="<?php echo htmlspecialchars($sponsor['logo_path']); ?>" alt="Sponsor">
                                        <input type="hidden" name="existing_sponsors[]" value="<?php echo htmlspecialchars($sponsor['logo_path']); ?>">
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

                    <?php if ($event['status'] == 'archived'): ?>
                    <div class="form-card" style="font-family: 'Poppins', sans-serif;">
                        <div class="card-header flex-header">
                            <h3>Event Leaderboard</h3>
                            <button type="button" id="add-leaderboard" class="btn-secondary small-btn">+ Add Player</button>
                        </div>
                        <div class="card-body">
                            <p style="margin-bottom: 15px; font-size: 0.9em; color: #666;">Enter the final standings. You can specify different categories or divisions.</p>
                            <div id="leaderboard-container">
                                <?php if ($leaderboards && $leaderboards->num_rows > 0): ?>
                                    <?php while ($lb = $leaderboards->fetch_assoc()): ?>
                                    <div class="schedule-row slide-in">
                                        <div class="form-group" style="flex: 0.5;">
                                            <label>Rank</label>
                                            <input type="text" name="lb_rank[]" value="<?php echo htmlspecialchars($lb['rank']); ?>" required style="font-family: 'Poppins', sans-serif;">
                                        </div>
                                        <div class="form-group" style="flex: 2;">
                                            <label>Player Name</label>
                                            <input type="text" name="lb_name[]" value="<?php echo htmlspecialchars($lb['player_name']); ?>" required style="font-family: 'Poppins', sans-serif;">
                                        </div>
                                        <div class="form-group" style="flex: 1.5;">
                                            <label>Category</label>
                                            <input type="text" name="lb_category[]" value="<?php echo htmlspecialchars($lb['category']); ?>" required style="font-family: 'Poppins', sans-serif;">
                                        </div>
                                        <div class="form-group" style="flex: 1;">
                                            <label>Score/Rating</label>
                                            <input type="text" name="lb_score[]" value="<?php echo htmlspecialchars($lb['score']); ?>" required style="font-family: 'Poppins', sans-serif;">
                                        </div>
                                        <button type="button" class="btn-icon-danger" onclick="this.closest('.schedule-row').remove()">
                                            &times;
                                        </button>
                                    </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary-large" style="margin-right: 15px;" onclick="window.location.href='manage_upcoming.php';">Cancel</button>
                        <button type="submit" class="btn-primary-large">Update Event</button>
                    </div>
                </form>
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

    const existingCategories = <?php echo $existing_categories_json; ?>;
    const categorySelect = document.querySelector('select[name="category"]');
    const dynamicCategoriesWrapper = document.getElementById('dynamic-categories-wrapper');

    function renderCategorySections() {
        const selectedSport = categorySelect.value.toLowerCase();
        dynamicCategoriesWrapper.innerHTML = '';

        if (selectedSport === 'all') {
            createSportSection('skateboard', 'Skateboard Categories', existingCategories['skateboard'] || []);
            createSportSection('inline', 'Inline Categories', existingCategories['inline'] || []);
            createSportSection('bmx', 'BMX Categories', existingCategories['bmx'] || []);
        } else {
            const title = selectedSport.charAt(0).toUpperCase() + selectedSport.slice(1) + ' Categories';
            createSportSection(selectedSport, title, existingCategories[selectedSport] || []);
        }
    }

    function createSportSection(sportKey, title, existingData = []) {
        const section = document.createElement('div');
        section.style.marginTop = '20px';
        section.style.padding = '15px';
        section.style.border = '1px solid #ddd';
        section.style.borderRadius = '8px';
        section.style.fontFamily = "'Poppins', sans-serif";

        const header = document.createElement('div');
        header.classList.add('flex-header');
        header.style.display = 'flex';
        header.style.justifyContent = 'space-between';
        header.style.alignItems = 'center';
        header.style.marginBottom = '15px';

        const heading = document.createElement('h4');
        heading.innerText = title;
        heading.style.margin = '0';
        heading.style.fontFamily = "'Poppins', sans-serif";

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.classList.add('btn-secondary', 'small-btn');
        addBtn.innerText = '+ Add Category';
        addBtn.style.fontFamily = "'Poppins', sans-serif";

        const rowsContainer = document.createElement('div');

        const addRow = (nameVal = '', feeVal = '') => {
            const row = document.createElement('div');
            row.classList.add('form-grid');
            row.style.alignItems = 'end';
            row.style.marginBottom = '10px';

            row.innerHTML = `
                <div class="form-group">
                    <label style="font-family: 'Poppins', sans-serif;">Category Name</label>
                    <input type="text" name="sport_categories[${sportKey}][name][]" value="${nameVal}" placeholder="e.g. Open Class" required style="font-family: 'Poppins', sans-serif;">
                </div>
                <div class="form-group">
                    <label style="font-family: 'Poppins', sans-serif;">Registration Fee (PHP)</label>
                    <input type="number" name="sport_categories[${sportKey}][fee][]" value="${feeVal}" min="0" step="0.01" placeholder="0.00" required style="font-family: 'Poppins', sans-serif;">
                </div>
                <button type="button" class="btn-icon-danger remove-sub-category" style="margin-bottom: 15px; font-family: 'Poppins', sans-serif;">&times;</button>
            `;

            row.querySelector('.remove-sub-category').addEventListener('click', () => row.remove());
            rowsContainer.appendChild(row);
        };

        if (existingData.length > 0) {
            existingData.forEach(data => addRow(data.name, data.fee));
        }

        addBtn.addEventListener('click', () => addRow());

        header.appendChild(heading);
        header.appendChild(addBtn);
        section.appendChild(header);
        section.appendChild(rowsContainer);
        dynamicCategoriesWrapper.appendChild(section);
    }

    categorySelect.addEventListener('change', renderCategorySections);
    renderCategorySections();

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

    function removeElement(button) {
        button.closest('.media-item').remove();
    }

    <?php if ($event['status'] == 'archived'): ?>
    const addLeaderboardBtn = document.getElementById('add-leaderboard');
    if (addLeaderboardBtn) {
        addLeaderboardBtn.addEventListener('click', function () {
            const container = document.getElementById('leaderboard-container');
            const lbDiv = document.createElement('div');
            lbDiv.className = 'schedule-row slide-in';
            lbDiv.innerHTML = `
                <div class="form-group" style="flex: 0.5;">
                    <label>Rank</label>
                    <input type="text" name="lb_rank[]" placeholder="e.g. 1st, 2nd" required style="font-family: 'Poppins', sans-serif;">
                </div>
                <div class="form-group" style="flex: 2;">
                    <label>Player Name</label>
                    <input type="text" name="lb_name[]" placeholder="Enter player name" required style="font-family: 'Poppins', sans-serif;">
                </div>
                <div class="form-group" style="flex: 1.5;">
                    <label>Category</label>
                    <input type="text" name="lb_category[]" placeholder="e.g. Open, Pro" required style="font-family: 'Poppins', sans-serif;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Score/Rating</label>
                    <input type="text" name="lb_score[]" placeholder="Score or Time" required style="font-family: 'Poppins', sans-serif;">
                </div>
                <button type="button" class="btn-icon-danger" onclick="this.closest('.schedule-row').remove()">
                    &times;
                </button>
            `;
            container.appendChild(lbDiv);
        });
    }
    <?php endif; ?>

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