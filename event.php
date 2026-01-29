<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Css/event.css">
</head>

<body>
<header>
        <nav class="navbar">
            <img src="images/basflogo.png" alt="BASF Logo" class="logo">
            <div class="nav-center">
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="spots.html">Spots</a></li>
                    <li><a href="event.php">Events</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="sponsorship.html">Sponsorship</a></li>
                    <li><a href="contactUs.html">Contact Us</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h2>Event Lists</h2>
        </div>
    </section>

    <section class="event-navigation"> 
        <div class="advertisement animate-on-scroll">
            <a id="ad-link" href="#" target="_blank">
                <div class="ad-container">
                    <img id="ad-image" src="" alt="Advertisement">
                    <span class="ad-label">Ads</span>
                </div>
            </a>
        </div>
    </section>

    <div class="event-filter">
        <input type="text" id="searchFilter" placeholder="Search Event Name...">

        <select id="categoryFilter">
            <option value="all">All Categories</option>
            <option value="skateboard">Skateboard</option>
            <option value="bmx">BMX</option>
            <option value="inline">Inline</option>
        </select>

        <select id="dateFilter">
            <option value="all">All Dates</option>
            <option value="upcoming">Upcoming</option>
            <option value="this-week">This Week</option>
            <option value="this-month">This Month</option>
        </select>
    </div>

    <section class="container event-container animate-on-scroll" id="upcoming">
        <h2>Events & Activities</h2>
        <div id="event-count" class="event-count">Total Events: 0</div>
        
        <div class="event-grid" id="eventGrid">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "basf_events";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "
            SELECT 
                e.id,
                e.event_name, 
                e.category, 
                s.event_date, 
                MIN(i.image_path) AS image_path
            FROM 
                upcoming_events e
            JOIN 
                event_schedules s ON e.id = s.event_id
            JOIN 
                event_images i ON e.id = i.event_id
            WHERE 
                e.status = 'active'
            GROUP BY 
                e.id
            ORDER BY 
                e.id DESC
            ";

            $result = $conn->query($sql);

            $trending_events = [];
            $regular_events = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $trend_sql = "
                        SELECT COUNT(r.id) AS recent_registrations
                        FROM event_registrations r
                        WHERE r.event_id = " . $row['id'] . "
                        AND r.registration_time > NOW() - INTERVAL 7 DAY
                    ";
                    $trend_result = $conn->query($trend_sql);
                    $trend_row = $trend_result->fetch_assoc();
                    $recent_registrations = $trend_row['recent_registrations'];
                    $is_trending = $recent_registrations > 5;

                    if ($is_trending) {
                        $trending_events[] = ['data' => $row, 'trending' => true];
                    } else {
                        $regular_events[] = ['data' => $row, 'trending' => false];
                    }
                }

                $all_events = array_merge($trending_events, $regular_events);

                foreach ($all_events as $event) {
                    $row = $event['data'];
                    $is_trending = $event['trending'];

                    echo '<div class="event-item animate-on-scroll" 
                            data-category="' . htmlspecialchars($row['category']) . '" 
                            data-date="' . htmlspecialchars($row['event_date']) . '">
                            <a href="eventPages.php?id=' . $row['id'] . '">
                                <div class="flip-card">
                                    <div class="flip-card-inner">
                                        ' . ($is_trending ? '<span class="trending-tag">Trending Now</span>' : '') . '
                                        <div class="flip-card-front">
                                            <img src="' . $row["image_path"] . '" alt="' . $row["event_name"] . '">
                                        </div>
                                        <div class="flip-card-back" style="background-image: url(' . "'" . $row["image_path"] . "'" . ');">
                                            <div class="back-content">
                                                <p>' . $row["event_name"] . '</p>
                                                <p>Category: ' . $row["category"] . '</p>';

                                                $event_date = new DateTime($row["event_date"]);
                                                $formatted_date = $event_date->format('l, F j, Y');
                                                echo '<p>Date: ' . $formatted_date . '</p>';

                                                echo '<br><p>Click for more...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>';
                }
            } else {
                echo "<p>No upcoming events found.</p>";
            }

            $conn->close();
            ?>
        </div>
        
        <div id="pagination-controls" class="pagination-container"></div>
    </section>

    
    <div class="footer-ramp-icons animate-on-scroll">
        <img src="images/ramp.png" alt="Left Ramp" class="ramp-icon left">
        <img src="images/pyramid.png" alt="Center Pyramid Ramp" class="ramp-icon center">
        <img src="images/rampright.png" alt="Right Ramp" class="ramp-icon right">
    </div>
    
    <footer class="footer animate-on-scroll">
        <div class="footer-section logo-section">
            <img src="images/whitelogo.png" alt="BASF Logo" class="footer-logo">
        </div>
    
        <div class="footer-section explore-section">
            <h3>Explore Us</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="skateboard.php">Skateboarding</a></li>
                <li><a href="inline.php">In-Line</a></li>
                <li><a href="bmx.php">BMX</a></li>
                <li><a href="spots.html">Spots</a></li>
                <li><a href="event.php">Events</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="sponsorship.html">Sponsorship</a></li>
                <li><a href="contactUs.html">Contact Us</a></li>
            </ul>
        </div>
    
        <div class="footer-section contact-section">
            <h3>Contact Us</h3>
            <ul>
                <li>09094431201</li>
                <li>09348913502</li>
                <li>09761816282</li>
                <li>basf@gmail.com</li>
            </ul>
        </div>
    
        <div class="footer-section social-section">
            <h3>Connect with us</h3>
            <div class="social-icons">
                <a href="https://facebook.com"><img src="images/fbwhite.png" alt="Facebook"></a>
                <a href="https://instagram.com"><img src="images/igwhite.png" alt="Instagram"></a>
            </div>
        </div>
    
        <div class="footer-section supported-section">
            <h3>Supported by</h3>
            <img src="images/vanswhite.png" alt="Sponsor Logo" class="sponsor-logo">
        </div>
    </footer>

    <script src="jsScript/event.js"></script>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    
    // --- 1. Animation Logic ---
    const elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach(el => { el._fadeTimeout = null; });

    function toggleVisibility() {
        const currentElements = document.querySelectorAll('.animate-on-scroll');
        currentElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const inView = rect.top <= window.innerHeight * 0.85 && rect.bottom >= 0;

            if (inView) {
                clearTimeout(el._fadeTimeout);
                el.classList.add('visible');
                el.style.visibility = 'visible';
            } else {
                el.classList.remove('visible');
                clearTimeout(el._fadeTimeout);
                el._fadeTimeout = setTimeout(() => {
                    el.style.visibility = 'hidden';
                }, 600);
            }
        });
    }

    window.addEventListener('scroll', toggleVisibility);
    window.addEventListener('resize', toggleVisibility);
    toggleVisibility(); 

    // --- 2. Ads Logic ---
    const ads = [
        { image: 'images/vansads.png', link: 'https://www.vans.com/en-us/shoes-c00081/old-skool-shoe-pvn000d3hy28' },
        { image: 'images/nikead.webp', link: 'https://www.nike.com/ph/' },
        { image: 'images/redbullad.png', link: 'https://www.redbull.com/ph-en' }
    ];
    let currentAd = 0;
    function rotateAd() {
        const ad = ads[currentAd];
        const adImg = document.getElementById('ad-image');
        const adLink = document.getElementById('ad-link');
        if(adImg && adLink) {
            adImg.src = ad.image;
            adLink.href = ad.link;
            currentAd = (currentAd + 1) % ads.length;
        }
    }
    rotateAd();
    setInterval(rotateAd, 3000);

    // --- 3. Search, Filter & Pagination Logic ---
    let currentPage = 1;
    let itemsPerPage = 8;
    
    // Grab all items rendered by PHP immediately
    let filteredItems = Array.from(document.querySelectorAll('.event-item')); 

    const searchInput = document.getElementById('searchFilter');
    const categorySelect = document.getElementById('categoryFilter');
    const dateSelect = document.getElementById('dateFilter');
    const eventGrid = document.getElementById('eventGrid');
    const eventCount = document.getElementById('event-count');
    const paginationContainer = document.getElementById('pagination-controls');

    // FIX: Update the counter immediately on load
    if(eventCount) {
        eventCount.textContent = `Total Events: ${filteredItems.length}`;
    }

    function calculateItemsPerPage() {
        if (!eventGrid) return;
        const gridWidth = eventGrid.offsetWidth;
        const cardWidth = 270; 
        let columns = Math.floor(gridWidth / cardWidth);
        if (columns < 1) columns = 1;
        itemsPerPage = columns * 2;
        renderPage(); 
    }

    function renderPage() {
        const allItems = document.querySelectorAll('.event-item');
        allItems.forEach(item => item.style.display = 'none');

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageItems = filteredItems.slice(start, end);

        pageItems.forEach(item => {
            item.style.display = 'block';
            item.classList.add('animate-on-scroll');
        });

        renderPaginationControls();
        
        // Retrigger animation check for new items
        setTimeout(toggleVisibility, 100);
    }

    function renderPaginationControls() {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(filteredItems.length / itemsPerPage);

        if (totalPages <= 1) return;

        const prevBtn = document.createElement('button');
        prevBtn.innerText = 'Prev';
        prevBtn.style.fontFamily = 'Poppins, sans-serif';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderPage();
                eventGrid.scrollIntoView({ behavior: 'smooth' });
            }
        };
        paginationContainer.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.style.fontFamily = 'Poppins, sans-serif';
            if (i === currentPage) btn.classList.add('active');
            btn.onclick = () => {
                currentPage = i;
                renderPage();
                eventGrid.scrollIntoView({ behavior: 'smooth' });
            };
            paginationContainer.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.innerText = 'Next';
        nextBtn.style.fontFamily = 'Poppins, sans-serif';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPage();
                eventGrid.scrollIntoView({ behavior: 'smooth' });
            }
        };
        paginationContainer.appendChild(nextBtn);
    }

    function fetchEvents() {
        const searchTerm = searchInput.value;
        const category = categorySelect.value;
        const dateFilter = dateSelect.value;

        const formData = new FormData();
        formData.append('search', searchTerm);
        formData.append('category', category);
        formData.append('date', dateFilter);

        fetch('fetch_events.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            eventGrid.innerHTML = data;
            
            // Update our list of items based on new results
            filteredItems = Array.from(eventGrid.querySelectorAll('.event-item'));
            
            // Update the counter
            eventCount.textContent = `Total Events: ${filteredItems.length}`;
            
            // Reset to page 1
            currentPage = 1;
            
            calculateItemsPerPage();
        })
        .catch(error => console.error('Error:', error));
    }

    searchInput.addEventListener('input', fetchEvents);
    categorySelect.addEventListener('change', fetchEvents);
    dateSelect.addEventListener('change', fetchEvents);
    window.addEventListener('resize', calculateItemsPerPage);

    // Initial calculation
    calculateItemsPerPage();
});
</script>
</body>
</html>