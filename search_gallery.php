<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "basf_gallery";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$input = "";
if(isset($_POST['input'])){
    $input = $_POST['input'];
}

$sql = "SELECT * FROM gallery WHERE title LIKE ? OR description LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $input . "%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td><img src="<?php echo 'images/uploads/' . basename($row['thumbnail']); ?>" width="100"></td>
            <td>
                <div class="text-limit" title="<?php echo htmlspecialchars($row['title']); ?>">
                    <?php echo $row['title']; ?>
                </div>
            </td>
            <td>
                <div class="text-limit" title="<?php echo htmlspecialchars($row['description']); ?>">
                    <?php echo $row['description']; ?> 
                </div>
            </td>
            <td>
                <a href="view_gallery.php?id=<?php echo $row['id']; ?>" title="View">
                    <i class="fas fa-eye"></i>
                </a> |
                <a href="edit_gallery.php?id=<?php echo $row['id']; ?>" title="Edit">
                    <i class="fas fa-edit"></i>
                </a> |
                <a href="delete_gallery.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" title="Delete">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center;'>No matching records found.</td></tr>";
}

$stmt->close();
$conn->close();
?>