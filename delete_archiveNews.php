<?php
$conn = new mysqli("localhost", "root", "", "basf_news");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $news_id = intval($_GET['id']); 

    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $delete_sql = "DELETE FROM news_announcements WHERE news_id = $news_id";

        if ($conn->query($delete_sql)) {
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
                            text: 'News item permanently deleted.',
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
            die("Error deleting news: " . $conn->error);
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
                        title: 'Permanently Delete?',
                        text: \"You won't be able to revert this!\",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'delete_archiveNews.php?id=$news_id&confirm=yes';
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