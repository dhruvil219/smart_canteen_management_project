<?php
require_once 'session.php';

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
