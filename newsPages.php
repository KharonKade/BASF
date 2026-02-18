<?php
$servername = "localhost";
$username = "u142318015_usr_vf0t87O1";
$password = "W1xz8gB^";
$dbname = "u142318015_db_vf0t87O1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$news_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id > 0) {
    $news_sql = "
        SELECT n.news_title, n.news_content, n.category, n.publish_date
        FROM news_announcements n
        WHERE n.news_id = $news_id";

    $news_result = $conn->query($news_sql);

    if ($news_result && $news_result->num_rows > 0) {
        $news = $news_result->fetch_assoc();
    } else {
        echo "News not found.";
        exit;
    }

    $image_sql = "SELECT image_path FROM news_images WHERE news_id = $news_id";
    $image_result = $conn->query($image_sql);

    if ($image_result && $image_result->num_rows > 0) {
        $images = $image_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $images = [];
    }
} else {
    echo "Invalid news ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Page</title>
    <link rel="stylesheet" href="Css/newsPages.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <nav class="navbar">
            <img src="images/basflogo.png" alt="BASF Logo" class="logo">
            <div class="nav-center">
                <ul class="nav-links" id="navLinks">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="spots.html">Spots</a></li>
                    <li><a href="event.php">Events</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="sponsorship.html">Sponsorship</a></li>
                    <li><a href="contactUs.html">Contact Us</a></li>
                </ul>
            </div>
            <div class="hamburger" id="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <section class="news-hero">
        <div class="news-hero-content">
            <h1><?php echo isset($news['news_title']) ? $news['news_title'] : 'News not found'; ?></h1>
        </div>
    </section>

   <div class="news-page-container animate-on-scroll">
    <div class="content-wrapper">
        <button onclick="history.back()" class="return-btn">
            <i class="fas fa-arrow-left"></i> &larr; Return
        </button>

        <div class="news-card">
            <div class="left-section">
                <div class="swiper swiper-container">
                    <div class="swiper-wrapper">
                        <?php
                        if (!empty($images)) {
                            foreach ($images as $image) {
                                echo '<div class="swiper-slide">
                                        <img src="' . $image['image_path'] . '" alt="News Image" onclick="openModal(\'' . $image['image_path'] . '\')">
                                      </div>';
                            }
                        } else {
                            echo '<div class="no-image-placeholder"><span>No images available</span></div>';
                        }
                        ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>

            <div class="right-section">
                <div class="news-header">
                    <div class="meta-group">
                        <span class="category-badge">
                            <?php echo isset($news['category']) ? htmlspecialchars($news['category']) : 'General'; ?>
                        </span>
                        
                        <?php 
                        if (isset($news['publish_date']) && !empty($news['publish_date'])) {
                            $publish_date_obj = new DateTime($news['publish_date']);
                            $formatted_publish_date = $publish_date_obj->format('F j, Y'); 
                        } else {
                            $formatted_publish_date = 'Date not available';
                        }
                        ?>
                        <span class="publish-date"><?php echo $formatted_publish_date; ?></span>
                    </div>

                    <div class="share-container">
                        <button class="share-btn" onclick="toggleShareMenu()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                            <span>Share</span>
                        </button>
                        <div id="shareDropdown" class="share-dropdown">
                            <a href="#" onclick="shareTo('facebook')" class="share-option">
                                <img src="images/fbwhite.png" alt="FB" style="filter: invert(1);"> Facebook
                            </a>
                            <a href="#" onclick="shareTo('twitter')" class="share-option">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                 X (Twitter)
                            </a>
                            <a href="#" onclick="shareTo('copy')" class="share-option">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                Copy Link
                            </a>
                        </div>
                    </div>
                </div>

                <div class="news-content">
                    <?php echo isset($news['news_content']) ? nl2br($news['news_content']) : 'Content not available.'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img class="modal-content-img" id="modalImage" />
        </div>
    </div>

    <div id="toast-notification" class="toast">Link copied to clipboard!</div>

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

    <script>
        const swiper = new Swiper('.swiper', {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            autoplay: false,
        });

        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImage.src = src;
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
        }
    </script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
    const registerBtn = document.getElementById('registerBtn');
    const registrationModal = document.getElementById('registrationModal');
    const closeModalBtn = document.querySelector('.close');

    if (registerBtn) {
        registerBtn.onclick = function() {
            registrationModal.style.display = 'block';
        };
    }

    if (closeModalBtn) {
        closeModalBtn.onclick = function() {
            registrationModal.style.display = 'none';
        };
    }

    window.onclick = function(event) {
        if (event.target === registrationModal) {
            registrationModal.style.display = 'none';
        }
    };
});
</script>

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

    function showToast(message) {
        const toast = document.getElementById("toast-notification");
        toast.innerText = message;
        toast.classList.add("show");
        setTimeout(function() {
            toast.classList.remove("show");
        }, 3000);
    }

    function toggleShareMenu() {
        const dropdown = document.getElementById('shareDropdown');
        dropdown.classList.toggle('show');
    }

    window.addEventListener('click', function(e) {
        if (!e.target.matches('.share-btn') && !e.target.closest('.share-btn')) {
            const dropdown = document.getElementById('shareDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });

    function shareTo(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent("<?php echo isset($news['news_title']) ? addslashes($news['news_title']) : 'Check out this news'; ?>");
        let shareUrl = '';

        switch (platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                window.open(shareUrl, '_blank', 'width=600,height=400');
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
                window.open(shareUrl, '_blank', 'width=600,height=400');
                break;
            case 'copy':
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showToast('Link copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
                break;
        }
        document.getElementById('shareDropdown').classList.remove('show');
    }
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

<?php
$conn->close();
?>