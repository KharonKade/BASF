<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Not logged in as admin, redirect to admin login page
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Css/admin.css?v=1.0">
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
            <h2>Create News & Announcements</h2>
            <div class="form-container">
            <form action="store_news.php" method="post" enctype="multipart/form-data">
                <label for="news_title">News Title:</label>
                <input type="text" name="news_title" id="news_title" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="5"></textarea>

                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="skateboard">Skateboard</option>
                    <option value="inline">Inline</option>
                    <option value="bmx">BMX</option>
                    <option value="all">All</option>
                </select>

                <label for="news_date">News Date:</label>
                <input type="date" name="news_date" id="news_date" required>

                <label for="image">News Image:</label>
                <input type="file" name="image[]" id="image" required>

                <button type="submit">Create News</button>
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
                    // Sync CKEditor data to the hidden textarea
                    document.querySelector('#description').value = editorInstance.getData();
                    
                    // Validate the content before submission
                    if (editorInstance.getData().trim() === "") {
                        // If the editor has no content, show an alert or prevent form submission
                        e.preventDefault(); // Prevent form submission
                        alert("Please enter a description for the news.");
                    }
                }
            } catch (error) {
                console.error("CKEditor content sync failed:", error);
            }
        });
    </script>
</body>
</html>
