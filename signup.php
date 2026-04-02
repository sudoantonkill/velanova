<?php
include 'connection.php'; // Include the database connection file

$error_message = ""; // Initialize error message variable

// Handle traditional signup when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt the password

    // Check if the email already exists in the database
    $check_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_user->bind_param("s", $email);
    $check_user->execute();
    $result = $check_user->get_result();

    if ($result->num_rows > 0) {
        $error_message = "This email is already registered. Please use a different email.";
    } else {
        // Prepare and bind the statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        // Execute and check for errors
        if ($stmt->execute()) {
            header("Location: login.php"); // Redirect to login page
            exit(); // Stop further script execution
        } else {
            $error_message = "Error: " . $stmt->error; // Display any errors
        }

        $stmt->close();
    }

    $check_user->close();
    $conn->close();
}

// Handle Google Sign-In when ID token is sent via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_token'])) {
    $id_token = $_POST['id_token'];
    $client_id = '806215493416-dqd76h10hfmebetvmrqcv5gqe0mhnola.apps.googleusercontent.com'; // Replace with your actual Google Client ID

    // Include Google Client Library (ensure you have it installed via Composer)
    require_once 'vendor/autoload.php'; // Ensure the Google Client Library is installed
 
    $client = new Google_Client(['client_id' => $client_id]);  // Set the Client ID
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $email = $payload['email'];
        $name = $payload['name'];

        // Check if the user already exists
        $check_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check_user->bind_param("s", $email);
        $check_user->execute();
        $result = $check_user->get_result();

        if ($result->num_rows > 0) {
            header("Location: login.php"); // Redirect existing user to login
        } else {
            // Insert new user if not found
            $stmt = $conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $email);

            if ($stmt->execute()) {
                header("Location: login.php"); // Redirect after successful signup
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check_user->close();
    } else {
        echo "Invalid ID token"; // Handle invalid token
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <img src="velanova.png" alt="Logo"> <!-- Replace with your actual logo -->
        </div>
        <form class="register-form" action="signup.php" method="POST">
            <h2>Register</h2>

            <!-- Display error message if exists -->


            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit" class="register-btn" name="signup">Register</button>

            <div class="or-divider">
                <span>OR</span>
            </div>

            <div id="g_id_onload"
                 data-client_id="806215493416-dqd76h10hfmebetvmrqcv5gqe0mhnola.apps.googleusercontent.com"
                 data-callback="handleCredentialResponse"
                 data-auto_prompt="false">
            </div>
            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="rectangular"
                 data-theme="outline"
                 data-text="sign_in_with"
                 data-size="large">
            </div>
        </form>
    </div>

    <script>
        function handleCredentialResponse(response) {
            console.log('Encoded JWT ID token:', response.credential);
            // Send the ID token to your server for verification
            const formData = new FormData();
            formData.append('id_token', response.credential);

            fetch('signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Server response:', data); // Handle server response
                window.location.href = 'login.php'; // Redirect after successful signup/login
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while connecting. Please check the console for details.');
            });
        }

        window.onload = function () {
            google.accounts.id.initialize({
                client_id: '806215493416-dqd76h10hfmebetvmrqcv5gqe0mhnola.apps.googleusercontent.com',
                callback: handleCredentialResponse
            });
            google.accounts.id.renderButton(
                document.querySelector('.g_id_signin'), {
                    theme: 'outline',
                    size: 'large'
                }
            );
            google.accounts.id.prompt(); // Prompt the user to sign in with Google if needed
        };
    </script>
    <script src='signup.js'></script>
</body>
</html>
