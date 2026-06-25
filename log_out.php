<?php
session_start();

// Clear session data
$_SESSION = [];

// Destroy session
session_destroy();

header("Location: home.php");
exit();
?>