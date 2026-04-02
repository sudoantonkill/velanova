<?php
session_start(); // Start the session

// Include the database connection (ensure this file has the correct DB details)
include 'connection.php'; // Database connection file

// Check if the user is logged in
$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $logged_in ? $_SESSION['username'] : null;
$profilePic = 'uploads/default-profile.png'; // Default profile picture

// Fetch the profile picture and username if the user is logged in
if ($logged_in) {
    $userEmail = $_SESSION['email']; // Fetch the email from the session

    // Fetch user details from the database based on email
    $sql = "SELECT username, profile_pic FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $username = $user['username']; // Correct username from database
            
            // Check if profile picture exists, otherwise use default
            if (!empty($user['profile_pic'])) {
                $profilePic = $user['profile_pic']; // Update the profile picture from the database
            }
            
            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['profile_pic'] = $profilePic;
        } else {
            // User not found, reset session variables
            $_SESSION['username'] = 'Unknown User';
            $_SESSION['profile_pic'] = 'uploads/default-profile.png';
        }

        // Close the statement
        $stmt->close();
    } else {
        // SQL statement preparation failed
        echo "Error preparing SQL statement: " . $conn->error;
    }
}

// Close the database connection
$conn->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Compare - Find the Best Deals!</title>
    <link rel="stylesheet" href="styles3.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navigation Bar -->
    <header>
        <nav class="navbar">
            <div class="container">
                <h1 class="brand">VelaNova</h1>
                <ul class="nav-links">
                    <li><a href="index3.php">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="#">Contact</a></li>
                    
                    <!-- Conditional login/logout buttons -->
                    <?php if ($logged_in): ?>
                        <li>
                            <div class="user-menu">
                                <div id="profile-logo" class="circular-logo">
                                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="User Logo" class="logo">
                                </div>
                                <div id="dropdown-menu" class="dropdown-menu" style="display: none;">
                                    <p><?php echo htmlspecialchars($username); ?></p>
                                    <a href="Profile.php">Profile</a>
                                    <a href="Wishlist.php">Wishlist</a>
                                    <a href="Logout.php" id="logout-button">Logout</a> <!-- Logout button -->
                                </div>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <section class="menu-bar">
                <div class="container">
                    <a href="#" class="menu-button">Today's Deals</a>
                    <a href="#" class="menu-button">Best Sellers</a>
                    <a href="#" class="menu-button">New Releases</a>
                    <a href="#" class="menu-button">Gift Ideas</a>
                    <a href="#" class="menu-button">Customer Service</a>
                </div>
            </section>
        </nav>
    </header>

    <!-- JavaScript for User Menu Toggle -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileLogo = document.getElementById('profile-logo');
        const dropdownMenu = document.getElementById('dropdown-menu');

        profileLogo.addEventListener('click', function () {
            // Toggle dropdown visibility
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Close the dropdown if clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.circular-logo') && !event.target.closest('.user-menu')) {
                dropdownMenu.style.display = 'none'; // Close the dropdown if clicked outside
            }
        };
    });
    </script>

    <!-- Hero Section with Background Image -->
    <section class="hero">
        <div class="hero-content container">
            <h1>Find the Best Prices, Anytime</h1>
            <form id="product-form" class="search-form">
                <input type="text" id="product-name" name="product_name" placeholder="Search for products..." required>
                <button type="submit">Compare</button>
            </form>

            <div id="dynamic-form-section" style="display:none;">
                <h2 id="product-type-title"></h2>
                <form id="dynamic-form">
                    <!-- Dynamic form fields will be appended here -->
                </form>
            </div>

            <!-- Result Section -->
            <section id="results-section" class="results-section">
                <h2>Search Results</h2>
                <div id="loader" class="loader"></div>
                <div id="results" class="results"></div>
            </section>
            <section class="white-background">
                <!-- Your existing content below the hero section -->
            </section>
        </div>
    </section>

    <!-- First Set of Image Boxes -->
    <section class="image-boxes">
        <a href="iphone-details.html" class="box">
            <img src="iphone.png" alt="Example 1">
            <h3>Deals on iPhone</h3>
        </a>
        <a href="example2-details.html" class="box">
            <img src="sam.png" alt="Example 2">
            <h3>S22 at best price</h3>
        </a>
        <a href="example3-details.html" class="box">
            <img src="mac.webp" alt="Example 3">
            <h3>Powerful Macs</h3>
        </a>
        <a href="example4-details.html" class="box">
            <img src="watch.png" alt="Example 4">
            <h3>Next-gen Watches</h3>
        </a>
    </section>
    
        
    </div>

        <!-- First Set of Image Boxes -->

        <!-- Slideshow Section -->
    <section class="slideshow">
        <div class="slideshow-container">
            <div class="slide">
                <a href="login.html">
                <img src="slide1.jpeg" alt="Slide 1">
                </a>
            </div>
            <div class="slide">
                <a href="https://example.com/page2" target="_blank">
                    <img src="slide2.jpeg" alt="Slide 2">
                </a>
            </div>
            <div class="slide">
                <a href="https://example.com/page3" target="_blank">
                    <img src="slide3.png" alt="Slide 3">
                </a>
            </div>
            <div class="slide">
                <a href="https://example.com/page4" target="_blank">
                    <img src="slide4.png" alt="Slide 4">
                </a>
            </div>
        </div>

        <div class="brand-logos">
            <h2 class="brand-title">Customer Favorite Brands</h2>
            <div class="logo-grid">
                <a href="https://www.levis.com" class="logo-box">
                    <img src="levi.png" alt="Levi's Logo">
                    <label>LEVI'S</label>
                </a>
                <a href="https://www.samsung.com" class="logo-box">
                    <img src="sambhai.png" alt="Samsung Logo">
                    <label>Samsung</label>
                </a>
                <a href="https://www.apple.com" class="logo-box">
                    <img src="apple.png" alt="Apple Logo">
                    <label>Apple</label>
                </a>
                <a href="https://www.lg.com" class="logo-box">
                    <img src="lg.png" alt="LG Logo">
                    <label>LG</label>
                </a>
                <a href="#" class="logo-box">
                    <img src="op.png" alt="Brand 5 Logo">
                    <label>OnePlus</label>
                </a>
                <a href="#" class="logo-box">
                    <img src="haier.png" alt="Brand 6 Logo">
                    <label>Haier</label>
                </a>
                <a href="#" class="logo-box">
                    <img src="bs.png" alt="Brand 7 Logo">
                    <label>BlueStar</label>
                </a>
                <a href="#" class="logo-box">
                    <img src="daikin.png" alt="Brand 8 Logo">
                    <label>Daikin</label>
                </a>
                <a href="#" class="logo-box">
                    <img src="og.png" alt="Brand 9 Logo">
                    <label>OGeneral</label>
                </a>
            </div>
        </div>
        
    </section>


       

    <!-- Blocks with Placeholder Images -->
    <section class="image-blocks container">
        <h2>Popular Categories</h2>
        <div class="blocks-grid">
            <div class="block">
                <img src="videocon-ha.jpg" alt="Electronics">
                <h3>Electronics</h3>
            </div>
            <div class="block">
                <img src="fashion.jpeg" alt="Fashion">
                <h3>Fashion</h3>
            </div>
            <div class="block">
                <img src="home1.jpeg" alt="Home Appliances">
                <h3>Home Appliances</h3>
            </div>
            <div class="block">
                <img src="sale.png" alt="Hot Deals">
                <h3>Hot Deals</h3>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <p>&copy; 2024 PriceCompare. All rights reserved.</p>
            </div>
            <div class="footer-bottom">
                <div class="company-info">
                    <h3>Contact Us</h3>
                    <p>Room no 514, The Ashtavakra Building</p>
                    <p>Mumbai, Maharashtra - 400022</p>
                    <p>Email: siddharth.koul@somaiya.edu</p>
                    <p>Phone: +91 (989) 256-5705</p>
                </div>
                <div class="social-media">
                    <h3>Follow Us</h3>
                    <a href="https://www.facebook.com/" class="social-icon"><img src="fb.png" alt="Facebook"></a>
                    <a href="https://x.com/" class="social-icon"><img src="x.webp" alt="Twitter"></a>
                    <a href="https://www.instagram.com/" class="social-icon"><img src="insta1.png" alt="Instagram"></a>
                    <a href="https://in.linkedin.com/" class="social-icon"><img src="linkedin.png" alt="LinkedIn"></a>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="index3.js"></script>
</body>
</html>
