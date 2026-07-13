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
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/admin.css?v=1.2">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h2>Create New Event</h2>
                    <p>Fill in the details below to publish a new event.</p>
                </div>

                <form action="store_event.php" method="post" enctype="multipart/form-data" class="main-form">
                    
                    <div class="form-card">
                        <div class="card-header">
                            <h3>Event Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="event_name">Event Name</label>
                                    <input type="text" name="event_name" id="event_name" placeholder="Enter event name" required>
                                </div>

                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" name="location" id="location" placeholder="Enter venue location" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <div class="select-wrapper">
                                    <select name="category" id="category" required>
                                        <option value="skateboard">Skateboard</option>
                                        <option value="inline">Inline</option>
                                        <option value="bmx">BMX</option>
                                        <option value="All">All</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Event Description</label>
                                <textarea name="description" id="description"></textarea>
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
                                    <input type="checkbox" name="registration" id="registration" value="1">
                                    <span class="slider round"></span>
                                </label>
                                <span class="toggle-label">Enable Registration</span>
                            </div>

                            <div id="registration-options" style="display: none;" class="fade-in-section">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="registration_limit">Registration Limit</label>
                                        <input type="number" name="registration_limit" id="registration_limit" min="1" placeholder="Max participants">
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
                            <h3>Event Schedule</h3>
                            <button type="button" id="add_schedule" class="btn-secondary small-btn">+ Add Date</button>
                        </div>
                        <div class="card-body">
                            <div id="schedule-container">
                                <div class="schedule-row">
                                    <div class="form-group">
                                        <label>Event Date</label>
                                        <input type="date" name="event_date[]" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" name="start_time[]" required>
                                    </div>
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" name="end_time[]" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="card-header">
                            <h3>Media Uploads</h3>
                        </div>
                        <div class="card-body">
                            <div class="media-section">
                                <h4>Event Posters</h4>
                                <div class="upload-box">
                                    <input type="file" name="posters[]" id="posters" multiple required class="file-input">
                                </div>
                            </div>

                            <div class="media-section">
                                <h4>Sponsor Logos</h4>
                                <div class="upload-box">
                                    <input type="file" name="sponsors[]" id="sponsors" multiple required class="file-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="history.back();">Cancel</button>
                        <button type="submit" class="btn-primary-large">Publish Event</button>
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
            newSchedule.classList.add('schedule-row', 'slide-in');

            newSchedule.innerHTML = `
                <div class="form-group">
                    <label>Event Date</label>
                    <input type="date" name="event_date[]" required>
                </div>
                <div class="form-group">
                    <label>Start Time</label>
                    <input type="time" name="start_time[]" required>
                </div>
                <div class="form-group">
                    <label>End Time</label>
                    <input type="time" name="end_time[]" required>
                </div>
                <button type="button" class="btn-icon-danger remove-schedule">
                    &times;
                </button>
            `;

            scheduleContainer.appendChild(newSchedule);

            newSchedule.querySelector('.remove-schedule').addEventListener('click', () => {
                newSchedule.remove();
            });
        });

        document.getElementById("registration").addEventListener("change", function () {
            var registrationOptions = document.getElementById("registration-options");
            if (this.checked) {
                registrationOptions.style.display = "block";
                setTimeout(() => registrationOptions.style.opacity = 1, 10);
            } else {
                registrationOptions.style.display = "none";
                registrationOptions.style.opacity = 0;
            }
        });

        const categorySelect = document.getElementById('category');
        const dynamicCategoriesWrapper = document.getElementById('dynamic-categories-wrapper');

        function renderCategorySections() {
            const selectedSport = categorySelect.value;
            dynamicCategoriesWrapper.innerHTML = '';

            if (selectedSport === 'All') {
                createSportSection('skateboard', 'Skateboard Categories');
                createSportSection('inline', 'Inline Categories');
                createSportSection('bmx', 'BMX Categories');
            } else {
                const title = selectedSport.charAt(0).toUpperCase() + selectedSport.slice(1) + ' Categories';
                createSportSection(selectedSport, title);
            }
        }

        function createSportSection(sportKey, title) {
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

            addBtn.addEventListener('click', () => {
                const row = document.createElement('div');
                row.classList.add('form-grid');
                row.style.alignItems = 'end';
                row.style.marginBottom = '10px';

                row.innerHTML = `
                    <div class="form-group">
                        <label style="font-family: 'Poppins', sans-serif;">Category Name</label>
                        <input type="text" name="sport_categories[${sportKey}][name][]" placeholder="e.g. Open Class" required style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <div class="form-group">
                        <label style="font-family: 'Poppins', sans-serif;">Registration Fee (PHP)</label>
                        <input type="number" name="sport_categories[${sportKey}][fee][]" min="0" step="0.01" placeholder="0.00" required style="font-family: 'Poppins', sans-serif;">
                    </div>
                    <button type="button" class="btn-icon-danger remove-sub-category" style="margin-bottom: 15px; font-family: 'Poppins', sans-serif;">&times;</button>
                `;

                row.querySelector('.remove-sub-category').addEventListener('click', () => row.remove());
                rowsContainer.appendChild(row);
            });

            header.appendChild(heading);
            header.appendChild(addBtn);
            section.appendChild(header);
            section.appendChild(rowsContainer);
            dynamicCategoriesWrapper.appendChild(section);
        }

        categorySelect.addEventListener('change', renderCategorySections);
        renderCategorySections();
    </script>
</body>
</html>