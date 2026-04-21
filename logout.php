<?php
// logout.php
session_start();
session_unset();     // clear all session variables
session_destroy();   // destroy the session
header("Location: login.php"); // redirect the user to the login page
exit();
?>