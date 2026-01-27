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
            <h1>Admin Dashboard</h1>
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

        <div class="section-card">
            <h2>
                <span>About Us</span>
                <button onclick="showEditForm('aboutUsForm')" class="btn-primary">
                    <i class="fa fa-edit"></i> Edit Content
                </button>
            </h2>
            
            <?php
            $result = $conn_content->query("SELECT content FROM content WHERE section='about_us'");
            $aboutUsContent = "";
            if ($row = $result->fetch_assoc()) {
                $aboutUsContent = $row['content'];
                echo '<div class="wrapped-text">' . $row['content'] . '</div>';
            } else {
                echo "<p class='no-data'>About Us content not found.</p>";
            }
            ?>
            
            <form id="aboutUsForm" class="form-container" style="display:none;" method="post" action="handle_aboutus.php">
                <textarea name="about_us" id="about_us_editor"><?php echo htmlspecialchars($aboutUsContent); ?></textarea>
                <div class="action-buttons">
                    <button type="submit" class="btn-success"><i class="fa fa-check"></i> Update</button>
                    <button type="button" onclick="hideForm('aboutUsForm')" class="btn-danger"><i class="fa fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>

        <div class="section-card">
            <h2>
                <span>Highlight Carousel</span>
                <button onclick="showAddForm('addHighlightForm')" class="btn-primary">
                    <i class="fa fa-plus"></i> Add Highlight
                </button>
            </h2>

            <form id="addHighlightForm" class="form-container" style="display:none;" method="post" action="handle_highlight.php" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Video File</label>
                        <input type="file" name="video" required>
                    </div>
                    <div>
                        <label>Title</label>
                        <input type="text" name="title" placeholder="Enter title" required>
                    </div>
                </div>
                <label>Description</label>
                <textarea name="description" placeholder="Enter description" required></textarea>
                <div class="action-buttons">
                    <button type="submit" class="btn-success"><i class="fa fa-check"></i> Add</button>
                    <button type="button" onclick="hideForm('addHighlightForm')" class="btn-danger"><i class="fa fa-times"></i> Cancel</button>
                </div>
            </form>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $result = $conn_content->query("SELECT id, video, title, description FROM highlight_carousel");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr id='row{$row['id']}'>";
                        echo "<td>" . basename($row["video"]) . "</td>";
                        echo "<td><strong>{$row['title']}</strong></td>";
                        echo "<td>{$row['description']}</td>";
                        echo "<td style='text-align: right;'>
                                <button onclick=\"toggleEditForm('editRow{$row['id']}')\" class='btn-secondary btn-icon' title='Edit'><i class='fa fa-edit'></i></button>
                                <a href='handle_highlight.php?delete_id={$row['id']}' class='btn-danger btn-icon' style='text-decoration:none; display:inline-block;' onclick='return confirm(\"Are you sure?\")'><i class='fa fa-trash'></i></a>
                            </td>";
                        
                        echo "<tr id='editRow{$row['id']}' style='display:none;'>";
                        echo "<td colspan='4' style='padding:0;'>
                                <div class='form-container' style='margin: 10px;'>
                                    <form method='post' action='handle_highlight.php' enctype='multipart/form-data'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <label>Edit Video File</label>
                                        <input type='file' name='video'>
                                        <label>Title</label>
                                        <input type='text' name='title' value='{$row['title']}' required>
                                        <label>Description</label>
                                        <textarea name='description' required>{$row['description']}</textarea>
                                        <div class='action-buttons'>
                                            <button type='submit' class='btn-success'>Update</button>
                                            <button type='button' onclick=\"toggleEditForm('editRow{$row['id']}')\" class='btn-danger'>Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </td>";
                        echo "</tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>

        <div class="section-card">
            <h2>
                <span>Top Athletes</span>
                <button onclick="showAddForm('addAthleteForm')" class="btn-primary">
                    <i class="fa fa-plus"></i> Add New Athlete
                </button>
            </h2>
            
            <form id="addAthleteForm" class="form-container" style="display:none;" method="post" action="handle_athletes.php" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Name</label>
                        <input type="text" name="name" required>
                        <label>Profile Image</label>
                        <input type="file" name="image" required>
                        <label>Specialty</label>
                        <input type="text" name="specialty" required>
                    </div>
                    <div>
                        <label>Wins</label>
                        <input type="number" name="wins" required>
                        <label>Podium Finishes</label>
                        <input type="number" name="podium_finishes" required>
                        <label>Years Active</label>
                        <input type="number" name="years_active" required>
                    </div>
                </div>
                
                <label>Bio</label>
                <textarea name="bio" rows="4" required></textarea>
                
                <label>Short Description</label>
                <textarea name="description" required></textarea>

                <h3>Achievements</h3>
                <div id="achievements-container">
                    <div class="achievement-item">
                        <input type="text" name="achievements[]" placeholder="Title" required>
                        <textarea name="achievements_descriptions[]" placeholder="Description" required></textarea>
                    </div>
                </div>
                <button type="button" onclick="addNewAchievement()" class="btn-secondary" style="margin-bottom: 20px;">
                    <i class="fa fa-trophy"></i> Add Another Achievement
                </button>

                <h3>Gallery</h3>
                <div id="gallery-container">
                    <div class="new-gallery-item">
                        <input type="file" name="athlete_gallery[]" required>
                        <textarea name="gallery_descriptions[]" placeholder="Image description" required></textarea>
                    </div>
                </div>
                <button type="button" onclick="addNewGalleryImage()" class="btn-secondary" style="margin-bottom: 20px;">
                    <i class="fa fa-image"></i> Add Another Image
                </button>
                
                <div class="action-buttons">
                    <button type="submit" class="btn-success">Save Athlete</button>
                    <button type="button" onclick="hideForm('addAthleteForm')" class="btn-danger">Cancel</button>
                </div>
            </form>

            <?php
            $items_per_page = 1; 
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $items_per_page;
            
            $total_result = $conn_content->query("SELECT COUNT(*) as total FROM top_athletes");
            $total_row = $total_result->fetch_assoc();
            $total_athletes = $total_row['total'];
            $total_pages = ceil($total_athletes / $items_per_page);
            
            $result = $conn_content->query("SELECT * FROM top_athletes LIMIT $items_per_page OFFSET $offset");
            while ($row = $result->fetch_assoc()) {
                echo "<div class='athlete-profile'>";
                    echo "<div class='athlete-sidebar'>";
                        echo "<img src='{$row['image']}' alt='{$row['name']}'>";
                        echo "<h3>{$row['name']}</h3>";
                        echo "<p style='color: var(--gray); margin-bottom: 20px;'>{$row['specialty']}</p>";
                        echo "<div style='display: flex; gap: 10px; justify-content: center;'>";
                            echo "<button onclick=\"showEditForm('editAthleteForm{$row['id']}')\" class='btn-primary btn-icon'><i class='fa fa-edit'></i></button>";
                            echo "<button onclick=\"confirmDelete({$row['id']})\" class='btn-danger btn-icon'><i class='fa fa-trash'></i></button>";
                        echo "</div>";
                    echo "</div>";

                    echo "<div class='athlete-details'>";
                        echo "<h4>About</h4>";
                        echo "<p>{$row['bio']}</p>";
                        echo "<p style='margin-top: 10px; font-style: italic; color: var(--gray);'>{$row['description']}</p>";

                        echo "<div class='stat-grid'>";
                            echo "<div class='stat-box'><strong>Wins</strong><span>{$row['wins']}</span></div>";
                            echo "<div class='stat-box'><strong>Podiums</strong><span>{$row['podium_finishes']}</span></div>";
                            echo "<div class='stat-box'><strong>Years</strong><span>{$row['years_active']}</span></div>";
                            echo "<div class='stat-box'><strong>Specialty</strong><span>{$row['specialty']}</span></div>";
                        echo "</div>";

                        echo "<h4>Achievements</h4>";
                        $achievements = $conn_content->query("SELECT title, description FROM achievements WHERE athlete_id='{$row['id']}'");
                        echo "<ul style='margin-left: 20px; margin-bottom: 20px;'>";
                        while ($ach = $achievements->fetch_assoc()) {
                            echo "<li><strong>{$ach['title']}</strong>: {$ach['description']}</li>";
                        }
                        echo "</ul>";

                        echo "<h4>Gallery</h4>";
                        echo "<div class='gallery-grid'>";
                        $gallery = $conn_content->query("SELECT image, description FROM athlete_gallery WHERE athlete_id='{$row['id']}'");
                        while ($img = $gallery->fetch_assoc()) {
                            echo "<img src='{$img['image']}' class='gallery-thumb' title='{$img['description']}'>";
                        }
                        echo "</div>";
                    echo "</div>"; 
                echo "</div>";

                echo "<form id='editAthleteForm{$row['id']}' class='form-container' style='display:none;' method='post' action='handle_athletes.php' enctype='multipart/form-data'>";
                echo "<input type='hidden' name='edit_id' value='{$row['id']}'>";
                echo "<input type='hidden' name='page' value='$page'>";
                echo "<h3>Edit {$row['name']}</h3>";
                echo "<label>Name</label><input type='text' name='name' value='{$row['name']}' required>";
                echo "<label>Bio</label><textarea name='bio' required>{$row['bio']}</textarea>";
                echo "<label>Description</label><textarea name='description' required>{$row['description']}</textarea>";
                
                echo "<div class='stat-grid'>";
                    echo "<div><label>Wins</label><input type='number' name='wins' value='{$row['wins']}' required></div>";
                    echo "<div><label>Podiums</label><input type='number' name='podium_finishes' value='{$row['podium_finishes']}' required></div>";
                    echo "<div><label>Years</label><input type='number' name='years_active' value='{$row['years_active']}' required></div>";
                    echo "<div><label>Specialty</label><input type='text' name='specialty' value='{$row['specialty']}' required></div>";
                echo "</div>";

                echo "<label>Update Profile Image</label><input type='hidden' name='existing_image' value='{$row['image']}'><input type='file' name='image'>";

                echo "<h4>Manage Achievements</h4>";
                echo "<div id='achievements-container-{$row['id']}'>";
                $achievements = $conn_content->query("SELECT id, title, description FROM achievements WHERE athlete_id='{$row['id']}'");
                while ($ach = $achievements->fetch_assoc()) {
                    echo "<div class='achievement-item' id='achievement-{$ach['id']}' style='border:1px solid #ddd; padding:10px; margin-bottom:10px;'>";
                    echo "<input type='hidden' name='achievement_ids[]' value='{$ach['id']}'>";
                    echo "<input type='text' name='achievements[]' value='{$ach['title']}' required>";
                    echo "<textarea name='achievements_descriptions[]' required>{$ach['description']}</textarea>";
                    echo "<button type='button' class='btn-danger btn-icon' onclick=\"removeAchievement('achievement-{$ach['id']}')\"><i class='fa fa-trash'></i></button>";
                    echo "</div>";
                }
                echo "</div>";
                echo "<button type='button' class='btn-secondary' onclick=\"addAchievement('achievements-container-{$row['id']}')\"><i class='fa fa-plus'></i> Add Achievement</button>";
                
                echo "<h4 style='margin-top:20px;'>Manage Gallery</h4>";
                echo "<div id='gallery-container-{$row['id']}' class='grid-cards'>";
                echo "<input type='hidden' name='deleted_images' id='deleted_images_{$row['id']}'>";
                $gallery = $conn_content->query("SELECT id, image, description FROM athlete_gallery WHERE athlete_id='{$row['id']}'");
                while ($img = $gallery->fetch_assoc()) {
                    echo "<div class='card-item' id='gallery-{$img['id']}'>";
                    echo "<input type='hidden' name='gallery_image_ids[]' value='{$img['id']}'>";
                    echo "<img src='{$img['image']}' style='width:100%; height:100px; object-fit:cover;'>";
                    echo "<input type='hidden' name='gallery_existing_images[]' value='{$img['image']}'>";
                    echo "<input type='file' name='athlete_gallery[]' style='margin-top:10px;'>";
                    echo "<textarea name='gallery_descriptions[]' required style='margin-top:5px;'>{$img['description']}</textarea>";
                    echo "<button type='button' class='btn-danger' style='margin-top:10px;' onclick=\"removeGalleryImage('gallery-{$img['id']}', '{$row['id']}')\">Remove</button>";
                    echo "</div>";
                }
                echo "</div>";
                echo "<button type='button' class='btn-secondary' style='margin-top:15px;' onclick=\"addGalleryImage('gallery-container-{$row['id']}')\"><i class='fa fa-plus'></i> Add Gallery Image</button>";

                echo "<div class='action-buttons' style='margin-top: 30px;'>";
                echo "<button type='submit' class='btn-success'>Update Athlete</button>";
                echo "<button type='button' class='btn-danger' onclick=\"hideForm('editAthleteForm{$row['id']}')\">Cancel</button>";
                echo "</div>";
                echo "</form>";
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
        </div>

        <div class="section-card">
            <h2>
                <span>Community Leaders</span>
                <button onclick="toggleForm('addLeaderForm')" class="btn-primary">
                    <i class="fa fa-user-plus"></i> Add Leader
                </button>
            </h2>

            <form id="addLeaderForm" class="form-container" style="display: none;" method="POST" action="handle_leaders.php" enctype="multipart/form-data">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="text" name="role" placeholder="Role" required>
                </div>
                <input type="file" name="image" required>
                <div class="action-buttons">
                    <button type="submit" class="btn-success">Add</button>
                    <button type="button" onclick="hideForm('addLeaderForm')" class="btn-danger">Cancel</button>
                </div>
            </form>

            <div class="grid-cards">
                <?php
                $result = $conn_content->query("SELECT id, name, role, image FROM community_leaders");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="card-item">';
                        echo '<img src="' . htmlspecialchars($row["image"]) . '" class="card-img" alt="Leader">';
                        echo '<h3>' . htmlspecialchars($row["name"]) . '</h3>';
                        echo '<p style="color: var(--gray);">' . htmlspecialchars($row["role"]) . '</p>';
                        echo '<div style="margin-top:15px; display:flex; gap:10px; justify-content:center;">';
                        echo '<button onclick="toggleForm(\'editLeaderForm' . $row['id'] . '\')" class="btn-secondary btn-icon"><i class="fa fa-edit"></i></button>';
                        echo '<form method="POST" action="handle_leaders.php" style="display:inline;">
                                <input type="hidden" name="id" value="' . $row['id'] . '">
                                <input type="hidden" name="delete" value="1">
                                <button type="submit" class="btn-danger btn-icon" onclick="return confirm(\'Delete?\')"><i class="fa fa-trash"></i></button>
                            </form>';
                        echo '</div>';
                        
                        echo '<form id="editLeaderForm' . $row['id'] . '" class="form-container" style="display: none; margin-top: 15px; text-align:left;" method="POST" action="handle_leaders.php" enctype="multipart/form-data">
                                <input type="hidden" name="edit_id" value="' . $row['id'] . '">
                                <label>Name</label><input type="text" name="name" value="' . htmlspecialchars($row['name']) . '" required>
                                <label>Role</label><input type="text" name="role" value="' . htmlspecialchars($row['role']) . '" required>
                                <label>Image</label><input type="file" name="image">
                                <button type="submit" class="btn-success" style="width:100%">Update</button>
                            </form>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No community leaders added yet.</p>';
                }
                ?>
            </div>
        </div>

        <div class="section-card">
            <h2>
                <span>Partners & Sponsors</span>
                <button onclick="toggleForm('addPartnerForm')" class="btn-primary">
                    <i class="fa fa-handshake"></i> Add Partner
                </button>
            </h2>

            <form id="addPartnerForm" class="form-container" style="display: none;" method="POST" action="handle_partnerships.php" enctype="multipart/form-data">
                <label>Logo File</label>
                <input type="file" name="logo" required>
                <div class="action-buttons">
                    <button type="submit" class="btn-success">Add</button>
                    <button type="button" onclick="hideForm('addPartnerForm')" class="btn-danger">Cancel</button>
                </div>
            </form>

            <div class="grid-cards">
                <?php
                $result = $conn_content->query("SELECT id, logo FROM partnerships");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="card-item">';
                        echo '<img src="' . htmlspecialchars($row["logo"]) . '" class="logo-img" alt="Partner Logo">';
                        echo '<form method="POST" action="handle_partnerships.php">
                                <input type="hidden" name="id" value="' . $row['id'] . '">
                                <button type="submit" name="delete" class="btn-danger" style="width:100%">Remove</button>
                            </form>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No partners added yet.</p>';
                }
                ?>
            </div>
        </div>

        <script>
        let editorInstance;

        ClassicEditor
        .create(document.querySelector('#about_us_editor'))
        .then(editor => {
            editor.ui.view.editable.element.parentElement.style.display = 'block';
        })
        .catch(error => {
            console.error(error);
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            try {
                if (editorInstance) {
                    document.querySelector('#description').value = editorInstance.getData();
                }
            } catch (error) {
                console.error("CKEditor content sync failed:", error);
            }
        });

        function showEditForm(id) { document.getElementById(id).style.display = 'block'; }
        function showAddForm(id) { document.getElementById(id).style.display = 'block'; }
        function hideForm(formId) { document.getElementById(formId).style.display = 'none'; }
        
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
        }

        function toggleEditForm(id) {
            var form = document.getElementById(id);
            form.style.display = (form.style.display === "none" || form.style.display === "") ? "table-row" : "none";
        }

        function confirmDelete(athleteId) {
            if (confirm('Are you sure you want to delete this athlete? This action cannot be undone.')) {
                window.location.href = 'handle_athletes.php?delete_id=' + athleteId;
            }
        }

        function addNewAchievement() {
            const container = document.getElementById('achievements-container');
            if (!container) return;
            const newAchievement = document.createElement('div');
            newAchievement.classList.add('achievement-item');
            newAchievement.style.marginTop = '10px';
            newAchievement.innerHTML = `
                <input type="text" name="achievements[]" placeholder="Achievement Title" required>
                <textarea name="achievements_descriptions[]" placeholder="Description" required></textarea>
                <button type="button" onclick="this.parentNode.remove()" class="btn-danger btn-icon"><i class="fa fa-trash"></i></button>
            `;
            container.appendChild(newAchievement);
        }

        function addNewGalleryImage() {
            const container = document.getElementById('gallery-container');
            if (!container) return;
            const newGalleryItem = document.createElement('div');
            newGalleryItem.classList.add('new-gallery-item');
            newGalleryItem.style.marginTop = '10px';
            newGalleryItem.innerHTML = `
                <input type="file" name="athlete_gallery[]" accept="image/*" required>
                <textarea name="gallery_descriptions[]" placeholder="Enter description" required></textarea>
                <button type="button" onclick="this.parentNode.remove()" class="btn-danger btn-icon"><i class="fa fa-trash"></i></button>
            `;
            container.appendChild(newGalleryItem);
        }

        window.removeAchievement = function (achievementId) {
            let achievement = document.getElementById(achievementId);
            if (achievement) achievement.remove();
        };

        window.removeGalleryImage = function (galleryId, athleteId) {
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
            }
        };

        window.addAchievement = function (containerId) {
            let container = document.getElementById(containerId);
            if (!container) return;
            let uniqueId = `new-achievement-${Date.now()}`;
            let newAchievement = document.createElement("div");
            newAchievement.classList.add("achievement-item");
            newAchievement.id = uniqueId;
            newAchievement.style.border = "1px solid #ddd";
            newAchievement.style.padding = "10px";
            newAchievement.style.marginBottom = "10px";
            newAchievement.innerHTML = `
                <input type="hidden" name="achievement_ids[]" value="new">
                <input type="text" name="achievements[]" placeholder="Title" required>
                <textarea name="achievements_descriptions[]" placeholder="Description" required></textarea>
                <button type="button" onclick="removeAchievement('${uniqueId}')" class="btn-danger btn-icon"><i class="fa fa-trash"></i></button>
            `;
            container.appendChild(newAchievement);
        };

        window.addGalleryImage = function (containerId) {
            let container = document.getElementById(containerId);
            if (!container) return;
            let uniqueId = `new-gallery-${Date.now()}`;
            let newImageDiv = document.createElement("div");
            newImageDiv.classList.add("card-item");
            newImageDiv.id = uniqueId;
            newImageDiv.innerHTML = `
                <input type="hidden" name="gallery_existing_ids[]" value="new">
                <input type="file" name="athlete_gallery[]" accept="image/*" required>
                <textarea name="gallery_descriptions[]" placeholder="Image Description" required></textarea>
                <button type="button" onclick="removeGalleryImage('${uniqueId}', '${containerId}')" class="btn-danger" style="margin-top:10px;">Remove</button>
            `;
            container.appendChild(newImageDiv);
        };
        </script>

        <?php $conn_content->close(); ?>
    </main>
    </div>
</body>
</html>