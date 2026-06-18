<?php
session_start();
include "config.php";

// Set charset to avoid any weird diamond question marks
mysqli_set_charset($conn, "utf8mb4");

// Protection Layer
if (!isset($_SESSION['username']) && !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$session_user = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$session_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Alert messages logic
$message = ""; 
if(isset($_SESSION['msg_success'])){
    $message = "<div class='msg-box success-msg'>" . $_SESSION['msg_success'] . "</div>";
    unset($_SESSION['msg_success']);
}
if(isset($_SESSION['msg_error'])){
    $message = "<div class='msg-box error-msg'>❌ " . $_SESSION['msg_error'] . "</div>";
    unset($_SESSION['msg_error']);
}

// User current coins check
$user_q = mysqli_query($conn, "SELECT * FROM user WHERE username='$session_user' OR email='$session_email'");
$user_data = mysqli_fetch_assoc($user_q);
$current_coins = $user_data['coin'];
$username = $user_data['username'];

// DEPOSIT FORM PROCESSING
if(isset($_POST['submit_deposit'])){
    $pkg_coins = (int)$_POST['dep_pkg'];
    $method = mysqli_real_escape_string($conn, $_POST['dep_method']);
    $txid = mysqli_real_escape_string($conn, $_POST['dep_txid']);
    
    if($pkg_coins < 100) {
        $_SESSION['msg_error'] = "Deposit request failed! Minimum 100 coins required.";
        header("Location: wallet.php");
        exit();
    }
    
    // File upload
    $proof_name = "";
    if(isset($_FILES['dep_proof']) && $_FILES['dep_proof']['error'] == 0){
        $target_dir = "uploads/proofs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['dep_proof']['name'], PATHINFO_EXTENSION);
        $proof_name = "proof_" . time() . "_" . rand(1000,9999) . "." . $file_ext;
        $target_file = $target_dir . $proof_name;
        move_uploaded_file($_FILES['dep_proof']['tmp_name'], $target_file);
    }
    
    // Fixed special character dash to avoid Encoding glitch ()
    $desc = "Deposit Request - " . $method . " (TID: " . $txid . ")";
    
    $ins_query = "INSERT INTO transactions (username, description, type, amount, status) 
                  VALUES ('$username', '$desc', 'credit', '$pkg_coins', 'pending')";
                  
    if(mysqli_query($conn, $ins_query)){
        $_SESSION['msg_success'] = "🪙 Deposit request submitted successfully! Pending admin verification.";
        header("Location: wallet.php"); 
        exit();
    } else {
        $_SESSION['msg_error'] = "Database SQL Error: " . mysqli_error($conn);
        header("Location: wallet.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <title>Wallet — Pro Tournament Arena</title>
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

    .msg-box { padding: 14px; border-radius: 6px; font-size: 14px; font-weight: 600; margin-bottom: 24px; text-align: center; }
    .success-msg { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.3); }
    .error-msg { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.3); }
    
    .b-success { background: rgba(46, 213, 115, 0.2); color: #2ed573; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .b-danger { background: rgba(255, 71, 87, 0.2); color: #ff4757; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .b-warning { background: rgba(255, 184, 0, 0.2); color: #ffb800; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    
    #cursor, #cursor-ring { pointer-events: none !important; }
    body, html, select, option, input { cursor: none !important; }
    
    /* Ensure buttons are clickable */
    button, .btn, a { cursor: pointer !important; position: relative; z-index: 10; pointer-events: auto !important; }

    .dashboard-layout {
      display: grid;
      grid-template-columns: 1fr;
      min-height: 100vh;
      padding-top: 75px;
      box-sizing: border-box;
    }

    .dashboard-content {
      padding: 32px;
      box-sizing: border-box;
      max-width: 1200px;
      margin: 0 auto;
      width: 100%;
    }

    /* ─── COIN VECTOR RENDER ─── */
    .pta-coin-inline {
        display: inline-block;
        width: 16px;
        height: 16px;
        vertical-align: middle;
        margin-right: 4px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300f0ff" stroke-width="6" fill="rgba(10,13,36,0.95)" /><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="50" font-family="Rajdhani,sans-serif" font-weight="900" fill="%2300f0ff">P</text></svg>');
        background-size: contain;
        background-repeat: no-repeat;
    }

    .pta-coin-large {
        width: 64px;
        height: 64px;
        margin: 0 auto 12px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300f0ff" stroke-width="5" fill="rgba(5,7,20,0.9)" /><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="46" font-family="Rajdhani,sans-serif" font-weight="900" fill="%2300f0ff">P</text></svg>');
        background-size: contain;
        background-repeat: no-repeat;
    }

    /* ─── WALLET CARD & FIXES ─── */
    .wallet-card {
        background: radial-gradient(circle at top right, rgba(123, 46, 255, 0.15), rgba(12, 15, 36, 0.95));
        border: 2px solid rgba(0, 240, 255, 0.2);
        border-radius: 16px;
        padding: 32px 24px;
        text-align: center;
        box-shadow: 0 12px 40px rgba(0,0,0,0.5);
    }
    .wallet-label { font-size: 11px; letter-spacing: 2px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; }
    .wallet-balance { font-family: 'Rajdhani', sans-serif; font-size: 42px; font-weight: 900; color: #fff; line-height: 1; }
    .wallet-currency { font-size: 13px; color: var(--neon-cyan); font-weight: 700; margin-top: 6px; text-transform: uppercase; }

    /* Clean Uniform Action Buttons Layout */
    .wallet-action-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        margin-top: 24px;
        width: 100%;
    }
    .btn-wallet-dep, .btn-wallet-wd {
        flex: 1;
        max-width: 180px;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 12px 20px;
        border-radius: 8px;
        transition: all 0.25s ease;
        text-align: center;
        border: none;
    }
    .btn-wallet-dep {
        background: linear-gradient(135deg, var(--cyber-orange), #ff6b6b);
        color: #050714;
        box-shadow: 0 4px 15px rgba(255, 159, 67, 0.3);
    }
    .btn-wallet-dep:hover {
        box-shadow: 0 6px 20px rgba(255, 159, 67, 0.5);
        transform: translateY(-2px);
    }
    .btn-wallet-wd {
        background: transparent;
        color: var(--neon-cyan);
        border: 2px solid var(--neon-cyan);
        box-shadow: 0 4px 15px rgba(0, 240, 255, 0.1);
    }
    .btn-wallet-wd:hover {
        background: rgba(0, 240, 255, 0.1);
        box-shadow: 0 6px 20px rgba(0, 240, 255, 0.3);
        transform: translateY(-2px);
    }

    /* ─── PACKAGES HUD GRID ─── */
    .coin-packages {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 16px;
    }

    .coin-pkg {
        background: #0c0f24;
        border: 1px solid rgba(123, 46, 255, 0.2);
        border-radius: 12px;
        padding: 24px 16px;
        text-align: center;
        position: relative;
        transition: transform 0.25s ease, border-color 0.25s ease;
    }
    .coin-pkg:hover {
        transform: translateY(-5px);
        border-color: var(--neon-cyan);
    }
    
    .pkg-coin-render {
        width: 44px; height: 44px; margin: 0 auto 12px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300f0ff" stroke-width="6" fill="none" /><text x="50%" y="60%" dominant-baseline="middle" text-anchor="middle" font-size="48" font-family="Rajdhani,sans-serif" font-weight="900" fill="%23fff">P</text></svg>');
        background-size: contain; background-repeat: no-repeat;
    }

    .pkg-coins { font-family: 'Rajdhani', sans-serif; font-size: 32px; font-weight: 900; color: #fff; }
    .pkg-bonus { font-size: 11px; color: #2ed573; font-weight: 700; margin: 6px 0 14px; text-transform: uppercase; }
    .pkg-price { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px; }
    .pkg-price span { color: #fff; font-weight: 700; font-size: 15px; }

    /* ─── CYBER INPUTS SYSTEM ─── */
    .cyber-input-field, .cyber-select, .form-file-input {
        background-color: #050714 !important;
        color: #ffffff !important;
        border: 1px solid rgba(0, 240, 255, 0.2) !important;
        padding: 14px !important;
        border-radius: 8px !important;
        width: 100%;
        box-sizing: border-box;
        outline: none;
        font-size: 15px;
    }
    .cyber-select option { background-color: #0c0f24 !important; color: #ffffff !important; }

    .custom-input-wrapper { position: relative; display: flex; align-items: center; width: 100%; }
    .cyber-number-input { padding-right: 65px !important; font-family: 'Rajdhani', sans-serif !important; font-weight: 700; }
    .input-suffix-coin { position: absolute; right: 16px; color: var(--neon-cyan); font-family: 'Rajdhani', sans-serif; font-weight: 700; }
    .input-hint-text { display: block; font-size: 11px; color: var(--text-muted); margin-top: 6px; }

    .cyber-number-input.input-invalid { border-color: var(--cyber-danger) !important; color: var(--cyber-danger) !important; }
    .input-hint-text.text-invalid { color: var(--cyber-danger) !important; font-weight: 600; }

    /* Desktop View Data Table Style */
    .data-table-wrap { width: 100%; border-radius: 8px; background: #0c0f24; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; }
    .data-table th, .data-table td { padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .data-table th { background: rgba(0,0,0,0.2); font-family: 'Rajdhani', sans-serif; font-weight: 700; text-transform: uppercase; color: var(--neon-cyan); letter-spacing: 0.5px; }

    /* ─── 🔥 MODAL BASE SYSTEM (Fixed the invisble block issue) ─── */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.open {
        opacity: 1;
        visibility: visible;
    }
    .modal {
        background: var(--panel-dark);
        border: 1px solid rgba(0, 240, 255, 0.2);
        border-radius: 16px;
        width: 90%;
        max-width: 450px;
        padding: 24px;
        transform: translateY(30px);
        transition: all 0.3s ease;
    }
    .modal-overlay.open .modal {
        transform: translateY(0);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-bottom: 12px;
    }
    .modal-title { font-family: 'Rajdhani', sans-serif; font-size: 20px; font-weight: 700; color: #fff; }
    .modal-close { background: none; border: none; color: var(--text-muted); font-size: 28px; padding: 0; margin: 0; transition: color 0.2s; }
    .modal-close:hover { color: var(--cyber-danger); }

    /* ─── MOBILE RESPONSIVE ADAPTATIONS ─── */
    @media (max-width: 768px) {
        body, html, button, .btn, a, select, option, input { 
            cursor: default !important;
        }
        #cursor, #cursor-ring { display: none !important; }
        .dashboard-layout { padding-top: 70px; }
        .dashboard-content { padding: 16px; padding-bottom: 80px; }

        .wallet-card { padding: 20px 14px; }
        .wallet-balance { font-size: 36px; }
        .wallet-action-container {
            flex-direction: row !important;
            gap: 12px !important;
        }
        .btn-wallet-dep, .btn-wallet-wd { 
            max-width: 100%; 
            padding: 12px 10px; 
            font-size: 13px;
        }

        .coin-packages {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .coin-pkg { padding: 16px 10px; border-radius: 8px; }
        .pkg-coins { font-size: 24px; }
        .pkg-price span { font-size: 14px; }

        .data-table, .data-table thead, .data-table tbody, .data-table th, .data-table td, .data-table tr {
            display: block;
        }
        .data-table thead { display: none; }
        .data-table tbody tr {
            background: #0c0f24;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 12px;
            padding: 14px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .data-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(255,255,255,0.03);
            text-align: right;
            font-size: 13px;
        }
        .data-table td:last-child { border-bottom: none; }
        
        .data-table td::before {
            content: attr(data-label);
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            float: left;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        /* Mobile specific bottom-sheet modal */
        .modal {
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 16px 16px 0 0 !important;
            position: absolute;
            bottom: -100%;
            left: 0;
            margin: 0;
            transform: none !important;
            transition: bottom 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            background: #0c0f24;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.6);
        }
        .modal-overlay.open .modal { bottom: 0; }
        .modal-overlay { align-items: flex-end; }
        .modal-body { max-height: 75vh; overflow-y: auto; padding-bottom: 40px; }
    }
  </style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursor-ring"></div>

<?php include "header.php"; ?>

<div class="dashboard-layout">
  <main class="dashboard-content">
    
    <?php echo $message; ?>

    <div class="page-title"><i class="pta-coin-inline" style="width:24px; height:24px;"></i> Coin Wallet</div>
    <div class="page-sub">Manage your Arena Coins — buy, earn, and withdraw.</div>
    
    <div class="wallet-card reveal" style="margin-bottom: 32px;">
      <div class="pta-coin-large"></div>
      <div class="wallet-label">ARENA COIN BALANCE</div>
      <div class="wallet-balance" id="balance-display"><?php echo number_format((int)$current_coins); ?></div>
      <div class="wallet-currency">PTA Hex Coins</div>
      
      <div class="wallet-action-container">
        <button class="btn-wallet-dep" onclick="openModal('deposit-modal')">+ Deposit</button>
        <button class="btn-wallet-wd" onclick="window.location.href='withdraw.php';">- Withdraw</button>
      </div>
    </div>
    
    <h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:20px;margin-bottom:16px">Buy Arena Coins</h3>
    
    <div class="coin-packages reveal">
      <div class="coin-pkg">
          <div class="pkg-coin-render"></div>
          <div class="pkg-coins">100</div>
          <div class="pkg-bonus">Starter Pack</div>
          <div class="pkg-price">Price: <span>100 PKR</span></div>
          <button class="btn btn-primary btn-sm" style="width:100%" onclick="buyCoins(100)">Buy</button>
      </div>
      
      <div class="coin-pkg" style="border-color:var(--neon-purple)">
          <div style="position:absolute;top:0;left:0;right:0;text-align:center;background:var(--neon-purple);font-size:9px;letter-spacing:1px;text-transform:uppercase;padding:2px;color:#fff;border-radius:6px 6px 0 0">HOT</div>
          <div class="pkg-coin-render" style="margin-top:8px;"></div>
          <div class="pkg-coins">550</div>
          <div class="pkg-bonus">+50 Free</div>
          <div class="pkg-price">Price: <span>500 PKR</span></div>
          <button class="btn btn-primary btn-sm" style="width:100%" onclick="buyCoins(550)">Buy</button>
      </div>
      
      <div class="coin-pkg">
          <div class="pkg-coin-render"></div>
          <div class="pkg-coins">1,200</div>
          <div class="pkg-bonus">+200 Free</div>
          <div class="pkg-price">Price: <span>1000 PKR</span></div>
          <button class="btn btn-primary btn-sm" style="width:100%" onclick="buyCoins(1200)">Buy</button>
      </div>
      
      <div class="coin-pkg">
          <div class="pkg-coin-render"></div>
          <div class="pkg-coins">6,500</div>
          <div class="pkg-bonus">+1500 Free</div>
          <div class="pkg-price">Price: <span>5000 PKR</span></div>
          <button class="btn btn-primary btn-sm" style="width:100%" onclick="buyCoins(6500)">Buy</button>
      </div>
    </div>
    
    <h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:20px;margin:32px 0 16px">Transaction History</h3>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="tx-table">
          <?php
          $tx_query = mysqli_query($conn, "SELECT * FROM transactions WHERE username='$username' ORDER BY id DESC");
          
          if(mysqli_num_rows($tx_query) == 0) {
              echo '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:40px">No transactions yet</td></tr>';
          } else {
              while($row = mysqli_fetch_assoc($tx_query)) {
                  $date_formatted = date("d/m/Y", strtotime($row['date']));
                  $type_badge = ($row['type'] == 'credit') ? '<span class="b-success">Credit</span>' : '<span class="b-danger">Debit</span>';
                  $amount_color = ($row['type'] == 'credit') ? '#2ed573' : '#ff4757';
                  $amount_sign = ($row['type'] == 'credit') ? '+' : '−';
                  
                  if($row['status'] == 'completed') { $status_badge = '<span class="b-success">Completed</span>'; }
                  else if($row['status'] == 'pending') { $status_badge = '<span class="b-warning">Pending</span>'; }
                  else { $status_badge = '<span class="b-danger">Rejected</span>'; }
                  
                  echo "<tr>
                          <td data-label='Date' style='font-size:12px;color:var(--text-muted)'>$date_formatted</td>
                          <td data-label='Details'>".htmlspecialchars($row['description'])."</td>
                          <td data-label='Type'>$type_badge</td>
                          <td data-label='Amount' style=\"font-family:'Rajdhani',sans-serif;font-weight:700;color:$amount_color\">$amount_sign".number_format($row['amount'])."</td>
                          <td data-label='Status'>$status_badge</td>
                        </tr>";
              }
          }
          ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<div class="modal-overlay" id="deposit-modal" onclick="closeModal('deposit-modal')">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-title">Deposit Coins</div>
      <button class="modal-close" onclick="closeModal('deposit-modal')">×</button>
    </div>
    <div class="modal-body">
      <p style="color:var(--text-muted);margin-bottom:16px;font-size:13px">Send payment to one of our accounts below, then submit your transaction ID along with screenshot proof.</p>
      <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px">
        <div style="background:rgba(255,255,255,.03);padding:10px;border:1px solid rgba(255,255,255,0.05);border-radius:6px"><div style="font-size:10px;color:var(--text-muted)">Easypaisa Account</div><div style="font-weight:700;font-size:14px;color:#fff">0300-1234567 — Arena PTA</div></div>
        <div style="background:rgba(255,255,255,.03);padding:10px;border:1px solid rgba(255,255,255,0.05);border-radius:6px"><div style="font-size:10px;color:var(--text-muted)">JazzCash Account</div><div style="font-weight:700;font-size:14px;color:#fff">0321-7654321 — Arena PTA</div></div>
      </div>
      
      <form id="deposit-form" action="wallet.php" method="POST" enctype="multipart/form-data" onsubmit="return validateDepositForm()"> 
        
        <div class="form-group">
          <label class="form-label">Coin Package</label>
          <div class="custom-input-wrapper">
            <input type="number" class="cyber-input-field cyber-number-input" id="dep-pkg" name="dep_pkg" placeholder="minimum 100" min="100" value="100" required>
            <span class="input-suffix-coin">Coins</span>
          </div>
          <small class="input-hint-text" id="hint-msg">Kam az kam 100 coins purchase karna zaroori hain.</small>
        </div>

        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select class="cyber-select" name="dep_method">
            <option value="Easypaisa">Easypaisa</option>
            <option value="JazzCash">JazzCash</option>
            <option value="Bank Transfer">Bank Transfer</option>
          </select>
        </div>

        <div class="form-group">
            <label class="form-label">Transaction ID</label>
            <input type="text" class="cyber-input-field" name="dep_txid" placeholder="e.g. EP-1234567890" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Screenshot Proof</label>
            <input type="file" class="form-file-input" name="dep_proof" accept="image/*" required>
        </div>
        
        <button type="submit" name="submit_deposit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:10px;padding:14px !important">Submit Deposit Request</button>
      </form>
    </div>
  </div>
</div>

<?php include "navbar.php"; ?>

<script src="app.js"></script>
<script>
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

function buyCoins(coins){ 
    openModal('deposit-modal'); 
    const inputField = document.getElementById('dep-pkg');
    inputField.value = coins; 
    checkValidation(inputField); 
}

const coinInput = document.getElementById('dep-pkg');
const hintMsg = document.getElementById('hint-msg');

function checkValidation(inputElement) {
    let val = parseInt(inputElement.value);
    if(isNaN(val) || val < 100) {
        inputElement.classList.add('input-invalid');
        hintMsg.classList.add('text-invalid');
        hintMsg.innerText = "❌ Error: Amount 100 se kam nahi ho sakti!";
    } else {
        inputElement.classList.remove('input-invalid');
        hintMsg.classList.remove('text-invalid');
        hintMsg.innerText = "Kam az kam 100 coins purchase karna zaroori hain.";
    }
}

if(coinInput) {
    coinInput.addEventListener('input', function() { checkValidation(this); });
}

function validateDepositForm() {
    const val = parseInt(coinInput.value);
    if(isNaN(val) || val < 100) {
        checkValidation(coinInput);
        coinInput.focus();
        return false; 
    }
    return true;
}
</script>
</body>
</html>