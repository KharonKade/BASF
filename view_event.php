<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_GET['id'];

$event_query = "SELECT * FROM upcoming_events WHERE id = $event_id";
$event = $conn->query($event_query);
if (!$event || $event->num_rows === 0) {
    die("Event not found: " . $conn->error);
}
$event = $event->fetch_assoc();

$schedules_query = "SELECT * FROM event_schedules WHERE event_id = $event_id";
$schedules = $conn->query($schedules_query);

$images_query = "SELECT * FROM event_images WHERE event_id = $event_id";
$images = $conn->query($images_query);
if (!$images) {
    die("Error fetching images: " . $conn->error);
}

$sponsors_query = "SELECT * FROM sponsor_logos WHERE event_id = $event_id";
$sponsors = $conn->query($sponsors_query);
if (!$sponsors) {
    die("Error fetching sponsors: " . $conn->error);
}

$registrations_query = "SELECT *, IFNULL(status, 'pending') AS status FROM event_registrations WHERE event_id = $event_id";
$registrations = $conn->query($registrations_query);

$categories_query = "SELECT * FROM event_categories WHERE event_id = $event_id ORDER BY sport_type ASC, id ASC";
$categories_result = $conn->query($categories_query);
$categories_data = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($cat = $categories_result->fetch_assoc()) {
        $categories_data[$cat['sport_type']][] = $cat;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/view_event.css?v=1.1">
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
                        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
                        <span class="category-badge"><?php echo ucfirst(htmlspecialchars($event['category'])); ?></span>
                    </div>
                    <button onclick="window.location.href='manage_upcoming.php';" class="btn-secondary">Return</button>
                </div>

                <div class="dashboard-grid">
                    <div class="card event-details">
                        <h3>General Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Location</label>
                                <p><?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                            <div class="info-item">
                                <label>Registration Status</label>
                                <p>
                                    <span class="status-pill <?php echo $event['registration'] == 1 ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $event['registration'] == 1 ? 'Active' : 'Closed'; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="info-item">
                                <label>Registration Limit</label>
                                <p><?php echo isset($event['registration_limit']) && $event['registration_limit'] > 0 ? $event['registration_limit'] : 'No limit'; ?></p>
                            </div>
                        </div>
                        <div class="description-box">
                            <label>Description</label>
                            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    </div>

                    <div class="card event-schedules">
                        <h3>Schedules</h3>
                        <div class="schedule-list">
                            <?php if ($schedules->num_rows > 0): ?>
                                <?php while ($schedule = $schedules->fetch_assoc()): ?>
                                    <div class="schedule-card">
                                        <div class="sch-date"><?php echo htmlspecialchars($schedule['event_date']); ?></div>
                                        <div class="sch-time">
                                            <span><?php echo htmlspecialchars($schedule['start_time']); ?></span> — 
                                            <span><?php echo htmlspecialchars($schedule['end_time']); ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-state">No schedules available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card event-categories-card" style="grid-column: 1 / -1; font-family: 'Poppins', sans-serif;">
                        <h3 style="font-family: 'Poppins', sans-serif;">Categories & Fees</h3>
                        <div class="categories-list" style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <?php if (!empty($categories_data)): ?>
                                <?php foreach ($categories_data as $sport => $cats): ?>
                                    <div class="sport-category-group" style="flex: 1; min-width: 250px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                                        <h4 style="margin-top: 0; margin-bottom: 10px; color: #333; text-transform: capitalize; font-family: 'Poppins', sans-serif;"><?php echo htmlspecialchars($sport); ?></h4>
                                        <ul style="list-style: none; padding: 0; margin: 0;">
                                            <?php foreach ($cats as $c): ?>
                                                <li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-family: 'Poppins', sans-serif;">
                                                    <span><?php echo htmlspecialchars($c['category_name']); ?></span>
                                                    <span style="font-weight: 600; color: #2ecc71;">
                                                        <?php echo $c['fee'] > 0 ? '₱' . number_format($c['fee'], 2) : 'Free'; ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-state" style="font-family: 'Poppins', sans-serif;">No specific categories set.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="media-grid">
                    <div class="card">
                        <h3>Event Posters</h3>
                        <div class="poster-gallery">
                            <?php if ($images->num_rows > 0): ?>
                                <?php while ($image = $images->fetch_assoc()): ?>
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Poster" class="gallery-img">
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-state">No posters uploaded.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <h3>Sponsors</h3>
                        <div class="sponsor-flex">
                            <?php if ($sponsors->num_rows > 0): ?>
                                <?php while ($sponsor = $sponsors->fetch_assoc()): ?>
                                    <div class="sponsor-item">
                                        <img src="<?php echo htmlspecialchars($sponsor['logo_path']); ?>" alt="Sponsor Logo">
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-state">No sponsors added.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card registration-section">
                    <div class="table-header">
                        <h3>Registered Participants</h3>
                        <div class="filter-controls">
                            <input type="text" id="nameSearch" placeholder="Search by name..." onkeyup="filterTable()">
                            <select id="categoryFilter" onchange="filterTable()">
                                <option value="all">All Categories</option>
                                <option value="Skateboard">Skateboard</option>
                                <option value="Inline">Inline</option>
                                <option value="BMX">BMX</option>
                            </select>
                            <button onclick="exportToCSV()" class="btn-export">Export CSV</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <?php if ($registrations->num_rows > 0): ?>
                            <table id="participantsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Date Registered</th>
                                        <th>Time Registered</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1; 
                                    while ($registration = $registrations->fetch_assoc()): ?>
                                        <tr class="registration-row" data-category="<?php echo htmlspecialchars($registration['category']); ?>">
                                            <td><?php echo $counter++; ?></td>
                                            <td class="bold-text"><?php echo htmlspecialchars($registration['name']); ?></td>
                                            <td><?php echo htmlspecialchars($registration['email']); ?></td>
                                            <td><?php echo htmlspecialchars($registration['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($registration['age']); ?></td>
                                            <td><?php echo htmlspecialchars($registration['gender']); ?></td>
                                            <td><?php echo htmlspecialchars($registration['registration_date']); ?></td>
                                            <td><?php echo htmlspecialchars($registration['registration_time']); ?></td>
                                            <td><span class="cat-pill"><?php echo htmlspecialchars($registration['category']); ?></span></td>
                                            <td>
                                                <span class="status-pill <?php echo strtolower($registration['status']); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($registration['status'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-table-state">No users have registered for this event yet.</div>
                        <?php endif; ?>
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

    function filterTable() {
        let selectedCategory = document.getElementById("categoryFilter").value;
        let nameQuery = document.getElementById("nameSearch").value.toLowerCase();
        let rows = document.querySelectorAll(".registration-row");
        
        rows.forEach(row => {
            let category = row.getAttribute("data-category");
            let name = row.querySelector(".bold-text").textContent.toLowerCase();
            
            let matchesCategory = (selectedCategory === "all" || category.includes(selectedCategory));
            let matchesName = name.includes(nameQuery);
            
            if (matchesCategory && matchesName) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    function exportToCSV() {
        let table = document.getElementById("participantsTable");
        let rows = table.querySelectorAll("tr");
        let csvData = [];

        let eventName = "<?php echo addslashes($event['event_name']); ?>";
        let exportDate = "<?php echo date('F d, Y h:i A'); ?>";

        csvData.push(`"EVENT REGISTRATIONS:","${eventName}"`);
        csvData.push(`"GENERATED ON:","${exportDate}"`);
        csvData.push(""); 

        for (let i = 0; i < rows.length; i++) {
            if (rows[i].style.display === "none") {
                continue;
            }

            let row = [];
            let cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 1; j < cols.length; j++) {
                let cellText = cols[j].innerText.replace(/"/g, '""'); 
                row.push(`"${cellText}"`);
            }
            csvData.push(row.join(","));
        }

        let csvContent = "data:text/csv;charset=utf-8," + csvData.join("\n");
        let encodedUri = encodeURI(csvContent);
        
        let link = document.createElement("a");
        let safeFileName = eventName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", safeFileName + "_participants.csv");
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>
<?php $conn->close(); ?>