<?php include_once 'visit_tracker.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="Css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar">
            <img src="images/basflogo.png" alt="BASF Logo"  id="basflogo" class="logo">
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

    <section class="container sports-container animate-on-scroll">
        <h1>Sports</h1>
        <div class="sports-buttons animate-on-scroll">
            <button onclick="window.location.href='bmx.php'">BMX</button>
            <button onclick="window.location.href='inline.php'">In-Line</button>
            <button onclick="window.location.href='skateboard.php'">Skateboard</button>
        </div>
    </section>

        <section id="news" class="news-container animate-on-scroll">
            <h2>News & Announcements</h2>
            <div class="news-grid-wrapper">
                <div class="news-grid">
                <?php
                    $conn = new mysqli("localhost", "root", "", "basf_news");

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $items_per_page = 8;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $items_per_page;

                    $total_sql = "SELECT COUNT(*) FROM news_announcements WHERE status = 'active'";
                    $total_result = $conn->query($total_sql);
                    $total_row = $total_result->fetch_row();
                    $total_items = $total_row[0];
                    $total_pages = ceil($total_items / $items_per_page);

                    $sql = "SELECT * FROM news_announcements WHERE status = 'active' ORDER BY publish_date DESC LIMIT $offset, $items_per_page";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $news_title = $row['news_title'];
                            $news_content = $row['news_content'];
                            $publish_date = $row['publish_date'];
                            $image_path = '';

                            $publish_date_obj = new DateTime($publish_date);
                            $formatted_publish_date = $publish_date_obj->format('l, F j, Y');

                            $news_id = $row['news_id'];
                            $image_sql = "SELECT * FROM news_images WHERE news_id = '$news_id' LIMIT 1";
                            $image_result = $conn->query($image_sql);
                            if ($image_result->num_rows > 0) {
                                $image_row = $image_result->fetch_assoc();
                                $image_path = $image_row['image_path'];
                            }

                            echo '
                                <div class="news-item">
                                    <img src="' . $image_path . '" alt="' . $news_title . '">
                                    <div class="news-item-content">
                                        <h3>' . $news_title . '</h3>
                                            <p class="news-desc">' . substr(strip_tags($news_content), 0, 50) . '...</p>
                                            <p class="publish-date">' . $formatted_publish_date . '</p>
                                            <a class="read-more" href="newsPages.php?id=' . $news_id . '">Read More</a>
                                    </div>
                                </div>';
                        }
                    } else {
                        echo '<p>No news available at the moment.</p>';
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>#news" class="page-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>#news" class="page-link <?php if ($page == $i) echo 'active'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>#news" class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php $conn->close(); ?>
            </div>
        </section>


        <div class="advertisement animate-on-scroll">
            <a id="ad-link" href="#" target="_blank">
                <div class="ad-container">
                    <img id="ad-image" src="" alt="Advertisement">
                    <span class="ad-label">Ads</span>
                </div>
            </a>
        </div>
    </section>

    <section class="partnership-section animate-on-scroll">
        <h2>In Partnership With</h2>
        <div class="partner-logos">
            <div class="partner-logo-container"><img src="images/vanlogo.png" alt="Partner 1" class="partner-logo"></div>
            <div class="partner-logo-container"><img src="images/sk.jpg" alt="Partner 2" class="partner-logo"></div>
            <div class="partner-logo-container"><img src="images/skate.jpg" alt="Partner 3" class="partner-logo"></div>
            <div class="partner-logo-container"><img src="images/bmx logo.png" alt="Partner 4" class="partner-logo"></div>
            <div class="partner-logo-container"><img src="images/wheel.png" alt="Partner 5" class="partner-logo"></div>
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

        <div class="footer-section explore-section ">
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
    
    <script src="jsScript/index.js"></script>
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
        document.getElementById("basflogo").addEventListener("dblclick", function () {
            window.location.href = "admin.php";
        });
        </script>
</body>
</html>