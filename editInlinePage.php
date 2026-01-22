<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inline Page</title>
    <link rel="stylesheet" href="Css/editInlinePage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manage_upcoming.php"><i class="fas fa-calendar-check"></i>Events</a></li>
                <li><a href="manage_news.php"><i class="fas fa-edit"></i>News & Announcements</a></li>
                <li><a href="admin_gallery.php"><i class="fas fa-images"></i>Gallery Page</a></li>
                <li><a href="editInlinePage.php"><i class="fas fa-skating"></i>Inline Page</a></li>
                <li><a href="editBmxPage.php"><i class="fas fa-bicycle"></i>BMX Page</a></li>
                <li><a href="editSkateboardPage.php"><i class="fas fa-snowboarding"></i>Skateboard Page</a></li>
                <li><a href="view_inquiries.php"><i class="fas fa-question-circle"></i> Inquiries</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="content">
            <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname_content = "basf_content";

                $conn_content = new mysqli($servername, $username, $password, "basf_content");

                if ($conn_content->connect_error) {
                    die("Connection failed: " . $conn_content->connect_error);
                }
                include 'handle_athletes.php';
            ?>
            <h2>Manage Inline Page</h2>
            <section>
                <label>About Us:</label>
                <?php
                $result = $conn_content->query("SELECT content FROM content WHERE section='about_us'");
                $aboutUsContent = "";
                if ($row = $result->fetch_assoc()) {
                    $aboutUsContent = $row['content'];
                    echo '<p class="wrapped-text">' . $row['content'] . '</p>';
                } else {
                    echo "<p>About Us content not found.</p>";
                }
                ?>
                
                <button onclick="showEditForm('aboutUsForm')" style="display: inline-block; padding: 10px; background-color: #C76E00; color: white; border-radius: 5px;">
                    <i class="fa fa-edit"></i> Edit
                </button>

                <form id="aboutUsForm" style="display:none;" method="post" action="handle_aboutus.php">
                    <textarea name="about_us" id="about_us_editor"><?php echo htmlspecialchars($aboutUsContent); ?></textarea>
                    <button type="submit" style="display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;">
                        <i class="fa fa-check"></i> Update
                    </button>
                    <button type="button" onclick="hideForm('aboutUsForm')" style="display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </form>
            </section>

            <section>
                <label>Highlight Carousel</label>
                <button onclick="showAddForm('addHighlightForm')">
                    <i class="fa fa-plus"></i> Add Highlight
                </button>
                <form id="addHighlightForm" style="display:none;" method="post" action="handle_highlight.php" enctype="multipart/form-data">
                    <input type="file" name="video" required>
                    <input type="text" name="title" placeholder="Title" required>
                    <textarea name="description" placeholder="Description" required></textarea>
                    <button type="submit" style="display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;">
                        <i class="fa fa-check"></i> Add
                    </button>
                    <button type="button" onclick="hideForm('addHighlightForm')" style="display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </form>

                <table border="1">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = $conn_content->query("SELECT id, video, title, description FROM highlight_carousel");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='row{$row['id']}'>";
                            echo "<td>" . basename($row["video"]) . "</td>";
                            echo "<td>{$row['title']}</td>";
                            echo "<td>{$row['description']}</td>";
                            echo "<td>
                                    <a href='#' onclick=\"toggleEditForm('editRow{$row['id']}')\" class='edit-link' title='Edit'>
                                        <i class='fa fa-edit'></i> 
                                    </a> |
                                    <a href='handle_highlight.php?delete_id={$row['id']}' class='remove-link'  title='Delete' onclick='return confirm(\"Are you sure?\")'>
                                        <i class='fa fa-trash'></i> 
                                    </a>
                                </td>";
                            
                            // Edit form in a separate row
                            echo "<tr id='editRow{$row['id']}' style='display:none;'>";
                            echo "<td colspan='4'>
                                    <form method='post' action='handle_highlight.php' enctype='multipart/form-data' style='display:flex; flex-direction:culomn; gap:5px; padding: 10px;'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <h3>Edit Video File:</h3>
                                        <input type='file' name='video'>
                                        <h3>Title:</h3>
                                        <input type='text' name='title' value='{$row['title']}' required>
                                        <h3>Description:</h3>
                                        <textarea name='description' required>{$row['description']}</textarea>
                                        <button type='submit' style='display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;'>
                                            <i class='fa fa-check'></i> Update
                                        </button>
                                        <button type='button' onclick=\"toggleEditForm('editRow{$row['id']}')\" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>
                                            <i class='fa fa-times'></i> Cancel
                                        </button>
                                    </form>
                                </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>



            <section>
                <label>Top Athletes:</label>
                <button onclick="showAddForm('addAthleteForm')" style="margin-bottom: 20px;">
                    <i class="fa fa-plus"></i> Add Athlete
                </button>
                
                <!-- Add Athlete Form -->
                <form id="addAthleteForm" style="display:none;" method="post" action="handle_athletes.php" enctype="multipart/form-data">
                    <input type="text" name="name" placeholder="Athlete Name" required>
                    <textarea name="bio" placeholder="Bio" required></textarea>
                    <textarea name="description" placeholder="Description" required></textarea>
                    <input type="number" name="wins" placeholder="Wins" required>
                    <input type="number" name="podium_finishes" placeholder="Podium Finishes" required>
                    <input type="number" name="years_active" placeholder="Years Active" required>
                    <input type="text" name="specialty" placeholder="Specialty" required>
                    <input type="file" name="image" required>

                    <!-- Achievements -->
                    <h3>Achievements</h3>
                    <div id="achievements-container">
                        <div class="achievement">
                            <input type="text" name="achievements[]" placeholder="Achievement Title" required>
                            <textarea name="achievements_descriptions[]" placeholder="Description" required></textarea>
                        </div>
                    </div>
                    <button type="button" onclick="addNewAchievement()">
                        <i class="fa fa-trophy"></i> + Add More Achievements
                    </button>

                    <!-- Gallery -->
                    <h3>Gallery</h3>
                    <label>Upload Images:</label>
                    <div id="gallery-container">
                        <div class="new-gallery-item">
                            <input type="file" name="athlete_gallery[]" required>
                            <textarea name="gallery_descriptions[]" placeholder="Enter description for this image." required></textarea>
                        </div>
                    </div>
                    <button type="button" onclick="addNewGalleryImage()">
                        <i class="fa fa-image"></i> + Add More Images
                    </button>
                    
                    <div class = "button-container">
                    <button type="submit" style="display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;">
                        <i class="fa fa-check"></i> Add
                    </button>
                    <button type="button" onclick="hideForm('addAthleteForm')" style="display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    </div>
                </form>

                <?php
                $items_per_page = 1; // Number of athletes per page
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $items_per_page;
                
                // Get the total number of athletes
                $total_result = $conn_content->query("SELECT COUNT(*) as total FROM top_athletes");
                $total_row = $total_result->fetch_assoc();
                $total_athletes = $total_row['total'];
                $total_pages = ceil($total_athletes / $items_per_page);
                
                // Fetch athletes with pagination
                $result = $conn_content->query("SELECT * FROM top_athletes LIMIT $items_per_page OFFSET $offset");
                while ($row = $result->fetch_assoc()) {
                    
                    echo "<div class='athlete-profile'>";
                    echo "<img src='{$row['image']}' alt='{$row['name']}'>";
                    echo "<h2>{$row['name']}</h2>";

                    echo "<div class='bio'>";
                    echo "<h3>About the Athlete</h3>";
                    echo "<p>{$row['bio']}</p>";
                    echo "</div>";

                    echo "<p>{$row['description']}</p>";

                    echo "<div class='stats'>";
                    echo "<h3>Player Statistics</h3>";
                    echo "<ul>";
                    echo "<li><strong>Wins:</strong> {$row['wins']}</li>";
                    echo "<li><strong>Podium Finishes:</strong> {$row['podium_finishes']}</li>";
                    echo "<li><strong>Years Active:</strong> {$row['years_active']}</li>";
                    echo "<li><strong>Specialty:</strong> {$row['specialty']}</li>";
                    echo "</ul></div>";

                    // Achievements Section
                    echo "<section class='achievements'>";
                    echo "<h3>Achievements</h3>";
                    $achievements = $conn_content->query("SELECT title, description FROM achievements WHERE athlete_id='{$row['id']}'");
                    echo "<div class='achievement-list'>";
                    while ($ach = $achievements->fetch_assoc()) {
                        echo "<div class='achievement'>";
                        echo "<h4>{$ach['title']}</h4>";
                        echo "<p>{$ach['description']}</p>";
                        echo "</div>";
                    }
                    echo "</div></section>";

                    // Gallery Section
                    echo "<section class='gallery'>";
                    echo "<h3>Gallery</h3>";
                    echo "<div class='gallery-container'>";

                    // Retrieve gallery images and descriptions
                    $gallery = $conn_content->query("SELECT image, description FROM athlete_gallery WHERE athlete_id='{$row['id']}'");

                    if ($gallery->num_rows > 0) {
                        while ($img = $gallery->fetch_assoc()) {
                            echo "<div class='gallery-item'>";
                            echo "<img src='{$img['image']}' alt='Athlete Gallery Image'>";
                            echo "<p class='gallery-description'>{$img['description']}</p>"; // Display image description
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No gallery images available.</p>";
                    }

                    echo "</div>";
                    echo "</section>";

                    // Edit Athlete Form
                    echo "<div class='edit-button'>";
                    echo "<button onclick=\"showEditForm('editAthleteForm{$row['id']}')\" style='display: inline-block; padding: 10px; background-color: #C76E00; color: white; border-radius: 5px;'>
                            <i class='fa fa-edit'></i> Edit
                        </button>";
                    echo "<button class='remove' onclick=\"confirmDelete({$row['id']})\" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>
                            <i class='fa fa-trash'></i> Delete
                        </button>"; // Added delete button
                    echo "</div>";
                    echo "<form id='editAthleteForm{$row['id']}' style='display:none;' method='post' action='handle_athletes.php' enctype='multipart/form-data'>";
                    echo "<input type='hidden' name='edit_id' value='{$row['id']}'>";
                    echo "<input type='hidden' name='page' value='$page'>";
                    echo "<input type='text' name='name' value='{$row['name']}' required>";
                    echo "<textarea name='bio' required>{$row['bio']}</textarea>";
                    echo "<textarea name='description' required>{$row['description']}</textarea>";
                    echo "<input type='number' name='wins' value='{$row['wins']}' required>";
                    echo "<input type='number' name='podium_finishes' value='{$row['podium_finishes']}' required>";
                    echo "<input type='number' name='years_active' value='{$row['years_active']}' required>";
                    echo "<input type='text' name='specialty' value='{$row['specialty']}' required>";
                    echo "<input type='hidden' name='existing_image' value='{$row['image']}'>";
                    echo "<input type='file' name='image'>";

                    // Achievements Section
                    echo "<h3>Achievements</h3>";
                    echo "<div id='achievements-container-{$row['id']}' >";
                    $achievements = $conn_content->query("SELECT id, title, description FROM achievements WHERE athlete_id='{$row['id']}'");
                    while ($ach = $achievements->fetch_assoc()) {
                        echo "<div class='achievement' id='achievement-{$ach['id']}'>";
                        echo "<input type='hidden' name='achievement_ids[]' value='{$ach['id']}'>";
                        echo "<input type='text' name='achievements[]' value='{$ach['title']}' required>";
                        echo "<textarea name='achievements_descriptions[]' required>{$ach['description']}</textarea>";
                        echo "<button class='remove' type='button' onclick=\"removeAchievement('achievement-{$ach['id']}')\">
                            <i class='fa fa-trash'></i> Remove
                        </button>";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "<div class='edit-button'>";
                    echo "<button type='button' onclick=\"addAchievement('achievements-container-{$row['id']}')\">
                        <i class='fa fa-plus'></i> Add More Achievements
                    </button>";
                    echo "</div>";

                    // Gallery Section
                    echo "<h3>Gallery</h3>";
                    echo "<div id='gallery-container-{$row['id']}' class='edit-gallery-container'>";
                    echo "<input type='hidden' name='deleted_images' id='deleted_images_{$row['id']}'>";
                    $gallery = $conn_content->query("SELECT id, image, description FROM athlete_gallery WHERE athlete_id='{$row['id']}'");

                    while ($img = $gallery->fetch_assoc()) {
                        echo "<div class='edit-gallery-item' id='gallery-{$img['id']}'>";
                        echo "<input type='hidden' name='gallery_image_ids[]' value='{$img['id']}'>"; 
                        echo "<img src='{$img['image']}' alt='Athlete Gallery Image' width='100'>";
                        echo "<input type='hidden' name='gallery_existing_images[]' value='{$img['image']}'>"; 
                        echo "<input type='file' name='athlete_gallery[]'>"; 
                        echo "<textarea name='gallery_descriptions[]' required>{$img['description']}</textarea>";
                        echo "<button class='remove' type='button' onclick=\"removeGalleryImage('gallery-{$img['id']}', '{$row['id']}')\">
                                <i class='fa fa-trash'></i> Remove
                            </button>";
                        echo "</div>";
                        
                    }
                    echo "</div>";
                    echo "<div class='edit-button'>";
                    echo "<button type='button' onclick=\"addGalleryImage('gallery-container-{$row['id']}')\">
                            <i class='fa fa-plus'></i> Add More Images
                        </button>";
                    echo "</div>";

                    echo "<div class='button-container'>";
                    echo "<button type='submit' style='display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;'>
                            <i class='fa fa-check'></i> Update
                        </button>";
                    echo "<button type='button' onclick=\"hideForm('editAthleteForm{$row['id']}')\" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>
                            <i class='fa fa-times'></i> Cancel
                        </button>";
                    echo "</div>";
                    echo "</form></div>";
                }
                ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="prev">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="next">Next</a>
                    <?php endif; ?>
                </div>
            </section>

            <section>
                <label class="section-heading">Community Leaders:</label>
                <button onclick="toggleForm('addLeaderForm')">
                    <i class="fa fa-user-plus"></i> Add Leader
                </button>

                <form id="addLeaderForm" style="display: none;" method="POST" action="handle_leaders.php" enctype="multipart/form-data">
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="text" name="role" placeholder="Role" required>
                    <input type="file" name="image" required>
                    <button type="submit" style="display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;">
                        <i class="fa fa-plus"></i> Add
                    </button>
                    <button type="button" onclick="hideForm('addLeaderForm')" style="display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </form>

                <table border="1">
                    <thead>
                        <tr>
                            <th>Profile Picture File</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn_content->query("SELECT id, name, role, image FROM community_leaders");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $imageFileName = basename($row["image"]); // Extract file name
                                echo '<tr>';
                                echo '<td style="word-wrap: break-word; white-space: normal; max-width: 200px;">' . htmlspecialchars($imageFileName) . '</td>';
                                echo '<td style="word-wrap: break-word; white-space: normal; max-width: 200px;">' . htmlspecialchars($row["name"]) . '</td>';
                                echo '<td style="word-wrap: break-word; white-space: normal; max-width: 200px;">' . htmlspecialchars($row["role"]) . '</td>';
                                echo '<td>
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <a href="javascript:void(0);" onclick="toggleForm(\'editLeaderForm' . $row['id'] . '\')" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a> |
                                         <form id="remove-form-' . $row['id'] . '" method="POST" action="handle_leaders.php" style="margin: 0; display: inline;">
                                            <input type="hidden" name="id" value="' . $row['id'] . '">
                                            <input type="hidden" name="delete" value="1">
                                            <!-- Remove link with JavaScript for form submission -->
                                            <a href="javascript:void(0);" onclick="document.getElementById(\'remove-form-' . $row['id'] . '\').submit();" title="Remove" >
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </form>
                                    </div>
                                    <form id="editLeaderForm' . $row['id'] . '" style="display: none; margin-top: 10px;" method="POST" action="handle_leaders.php" enctype="multipart/form-data">
                                        <input type="hidden" name="edit_id" value="' . $row['id'] . '">
                                        <input type="text" name="name" value="' . htmlspecialchars($row['name']) . '" required style="width: 90%;">
                                        <input type="text" name="role" value="' . htmlspecialchars($row['role']) . '" required style="width: 90%;">
                                        <input type="file" name="image" style="width: 90%;">
                                        <button type="submit" style="display: inline-block; padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;">
                                            <i class="fa fa-check"></i> Update
                                        </button>
                                        <button type="button" onclick="toggleForm(\'editLeaderForm' . $row['id'] . '\');" style="display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;">
                                            <i class="fa fa-times"></i> Cancel
                                        </button>
                                    </form>
                                </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">No community leaders added yet.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section>
                <label>Partners & Sponsors:</label>
                <button onclick="toggleForm('addPartnerForm')">
                    <i class="fa fa-handshake"></i> Add Partner
                </button>

                <form id="addPartnerForm" style="display: none;" method="POST" action="handle_partnerships.php" enctype="multipart/form-data">
                    <input type="file" name="logo" required>
                    <button type="submit" style="padding: 10px; background-color: #4CAF50; color: white; border-radius: 5px;">
                        <i class="fa fa-plus"></i> Add
                    </button>
                    <button type="button" onclick="hideForm('addPartnerForm')" style="padding: 10px; background-color: #f44336; color: white; border-radius: 5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                </form>

                <table border="1">
                    <thead>
                        <tr>
                            <th>Logo File Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn_content->query("SELECT id, logo FROM partnerships");
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $logoFileName = basename($row["logo"]); // Extract file name
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($logoFileName) . '</td>';
                                echo '<td>
                                        <form method="POST" action="handle_partnerships.php" style="margin: 0;">
                                            <input type="hidden" name="id" value="' . $row['id'] . '">
                                            <button type="submit" name="delete" style="display: none;"></button>
                                            <a href="javascript:void(0);" onclick="this.closest(\'form\').querySelector(\'button[type=submit]\').click();" title="Remove">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </form>
                                    </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="2">No partners or sponsors added yet.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>


            <script>
            let editorInstance;

            ClassicEditor
            .create(document.querySelector('#about_us_editor'))
            .then(editor => {
                // Show the textarea once CKEditor is fully initialized
                editor.ui.view.editable.element.parentElement.style.display = 'block';
            })
            .catch(error => {
                console.error(error);
            });


            // Listen for form submission and sync the editor content
            document.querySelector('form').addEventListener('submit', function (e) {
                try {
                    if (editorInstance) {
                        document.querySelector('#description').value = editorInstance.getData();
                    }
                } catch (error) {
                    console.error("CKEditor content sync failed:", error);
                }
            });

            function showEditForm(id) {
                document.getElementById(id).style.display = 'block';
            }
            function showAddForm(id) {
                document.getElementById(id).style.display = 'block';
            }
            function deleteItem(url) {
                if (confirm('Are you sure you want to delete this item?')) {
                    window.location.href = url;
                }
            }
            function toggleForm(formId) {
                var form = document.getElementById(formId);
                form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
            }

            function showAddForm(formId) {
            document.getElementById(formId).style.display = 'block';
            }

            function hideForm(formId) {
                document.getElementById(formId).style.display = 'none';
            }

            
            function deleteItem(url) {
                if (confirm("Are you sure you want to delete this item?")) {
                    window.location.href = url;
                }
            }

            function showEditForm(formId) {
                document.getElementById(formId).style.display = 'block';
            }

            function hideForm(formId) {
                document.getElementById(formId).style.display = 'none';
            }

                        // Functions for the "Add Athlete" Form
            function addNewAchievement() {
                const container = document.getElementById('achievements-container');
                if (!container) return; // Ensure the container exists
                const newAchievement = document.createElement('div');
                newAchievement.classList.add('achievement');
                newAchievement.innerHTML = `
                    <input type="text" name="achievements[]" placeholder="Achievement Title" required>
                    <textarea name="achievements_descriptions[]" placeholder="Description" required></textarea>
                    <button type="button" onclick="this.parentNode.remove()" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>Remove</button>
                `;
                container.appendChild(newAchievement);
            }

            function addNewGalleryImage() {
                const container = document.getElementById('gallery-container');
                if (!container) return;
                const newGalleryItem = document.createElement('div');
                newGalleryItem.classList.add('new-gallery-item');
                newGalleryItem.innerHTML = `
                    <input type="file" name="athlete_gallery[]" accept="image/*" required>
                    <textarea name="gallery_descriptions[]" placeholder="Enter description for this image." required></textarea>
                    <button type="button" onclick="this.parentNode.remove()" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>Remove</button>
                `;
                container.appendChild(newGalleryItem);
            }


            // Cancel entire form (hides it)
            function hideForm(formId) {
                const form = document.getElementById(formId);
                if (form) {
                    form.style.display = 'none';
                }
            }

            // Functions for the "Edit Athlete" Form
            document.addEventListener("DOMContentLoaded", function () {
                console.log("JavaScript loaded");

                // Remove Achievement
                window.removeAchievement = function (achievementId) {
                    console.log("Removing Achievement:", achievementId);
                    let achievement = document.getElementById(achievementId);
                    if (achievement) {
                        achievement.remove();
                    } else {
                        console.error("Achievement not found:", achievementId);
                    }
                };

                // Remove Gallery Image
                window.removeGalleryImage = function (galleryId, athleteId) {
                    console.log("Removing Gallery Image:", galleryId);
                    let galleryItem = document.getElementById(galleryId);
                    if (galleryItem) {
                        let imageIdInput = galleryItem.querySelector("input[name='gallery_image_ids[]']");
                        if (imageIdInput) {
                            let deletedImagesInput = document.getElementById(`deleted_images_${athleteId}`);
                            if (!deletedImagesInput) {
                                deletedImagesInput = document.createElement("input");
                                deletedImagesInput.type = "hidden";
                                deletedImagesInput.name = "deleted_images";
                                deletedImagesInput.id = `deleted_images_${athleteId}`;
                                document.getElementById(`gallery-container-${athleteId}`).appendChild(deletedImagesInput);
                            }
                            deletedImagesInput.value += deletedImagesInput.value ? `,${imageIdInput.value}` : imageIdInput.value;
                        }
                        galleryItem.remove();
                    } else {
                        console.error("Gallery item not found:", galleryId);
                    }
                };

                // Add New Achievement
                window.addAchievement = function (containerId) {
                    console.log("Adding new Achievement to:", containerId);
                    let container = document.getElementById(containerId);
                    if (!container) {
                        console.error("Achievement container not found:", containerId);
                        return;
                    }
                    let uniqueId = `new-achievement-${Date.now()}`;
                    let newAchievement = document.createElement("div");
                    newAchievement.classList.add("achievement");
                    newAchievement.id = uniqueId;
                    newAchievement.innerHTML = `
                        <input type="hidden" name="achievement_ids[]" value="new">
                        <input type="text" name="achievements[]" placeholder="Title" required>
                        <textarea name="achievements_descriptions[]" placeholder="Description" required></textarea>
                        <button type="button" onclick="removeAchievement('${uniqueId}')" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>Remove</button>
                    `;
                    container.appendChild(newAchievement);
                };

                // Add New Gallery Image
                window.addGalleryImage = function (containerId) {
                    console.log("Adding new Gallery Image to:", containerId);
                    let container = document.getElementById(containerId);
                    if (!container) {
                        console.error("Gallery container not found:", containerId);
                        return;
                    }
                    let uniqueId = `new-gallery-${Date.now()}`;
                    let newImageDiv = document.createElement("div");
                    newImageDiv.classList.add("gallery-item", "edit-gallery-item");
                    newImageDiv.id = uniqueId;
                    newImageDiv.innerHTML = `
                        <input type="hidden" name="gallery_existing_ids[]" value="new">
                        <input type="file" name="athlete_gallery[]" accept="image/*" required>
                        <textarea name="gallery_descriptions[]" placeholder="Image Description" required></textarea>
                        <button type="button" onclick="removeGalleryImage('${uniqueId}', '${containerId}')" style='display: inline-block; padding: 10px; background-color: #f44336; color: white; border-radius: 5px;'>Remove</button>
                    `;
                    container.appendChild(newImageDiv);
                };
            });

            function confirmDelete(athleteId) {
                if (confirm('Are you sure you want to delete this athlete? This action cannot be undone.')) {
                    window.location.href = 'handle_athletes.php?delete_id=' + athleteId;
                }
            }

            function toggleEditForm(id) {
                var form = document.getElementById(id);
                if (form.style.display === "none" || form.style.display === "") {
                    form.style.display = "table-cell"; 
                } else {
                    form.style.display = "none";
                }
            }

            </script>

            <?php
                $conn_content->close();
            ?>
        </main>
    </div>
</body>
</html>