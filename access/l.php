<?php
// Update the l.php file to properly start and maintain sessions
session_start();

// Check if user is authenticated
$authenticated = false;
if (isset($_SESSION["email"])) {
    $authenticated = true;
}
?>