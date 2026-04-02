<?php
session_start();
include('connection.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch user details from the users table
$userQuery = "SELECT profile_pic, username FROM users WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id); // Use $user_id to fetch the user's profile picture
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

// Correctly form the profile picture URL
$profilePic = !empty($userData['profile_pic']) ? "http://localhost:8080/login/" . htmlspecialchars($userData['profile_pic']) : "http://localhost:8080/login/uploads/default-profile.png";
$username = htmlspecialchars($userData['username']);

// Fetch wishlist items from the wishlist table
$wishlistQuery = "SELECT item_name, price, image FROM wishlist WHERE user_id = ?";
$stmt = $conn->prepare($wishlistQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlistResult = $stmt->get_result();

$wishlistItems = [];
while ($row = $wishlistResult->fetch_assoc()) {
    $wishlistItems[] = $row;
}   
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist</title>
    <link rel="stylesheet" href="wishlist.css">
</head>
<body>
    <!-- Include Header -->
    <header>
        <div class="navbar">
            <div class="container">
                <h1><a>VelaNova</a></h1>
                <ul class="nav-links">
                    <li><a href="index3.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li class="user-menu">
                        <div class="circular-logo" id="profile-btn">
                            <img src="<?php echo $profilePic; ?>" alt="User Logo" class="logo">
                        </div>
                        
                        <!-- JavaScript to toggle dropdown on click -->
                        <script>
                            document.getElementById("profile-btn").addEventListener("click", function() {
                                var dropdown = document.getElementById("dropdown");
                                dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
                            });
                        </script>

                        <!-- Dropdown Menu -->
                        <div class="dropdown-menu" id="dropdown">
                            <p><?php echo $username; ?></p>
                            <a href="profile.php">Profile</a>
                            <a href="wishlist.php">Wishlist</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="container">
        <h1>Your Wishlist</h1>
        <div class="wishlist-items">
            <?php if (count($wishlistItems) > 0): ?>
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-item">
                        <img src="http://localhost:8080/login/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                        <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['price']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Your wishlist is empty.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <script src="index3.js"></script>
</body>
</html>
