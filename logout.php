<?php
session_start();
session_destroy();  // Destroy all sessions
header("Location: home.php");  // Redirect to home page after logout
exit();
?>