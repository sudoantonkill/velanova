<?php
// db.php - Database Connection
$servername = "localhost";  // XAMPP default is 'localhost'
$username = "root";         // XAMPP default username is 'root'
$password = "";             // XAMPP default password is empty
$db_name = "velanova";        // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
