<?php
$conn = new mysqli("localhost", "u142318015_usr_vf0t87O1", "W1xz8gB^", "u142318015_db_vf0t87O1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $news_id = intval($_GET['id']);

    $image_sql = "SELECT image_path FROM news_images WHERE news_id = $news_id";
    $image_result = $conn->query($image_sql);
    if ($image_result->num_rows > 0) {
        while ($image = $image_result->fetch_assoc()) {
            $image_path = $image['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path); 
            }
        }
    }

    $delete_sql = "DELETE FROM news_announcements WHERE news_id = $news_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
            <style>body { font-family: 'Poppins', sans-serif; }</style>
        </head>
        <body>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'News item deleted successfully.',
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
        exit();
    } else {
        die("Error deleting news: " . $conn->error);
    }
} else {
    header("Location: manage_news.php");
    exit();
}

$conn->close();
?>