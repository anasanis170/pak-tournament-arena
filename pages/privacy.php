<?php 
session_start(); 
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Privacy Policy — PTA Arena</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #7B2EFF; --secondary: #00E5FF; --muted: #8a8fa3;
        --border: rgba(255,255,255,0.08); --card-bg: #0c0f24; --bg-dark: #02040a;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: var(--bg-dark); color: #fff; font-family: 'Space Grotesk', sans-serif; padding-bottom: 80px; }

    .page-container { max-width: 700px; margin: 0 auto; padding: 100px 20px 40px; }
    
    .page-card {
        background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px;
        padding: 32px 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    }
    .page-title { 
        font-family: 'Rajdhani', sans-serif; font-size: 28px; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 1px; color: var(--secondary); 
        margin-bottom: 6px; text-shadow: 0 0 20px rgba(0,229,255,0.3);
    }
    .page-sub { font-size: 13px; color: var(--muted); margin-bottom: 24px; }
    
    .section { margin-bottom: 20px; }
    .section h3 { 
        font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 700; 
        color: #fff; margin-bottom: 8px; 
    }
    .section p { font-size: 14px; color: var(--muted); line-height: 1.8; }
    .section ul { list-style: none; padding-left: 0; margin-top: 8px; }
    .section ul li { 
        font-size: 14px; color: var(--muted); line-height: 1.8; 
        padding-left: 20px; position: relative; 
    }
    .section ul li::before { 
        content: '•'; color: var(--secondary); position: absolute; left: 0; 
    }

    .btn-back {
        display: inline-block; background: var(--primary); color: #fff;
        padding: 10px 20px; border-radius: 8px; text-decoration: none;
        font-weight: 600; font-size: 13px; margin-bottom: 20px; transition: all 0.3s ease;
    }
    .btn-back:hover { box-shadow: 0 0 20px rgba(123,46,255,0.4); }
</style>
</head>
<body>

<?php if (file_exists("header.php")) { include "header.php"; } ?>

<div class="page-container">
    <a href="profile.php" class="btn-back">← Back to Profile</a>
    
    <div class="page-card">
        <div class="page-title">🛡️ Privacy Policy</div>
        <div class="page-sub">Last updated: January 2026</div>

        <div class="section">
            <h3>1. Information We Collect</h3>
            <p>When you use PTA Arena, we may collect the following information:</p>
            <ul>
                <li>Personal identification information (Name, email address, phone number)</li>
                <li>Game-related data (Game UID, in-game name, match statistics)</li>
                <li>Transaction data (Deposit/withdrawal history, coin balance)</li>
                <li>Device information (Browser type, IP address, device type)</li>
            </ul>
        </div>

        <div class="section">
            <h3>2. How We Use Your Information</h3>
            <p>We use the collected data for various purposes:</p>
            <ul>
                <li>To provide and maintain our tournament services</li>
                <li>To notify you about changes to our service</li>
                <li>To allow you to participate in interactive features</li>
                <li>To provide customer support</li>
                <li>To monitor the usage of our service</li>
                <li>To detect, prevent and address technical issues</li>
            </ul>
        </div>

        <div class="section">
            <h3>3. Data Security</h3>
            <p>The security of your data is important to us. We strive to use commercially acceptable means to protect your personal information, but remember that no method of transmission over the Internet or electronic storage is 100% secure.</p>
        </div>

        <div class="section">
            <h3>4. Third-Party Services</h3>
            <p>We may employ third-party companies and individuals to facilitate our service. These third parties have access to your personal information only to perform these tasks on our behalf and are obligated not to disclose or use it for any other purpose.</p>
        </div>

        <div class="section">
            <h3>5. Contact Us</h3>
            <p>If you have any questions about this Privacy Policy, please contact us:</p>
            <ul>
                <li>By email: support@ptaarena.com</li>
                <li>By WhatsApp: +92 300 0000000</li>
            </ul>
        </div>
    </div>
</div>

<?php if (file_exists("navbar.php")) { include "navbar.php"; } ?>

</body>
</html>