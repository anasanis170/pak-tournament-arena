<?php
session_start();
include "config.php";

// Protection Layer
if (!isset($_SESSION['username']) && !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$session_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$session_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$message = "";
$msg_type = "";

// User ke current coins check karne ke liye (Safe matching check)
$user_q = mysqli_query($conn, "SELECT * FROM user WHERE username='$session_user' OR email='$session_email'");
$user_data = mysqli_fetch_assoc($user_q);
$current_coins = $user_data['coin'];
$session_user = $user_data['username']; // Exact username fallback

// Withdraw Request Submit Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_withdraw'])) {
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $account_num = mysqli_real_escape_string($conn, $_POST['account_number']);
    $account_name = mysqli_real_escape_string($conn, $_POST['account_name']);
    $amount = (int)$_POST['withdraw_amount'];
    
    $description = "Withdraw to " . strtoupper($method) . " ($account_num) - Name: $account_name";

    // ─── LIMIT SET TO 300 COINS MINIMUM ───
    if ($amount < 300) { 
        $message = "❌ Minimum withdrawal limit is 300 coins!";
        $msg_type = "error";
    } elseif ($amount > $current_coins) {
        $message = "❌ Aapke paas itne coins nahi hain! Current Balance: 🪙 " . number_format($current_coins);
        $msg_type = "error";
    } else {
        // 1. User ke account se coins minus karo
        $deduct_q = mysqli_query($conn, "UPDATE user SET coin = coin - $amount WHERE username = '$session_user'");
        
        // 2. Transactions table mein 'debit' type ke sath insert karein
        $insert_q = mysqli_query($conn, "INSERT INTO transactions (username, type, amount, description, status, date) 
                                         VALUES ('$session_user', 'debit', $amount, '$description', 'pending', NOW())");
        
        if ($deduct_q && $insert_q) {
            // Success hone par direct dashboard par redirect green banner ke sath
            header("Location: dashboard.php?msg=withdraw_success");
            exit();
        } else {
            $message = "❌ Database error! Please try again.";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Withdraw Coins — Pro Tournament Arena</title>
  <link rel="stylesheet" href="style.css">
  <style>
    :root {
        --bg-main: #050714;
        --panel-dark: #0c0f24;
        --neon-cyan: #00f0ff;
        --neon-purple: #7b2eff;
        --text-muted: #8a8fa3;
        --cyber-danger: #ff4757;
    }

    /* Pure page aur inputs se system mouse ko khatam karo */
    body, html, .withdraw-card, .form-group, .form-input, select, option, button, a, label, .sidebar-link, input {
      cursor: none !important;
    }
    
    .withdraw-card { 
      max-width: 540px; 
      margin: 20px auto; 
      background: var(--panel-dark, #0c0f24); 
      border: 1px solid rgba(0, 240, 255, 0.2); 
      padding: 35px; 
      border-radius: 12px; 
      position: relative; 
      z-index: 1;
      box-shadow: 0 12px 40px rgba(0,0,0,0.5);
    }

    .withdraw-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple));
    }
    
    .balance-badge { 
      background: rgba(46, 213, 115, 0.1); 
      color: #2ed573; 
      padding: 12px 18px; 
      border-radius: 6px; 
      display: inline-block; 
      font-weight: 700; 
      margin-bottom: 25px;
      font-family: 'Rajdhani', sans-serif;
      font-size: 16px;
      border: 1px solid rgba(46, 213, 115, 0.2);
    }
    
    .msg-box { padding: 14px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: 600; }
    .msg-success { background: rgba(46,213,115,0.15); color: #2ed573; border: 1px solid rgba(46,213,115,0.3); }
    .msg-error { background: rgba(255,71,87,0.15); color: #ff4757; border: 1px solid rgba(255,71,87,0.3); }
    
    .cursor, .cursor-ring {
      position: fixed;
      pointer-events: none;
      transform: translate(-50%, -50%);
      z-index: 999999 !important;
    }

    /* ─── CYBER THEME INPUTS AND DROP DOWN STYLING ─── */
    .cyber-select, .cyber-input-field {
        background-color: #050714 !important;
        color: #ffffff !important;
        border: 1px solid rgba(0, 240, 255, 0.2) !important;
        padding: 12px 14px !important;
        border-radius: 6px !important;
        width: 100%;
        box-sizing: border-box;
        outline: none;
        font-size: 14px;
        transition: all 0.25s ease-in-out;
    }
    .cyber-select option {
        background-color: #0c0f24 !important;
        color: #ffffff !important;
        padding: 12px;
    }
    .cyber-select:focus, .cyber-input-field:focus {
        border-color: var(--neon-cyan) !important;
        box-shadow: 0 0 10px rgba(0, 240, 255, 0.3) !important;
    }

    /* Real-Time Validator System Styles */
    .custom-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .cyber-number-input {
        padding-right: 65px !important;
        font-family: 'Rajdhani', sans-serif !important;
        font-weight: 700 !important;
        font-size: 16px !important;
    }
    .input-suffix-coin {
        position: absolute;
        right: 16px;
        color: var(--neon-cyan);
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        pointer-events: none;
    }
    .input-hint-text {
        display: block;
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 6px;
        transition: color 0.25s ease;
    }

    /* Active Error Triggers (JS) */
    .cyber-input-field.input-invalid {
        border-color: var(--cyber-danger) !important;
        box-shadow: 0 0 15px rgba(255, 71, 87, 0.4) !important;
        color: var(--cyber-danger) !important;
    }
    .cyber-input-field.input-invalid + .input-suffix-coin {
        color: var(--cyber-danger) !important;
    }
    .input-hint-text.text-invalid {
        color: var(--cyber-danger) !important;
        font-weight: 600;
    }
  </style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursor-ring"></div>

<nav class="navbar" id="navbar">
  <a href="../index.php" class="nav-logo">
    <div class="logo-icon">⚡</div>PTA<span>Arena</span>
  </a>
  <div class="nav-actions"><a href="wallet.php" class="btn btn-outline btn-sm">← Back to Wallet</a></div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-title">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="tournaments.html" class="sidebar-link"><span class="s-icon">🏆</span> Tournaments</a>
      <a href="live-match.html" class="sidebar-link"><span class="s-icon">🔴</span> Live Match</a>
      <a href="results.html" class="sidebar-link"><span class="s-icon">📋</span> Results</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-title">My Account</div>
      <a href="wallet.php" class="sidebar-link active"><span class="s-icon">🪙</span> Coin Wallet</a>
      <a href="teams.html" class="sidebar-link"><span class="s-icon">👥</span> My Teams</a>
      <a href="notifications.html" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="profile.html" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-title">Explore</div>
      <a href="leaderboard.html" class="sidebar-link"><span class="s-icon">📊</span> Leaderboard</a>
      <a href="support.html" class="sidebar-link"><span class="s-icon">💬</span> Support</a>
      <a href="logout.php" class="sidebar-link"><span class="s-icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="dashboard-content">
    <div class="withdraw-card">
      <h2 style="font-family:'Rajdhani',sans-serif; font-weight:700; font-size:28px; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.5px; color:#fff;">🪙 Withdraw Cash</h2>
      <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px;">Jeete hue coins ko apne mobile wallet ya bank account mein instantly secure transfer karein.</p>
      
      <div class="balance-badge">Available Balance: 🪙 <?php echo number_format($current_coins); ?> Coins</div>

      <?php if(!empty($message)): ?>
          <div class="msg-box msg-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
      <?php endif; ?>

      <form action="withdraw.php" method="POST" onsubmit="return validateWithdrawForm()">
        <div class="form-group">
          <label class="form-label">Select Payment Method</label>
          <select class="form-select cyber-select" name="payment_method" required>
             <option value="EasyPaisa">EasyPaisa</option>
             <option value="JazzCash">JazzCash</option>
             <option value="Bank Transfer">Any Bank Transfer</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Account Number / Mobile Number</label>
          <input type="text" class="cyber-input-field" name="account_number" placeholder="e.g. 03001234567" required>
        </div>

        <div class="form-group">
          <label class="form-label">Account Holder Name</label>
          <input type="text" class="cyber-input-field" name="account_name" placeholder="Enter account title" required>
        </div>

        <div class="form-group">
          <label class="form-label">Coins to Withdraw</label>
          <div class="custom-input-wrapper">
            <input 
              type="number" 
              class="cyber-input-field cyber-number-input" 
              id="withdraw-amt" 
              name="withdraw_amount" 
              placeholder="Minimum 300 coins" 
              min="300" 
              required
            >
            <span class="input-suffix-coin" id="coin-suffix">Coins</span>
          </div>
          <small class="input-hint-text" id="hint-msg">Kam az kam 300 coins ka withdraw le saktay hain.</small>
        </div>

        <div style="background:rgba(255,184,0,.06); border:1px solid rgba(255,184,0,.15); padding:12px; margin-bottom:20px; border-radius:6px;">
           <div style="font-size:12px; color:#ffb800; font-weight:600;">📌 Processing Note: Withdrawals are audited and completed within 24 hours. Platform fee apply.</div>
        </div>

        <button type="submit" name="submit_withdraw" class="btn btn-accent" style="width: 100%; justify-content:center; padding:14px; font-weight:700;">
          Request Withdrawal 🚀
        </button>
      </form>
    </div>
  </main>
</div>

<script src="app.js"></script>
<script>
// Custom Gaming Cursor System
const cursor = document.getElementById('cursor');
const cursorRing = document.getElementById('cursor-ring');

if(cursor && cursorRing) {
    document.addEventListener('mousemove', (e) => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
        
        setTimeout(() => {
            cursorRing.style.left = e.clientX + 'px';
            cursorRing.style.top = e.clientY + 'px';
        }, 40);
    });
}

// REAL-TIME VALIDATION FOR 300 LIMIT
const withdrawInput = document.getElementById('withdraw-amt');
const hintMsg = document.getElementById('hint-msg');
const maxBalance = <?php echo (int)$current_coins; ?>;

function checkWithdrawValidation(inputElement) {
    let val = parseInt(inputElement.value);
    
    if(isNaN(val) || val < 300) {
        inputElement.classList.add('input-invalid');
        hintMsg.classList.add('text-invalid');
        hintMsg.innerText = "❌ Error: Minimum 300 coins ka withdraw lazmi hai!";
    } else if (val > maxBalance) {
        inputElement.classList.add('input-invalid');
        hintMsg.classList.add('text-invalid');
        hintMsg.innerText = "❌ Error: Aap ke paas sirf " + maxBalance + " coins hain!";
    } else {
        inputElement.classList.remove('input-invalid');
        hintMsg.classList.remove('text-invalid');
        hintMsg.innerText = "Kam az kam 300 coins ka withdraw le saktay hain.";
    }
}

withdrawInput.addEventListener('input', function() { checkWithdrawValidation(this); });
withdrawInput.addEventListener('blur', function() { checkWithdrawValidation(this); });

function validateWithdrawForm() {
    const val = parseInt(withdrawInput.value);
    if(isNaN(val) || val < 300 || val > maxBalance) {
        checkWithdrawValidation(withdrawInput);
        withdrawInput.focus();
        return false; 
    }
    return true;
}
</script>
</body>
</html>