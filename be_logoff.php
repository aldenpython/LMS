<?php
// Include authentication functions
require_once 'be_auth.php';

// Start session if not already started
ensure_session_started();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set message for next page
session_start();
set_message("You have been successfully logged out.", "success");

// Redirect to login page
header("Location: index.php");
exit;
?>