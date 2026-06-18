<?php
// 1. Session start kiya taake browser ko pata chale kaun logout ho raha hai
session_start();

// 2. Session ka sara data delete kar diya
session_unset();
session_destroy();

// 3. VIP Redirect: Seedha login.php par bhej diya jo isi pages folder mein hai
header("Location: login.php");
exit();
?>