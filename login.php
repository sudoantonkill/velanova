
<?php
include 'connection.php'; // Include the database connection file

$error = ''; // Initialize the error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Bind the result
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            echo "Login successful!";
        } else {
            $error = "Invalid password."; // Error message for invalid password
        }
    } else {
        $error = "No user found with that email."; // Error message for non-existent user
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <img src="velanova.png" alt="Logo"> <!-- Replace 'velanova.png' with your actual logo -->
        </div>
        <form class="login-form" action="login.php" method="POST">
            <h2>Login</h2>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
            
            <!-- Error message div with error-message class -->
            <?php if (!empty($error)) : ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="or-divider">
                <span>OR</span>
            </div>
            
            <button type="button" class="google-btn">
                <img src="google.png" alt="Google Logo">
                <span>Login with Google</span>
            </button>

            <!-- Link to Registration Page -->
            <div class="register-link">
                <p>Not registered? <a href="signup.php">Create an account</a></p>
            </div>
        </form>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            console.log('Encoded JWT ID token: ' + response.credential);
            // Send this token to your server to validate
        }
        window.onload = function () {
            google.accounts.id.initialize({
                client_id: '806215493416-dqd76h10hfmebetvmrqcv5gqe0mhnola.apps.googleusercontent.com', // Replace with your Google Client ID
                callback: handleCredentialResponse
            });
            google.accounts.id.renderButton(
                document.querySelector('.google-btn'), {
                    theme: 'outline',
                    size: 'large'
                }
            );<?php
session_start(); // Start the session
include 'connection.php'; // Include the database connection file

$error = ''; // Initialize the error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind the result
        $stmt->bind_result($user_id, $username, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Set session variables on successful login
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            // Redirect to the welcome page
            header("Location: index3.php");
            exit();
        } else {
            $error = "Invalid password."; // Error message for invalid password
        }
    } else {
        $error = "No user found with that email."; // Error message for non-existent user
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <img src="velanova.png" alt="Logo"> <!-- Replace 'velanova.png' with your actual logo -->
        </div>
        <form class="login-form" action="login.php" method="POST">
            <h2>Login</h2>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>

            <!-- Error message div with error-message class -->
            <?php if (!empty($error)) : ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="or-divider">
                <span>OR</span>
            </div>

            <!-- Google Sign-In button -->
            <button type="button" class="google-btn">
                <img src="google.png" alt="Google Logo">
                <span>Login with Google</span>
            </button>

            <!-- Link to Registration Page -->
            <div class="register-link">
                <p>Not registered? <a href="signup.php">Create an account</a></p>
            </div>
        </form>
    </div>

    <!-- Google Sign-In Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            console.log('Encoded JWT ID token: ' + response.credential);
            // Send this token to your server to validate
            // Example of sending token to your server using fetch (optional):
            fetch('google_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_token: response.credential })
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      window.location.href = 'welcome.php'; // Redirect on successful Google login
                  } else {
                      alert('Google login failed. Please try again.');
                  }
              }).catch(error => console.error('Error:', error));
        }

        window.onload = function () {
            google.accounts.id.initialize({
                client_id: '806215493416-dqd76h10hfmebetvmrqcv5gqe0mhnola.apps.googleusercontent.com', // Replace with your Google Client ID
                callback: handleCredentialResponse
            });
            google.accounts.id.renderButton(
                document.querySelector('.google-btn'), {
                    theme: 'outline',
                    size: 'large'
                }
            );
            google.accounts.id.prompt();
        };
    </script>
</body>
</html>

            google.accounts.id.prompt();
        };
    </script>
</body>
</html>
