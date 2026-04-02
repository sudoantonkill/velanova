<?php
session_start();
include('connection.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user ID
    $user_id = $_SESSION['user_id'];

    // Validate inputs
    $item_name = htmlspecialchars($_POST['item_name']);
    $price = htmlspecialchars($_POST['price']);

    // Check if the file was uploaded without errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadsDir = 'uploads/'; // Directory where images will be stored
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadsDir . $fileName;

        // Check if the file is a valid image
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $validExtensions = array('jpg', 'png', 'jpeg', 'gif'); // Allowed file extensions

        if (in_array($imageFileType, $validExtensions)) {
            // Move the file to the uploads directory
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                // Insert the wishlist item into the database
                $stmt = $conn->prepare("INSERT INTO wishlist (user_id, item_name, price, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isds", $user_id, $item_name, $price, $fileName);
                
                if ($stmt->execute()) {
                    echo "The item has been added to your wishlist.";
                } else {
                    echo "Error adding item to wishlist: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Invalid file type. Please upload a JPG, PNG, or GIF image.";
        }
    } else {
        echo "Error: " . $_FILES['image']['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Wishlist Item</title>
</head>
<body>
    <h1>Add Wishlist Item</h1>
    <form action="add_wishlist_item.php" method="post" enctype="multipart/form-data">
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" id="item_name" required>
        <br><br>
        
        <label for="price">Price:</label>
        <input type="text" name="price" id="price" required>
        <br><br>
        
        <label for="image">Select Image:</label>
        <input type="file" name="image" id="image" required>
        <br><br>
        
        <button type="submit">Add to Wishlist</button>
    </form>
</body>
</html>
