<?php
$conn = new mysqli("localhost", "root", "", "basf_news");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $news_id = intval($_GET['id']);

    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $restore_sql = "UPDATE news_announcements SET status = 'active' WHERE news_id = $news_id";

        if ($conn->query($restore_sql)) {
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
                            title: 'Restored!',
                            text: 'News item restored successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location = 'archived_news.php';
                            }
                        });
                    });
                </script>
            </body>
            </html>";
            exit();
        } else {
            die("Error restoring news: " . $conn->error);
        }
    } else {
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
                        title: 'Restore News?',
                        text: \"This will move the news back to the active list.\",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, restore it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'restore_news.php?id=$news_id&confirm=yes';
                        } else {
                            window.location.href = 'archived_news.php';
                        }
                    });
                });
            </script>
        </body>
        </html>";
        exit();
    }
} else {
    die("No news ID provided.");
}

$conn->close();
?>