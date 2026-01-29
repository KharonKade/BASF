<?php
$conn = new mysqli("localhost", "root", "", "basf_events");
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

$registrations_query = "SELECT * FROM event_registrations WHERE event_id = $event_id";
$registrations = $conn->query($registrations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event</title>
    <link rel="stylesheet" href="Css/view_event.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

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
                    <p><?php echo isset($event['registration_limit']) ? $event['registration_limit'] : 'No limit'; ?></p>
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
                            <th>Category</th>
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
                                <td><span class="cat-pill"><?php echo htmlspecialchars($registration['category']); ?></span></td>
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

<script>
function filterTable() {
    let selectedCategory = document.getElementById("categoryFilter").value;
    let nameQuery = document.getElementById("nameSearch").value.toLowerCase();
    let rows = document.querySelectorAll(".registration-row");
    
    rows.forEach(row => {
        let category = row.getAttribute("data-category");
        let name = row.querySelector(".bold-text").textContent.toLowerCase();
        
        let matchesCategory = (selectedCategory === "all" || category === selectedCategory);
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

    for (let i = 0; i < rows.length; i++) {
        if (rows[i].style.display === "none") {
            continue;
        }

        let row = [];
        let cols = rows[i].querySelectorAll("td, th");
        
        for (let j = 1; j < cols.length; j++) {
            let cellText = cols[j].innerText.replace(/,/g, ""); 
            row.push(cellText);
        }
        csvData.push(row.join(","));
    }

    let csvContent = "data:text/csv;charset=utf-8," + csvData.join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "event_participants.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</body>
</html>

<?php $conn->close(); ?>