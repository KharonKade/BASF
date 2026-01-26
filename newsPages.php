<?php
// Establish a connection to the database
$servername = "localhost"; // Your database host
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "basf_news";     // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the news ID from the URL
$news_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id > 0) {
    // Fetch news details based on the news ID
    $news_sql = "
        SELECT n.news_title, n.news_content, n.category, n.publish_date
        FROM news_announcements n
        WHERE n.news_id = $news_id";

    $news_result = $conn->query($news_sql);

    // Check if the news result is valid and contains data
    if ($news_result && $news_result->num_rows > 0) {
        $news = $news_result->fetch_assoc();
    } else {
        echo "News not found.";
        exit;
    }

    // Fetch news images based on the news ID
    $image_sql = "SELECT image_path FROM news_images WHERE news_id = $news_id";
    $image_result = $conn->query($image_sql);

    // Check if the image result is valid and contains data
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
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <div class="swiper-container">
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

                <div class="news-content">
                    <?php echo isset($news['news_content']) ? nl2br($news['news_content']) : 'Content not available.'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal for Image Preview -->
    <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img class="modal-content-img" id="modalImage" />
        </div>
    </div>

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
        const swiper = new Swiper('.swiper-container', {
            loop: true, // Enables infinite scrolling
            pagination: {
                el: '.swiper-pagination',
                clickable: true, // Enables clickable pagination dots
            },
            autoplay: false, // Autoplay off
        });

        // Open Modal Function
        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImage.src = src;

            // Store the image array and current index in global variables
            window.currentImages = images;
            window.currentIndex = images.indexOf(src);
        }

        // Close Modal Function
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

    // If the user clicks anywhere outside the modal, close it
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

<?php
$conn->close();
?>
