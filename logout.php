<?php
session_start(); // Start the session

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the main site (index3.html)
header("Location: index3.php");
exit();
?>
