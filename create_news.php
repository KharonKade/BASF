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
    <title>Create News & Announcements</title>
    <link rel="stylesheet" href="Css/admin.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
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

        <main class="content" id="create_news">
            <div class="admin-wrapper">
                <div class="page-header">
                    <h2>Create News & Announcements</h2>
                    <p>Publish the latest updates and announcements.</p>
                </div>

                <form action="store_news.php" method="post" enctype="multipart/form-data" class="main-form">
                    
                    <div class="form-card">
                        <div class="card-header">
                            <h3>News Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="news_title">News Title</label>
                                    <input type="text" name="news_title" id="news_title" placeholder="Enter headline" required>
                                </div>

                                <div class="form-group">
                                    <label for="news_date">Date</label>
                                    <input type="date" name="news_date" id="news_date" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="category">Category</label>
                                <div class="select-wrapper">
                                    <select name="category" id="category" required>
                                        <option value="skateboard">Skateboard</option>
                                        <option value="inline">Inline</option>
                                        <option value="bmx">BMX</option>
                                        <option value="all">All</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Content</label>
                                <textarea name="description" id="description" rows="5"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="card-header">
                            <h3>Featured Images</h3>
                        </div>
                        <div class="card-body">
                            <div class="media-section">
                                <div class="upload-box">
                                    <label for="image" style="cursor: pointer; width: 100%; display: block;">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px;"></i><br>
                                        Click to Upload News Images (Select Multiple)
                                    </label>
                                    <input type="file" name="image[]" id="image" required class="file-input" style="display: none;" multiple>
                                    <p id="file-name" style="margin-top: 10px; color: #64748b; font-size: 0.9rem;"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="history.back();">Cancel</button>
                        <button type="submit" class="btn-primary-large">Publish News</button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
        let editorInstance;

        ClassicEditor
            .create(document.querySelector('#description'))
            .then(editor => {
                editorInstance = editor;
                editor.ui.view.editable.element.parentElement.style.display = 'block';
            })
            .catch(error => {
                console.error(error);
            });

        document.querySelector('form').addEventListener('submit', function (e) {
            try {
                if (editorInstance) {
                    document.querySelector('#description').value = editorInstance.getData();
                    
                    if (editorInstance.getData().trim() === "") {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Content',
                            text: 'Please enter a description for the news.'
                        });
                    }
                }
            } catch (error) {
                console.error("CKEditor content sync failed:", error);
            }
        });

        document.getElementById('image').addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                const fileNames = Array.from(files).map(file => file.name).join(', ');
                document.getElementById('file-name').textContent = fileNames;
            } else {
                document.getElementById('file-name').textContent = "No file chosen";
            }
        });
    </script>
</body>
</html>