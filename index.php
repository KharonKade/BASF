<?php include_once 'visit_tracker.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="Css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <div id="usabilityModal" class="modal-overlay">
        <div class="modal-content">
            <h2>Usability Testing Notice</h2>
            <p>This website is currently for usability testing purposes. Please note that the content displayed here is not official.</p>
            <button id="closeModalBtn">I Understand</button>
        </div>
    </div>
    <header>
        <nav class="navbar">
            <img src="images/basflogo.png" alt="BASF Logo" id="basflogo" class="logo">
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
        
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search news title...">
        </div>

        <div class="news-grid-wrapper">
            <div class="news-grid" id="newsGrid">
                <?php
                $conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT * FROM news_announcements WHERE status = 'active' ORDER BY publish_date DESC";
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
                $conn->close();
                ?>
            </div>

            <div class="pagination-container" id="paginationControls">
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

        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('newsGrid');
        const paginationContainer = document.getElementById('paginationControls');
        const searchInput = document.getElementById('searchInput');
        let items = Array.from(grid.getElementsByClassName('news-item'));
        let currentPage = 1;

        function getItemsPerPage() {
            const gridStyle = window.getComputedStyle(grid);
            const gridColumns = gridStyle.getPropertyValue('grid-template-columns').split(' ').length;
            return gridColumns * 2; 
        }

        function showPage(page) {
            const itemsPerPage = getItemsPerPage();
            const totalItems = items.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            if (page < 1) page = 1;
            if (page > totalPages && totalPages > 0) page = totalPages;
            currentPage = page;

            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            items.forEach((item, index) => {
                if (index >= start && index < end) {
                    item.classList.remove('hidden');
                    item.style.display = 'flex'; 
                } else {
                    item.classList.add('hidden');
                    item.style.display = 'none';
                }
            });

            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            const prevBtnLink = document.createElement('a');
            prevBtnLink.href = 'javascript:void(0)';
            const prevBtn = document.createElement('button');
            prevBtn.innerText = 'Prev';
            if (currentPage === 1) {
                prevBtn.disabled = true;
            } else {
                prevBtn.onclick = () => showPage(currentPage - 1);
            }
            prevBtnLink.appendChild(prevBtn);
            paginationContainer.appendChild(prevBtnLink);

            let maxVisible = 4;
            let startPage = Math.max(1, currentPage - 1);
            let endPage = startPage + maxVisible - 1;

            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                const firstPageLink = document.createElement('a');
                firstPageLink.href = 'javascript:void(0)';
                const firstPageBtn = document.createElement('button');
                firstPageBtn.innerText = '1';
                firstPageBtn.onclick = () => showPage(1);
                firstPageLink.appendChild(firstPageBtn);
                paginationContainer.appendChild(firstPageLink);

                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.innerText = '...';
                    ellipsis.style.padding = '8px';
                    ellipsis.style.fontFamily = "'Poppins', sans-serif";
                    ellipsis.style.fontWeight = "bold";
                    paginationContainer.appendChild(ellipsis);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageLink = document.createElement('a');
                pageLink.href = 'javascript:void(0)';
                
                const pageBtn = document.createElement('button');
                pageBtn.innerText = i;
                if (i === currentPage) {
                    pageBtn.classList.add('active');
                }
                pageBtn.onclick = () => showPage(i);
                
                pageLink.appendChild(pageBtn);
                paginationContainer.appendChild(pageLink);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.innerText = '...';
                    ellipsis.style.padding = '8px';
                    ellipsis.style.fontFamily = "'Poppins', sans-serif";
                    ellipsis.style.fontWeight = "bold";
                    paginationContainer.appendChild(ellipsis);
                }
                
                const lastPageLink = document.createElement('a');
                lastPageLink.href = 'javascript:void(0)';
                const lastPageBtn = document.createElement('button');
                lastPageBtn.innerText = totalPages;
                lastPageBtn.onclick = () => showPage(totalPages);
                lastPageLink.appendChild(lastPageBtn);
                paginationContainer.appendChild(lastPageLink);
            }

            const nextBtnLink = document.createElement('a');
            nextBtnLink.href = 'javascript:void(0)';
            const nextBtn = document.createElement('button');
            nextBtn.innerText = 'Next';
            if (currentPage === totalPages) {
                nextBtn.disabled = true;
            } else {
                nextBtn.onclick = () => showPage(currentPage + 1);
            }
            nextBtnLink.appendChild(nextBtn);
            paginationContainer.appendChild(nextBtnLink);
        }

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                showPage(1); 
            }, 100);
        });

        searchInput.addEventListener('input', function() {
            const query = this.value;

            fetch(`get_news.php?q=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(data => {
                    grid.innerHTML = data;
                    items = Array.from(grid.getElementsByClassName('news-item'));
                    showPage(1);
                })
                .catch(error => console.error('Error:', error));
        });

        showPage(1);
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
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("usabilityModal");
        const closeBtn = document.getElementById("closeModalBtn");

        if (!localStorage.getItem("usabilityModalSeen")) {
            modal.style.display = "flex";
        }

        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";
            localStorage.setItem("usabilityModalSeen", "true");
        });
    });
    </script>
</body>
</html>