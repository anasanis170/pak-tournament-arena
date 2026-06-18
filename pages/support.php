<?php 
session_start(); 

// Check karna agar user already logged in hai
$is_logged_in = isset($_SESSION['user_id']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Support — PTA Arena</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* FAQ Interactivity ke liye simple CSS */
        .faq-item {
            background: var(--surface);
            border: 1px solid var(--border);
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            border-color: var(--primary);
        }
        .faq-q {
            padding: 20px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        .faq-a {
            padding: 0 20px 20px;
            font-size: 14px;
            color: var(--muted);
            display: none; /* By default hidden rahega */
            line-height: 1.6;
        }
        .faq-item.active .faq-a {
            display: block; /* Click karne par show hoga */
        }
        .faq-item.active .faq-icon {
            transform: rotate(45deg);
            color: var(--primary);
        }
        .faq-icon {
            transition: transform 0.2s ease;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="cursor" id="cursor"></div><div class="cursor-ring" id="cursor-ring"></div>

<nav class="navbar" id="navbar">
  <a href="../index.php" class="nav-logo"><div class="logo-icon">⚡</div>PTA<span>Arena</span></a>
  <ul class="nav-links">
    <li><a href="../index.php">Home</a></li>
    <li><a href="support.php" class="active">Support</a></li>
  </ul>
  <div class="nav-actions">
    <?php if($is_logged_in): ?>
        <a href="dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
    <?php else: ?>
        <a href="login.php" class="btn btn-outline btn-sm">Login</a>
    <?php endif; ?>
  </div>
</nav>

<div style="padding:100px 60px 40px;background:var(--surface);border-bottom:1px solid var(--border)">
  <div class="section-label" style="justify-content:flex-start">Help Center</div>
  <h1 style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:52px;letter-spacing:-2px;margin-bottom:8px">Support <span style="background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Center</span></h1>
  <div style="max-width:500px;margin:20px 0">
    <input type="text" id="faq-search" class="form-input" placeholder="🔍 Search help articles..." style="width:100%;padding:16px 20px;font-size:15px" onkeyup="filterFAQs()">
  </div>
</div>

<section class="section">
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:48px">
    <div style="background:var(--surface);border:1px solid var(--border);padding:28px;text-align:center;cursor:default;transition:all .3s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'"><div style="font-size:40px;margin-bottom:12px">🪙</div><h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:18px;margin-bottom:6px">Coins & Payments</h3><p style="font-size:13px;color:var(--muted)">Deposits, withdrawals, and coin packages</p></div>
    <div style="background:var(--surface);border:1px solid var(--border);padding:28px;text-align:center;cursor:default;transition:all .3s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'"><div style="font-size:40px;margin-bottom:12px">🏆</div><h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:18px;margin-bottom:6px">Tournaments</h3><p style="font-size:13px;color:var(--muted)">How to join, rules, and prize distribution</p></div>
    <div style="background:var(--surface);border:1px solid var(--border);padding:28px;text-align:center;cursor:default;transition:all .3s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'"><div style="font-size:40px;margin-bottom:12px">🔐</div><h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:18px;margin-bottom:6px">Account & Security</h3><p style="font-size:13px;color:var(--muted)">Login issues, password reset, account bans</p></div>
  </div>

  <h2 style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:28px;letter-spacing:-1px;margin-bottom:24px">Frequently Asked Questions</h2>
  
  <div class="faq-list" id="faq-accordion">
    <div class="faq-item">
        <div class="faq-q">How long does deposit verification take? <span class="faq-icon">+</span></div>
        <div class="faq-a">Deposits are usually verified within 1-2 hours during support hours (10AM-10PM PKT). After verification, coins are instantly added to your wallet.</div>
    </div>
    <div class="faq-item">
        <div class="faq-q">My coins weren't added after deposit. What do I do? <span class="faq-icon">+</span></div>
        <div class="faq-a">Contact support with your Transaction ID and screenshot. We'll verify and add coins manually. Never lose your transaction ID.</div>
    </div>
    <div class="faq-item">
        <div class="faq-q">Can I get a refund if I join a tournament that gets cancelled? <span class="faq-icon">+</span></div>
        <div class="faq-a">Yes! 100% coin refund is issued within 24 hours if a tournament is cancelled by admin. No refunds for player withdrawals after joining.</div>
    </div>
    <div class="faq-item">
        <div class="faq-q">How do I report a cheater? <span class="faq-icon">+</span></div>
        <div class="faq-a">Use the Contact page and select "Report Cheater". Include the player's username, match details, and any screenshot evidence. We investigate all reports.</div>
    </div>
    <div class="faq-item">
        <div class="faq-q">What happens if there's a technical issue during my match? <span class="faq-icon">+</span></div>
        <div class="faq-a">Screenshot the issue immediately and contact support. Admin can reschedule or provide compensation depending on the situation.</div>
    </div>
  </div>
</section>

<footer style="background:var(--surface);border-top:1px solid var(--border);padding:28px 60px;display:flex;justify-content:space-between;align-items:center">
    <div style="font-size:13px;color:var(--muted)">© 2026 <span style="color:var(--primary)">Pro Tournament Arena</span></div>
    <a href="contact.php" class="btn btn-primary btn-sm">Contact Support</a>
</footer>

<script src="../js/app.js"></script>

<script>
// FAQ Accordion Toggle Logic
document.querySelectorAll('.faq-item').forEach(item => {
    item.addEventListener('click', () => {
        // Agar pehle se koi open hai to usko close karein (Optional)
        document.querySelectorAll('.faq-item').forEach(el => {
            if(el !== item) el.classList.remove('active');
        });
        // Current wale ko toggle karein
        item.classList.toggle('active');
    });
});

// Live FAQ Search Filter Logic
function filterFAQs() {
    let input = document.getElementById('faq-search').value.toLowerCase();
    let faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        let question = item.querySelector('.faq-q').textContent.toLowerCase();
        let answer = item.querySelector('.faq-a').textContent.toLowerCase();
        
        if(question.includes(input) || answer.includes(input)) {
            item.style.display = "block";
        } else {
            item.style.display = "none";
        }
    });
}
</script>
</body>
</html>