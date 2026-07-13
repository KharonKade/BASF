<?php
$conn_events = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
$visit_conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

if ($conn_events->connect_error) {
    die("Connection failed: " . $conn_events->connect_error);
}
if ($visit_conn->connect_error) {
    die("Connection failed: " . $visit_conn->connect_error);
}

$reg_query = $conn_events->query("SELECT COUNT(*) as total FROM event_registrations");
$total_registrants = $reg_query->fetch_assoc()['total'];

$event_query = $conn_events->query("SELECT COUNT(*) as total FROM upcoming_events");
$total_events = $event_query->fetch_assoc()['total'];

$visit_query = $visit_conn->query("SELECT COUNT(*) as total FROM visit_counter");
$total_visits = $visit_query->fetch_assoc()['total'];

$conn_events->close();
$visit_conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sponsorship page</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="Css/sponsorship.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<style>
i.fas, i.fa-solid, i.fa-check, i.fa-times, i.fa-xmark {
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
}
</style>
<body style="font-family: 'Poppins', sans-serif;">
    <header>
        <nav class="navbar">
            <img src="images/basflogo.png" alt="BASF Logo" class="logo">
            <div class="nav-center">
                <ul class="nav-links" id="navLinks">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="spots.html">Spots</a></li>
                    <li><a href="event.php">Events</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="sponsorship.php">Sponsorship</a></li>
                    <li><a href="contactUs.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="hamburger" id="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <section class="event-hero">
        <div class="event-hero-content">
            <h2>Sponsorship</h2>
        </div>
    </section>
    
    <section class="first-section">
        <video class="video-background" autoplay muted loop playsinline>
            <source src="images/uploads/video-ads.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay"></div>
        <div class="first-section-content animate-on-scroll">
            <h2 class="subheading">PARTNER WITH BAGUIO ACTION SPORTS FEDERATION</h2>
            <h1 class="main-title">SUPPORT THE FUTURE OF ACTION SPORTS</h1>
            <p class="bold-text">Join us in empowering athletes, building the community, and promoting the spirit of action sports.</p>
            <div class="hero-btns">
                <a href="contactUs.php" class="sponsor-button primary">Become a Sponsor</a>
            </div>
        </div>
    </section>

    <section class="stats-section animate-on-scroll">
        <div class="container">
            <h2 class="section-title">Federation by the Numbers</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="stat-number"><?php echo number_format($total_registrants); ?></h3>
                    <p class="stat-label">Total Registered Participants</p>
                </div>
                <div class="stat-card">
                    <h3 class="stat-number"><?php echo number_format($total_events); ?></h3>
                    <p class="stat-label">Total Events Hosted</p>
                </div>
                <div class="stat-card">
                    <h3 class="stat-number"><?php echo number_format($total_visits); ?>+</h3>
                    <p class="stat-label">Total Website Visits</p>
                </div>
                <div class="stat-card">
                    <h3 class="stat-number">3</h3>
                    <p class="stat-label">Core Action Sports</p>
                </div>
            </div>
        </div>
    </section>

    <section class="second-section animate-on-scroll">
        <div class="container">
            <span class="section-badge">Opportunities</span>
            <h2 class="section-title">Why Partner with Us?</h2>
            
            <div class="sponsorship-grid">
                <div class="sponsor-card">
                    <div class="card-icon">🎯</div>
                    <h3>Mission</h3>
                    <p>We empower action sports athletes, build a united community, and host impactful events that inspire the next generation to push boundaries.</p>
                </div>

                <div class="sponsor-card highlight-card">
                    <div class="card-icon">🤝</div>
                    <h3>The Impact</h3>
                    <ul>
                        <li>Amplify your brand in a growing community.</li>
                        <li>Enable events that attract athletes and fans.</li>
                        <li>Improve local facilities and skateparks.</li>
                    </ul>
                </div>

                <div class="sponsor-card">
                    <div class="card-icon">💼</div>
                    <h3>Core Benefits</h3>
                    <ul>
                        <li>Logo placement on banners & website.</li>
                        <li>Shoutouts during livestreams.</li>
                        <li>Featured partner on digital platforms.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="tiers-section animate-on-scroll">
        <div class="container">
            <span class="section-badge" style="background: #25523B; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Packages</span>
            <h2 class="section-title" style="margin-top: 15px;">Choose Your Impact</h2>
            
            <div class="pricing-grid">
                <div class="pricing-card">
                    <h3>Community Partner</h3>
                    <p class="price">₱5,000</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Logo on the website Sponsors grid</li>
                        <li><i class="fas fa-check"></i> Shoutout on social media</li>
                        <li><i class="fas fa-times"></i> Physical event banners</li>
                        <li><i class="fas fa-times"></i> Event naming rights</li>
                    </ul>
                    <a href="contactUs.php" class="sponsor-button outline">Select Tier</a>
                </div>

                <div class="pricing-card popular">
                    <div class="popular-badge">Most Popular</div>
                    <h3>Event Sponsor</h3>
                    <p class="price">₱15,000</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Logo on website Sponsors grid</li>
                        <li><i class="fas fa-check"></i> Dedicated announcement post</li>
                        <li><i class="fas fa-check"></i> Logo on physical event banners</li>
                        <li><i class="fas fa-times"></i> Event naming rights</li>
                    </ul>
                    <a href="contactUs.php" class="sponsor-button primary" style="background: #25523B; color: white; padding: 12px 25px; border-radius: 30px; text-decoration: none; font-weight: 600; text-align: center;">Select Tier</a>
                </div>

                <div class="pricing-card">
                    <h3>Title Sponsor</h3>
                    <p class="price">₱30,000+</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Homepage featured logo</li>
                        <li><i class="fas fa-check"></i> Naming rights to an event</li>
                        <li><i class="fas fa-check"></i> VIP booth space at events</li>
                        <li><i class="fas fa-check"></i> Premium banner placement</li>
                    </ul>
                    <a href="contactUs.php" class="sponsor-button outline">Select Tier</a>
                </div>
            </div>
        </div>
    </section>

    <section class="marquee-section animate-on-scroll">
        <h2 class="section-title" style="margin: 0; padding-bottom: 40px;">Our Current Sponsors</h2>
        <div class="marquee-container">
            <div class="marquee-content">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
                <img src="images/vanswhite.png" alt="Sponsor">
                <img src="images/dclogo.png" alt="Sponsor">
            </div>
        </div>
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
                <li><a href="sponsorship.php">Sponsorship</a></li>
                <li><a href="contactUs.php">Contact Us</a></li>
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
                        } else {
                            el.classList.remove('visible');
                            clearTimeout(el._fadeTimeout);
                            el._fadeTimeout = setTimeout(() => {
                                el.style.visibility = 'hidden';
                            }, 600); 
                        }

                        if (inView) {
                            el.style.visibility = 'visible';
                        }
                    });
                }

                window.addEventListener('scroll', toggleVisibility);
                window.addEventListener('resize', toggleVisibility);
                toggleVisibility(); 
            });
        </script>
        <script>
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    </script>
</body>
</html>