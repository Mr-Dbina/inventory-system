<?php
session_start();
$authenticated = false;
if (isset($_SESSION["email"])) {
    $authenticated = true;
}
?>