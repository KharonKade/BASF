<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$news_title = $_POST['news_title'] ?? '';
$news_content = $_POST['description'] ?? '';
$category = $_POST['category'] ?? 'General';
$publish_date = $_POST['news_date'] ?? '';

if (empty($news_title) || empty($news_content) || empty($publish_date)) {
    die("Error: Please fill all the required fields.");
}

$news_title = $conn->real_escape_string($news_title);
$news_content = $conn->real_escape_string($news_content);
$category = $conn->real_escape_string($category);
$publish_date = $conn->real_escape_string($publish_date);

$sql = "INSERT INTO news_announcements (news_title, news_content, category, publish_date) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $news_title, $news_content, $category, $publish_date);

if ($stmt->execute()) {
    $news_id = $stmt->insert_id;

    if (!empty($_FILES['image']['tmp_name'])) {
        $upload_dir = "images/uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (is_array($_FILES['image']['tmp_name'])) {
            foreach ($_FILES['image']['tmp_name'] as $index => $tmp_name) {
                $image_name = basename($_FILES['image']['name'][$index]);
                $image_path = $upload_dir . $image_name;

                if (move_uploaded_file($tmp_name, $image_path)) {
                    $image_sql = "INSERT INTO news_images (news_id, image_path) VALUES ('$news_id', '$image_path')";
                    $conn->query($image_sql);
                }
            }
        } else {
            $image_name = basename($_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $image_sql = "INSERT INTO news_images (news_id, image_path) VALUES ('$news_id', '$image_path')";
                $conn->query($image_sql);
            }
        }
    }

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
        </style>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Success!',
                    text: 'News and Announcement created successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'manage_news.php';
                    }
                });
            });
        </script>
    </body>
    </html>";

} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>