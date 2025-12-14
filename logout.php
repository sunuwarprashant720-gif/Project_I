<?php
session_start();

if (isset($_POST['logout'])) {

    session_unset();        // Remove all session variables
    session_destroy();     // Destroy the session

    header("Location: login.php");
    exit();
}

// If accessed directly
header("Location: login.php");
exit();
