<?php
// Temporarily enable error reporting to catch any hidden fatal errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'secrets.php';

if (isset($_SERVER['HTTP_REFERER'])) {
    $referrer = basename($_SERVER['HTTP_REFERER']);
    if (in_array($referrer, ['event.php', 'bmx.php', 'inline.php', 'skateboard.php'])) {
        $_SESSION['referrer'] = $referrer;
    }
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$success_token = isset($_GET['success_token']) ? htmlspecialchars($_GET['success_token']) : '';

if ($event_id > 0) {
    // Removed e.registration_fee since we moved it to event_categories
    $event_sql = "SELECT e.event_name, e.description, e.location, e.registration, e.category, e.status FROM upcoming_events e WHERE e.id = $event_id AND e.status IN ('active', 'archived')";
    $event_result = $conn->query($event_sql);

    if ($event_result && $event_result->num_rows > 0) {
        $event = $event_result->fetch_assoc();
        if (!isset($_SESSION['viewed_event_' . $event_id])) {
            $conn->query("UPDATE upcoming_events SET views = views + 1 WHERE id = $event_id");
            $_SESSION['viewed_event_' . $event_id] = true;
        }
    } else {
        echo "Event not found or no active event available.";
        exit;
    }

    $event_sql = "SELECT registration_limit FROM upcoming_events WHERE id = $event_id";
    $event_result = $conn->query($event_sql);
    $event_data = $event_result->fetch_assoc();
    $registration_limit = $event_data['registration_limit'];

    $registration_count_sql = "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = $event_id AND status = 'paid'";
    $registration_count_result = $conn->query($registration_count_sql);

    if ($registration_count_result && $registration_count_result->num_rows > 0) {
        $registration_count_data = $registration_count_result->fetch_assoc();
        $registration_count = $registration_count_data['total'];
    } else {
        $registration_count = 0;
    }

    $popularity_status = 'Available';
    $popularity_color = '#25523B';
    $slots_left = 0;

    if ($registration_limit > 0) {
        $slots_left = $registration_limit - $registration_count;
        if ($registration_count >= 0.75 * $registration_limit) {
            $popularity_status = 'Filling Fast';
            $popularity_color = '#f39c12';
        }
        if ($registration_count >= $registration_limit) {
            $popularity_status = 'Almost Full';
            $popularity_color = '#c0392b';
        }
    }

    // Safely fetching schedules without fetch_all()
    $schedule_sql = "SELECT event_date, start_time, end_time FROM event_schedules WHERE event_id = $event_id";
    $schedule_result = $conn->query($schedule_sql);
    $schedules = [];
    if ($schedule_result && $schedule_result->num_rows > 0) {
        while($row = $schedule_result->fetch_assoc()) {
            $schedules[] = $row;
        }
    }

    // Safely fetching sponsors without fetch_all()
    $sponsor_sql = "SELECT logo_path FROM sponsor_logos WHERE event_id = $event_id";
    $sponsor_result = $conn->query($sponsor_sql);
    $sponsors = [];
    if ($sponsor_result && $sponsor_result->num_rows > 0) {
        while($row = $sponsor_result->fetch_assoc()) {
            $sponsors[] = $row;
        }
    }

    // Safely fetching images without fetch_all()
    $image_sql = "SELECT image_path FROM event_images WHERE event_id = $event_id";
    $image_result = $conn->query($image_sql);
    $images = [];
    if ($image_result && $image_result->num_rows > 0) {
        while($row = $image_result->fetch_assoc()) {
            $images[] = $row;
        }
    }

    $leaderboards = null;
    if (isset($event['status']) && $event['status'] === 'archived') {
        $table_check = $conn->query("SHOW TABLES LIKE 'event_leaderboards'");
        if ($table_check && $table_check->num_rows > 0) {
            $leaderboards = $conn->query("SELECT * FROM event_leaderboards WHERE event_id = $event_id ORDER BY id ASC");
        }
    }
} else {
    echo "Invalid event ID.";
    exit;
}

$categories_sql = "SELECT sport_type, category_name, fee FROM event_categories WHERE event_id = $event_id";
$categories_result = $conn->query($categories_sql);
$dynamic_categories = [];
$is_paid_event = false;

if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $sport = strtolower($row['sport_type']);
        $fee = (float)$row['fee'];
        
        if ($fee > 0) {
            $is_paid_event = true;
        }

        $dynamic_categories[$sport][] = [
            'name' => $row['category_name'],
            'fee' => $fee
        ];
    }
}
$dynamic_categories_json = json_encode($dynamic_categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Page</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="Css/eventPages.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <style>
        body, html, * {
            font-family: 'Poppins', sans-serif !important;
        }
    </style>
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
            <h1><?php echo isset($event['event_name']) ? htmlspecialchars($event['event_name']) : 'Event not found'; ?></h1>
        </div>
    </section>

    <div id="toast-notification" class="toast">Link copied to clipboard!</div>

    <div class="event-page animate-on-scroll">
    <div class="event-container">
        
        <div class="left-section">
            <div class="sticky-wrapper">
                <div class="swiper-wrapper-container">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <?php
                            if (!empty($images)) {
                                foreach ($images as $image) {
                                    echo '<div class="swiper-slide">';
                                    echo '<img src="' . htmlspecialchars($image['image_path']) . '" alt="Event Poster" class="event-poster" onclick="openModal(\'' . htmlspecialchars($image['image_path']) . '\')">';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="swiper-slide"><p>No images available.</p></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <div class="right-section">
            <div class="header-actions">
                <button onclick="goBack()" class="return-link">
                    <span>&#8592;</span> Return to Events
                </button>
                <div class="share-container">
                    <button class="share-btn" onclick="toggleShareMenu()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                        Share Event
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

            <div class="event-content">
                <span style="display: inline-block; background-color: <?php echo $is_paid_event ? '#f39c12' : '#27ae60'; ?>; color: #ffffff; padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">
                    <?php echo $is_paid_event ? 'Paid Event' : 'Free Event'; ?>
                </span>
                <h3 class="section-title">Schedule</h3>
                <div class="info-card">
                    <ul class="schedule-list">
                    <?php
                    if (!empty($schedules)) {
                        foreach ($schedules as $schedule) {
                            $event_date = new DateTime($schedule['event_date']);
                            $start_time = new DateTime($schedule['start_time']);
                            $end_time = new DateTime($schedule['end_time']);
                            echo '<li>' . $event_date->format('l, F j, Y') . ' — ' . $start_time->format('g:i A') . ' to ' . $end_time->format('g:i A') . '</li>';
                        }
                    } else {
                        echo "<li>No schedule available.</li>";
                    }
                    ?>
                    </ul>
                </div>

                <div class="location-box">
                    <span style="font-size: 1.2rem;">📍</span>
                    <span><?php echo isset($event['location']) ? htmlspecialchars($event['location']) : 'Location not available'; ?></span>
                </div>

                <h3 class="section-title">About This Event</h3>
                <div class="description-text">
                    <?php echo isset($event['description']) ? $event['description'] : 'No description provided.'; ?>
                </div>

                <?php if ($event['registration'] == 1): ?>
                    <div class="registration-area" <?php echo ($event['status'] === 'archived') ? 'style="padding: 15px; display: flex; justify-content: center; min-height: auto;"' : ''; ?>>
                        
                        <?php if ($event['status'] === 'archived'): ?>
                            <div class="event-popularity" style="margin: 0;">
                                <span class="popularity-badge" style="background-color: #6b7280; font-family: 'Poppins', sans-serif;">
                                    Event Concluded
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="registration-header">
                                <div class="fee-display" style="font-family: 'Poppins', sans-serif;">
                                    Registration Open
                                </div>

                                <?php if ($registration_limit > 0 && $registration_count >= $registration_limit): ?>
                                    <div class="event-popularity">
                                        <span class="popularity-badge" style="background-color: #ef4444; font-family: 'Poppins', sans-serif;">
                                            Registration Closed - Full
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="event-popularity">
                                        <span class="popularity-badge" style="font-family: 'Poppins', sans-serif;">
                                            <?php echo ($registration_limit > 0) ? "$slots_left Slots Remaining" : "$registration_count Joined"; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($registration_limit == 0 || $registration_count < $registration_limit): ?>
                                <button id="registerBtn" class="register-btn" style="font-family: 'Poppins', sans-serif;">
                                    Secure Your Spot
                                </button>
                            <?php endif; ?>
                            
                            <div class="link-container">
                                <a href="#" class="token-link" onclick="showTokenModal()" style="font-family: 'Poppins', sans-serif;">Already registered? Edit registration here</a>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>

                <?php if ($event['status'] === 'archived'): ?>
                <h3 class="section-title">Event Leaderboard</h3>
                <div class="info-card" style="padding: 0; overflow: hidden; border: 1px solid #ddd; border-radius: 8px;">
                    <?php if ($leaderboards && $leaderboards->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f9f9f9; border-bottom: 2px solid #eee;">
                                    <th style="padding: 12px 15px; text-align: left; font-weight: 600;">Rank</th>
                                    <th style="padding: 12px 15px; text-align: left; font-weight: 600;">Name</th>
                                    <th style="padding: 12px 15px; text-align: left; font-weight: 600;">Category</th>
                                    <th style="padding: 12px 15px; text-align: left; font-weight: 600;">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($lb = $leaderboards->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 12px 15px;"><?php echo htmlspecialchars($lb['rank']); ?></td>
                                        <td style="padding: 12px 15px; font-weight: 600;"><?php echo htmlspecialchars($lb['player_name']); ?></td>
                                        <td style="padding: 12px 15px;">
                                            <span style="background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 20px; font-size: 0.85em; font-weight: 500;">
                                                <?php echo htmlspecialchars($lb['category']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px 15px;"><?php echo htmlspecialchars($lb['score']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="padding: 20px; text-align: center; color: #666;">No leaderboard data available for this event.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <h3 class="section-title">Partners & Sponsors</h3>
                <div class="sponsors-grid">
                    <?php
                    if (!empty($sponsors)) {
                        foreach ($sponsors as $sponsor) {
                            echo '<div class="sponsor-logo-container">';
                            echo '<img src="' . htmlspecialchars($sponsor['logo_path']) . '" alt="Sponsor Logo" class="sponsor-logo">';
                            echo '</div>';
                        }
                    } else {
                        echo "<p class='text-muted'>No sponsors listed.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <div id="registrationModal" class="registration-modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeRegistrationModal()">&times;</span>
            
            <div id="step1-waiver" class="step-container active">
                <h2>Consent & Liability Waiver</h2>
                <div class="waiver-box">
                    <p><strong>WAIVER AND RELEASE OF LIABILITY</strong></p>
                    <p>In consideration of being allowed to participate in this event, I hereby agree to the following:</p>
                    <ol>
                        <li>I acknowledge that skateboarding, BMX, and inline skating are hazardous activities and involve significant risks of injury.</li>
                        <li>I assume full responsibility for any risks, injuries, or damages, known or unknown, which I might incur as a result of participating in the event.</li>
                        <li>I hereby release, discharge, and covenant not to sue the event organizers, sponsors, and property owners from any and all liability caused by my participation.</li>
                        <li>I consent to emergency medical treatment in the event of injury or illness.</li>
                        <li>I grant permission for the use of photographs or video recordings of me for promotional purposes.</li>
                    </ol>
                    <p>By checking the box below, I acknowledge that I have read and fully understand this waiver.</p>
                </div>
                <div class="waiver-checkbox-container">
                    <input type="checkbox" id="waiverCheck">
                    <label for="waiverCheck">I have read and agree to the Consent & Liability Waiver.</label>
                </div>
                <button id="waiverNextBtn" class="next-btn" disabled>Next</button>
            </div>

            <div id="step2-form" class="step-container">
                <h2>Register for the Event</h2>
                <div style="background: rgba(37, 82, 59, 0.05); border: 2px solid rgba(37, 82, 59, 0.2); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 25px; display: flex; flex-direction: column; justify-content: center; align-items: center;" id="feeDisplayContainer">
                    <span style="font-size: 1rem; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Total Registration Fee</span>
                    <span id="dynamicFeeDisplay" style="font-size: 2.8rem; font-weight: 800; color: #25523B; line-height: 1;">₱0.00</span>
                </div>
                
                <form id="registrationForm" action="submit_registration.php" method="POST">
                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

                    <?php if (isset($event['category']) && strtolower($event['category']) === 'all'): ?>
                        <label for="sportCategory">Sport Category:</label>
                        <select name="category" id="sportCategory" required>
                            <option value="">Select Sport...</option>
                            <option value="skateboard">Skateboard</option>
                            <option value="inline">Inline</option>
                            <option value="bmx">BMX</option>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="category" id="sportCategory" value="<?php echo htmlspecialchars(strtolower($event['category'])); ?>">
                    <?php endif; ?>

                    <label for="sub_category">Event Category:</label>
                    <select name="sub_category" id="sub_category" required disabled>
                        <option value="">Select Event Category...</option>
                    </select>
                    <input type="hidden" name="calculated_fee" id="calculated_fee" value="0">

                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required>
                    <label for="age">Age:</label>
                    <input type="number" name="age" id="age" required>
                    <label for="gender">Gender:</label>
                    <select name="gender" id="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
        
                    <div class="g-recaptcha" data-sitekey="6LezuAorAAAAAN_jcei_sHBW0gNq_im-TA4oZ8wI"></div>
                    <button type="submit" id="submitBtn">
                        Submit Registration
                    </button>
                    <div id="loader" style="display:none; text-align:center; margin-top:10px;">Processing...</div>
                    <div id="registrationStatus" class="status-msg"></div>
                </form>
            </div>
        </div>
    </div>

    <div id="tokenSuccessModal" class="registration-modal" style="display:none;" onclick="closeTokenSuccessModal(event)">
        <div class="modal-content" onclick="event.stopPropagation();">
            <span class="close" onclick="closeTokenSuccessModal()">&times;</span>
            <h2>Registration Successful!</h2>
            <p>Your token is:</p>
            <div class="token" id="generatedTokenText" style="font-weight: bold; font-size: 1.2rem; margin: 10px 0;"></div>
            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                <button onclick="copyGeneratedToken()" style="background: #3498db; color: white; padding: 8px 12px; border: none; border-radius: 5px;">Copy</button>
                <div id="flashMessage" style="display: none; color: green; font-weight: bold;"></div>
                <button onclick="closeTokenSuccessModal()" style="background: #2ecc71; color: white; padding: 8px 12px; border: none; border-radius: 5px;">Okay</button>
                <p>Use token to manage your Registration</p>
            </div>
        </div>
    </div>
    
    <div id="tokenModal" class="registration-modal" style="display:none;" onclick="closeTokenModal(event)">
        <div class="modal-content" onclick="event.stopPropagation();">
            <span class="close" onclick="closeTokenModal()">&times;</span>
            <h2>Enter Your Token</h2>
            <form id="tokenForm" action="manage_registration.php" method="POST">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <input type="text" id="token" name="token" required placeholder="Enter your token here">
                <button type="submit">Submit</button>
                <a href="javascript:void(0);" id="forgotTokenLink" onclick="showForgotTokenForm()">Forgot your token?</a>
            </form>
            <div id="forgotTokenForm" style="display:none;">
                <h3>Retrieve Your Token</h3>
                <form id="retrieveTokenForm">
                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                    <label for="email">Enter your email:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                    <button type="submit">Retrieve Token</button>
                </form>
                <div id="retrieveTokenMessage" class="status-msg"></div>
            </div>
        </div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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
        const swiper = new Swiper('.swiper-container', {
            loop: true,
            pagination: { el: '.swiper-pagination', clickable: true },
            autoplay: false,
        });

        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImage.src = src;
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        document.addEventListener("DOMContentLoaded", function () {
            const registerBtn = document.getElementById('registerBtn');
            const registrationModal = document.getElementById('registrationModal');
            const closeModalBtn = document.querySelector('.close');
            
            const step1Waiver = document.getElementById('step1-waiver');
            const step2Form = document.getElementById('step2-form');
            const waiverCheck = document.getElementById('waiverCheck');
            const waiverNextBtn = document.getElementById('waiverNextBtn');

            function resetRegistrationModal() {
                step1Waiver.classList.add('active');
                step2Form.classList.remove('active');
                step1Waiver.style.display = 'block';
                step2Form.style.display = 'none';
                waiverCheck.checked = false;
                waiverNextBtn.disabled = true;
            }

            waiverCheck.addEventListener('change', function() {
                waiverNextBtn.disabled = !this.checked;
            });

            waiverNextBtn.addEventListener('click', function() {
                step1Waiver.style.display = 'none';
                step1Waiver.classList.remove('active');
                step2Form.style.display = 'block';
                step2Form.classList.add('active');
            });

            <?php if (!empty($success_token)): ?>
                showTokenSuccessModal(<?php echo json_encode($success_token); ?>);
            <?php endif; ?>

            if (registerBtn) {
                registerBtn.onclick = () => {
                    resetRegistrationModal();
                    registrationModal.style.display = 'block';
                    const eventId = "<?php echo $event_id; ?>";
                    const formData = new FormData();
                    formData.append('event_id', eventId);
                    
                    fetch('track_click.php', {
                        method: 'POST',
                        body: formData
                    }).catch(err => console.error(err));
                };
            }

            if (closeModalBtn) closeModalBtn.onclick = () => registrationModal.style.display = 'none';

            window.onclick = function (event) {
                if (event.target === registrationModal) registrationModal.style.display = 'none';
            };

            const elements = document.querySelectorAll('.animate-on-scroll');
            elements.forEach(el => { el._fadeTimeout = null; });

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
                        el._fadeTimeout = setTimeout(() => { el.style.visibility = 'hidden'; }, 600);
                    }
                });
            }

            window.addEventListener('scroll', toggleVisibility);
            window.addEventListener('resize', toggleVisibility);
            toggleVisibility();

            const registrationForm = document.getElementById("registrationForm");
            const statusDiv = document.getElementById('registrationStatus');
            const dynamicCategories = <?php echo $dynamic_categories_json; ?>;
            const sportCategoryInput = document.getElementById('sportCategory');
            const subCategorySelect = document.getElementById('sub_category');
            const dynamicFeeDisplay = document.getElementById('dynamicFeeDisplay');
            const calculatedFeeInput = document.getElementById('calculated_fee');
            const submitBtnDisplay = document.getElementById('submitBtn');
            const defaultSport = "<?php echo htmlspecialchars(strtolower($event['category'])); ?>";

            function updateSubCategories(sportType) {
                subCategorySelect.innerHTML = '<option value="">Select Event Category...</option>';
                dynamicFeeDisplay.innerText = '₱0.00';
                calculatedFeeInput.value = '0';
                if(submitBtnDisplay) submitBtnDisplay.innerText = 'Submit Registration';
                subCategorySelect.disabled = true;

                if (sportType && dynamicCategories[sportType]) {
                    dynamicCategories[sportType].forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.name;
                        option.text = cat.name;
                        option.dataset.fee = cat.fee;
                        subCategorySelect.appendChild(option);
                    });
                    subCategorySelect.disabled = false;
                }
            }

            if (sportCategoryInput && sportCategoryInput.tagName === 'SELECT') {
                sportCategoryInput.addEventListener('change', function() {
                    updateSubCategories(this.value.toLowerCase());
                });
            } else if (sportCategoryInput) {
                updateSubCategories(defaultSport);
            }

            if (subCategorySelect) {
                subCategorySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value !== "") {
                        const fee = parseFloat(selectedOption.dataset.fee);
                        calculatedFeeInput.value = fee;
                        if (fee > 0) {
                            dynamicFeeDisplay.innerText = '₱' + fee.toFixed(2);
                            if(submitBtnDisplay) submitBtnDisplay.innerText = 'Proceed to Payment';
                        } else {
                            dynamicFeeDisplay.innerText = 'Free';
                            if(submitBtnDisplay) submitBtnDisplay.innerText = 'Submit Registration';
                        }
                    } else {
                        dynamicFeeDisplay.innerText = '₱0.00';
                        calculatedFeeInput.value = '0';
                        if(submitBtnDisplay) submitBtnDisplay.innerText = 'Submit Registration';
                    }
                });    
            }

            if (registrationForm) {
                registrationForm.addEventListener("submit", function (event) {
                    event.preventDefault();
                    
                    const submitBtn = document.getElementById('submitBtn');
                    const loader = document.getElementById('loader');

                    submitBtn.style.display = 'none';
                    loader.style.display = 'block';
                    statusDiv.style.display = 'none';
                    statusDiv.className = 'status-msg'; 

                    const formData = new FormData(this);

                    fetch("submit_registration.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(async response => {
                        const text = await response.text();
                        try {
                            const data = JSON.parse(text);
                            
                            if (data.success) {
                                if (data.is_paid_event && data.checkout_url) {
                                    window.location.href = data.checkout_url;
                                } else if (!data.is_paid_event && data.token) {
                                    loader.style.display = 'none';
                                    submitBtn.style.display = 'block';
                                    showTokenSuccessModal(data.token);
                                } else {
                                    throw new Error("Unknown registration status.");
                                }
                            } else {
                                throw new Error(data.message || "Registration initialization failed.");
                            }
                        } catch (e) {
                            console.error("Error:", e);
                            loader.style.display = 'none';
                            submitBtn.style.display = 'block';
                            
                            statusDiv.textContent = e.message || "Error processing request.";
                            statusDiv.classList.add('error');
                            statusDiv.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        loader.style.display = 'none';
                        submitBtn.style.display = 'block';
                        
                        statusDiv.textContent = "Connection error. Please check your internet.";
                        statusDiv.classList.add('error');
                        statusDiv.style.display = 'block';
                    });
                });
            }
            
            const forgotForm = document.getElementById('retrieveTokenForm');
            if (forgotForm) {
                forgotForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;
                    const msgDisplay = document.getElementById('retrieveTokenMessage');
                    
                    submitBtn.textContent = "Sending Email...";
                    submitBtn.disabled = true;
                    
                    msgDisplay.style.display = 'none';
                    msgDisplay.className = 'status-msg';

                    const formData = new FormData(this);
                    
                    fetch('forgot_token.php', { method: 'POST', body: formData })
                    .then(async response => {
                        const data = await response.json();
                        
                        msgDisplay.textContent = data.message;
                        msgDisplay.style.display = 'block';

                        if (data.success) {
                            msgDisplay.classList.add('success');
                            setTimeout(() => {
                                closeTokenModal(); 
                            }, 3000);
                        } else {
                            msgDisplay.classList.add('error');
                        }
                    })
                    .catch(() => {
                        msgDisplay.textContent = 'Something went wrong. Please try again.';
                        msgDisplay.classList.add('error');
                        msgDisplay.style.display = 'block';
                    })
                    .finally(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }
        });

        function showTokenModal() {
            document.getElementById('tokenModal').style.display = 'block';
        }

        function closeTokenModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('tokenModal').style.display = 'none';
            document.getElementById('forgotTokenForm').style.display = 'none';
            document.getElementById('tokenForm').style.display = 'block';
        }

        function showTokenSuccessModal(token) {
            document.getElementById('generatedTokenText').textContent = token;
            document.getElementById('tokenSuccessModal').style.display = 'block';
            document.getElementById('registrationModal').style.display = 'none';
        }

        function closeTokenSuccessModal() {
            document.getElementById('tokenSuccessModal').style.display = 'none';
            window.location.href = "eventPages.php?id=<?php echo $event_id; ?>";
        }

        function copyGeneratedToken() {
            const token = document.getElementById('generatedTokenText').textContent;
            navigator.clipboard.writeText(token).then(() => {
                const flash = document.getElementById('flashMessage');
                flash.textContent = "Token copied to clipboard!";
                flash.style.display = 'block';
            }).catch(() => {
                const flash = document.getElementById('flashMessage');
                flash.textContent = "Failed to copy token.";
                flash.style.display = 'block';
            });
        }

        function showForgotTokenForm() {
            document.getElementById('tokenForm').style.display = 'none';
            document.getElementById('forgotTokenForm').style.display = 'block';
        }

        function goBack() {
            const referrer = '<?php echo isset($_SESSION['referrer']) ? htmlspecialchars($_SESSION['referrer']) : ''; ?>';
            window.location.href = referrer ? referrer : 'event.php';
        }

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
            const title = encodeURIComponent("<?php echo isset($event['event_name']) ? addslashes(htmlspecialchars($event['event_name'])) : 'Check out this event'; ?>");
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

        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>