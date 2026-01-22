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
            <h1>Events</h1>
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

        <div id="event-count" class="event-count">Total Events: 0</div>
    </div>

    <section class="container event-container animate-on-scroll" id="upcoming">
        <h2>Events & Activities</h2>
        
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
        const elements = document.querySelectorAll('.animate-on-scroll');

        elements.forEach(el => {
            el._fadeTimeout = null;
        });

        function toggleVisibility() {
            elements.forEach(el => {
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
    });

    </script>
    <script>
        const ads = [
            {
                image: 'images/vansads.png',
                link: 'https://www.vans.com/en-us/shoes-c00081/old-skool-shoe-pvn000d3hy28'
            },
            {
                image: 'images/nikead.webp',
                link: 'https://www.nike.com/ph/'
            },
            {
                image: 'images/redbullad.png',
                link: 'https://www.redbull.com/ph-en'
            }
        ];

        let currentAd = 0;

        function rotateAd() {
            const ad = ads[currentAd];
            document.getElementById('ad-image').src = ad.image;
            document.getElementById('ad-link').href = ad.link;
            currentAd = (currentAd + 1) % ads.length;
        }

        rotateAd();
        setInterval(rotateAd, 3000);
    </script>
    
    <script>
        let currentPage = 1;
        let itemsPerPage = 8;
        let filteredItems = []; 

        document.getElementById('categoryFilter').addEventListener('change', () => { currentPage = 1; filterEvents(); });
        document.getElementById('dateFilter').addEventListener('change', () => { currentPage = 1; filterEvents(); });
        window.addEventListener('resize', () => { calculateItemsPerPage(); renderPage(); });

        function calculateItemsPerPage() {
            const grid = document.getElementById('eventGrid');
            if (!grid) return;
            
            const gridWidth = grid.offsetWidth;
            const cardWidth = 270; 
            
            let columns = Math.floor(gridWidth / cardWidth);
            if (columns < 1) columns = 1;
            
            itemsPerPage = columns * 2;
        }

        function filterEvents() {
            const category = document.getElementById('categoryFilter').value;
            const date = document.getElementById('dateFilter').value;
            const allItems = Array.from(document.querySelectorAll('.event-item'));
            const today = new Date();

            filteredItems = allItems.filter(item => {
                const itemCategory = item.getAttribute('data-category');
                const itemDate = new Date(item.getAttribute('data-date'));
                let matchCategory = true;
                let matchDate = true;

                if (category !== 'all' && category !== itemCategory) {
                    matchCategory = false;
                }

                if (date === 'upcoming' && itemDate < today) {
                    matchDate = false;
                } else if (date === 'this-week') {
                    const dayOfWeek = today.getDay();
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - dayOfWeek);
                    const endOfWeek = new Date(today);
                    endOfWeek.setDate(today.getDate() + (6 - dayOfWeek));

                    startOfWeek.setHours(0, 0, 0, 0);
                    endOfWeek.setHours(23, 59, 59, 999);
                    itemDate.setHours(0, 0, 0, 0);

                    if (itemDate < startOfWeek || itemDate > endOfWeek) {
                        matchDate = false;
                    }
                } else if (date === 'this-month') {
                    if (itemDate.getMonth() !== today.getMonth() || itemDate.getFullYear() !== today.getFullYear()) {
                        matchDate = false;
                    }
                }

                return matchCategory && matchDate;
            });

            updateEventCount(filteredItems.length);
            calculateItemsPerPage();
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
            });

            renderPaginationControls();
        }

        function renderPaginationControls() {
            const container = document.getElementById('pagination-controls');
            container.innerHTML = '';

            const totalPages = Math.ceil(filteredItems.length / itemsPerPage);

            if (totalPages <= 1) return;

            const prevBtn = document.createElement('button');
            prevBtn.innerText = 'Prev';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                }
            };
            container.appendChild(prevBtn);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                if (i === currentPage) btn.classList.add('active');
                btn.onclick = () => {
                    currentPage = i;
                    renderPage();
                };
                container.appendChild(btn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.innerText = 'Next';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            };
            container.appendChild(nextBtn);
        }

        function updateEventCount(count) {
            document.getElementById('event-count').textContent = `Total Events: ${count}`;
        }

        window.onload = function() {
            filterEvents();
        };
    </script>
</body>
</html>