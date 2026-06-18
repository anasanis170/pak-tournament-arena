<?php
session_start();
include "config.php";

// 🔥 PHP 8 ko crash hone se bachane aur sahi error dikhane ke liye ye line zaroori hai
mysqli_report(MYSQLI_REPORT_OFF); 

mysqli_set_charset($conn, "utf8mb4");

// Agar user pehle se login hai toh dashboard/wallet par bhej dein
if (isset($_SESSION['username']) || isset($_SESSION['email'])) {
    header("Location: wallet.php");
    exit();
}

$message = "";

// REGISTRATION FORM PROCESSING
if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $uid       = mysqli_real_escape_string($conn, $_POST['uid']);
    $password  = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Validation checks
    if ($password !== $cpassword) {
        $message = "<div class='msg-box error-msg'>❌ Passwords aapas mein match nahi kar rahe!</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='msg-box error-msg'>❌ Password kam az kam 6 characters ka hona chahiye.</div>";
    } else {
        // Check if username or email already exists
        $check_query = mysqli_query($conn, "SELECT * FROM user WHERE username='$username' OR email='$email'");
        
        if (!$check_query) {
            // Agar table hi nahi milti toh ye error dikhaye crash na ho
            $message = "<div class='msg-box error-msg'>❌ Database Error (Check Query): " . mysqli_error($conn) . "</div>";
        } else if (mysqli_num_rows($check_query) > 0) {
            $message = "<div class='msg-box error-msg'>❌ Username ya Email pehle se maujood hai. Koi aur try karein.</div>";
        } else {
            // Password hashing for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database (Initial coin balance is 0)
            $insert_query = "INSERT INTO user (name, username, email, uid, password, coin) 
                             VALUES ('$full_name', '$username', '$email', '$uid', '$hashed_password', 0)";
            
            if (mysqli_query($conn, $insert_query)) {
                $_SESSION['msg_success'] = "✅ Account kamyabi se ban gaya! Ab aap login kar sakte hain.";
                header("Location: login.php");
                exit();
            } else {
                $message = "<div class='msg-box error-msg'>❌ Database Error (Insert): " . mysqli_error($conn) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <title>Register — Pro Tournament Arena</title>
  <link rel="stylesheet" href="style.css">
  <style>
    :root {
        --bg-main: #050714;
        --panel-dark: #0c0f24;
        --neon-cyan: #00f0ff;
        --neon-purple: #7b2eff;
        --text-muted: #8a8fa3;
        --cyber-danger: #ff4757;
        --cyber-orange: #ff9f43;
    }

    body {
        background-color: var(--bg-main);
        color: #fff;
        font-family: 'Rajdhani', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
        box-sizing: border-box;
    }

    .auth-container {
        width: 100%;
        max-width: 450px;
        background: radial-gradient(circle at top right, rgba(123, 46, 255, 0.05), rgba(12, 15, 36, 0.95));
        border: 1px solid rgba(0, 240, 255, 0.2);
        border-radius: 16px;
        padding: 40px 30px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.6);
        position: relative;
        overflow: hidden;
    }

    /* Top glow effect */
    .auth-container::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, transparent, var(--neon-cyan), transparent);
    }

    .auth-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .auth-title {
        font-size: 32px;
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 8px 0;
    }
    .auth-sub {
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .msg-box { padding: 14px; border-radius: 6px; font-size: 14px; font-weight: 600; margin-bottom: 24px; text-align: center; }
    .success-msg { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.3); }
    .error-msg { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.3); }

    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        font-size: 13px;
        color: var(--neon-cyan);
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .cyber-input {
        background-color: rgba(5, 7, 20, 0.8);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 14px 16px;
        border-radius: 8px;
        width: 100%;
        box-sizing: border-box;
        outline: none;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.3s ease;
    }
    .cyber-input:focus {
        border-color: var(--neon-cyan);
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.1);
        background-color: #050714;
    }
    .cyber-input::placeholder {
        color: rgba(255,255,255,0.2);
    }

    /* Grid for 2 columns */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .btn-register {
        background: linear-gradient(135deg, var(--neon-cyan), #0097a7);
        color: #050714;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 800;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 16px;
        border: none;
        border-radius: 8px;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(0, 240, 255, 0.3);
    }
    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 240, 255, 0.5);
    }

    .auth-footer {
        text-align: center;
        margin-top: 24px;
        font-size: 14px;
        color: var(--text-muted);
    }
    .auth-footer a {
        color: var(--neon-purple);
        font-weight: 700;
        text-decoration: none;
        transition: color 0.3s;
    }
    .auth-footer a:hover {
        color: #fff;
    }

    @media (max-width: 480px) {
        .form-row { grid-template-columns: 1fr; gap: 0; }
        .auth-container { padding: 30px 20px; }
        .auth-title { font-size: 28px; }
    }
    
.top-corner-btn {
    position: fixed; top: 20px; right: 20px; z-index: 999;
    background: transparent; color: #00f0ff;
    border: 1px solid rgba(0,240,255,0.3);
    padding: 10px 20px; border-radius: 8px;
    text-decoration: none; font-family: 'Rajdhani', sans-serif;
    font-weight: 700; font-size: 14px; text-transform: uppercase;
    transition: all 0.3s;
}
.top-corner-btn:hover {
    background: rgba(0,240,255,0.1);
    border-color: #00f0ff;
    box-shadow: 0 0 15px rgba(0,240,255,0.3);
}

</style>
<a href="register.php" class="top-corner-btn">Register</a>
<!-- Ya -->
<a href="login.php" class="top-corner-btn">Login</a>
  
</head>
<body>
 <div style="position:fixed;top:20px;right:20px;z-index:999;">
    <a href="login.php" style="background:transparent;color:#00f0ff;border:1px solid rgba(0,240,255,0.3);padding:10px 20px;border-radius:8px;text-decoration:none;font-family:'Rajdhani',sans-serif;font-weight:700;font-size:14px;text-transform:uppercase;transition:all 0.3s;">
        Login
    </a>
</div>

<div class="auth-container">
    <div class="auth-header">
        <h1 class="auth-title">Join Arena</h1>
        <div class="auth-sub">Create your account to start competing.</div>
    </div>

    <?php echo $message; ?>

    <form action="register.php" method="POST">
        
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="cyber-input" placeholder="Enter your full name" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="cyber-input" placeholder="Player ID" required>
            </div>
            <div class="form-group">
                <label class="form-label">In-Game UID</label>
                <input type="text" name="uid" class="cyber-input" placeholder="Free Fire / CS2 UID" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Gmail Address</label>
            <input type="email" name="email" class="cyber-input" placeholder="example@gmail.com" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="cyber-input" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label class="form-label">Re-enter Password</label>
                <input type="password" name="cpassword" class="cyber-input" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" name="register" class="btn-register">Create Account</button>
    </form>

    <div class="auth-footer">
        Pehle se account hai? <a href="login.php">Login Here</a>
    </div>
</div>

</body>
</html>