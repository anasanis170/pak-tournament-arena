<?php 
session_start(); 
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Terms & Conditions — PTA Arena</title>
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
        <div class="page-title">📄 Terms & Conditions</div>
        <div class="page-sub">Last updated: January 2026</div>

        <div class="section">
            <h3>1. Acceptance of Terms</h3>
            <p>By accessing and using PTA Arena, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
        </div>

        <div class="section">
            <h3>2. User Accounts</h3>
            <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the terms.</p>
            <ul>
                <li>You are responsible for safeguarding the password</li>
                <li>You must notify us immediately upon becoming aware of any breach of security</li>
                <li>One person may not maintain more than one account</li>
            </ul>
        </div>

        <div class="section">
            <h3>3. Tournament Rules</h3>
            <p>All tournaments hosted on PTA Arena are subject to specific rules:</p>
            <ul>
                <li>No hacking, cheating, or use of third-party tools</li>
                <li>Players must join within the specified time</li>
                <li>Match results must be submitted with valid proof (screenshot/video)</li>
                <li>Admin decisions are final in case of disputes</li>
            </ul>
        </div>

        <div class="section">
            <h3>4. Payments & Refunds</h3>
            <p>All coin purchases and tournament entry fees are processed securely. Refund policies are as follows:</p>
            <ul>
                <li>Entry fees are non-refundable once tournament starts</li>
                <li>Cancelled tournaments will receive full refund</li>
                <li>Withdrawal requests are processed within 24-48 hours</li>
            </ul>
        </div>

        <div class="section">
            <h3>5. Termination</h3>
            <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
        </div>

        <div class="section">
            <h3>6. Contact Information</h3>
            <p>For any questions about these Terms, please contact us:</p>
            <ul>
                <li>Email: support@ptaarena.com</li>
                <li>WhatsApp: +92 300 0000000</li>
            </ul>
        </div>
    </div>
</div>

<?php if (file_exists("navbar.php")) { include "navbar.php"; } ?>

</body>
</html>