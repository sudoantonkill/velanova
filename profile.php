<?php
// Include the database connection
include 'connection.php'; // Ensure this file contains your database connection details

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "You must be logged in to view this page.";
    exit(); // Prevent loading the rest of the page
}

// Check if user_email is set
if (isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];

    // Fetch user details from the database
    $sql = "SELECT username, email, age, gender, profile_pic FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];
        $email = $user['email'];
        $age = $user['age'];
        $gender = $user['gender'];
        
        // Use default profile picture if none is set
        $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'uploads/default-profile.png';
    } else {
        echo "No user found.";
        exit();
    }
} else {
    echo "You must be logged in to view this page.";
    exit(); // Prevent loading the rest of the page
}

// Handle form submission for updating profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];
    $newAge = $_POST['age'];
    $newGender = $_POST['gender'];
    $newPassword = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : ""; // Update password only if provided

    // Handle profile picture upload
    $newProfilePic = $profilePic; // Default to current picture
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $targetDirectory = "uploads/"; // Directory to store uploaded pictures
        $imageFileType = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $imageFileType; // Unique file name to avoid conflicts
        $targetFile = $targetDirectory . $newFileName;

        // Check if the uploaded file is a valid image
        $check = getimagesize($_FILES['profile_pic']['tmp_name']);
        if ($check !== false) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
                $newProfilePic = $targetFile; // Set the new profile picture path
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    // Update the user details in the database
    $updateSql = "UPDATE users SET username = ?, email = ?, age = ?, gender = ?, profile_pic = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssss", $newUsername, $newEmail, $newAge, $newGender, $newProfilePic, $userEmail);

    if ($updateStmt->execute()) {
        echo "Profile updated successfully!";
        $_SESSION['user_email'] = $newEmail; // Update session email if the email was changed
        header("Location: profile.php"); // Redirect to refresh the data
        exit();
    } else {
        echo "Error updating profile: " . $updateStmt->error;
    }

    $updateStmt->close();
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile.css"> <!-- Link to the CSS file -->
    <style>
        /* Additional styles for modal */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.8); /* Black background with opacity */
        }
        
        .modal-content {
            margin: auto;
            display: block;
            width: 80%; /* Set a width for the image */
            max-width: 700px; /* Set a max-width for the image */
        }

        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Edit Your Profile</h1>
        <form action="profile.php" method="POST" enctype="multipart/form-data"> <!-- Form to update profile -->
            <div class="profile-pic-container">
                <a href="#" id="profile-pic-link">
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic"> <!-- Current profile picture -->
                </a>
            </div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="male" <?php if ($gender == 'male') echo 'selected'; ?>>Male</option>
                <option value="female" <?php if ($gender == 'female') echo 'selected'; ?>>Female</option>
                <option value="other" <?php if ($gender == 'other') echo 'selected'; ?>>Other</option>
            </select>

            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">

            <label for="profile_pic">Profile Picture:</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*"> <!-- File input for profile picture -->

            <button type="submit" class="save-btn">Save Changes</button>
        </form>

        <a href="index3.php" class="back-btn">Back to Website</a> <!-- Link to go back to the main site -->
    </div>

    <!-- Modal for full-size image -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
        <div id="caption"></div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the image and insert it inside the modal
        var img = document.getElementById("profile-pic-link");
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");

        img.onclick = function(event) {
            event.preventDefault(); // Prevent default action
            modal.style.display = "block"; // Show the modal
            modalImg.src = this.children[0].src; // Set the modal image source
            captionText.innerHTML = this.children[0].alt; // Set the caption text
        }

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none"; // Hide the modal
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none"; // Hide the modal
            }
        }
    </script>
</body>
</html>
