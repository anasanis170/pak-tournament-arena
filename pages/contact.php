<?php
session_start();
include "config.php"; // Database connection file

$message_alert = "";

// Jab user contact form submit kare
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_contact_msg'])) {
  $name = mysqli_real_escape_string($conn, $_POST['contact_name']);
  $email = mysqli_real_escape_string($conn, $_POST['contact_email']);
  $subject = mysqli_real_escape_string($conn, $_POST['contact_subject']);
  $message_text = mysqli_real_escape_string($conn, $_POST['contact_message']);

  // Database mein ticket save karne ki query
  $insert_query = "INSERT INTO support_tickets (name, email, subject, message) 
                   VALUES ('$name', '$email', '$subject', '$message_text')";

  if (mysqli_query($conn, $insert_query)) {
    $message_alert = "<div class='msg-box success-msg'>🚀 Message sent! Our team will reply within 24 hours. ✅</div>";
  } else {
    $message_alert = "<div class='msg-box error-msg'>❌ Database Error: " . mysqli_error($conn) . "</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <title>Contact — PTA Arena</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #8B5CF6;
      --secondary: #00F0FF;
      --surface: rgba(10, 14, 35, 0.6);
      --surface-solid: #0a0e23;
      --border: rgba(255, 255, 255, 0.1);
      --bg-dark: #040817;
      --muted: #94A3B8;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

    body {
      background: var(--bg-dark);
      color: #fff;
      font-family: 'Space Grotesk', sans-serif;
      overflow-x: hidden;
      background-image: 
        radial-gradient(circle at top right, rgba(139, 92, 246, 0.15), transparent 40%),
        radial-gradient(circle at bottom left, rgba(0, 240, 255, 0.1), transparent 40%);
      background-attachment: fixed;
    }

    /* VIP Mobile App Header (Replacing Navbar) */
    .app-header {
      position: sticky;
      top: 0;
      left: 0;
      width: 100%;
      background: rgba(4, 8, 23, 0.85);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
    }

    .back-btn {
      color: #fff;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.05);
      padding: 8px 14px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .back-btn:active { transform: scale(0.95); background: rgba(255, 255, 255, 0.1); }
    .header-title { font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 20px; letter-spacing: 1px; }

    /* Cinematic Load Animations */
    @keyframes eliteFadeUp { 
      from { opacity: 0; transform: translateY(20px); } 
      to { opacity: 1; transform: translateY(0); } 
    }
    .anim-1 { animation: eliteFadeUp 0.4s ease forwards; }
    .anim-2 { animation: eliteFadeUp 0.4s ease 0.1s forwards; opacity: 0; }

    /* Layout Containers */
    .contact-header {
      padding: 40px 24px 24px;
      text-align: center;
    }

    .contact-header h1 {
      font-family: 'Rajdhani', sans-serif;
      font-weight: 800;
      font-size: 42px;
      line-height: 1.1;
      letter-spacing: -1px;
      margin-bottom: 12px;
    }

    .contact-header p {
      color: var(--muted);
      font-size: 15px;
      line-height: 1.6;
      max-width: 90%;
      margin: 0 auto;
    }

    .contact-grid {
      display: flex;
      flex-direction: column;
      gap: 32px;
      padding: 0 24px 60px;
      max-width: 100%;
    }

    /* Info Blocks (Mobile Stacked & Compact) */
    .info-stack {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .info-card {
      background: var(--surface);
      border: 1px solid var(--border);
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      align-items: flex-start;
      border-radius: 16px;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    .info-icon {
      font-size: 24px;
      background: rgba(255,255,255,0.05);
      width: 40px; height: 40px;
      display: flex; justify-content: center; align-items: center;
      border-radius: 10px;
    }

    .info-title { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: var(--secondary); font-weight: 700; }
    .info-text { font-size: 13px; font-weight: 600; color: #fff; word-break: break-word; }

    /* VIP Form Design */
    .form-wrapper {
      background: linear-gradient(180deg, var(--surface) 0%, rgba(10, 14, 35, 0.2) 100%);
      border: 1px solid var(--border);
      padding: 24px;
      border-radius: 24px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
      position: relative;
      overflow: hidden;
    }

    .form-wrapper::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 20px;
    }

    .form-label {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--muted);
      font-family: 'Rajdhani', sans-serif;
      letter-spacing: 0.5px;
      margin-left: 4px;
    }

    /* 16px font-size prevents iOS Auto-Zoom on focus */
    .form-input, .form-select, .form-textarea {
      background: rgba(0, 0, 0, 0.3);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 16px;
      border-radius: 14px;
      font-size: 16px; 
      width: 100%;
      font-family: inherit;
      transition: all 0.3s ease;
      appearance: none; /* Removes default OS styling */
    }

    .form-textarea { min-height: 120px; resize: vertical; }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
      border-color: var(--secondary);
      outline: none;
      background: rgba(0, 0, 0, 0.5);
      box-shadow: 0 0 0 4px rgba(0, 240, 255, 0.1);
    }

    .btn-submit {
      background: linear-gradient(135deg, var(--primary), #6D28D9);
      color: white;
      border: none;
      padding: 18px;
      border-radius: 14px;
      font-size: 16px;
      font-weight: 700;
      width: 100%;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
      transition: all 0.2s ease;
      font-family: 'Space Grotesk', sans-serif;
    }

    .btn-submit:active {
      transform: scale(0.97);
      box-shadow: 0 5px 10px rgba(139, 92, 246, 0.2);
    }

    /* System Status Alerts */
    .msg-box {
      padding: 16px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 24px;
      text-align: center;
      animation: eliteFadeUp 0.3s ease;
    }

    .success-msg { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.3); }
    .error-msg { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.3); }

    /* Custom Gaming Cursor Fix for Desktop Only */
    #cursor, #cursor-ring { pointer-events: none !important; }
    
    @media (pointer: fine) {
      body, html, button, .btn-submit, a, select, option, input, textarea { cursor: none !important; }
      /* Desktop adjustments just in case it's viewed on PC */
      .contact-grid { max-width: 900px; margin: 0 auto; flex-direction: row; }
      .info-stack { grid-template-columns: 1fr; width: 40%; }
      .form-wrapper { width: 60%; }
    }

    /* Disable Custom Cursor on Mobile */
    @media (pointer: coarse) {
      #cursor, #cursor-ring { display: none !important; }
      body, html, button, .btn-submit, a, select, option, input, textarea { cursor: auto !important; }
    }
  </style>
</head>

<body>
  <!-- Custom Cursors (Hidden on Mobile automatically via CSS) -->
  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursor-ring"></div>

  <!-- APP-STYLE STICKY HEADER -->
  <header class="app-header">
    <a href="index.php" class="back-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
      Back
    </a>
    <div class="header-title">PTA<span style="color:var(--primary)">Arena</span></div>
  </header>

  <!-- HEADER TEXT -->
  <div class="contact-header anim-1">
    <h1>Contact <span style="background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Support</span></h1>
    <p>Need help or want to report an issue? Drop us a message and our team will assist you within 24 hours.</p>
  </div>

  <!-- MAIN CONTACT LAYOUT SYSTEM -->
  <section class="contact-grid anim-2">
    
    <!-- TOP INFO CARDS (Appears as a 2x2 Grid on mobile) -->
    <div class="info-stack">
      <div class="info-card">
        <div class="info-icon">📧</div>
        <div>
          <div class="info-title">Email</div>
          <div class="info-text">support@ptaarena.pk</div>
        </div>
      </div>
      
      <div class="info-card">
        <div class="info-icon">💬</div>
        <div>
          <div class="info-title">WhatsApp</div>
          <div class="info-text">+92-300-PTAARENA</div>
        </div>
      </div>
      
      <div class="info-card">
        <div class="info-icon">🎮</div>
        <div>
          <div class="info-title">Discord</div>
          <div class="info-text">discord.gg/ptaarena</div>
        </div>
      </div>
      
      <div class="info-card">
        <div class="info-icon">⏰</div>
        <div>
          <div class="info-title">Hours (PKT)</div>
          <div class="info-text">10AM – 10PM</div>
        </div>
      </div>
    </div>

    <!-- MAIN FORM AREA -->
    <div class="form-wrapper">
      <h3 style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:24px;margin-bottom:24px;">Send a Message</h3>

      <?php echo $message_alert; ?>

      <form id="contact-form" action="" method="POST">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="contact_name" class="form-input" placeholder="e.g. Muhammad Anas" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="contact_email" class="form-input" placeholder="you@email.com" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Subject</label>
          <select class="form-select" name="contact_subject">
            <option value="General Inquiry">General Inquiry</option>
            <option value="Technical Issue">Technical Issue</option>
            <option value="Deposit/Withdrawal Issue">Deposit/Withdrawal Issue</option>
            <option value="Report Cheater">Report Cheater</option>
            <option value="Partnership / Sponsorship">Partnership / Sponsorship</option>
            <option value="Tournament Feedback">Tournament Feedback</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Your Message</label>
          <textarea name="contact_message" class="form-textarea" placeholder="Describe your issue or question in detail..." required></textarea>
        </div>
        
        <button type="submit" name="send_contact_msg" class="btn-submit">
          Send Message 🚀
        </button>
      </form>
    </div>
  </section>

  <script src="app.js"></script>
  <script>
    // Custom Desktop Cursor Setup (Won't trigger on touch devices due to CSS prevention)
    const cursor = document.getElementById('cursor');
    const cursorRing = document.getElementById('cursor-ring');

    if (cursor && cursorRing) {
      document.addEventListener('mousemove', (e) => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';

        setTimeout(() => {
          cursorRing.style.left = e.clientX + 'px';
          cursorRing.style.top = e.clientY + 'px';
        }, 40);
      });
    }
  </script>
</body>
</html>