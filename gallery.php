<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$galleryItems = [];
$sql = "SELECT * FROM gallery";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $gallery_id = $row['id'];
    $images = [];
    $imgQuery = "SELECT image_path FROM gallery_images WHERE gallery_id = $gallery_id";
    $imgResult = $conn->query($imgQuery);
    
    while ($imgRow = $imgResult->fetch_assoc()) {
        $images[] = $imgRow['image_path'];
    }

    $galleryItems[] = [
        "id" => $row['id'],
        "title" => $row['title'],
        "description" => $row['description'],
        "thumbnail" => $row['thumbnail'],
        "images" => $images
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Page</title>
    <link rel="stylesheet" href="Css/gallery.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
        }

        .search-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        #searchInput {
            width: 100%;
            max-width: 400px;
            padding: 12px 20px;
            border: 2px solid #25523B;
            border-radius: 20px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        #searchInput:focus {
            border-color: #333;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        .gallery-container {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1024px) {
            .gallery-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .gallery-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .gallery-container {
                grid-template-columns: repeat(1, 1fr);
            }
        }
    </style>
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

    <section class="event-hero">
        <div class="event-hero-content">
            <h2>Projects & Programs</h2>
        </div>
    </section>

    <div class="gallery-section">
        <h1 class="gallery-title animate-on-scroll">Our Gallery</h1>
        
        <div class="search-container animate-on-scroll">
            <input type="text" id="searchInput" placeholder="Search gallery...">
        </div>

        <div class="gallery-container animate-on-scroll" id="galleryContainer">
            <?php foreach ($galleryItems as $item): ?>
                <div class="gallery-item" data-title="<?php echo strtolower($item['title']); ?>" onclick="showDetails(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">
                    <img src="<?php echo $item['thumbnail']; ?>" alt="<?php echo $item['title']; ?>">
                    <div class="gallery-overlay">
                        <p><?php echo $item['title']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="pagination-controls" class="pagination-container animate-on-scroll"></div>
    </div>

    <div id="galleryOverlay" class="modal-gallery-overlay" onclick="closeGalleryDetails()"></div>

    <div id="galleryDetails" class="gallery-details">
        <button class="close-gallery-details" onclick="closeGalleryDetails()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="details-content">
            <div class="details-header">
                <h2 id="details-title"></h2>
                <p id="details-description"></p>
            </div>
            
            <div id="details-images" class="details-images">
                
            </div>
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
        function showDetails(item) {
            document.getElementById("details-title").innerText = item.title;
            document.getElementById("details-description").innerHTML = item.description;

            let imageContainer = document.getElementById("details-images");
            imageContainer.innerHTML = "";

            if (item.thumbnail) {
                let thumbnailImg = document.createElement("img");
                thumbnailImg.src = item.thumbnail;
                thumbnailImg.className = "details-img";
                imageContainer.appendChild(thumbnailImg);
            }

            if (item.images && Array.isArray(item.images)) {
                item.images.forEach(img => {
                    let imgTag = document.createElement("img");
                    imgTag.src = img;
                    imgTag.className = "details-img";
                    imageContainer.appendChild(imgTag);
                });
            }

            document.getElementById("galleryOverlay").style.display = "block";
            document.getElementById("galleryDetails").style.display = "flex";
        }

        function closeGalleryDetails() {
            document.getElementById('galleryOverlay').style.display = 'none';
            document.getElementById('galleryDetails').style.display = 'none';
        }

        let currentPage = 1;
        let itemsPerPage = 8;
        let allGalleryItems = [];
        let filteredItems = [];

        function calculateItemsPerPage() {
            const width = window.innerWidth;
            if (width > 1024) {
                itemsPerPage = 8; 
            } else if (width > 768) {
                itemsPerPage = 6; 
            } else if (width > 480) {
                itemsPerPage = 4; 
            } else {
                itemsPerPage = 2; 
            }
            renderPage();
        }

        function renderPage() {
            allGalleryItems.forEach(item => item.style.display = 'none');

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

        document.addEventListener("DOMContentLoaded", function () {
            allGalleryItems = Array.from(document.querySelectorAll('.gallery-item'));
            filteredItems = [...allGalleryItems];
            
            calculateItemsPerPage();
            
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                
                filteredItems = allGalleryItems.filter(item => {
                    const title = item.getAttribute('data-title');
                    return title.includes(searchTerm);
                });

                currentPage = 1;
                renderPage();
            });

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
            window.addEventListener('resize', () => {
                calculateItemsPerPage();
                toggleVisibility();
            });
            toggleVisibility();
        });
        
    </script>
</body>
</html>