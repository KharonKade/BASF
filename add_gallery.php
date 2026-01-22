<?php
// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $thumbnail = "images/uploads/" . basename($_FILES["thumbnail"]["name"]);
    
    move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $thumbnail);

    $sql = "INSERT INTO gallery (title, description, thumbnail) VALUES ('$title', '$description', '$thumbnail')";
    
    if ($conn->query($sql) === TRUE) {
        $gallery_id = $conn->insert_id;

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $image_path = "images/uploads/" . basename($_FILES['images']['name'][$key]);
            move_uploaded_file($tmp_name, $image_path);
            $conn->query("INSERT INTO gallery_images (gallery_id, image_path) VALUES ('$gallery_id', '$image_path')");
        }

        echo "<script>alert('Gallery item added successfully!'); window.location='admin_gallery.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/admin_gallery.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <title>Add Gallery Item</title>
</head>
<body>

<div class="admin-container">
    <nav class="sidebar">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_upcoming.php"><i class="fas fa-calendar-check"></i> Events</a></li>
            <li><a href="manage_news.php"><i class="fas fa-edit"></i> News & Announcements</a></li>
            <li><a href="admin_gallery.php"><i class="fas fa-images"></i> Gallery Page</a></li>
            <li><a href="editInlinePage.php"><i class="fas fa-skating"></i> Inline Page</a></li>
            <li><a href="editBmxPage.php"><i class="fas fa-bicycle"></i> BMX Page</a></li>
            <li><a href="editSkateboardPage.php"><i class="fas fa-snowboarding"></i> Skateboard Page</a></li>
            <li><a href="view_inquiries.php"><i class="fas fa-question-circle"></i> Inquiries</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="content">
        <h1>Add New Gallery Item</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" required><br>
            
            <label>Description:</label>
            <textarea id="description" name="description"></textarea><br>
            
            <label>Thumbnail Image:</label>
            <input type="file" name="thumbnail" required><br>
            
            <label>Additional Images:</label>
            <input type="file" name="images[]" multiple required><br>
            
            <button type="submit">Add Gallery Item</button>
            <button type="button" class="button" onclick="history.back();">Cancel</button>
        </form>
    </div>
</div>

<script>
    let editorInstance;

    ClassicEditor
    .create(document.querySelector('#description'))
    .then(editor => {
        editor.ui.view.editable.element.parentElement.style.display = 'block';
        editorInstance = editor;
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
</script>
</body>
</html>