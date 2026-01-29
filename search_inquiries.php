<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    exit('Unauthorized');
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contact_us";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchText = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';
$filterConcern = isset($_POST['filter']) ? $conn->real_escape_string($_POST['filter']) : '';

$sql = "SELECT * FROM contact_inquiries WHERE archived = 0";

if (!empty($filterConcern)) {
    $sql .= " AND concerns = '$filterConcern'";
}

if (!empty($searchText)) {
    $sql .= " AND (full_name LIKE '%$searchText%' OR email LIKE '%$searchText%' OR contact_number LIKE '%$searchText%' OR message LIKE '%$searchText%')";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);
$counter = 1;

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $shortMessage = strlen($row["message"]) > 25 ? substr($row["message"], 0, 25) . '...' : $row["message"];
        
        echo "<tr>
                <td>" . $counter . "</td>
                <td>" . htmlspecialchars($row["full_name"]) . "</td>
                <td>" . htmlspecialchars($row["email"]) . "</td>
                <td>" . htmlspecialchars($row["contact_number"]) . "</td>
                <td>" . htmlspecialchars($row["concerns"]) . "</td>
                <td>" . htmlspecialchars($shortMessage) . "</td>
                <td>" . htmlspecialchars($row["submitted_at"]) . "</td>
                <td>
                    <a href='view_message.php?id=" . $row["id"] . "' title='View'><i class='fas fa-eye'></i></a> |
                    <a href='archive_inquiry.php?id=" . $row["id"] . "' onclick='return confirm(\"Are you sure you want to archive this inquiry?\");' title='Archive'><i class='fas fa-box-archive'></i></a> |
                    <a href='delete_inquiry.php?id=" . $row["id"] . "' onclick='return confirm(\"Are you sure you want to delete this inquiry?\");' title='Delete'><i class='fas fa-trash'></i></a>
                </td>
              </tr>";
        $counter++;
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center;'>No inquiries found matching your search.</td></tr>";
}

$conn->close();
?>