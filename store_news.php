<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "basf_news");

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$news_title = $_POST['news_title'] ?? '';
$news_content = $_POST['description'] ?? '';
$category = $_POST['category'] ?? 'General';
$publish_date = $_POST['news_date'] ?? '';

// Validate form inputs
if (empty($news_title) || empty($news_content) || empty($publish_date)) {
    die("Error: Please fill all the required fields.");
}

// Escape special characters in the inputs
$news_title = $conn->real_escape_string($news_title);
$news_content = $conn->real_escape_string($news_content);
$category = $conn->real_escape_string($category);
$publish_date = $conn->real_escape_string($publish_date);

// Insert main news data using prepared statements
$sql = "INSERT INTO news_announcements (news_title, news_content, category, publish_date) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $news_title, $news_content, $category, $publish_date);

if ($stmt->execute()) {
    $news_id = $stmt->insert_id;

    // Check if an image was uploaded
    if (!empty($_FILES['image']['tmp_name'])) {
        // Define the directory for image uploads
        $upload_dir = "images/uploads/";

        // Ensure the directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Handle multiple image uploads if any
        if (is_array($_FILES['image']['tmp_name'])) {
            foreach ($_FILES['image']['tmp_name'] as $index => $tmp_name) {
                // Sanitize file name and define the image path
                $image_name = basename($_FILES['image']['name'][$index]);
                $image_path = $upload_dir . $image_name;

                // Move the uploaded file to the desired location
                if (move_uploaded_file($tmp_name, $image_path)) {
                    echo "File uploaded: " . $image_path;

                    // Insert the image into the database
                    $image_sql = "INSERT INTO news_images (news_id, image_path) VALUES ('$news_id', '$image_path')";
                    $conn->query($image_sql);
                } else {
                    echo "Failed to upload: " . $_FILES['image']['name'][$index];
                }
            }
        } else {
            // For single image uploads
            $image_name = basename($_FILES['image']['name']); // Correct reference for single file
            $image_path = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                echo "File uploaded: " . $image_path;

                // Insert the image into the database
                $image_sql = "INSERT INTO news_images (news_id, image_path) VALUES ('$news_id', '$image_path')";
                $conn->query($image_sql);
            } else {
                echo "Failed to upload: " . $_FILES['image']['name'];
            }
        }
    }

    // Display success message in JavaScript
    echo "<script type='text/javascript'>
            alert('News and Announcement created successfully!');
            window.location.href = 'manage_news.php'; // Redirect to admin dashboard after creation
          </script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
