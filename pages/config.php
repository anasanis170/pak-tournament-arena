<?php
// ============================================
// PTA ARENA - DATABASE CONFIGURATION
// ============================================

// ✅ LOCALHOST (XAMPP) - Ghar pe testing ke liye
$localhost = false; // FALSE = Hosting, TRUE = XAMPP

if ($localhost) {
    // Local XAMPP Settings
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "ff_db";
} else {
    // 🌐 INFINITYFREE HOSTING SETTINGS
    $servername = "sql207.infinityfree.com";
    $username   = "if0_42191559";
    $password   = "anasanis1231";
    $dbname     = "if0_42191559_ptaarena"; // Apna database name yahan dalo
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}

// UTF-8 Support
mysqli_set_charset($conn, "utf8mb4");

// Timezone
date_default_timezone_set('Asia/Karachi');
?>