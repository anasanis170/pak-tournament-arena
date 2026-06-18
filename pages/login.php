<?php
session_start();
include "config.php";

mysqli_set_charset($conn, "utf8mb4");

// Agar user pehle se login hai, toh redirect
if(isset($_SESSION['username'])){
    $logged_user = $_SESSION['username'];
    $role_check = mysqli_query($conn, "SELECT role FROM user WHERE username = '$logged_user'");
    $role_data = mysqli_fetch_assoc($role_check);
    
    if($role_data && $role_data['role'] === 'admin'){
        header("Location: admin-tournaments.php");
    } else {
        header("Location: wallet.php");
    }
    exit();
}

$message = "";

if(isset($_POST['login_submit'])){
    $username_or_email = mysqli_real_escape_string($conn, trim($_POST['username_email']));
    $password = $_POST['password'];

    if(empty($username_or_email) || empty($password)) {
        $message = "<div class='msg-box error-msg'>❌ Please fill in all fields!</div>";
    } else {
        // ✅ FIXED: username OR email se login (gmail hata diya)
        $query = "SELECT * FROM user WHERE username='$username_or_email' OR email='$username_or_email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            // Password verification
            if(password_verify($password, $user_data['password']) || $password === $user_data['password']) {
                $_SESSION['user_id']   = $user_data['id'];
                $_SESSION['username']  = $user_data['username'];
                $_SESSION['user_name'] = $user_data['name'] ?? $user_data['username'];

                if($user_data['role'] === 'admin'){
                    header("Location: admin-tournaments.php");
                } else {
                    header("Location: wallet.php");
                }
                exit();
            } else {
                $message = "<div class='msg-box error-msg'>❌ Incorrect Password!</div>";
            }
        } else {
            $message = "<div class='msg-box error-msg'>❌ User not found! Check username or email.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <title>Login — Pro Tournament Arena</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&display=swap" rel="stylesheet">
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

    .btn-login {
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
    .btn-login:hover {
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

    @media (max-width: 480px) {
        .auth-container { padding: 30px 20px; }
        .auth-title { font-size: 28px; }
    }
  </style>
</head>
<body>

<a href="register.php" class="top-corner-btn">Register</a>

<div class="auth-container">
    <div class="auth-header">
        <h1 class="auth-title">Welcome Back</h1>
        <div class="auth-sub">Sign in to access your arena dashboard.</div>
    </div>

    <?php echo $message; ?>

    <form action="login.php" method="POST">
        
        <div class="form-group">
            <label class="form-label">Username or Email</label>
            <input type="text" name="username_email" class="cyber-input" placeholder="Enter username or email" required>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="cyber-input" placeholder="••••••••" required>
        </div>

        <button type="submit" name="login_submit" class="btn-login">Login Now</button>
    </form>

    <div class="auth-footer">
        New here? <a href="register.php">Create an account</a>
    </div>
</div>

</body>
</html>