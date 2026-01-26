<?php
// Include the database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basf_content_bmx";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Get the athlete ID from the query string
$athlete_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the athlete's information from the database
$query = "SELECT * FROM top_athletes WHERE id = $athlete_id";
$athlete_result = $conn->query($query);
$athlete = $athlete_result->fetch_assoc();

// Fetch the athlete's achievements
$achievements_query = "SELECT title, description FROM achievements WHERE athlete_id = $athlete_id";
$achievements_result = $conn->query($achievements_query);

// Fetch the athlete's gallery images
$gallery_query = "SELECT image, description FROM athlete_gallery WHERE athlete_id = $athlete_id";
$gallery_result = $conn->query($gallery_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile</title>
    <link rel="stylesheet" href="Css/playerPageBmx.css">
</head>
<body>
    <header class="player-header animate-on-scroll">
        <div class="overlay"></div>
        <div class="header-content">
            <?php if ($athlete): ?>
                <div class="img-container">
                    <img src="<?php echo $athlete['image']; ?>" alt="<?php echo $athlete['name']; ?>" class="player-img">
                </div>
                <div class="text-container">
                    <h1 class="player-name"><?php echo $athlete['name']; ?></h1>
                    <span class="player-badge"><?php echo $athlete['specialty']; ?></span>
                </div>
            <?php else: ?>
                <p>Athlete not found.</p>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="main-container">
        
        <section class="stats-bar animate-on-scroll">
            <?php if ($athlete): ?>
                <div class="stat-card">
                    <span class="stat-value"><?php echo $athlete['wins']; ?></span>
                    <span class="stat-label">Wins</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?php echo $athlete['podium_finishes']; ?></span>
                    <span class="stat-label">Podiums</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?php echo $athlete['years_active']; ?></span>
                    <span class="stat-label">Years Active</span>
                </div>
            <?php else: ?>
                <p>Data not available</p>
            <?php endif; ?>
        </section>

        <div class="content-grid">
            <section class="bio-section animate-on-scroll">
                <div class="section-header">
                    <h2>Athlete Biography</h2>
                    <div class="underline"></div>
                </div>
                <div class="bio-content">
                    <?php if ($athlete): ?>
                        <p><?php echo $athlete['bio']; ?></p>
                    <?php else: ?>
                        <p>No bio available.</p>
                    <?php endif; ?>
                </div>
            </section>
            
            <section class="achievements-section animate-on-scroll">
                <div class="section-header">
                    <h2>Achievements</h2>
                    <div class="underline"></div>
                </div>
                <div class="achievement-list">
                    <?php while ($ach = $achievements_result->fetch_assoc()): ?>
                        <div class="achievement-card">
                            <div class="icon-box">🏆</div>
                            <div class="ach-text">
                                <h3><?php echo $ach['title']; ?></h3>
                                <p><?php echo $ach['description']; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </div>
        
        <section class="gallery-section animate-on-scroll">
            <div class="section-header center-header">
                <h2>Media Gallery</h2>
                <div class="underline"></div>
            </div>
            <div class="gallery-grid">
                <?php while ($img = $gallery_result->fetch_assoc()): ?>
                    <div class="gallery-card">
                        <div class="img-wrapper">
                            <img src="<?php echo $img['image']; ?>" alt="Gallery Image">
                        </div>
                        <div class="gallery-info">
                            <p><?php echo $img['description']; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <div class="action-area animate-on-scroll">
            <button onclick="history.back()" class="return-btn">
                <span>&larr; Return to Roster</span>
            </button>
        </div>
    </div>
    
    <div class="footer-ramp-icons animate-on-scroll">
        <img src="images/ramp.png" alt="Left Ramp" class="ramp-icon left">
        <img src="images/pyramid.png" alt="Center Pyramid Ramp" class="ramp-icon center">
        <img src="images/rampright.png" alt="Right Ramp" class="ramp-icon right">
    </div>
    
    <footer class="footer animate-on-scroll">
        <!-- BASF Logo Section -->
        <div class="footer-section logo-section">
            <img src="images/logo.png" alt="BASF Logo" class="footer-logo">
        </div>
    
        <!-- Explore Us Section -->
        <div class="footer-section explore-section">
            <h3>Explore Us</h3>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="skateboard.html">Skateboarding</a></li>
                <li><a href="inline.html">In-Line</a></li>
                <li><a href="bmx.html">BMX</a></li>
                <li><a href="spots.html">Spots</a></li>
                <li><a href="event.html">Events</a></li>
                <li><a href="gallery.html">Gallery</a></li>
                <li><a href="sponsorship.html">Sponsorship</a></li>
                <li><a href="contactUs.html">Contact Us</a></li>
            </ul>
        </div>
    
        <!-- Contact Us Section -->
        <div class="footer-section contact-section">
            <h3>Contact Us</h3>
            <ul>
                <li>09094431201</li>
                <li>09348913502</li>
                <li>09761816282</li>
                <li>basf@gmail.com</li>
            </ul>
        </div>
    
        <!-- Connect with Us Section -->
        <div class="footer-section social-section">
            <h3>Connect with us</h3>
            <div class="social-icons">
                <a href="https://facebook.com"><img src="images/fblogo.png" alt="Facebook"></a>
                <a href="https://instagram.com"><img src="images/iglogo.png" alt="Instagram"></a>
            </div>
        </div>
    
        <!-- Supported by Section -->
        <div class="footer-section supported-section">
            <h3>Supported by</h3>
            <img src="images/vanlogo.png" alt="Sponsor Logo" class="sponsor-logo">
        </div>
    </footer>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
        const elements = document.querySelectorAll('.animate-on-scroll');

        elements.forEach(el => {
            el._fadeTimeout = null; // custom property for tracking timeout
        });

        function toggleVisibility() {
            elements.forEach(el => {
                const rect = el.getBoundingClientRect();
                const inView = rect.top <= window.innerHeight * 0.85 && rect.bottom >= 0;

                if (inView) {
                    clearTimeout(el._fadeTimeout); // cancel any pending hide
                    el.classList.add('visible');
                } else {
                    // fade out first, then hide after transition
                    el.classList.remove('visible');
                    clearTimeout(el._fadeTimeout);
                    el._fadeTimeout = setTimeout(() => {
                        el.style.visibility = 'hidden';
                    }, 600); // must match transition duration
                }

                // Always reset visibility to visible if showing
                if (inView) {
                    el.style.visibility = 'visible';
                }
            });
        }

        window.addEventListener('scroll', toggleVisibility);
        window.addEventListener('resize', toggleVisibility);
        toggleVisibility(); // Run on load
    });
    </script>
    
</body>
</html>
