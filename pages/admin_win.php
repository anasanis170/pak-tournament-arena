<?php
session_start();
include "config.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$admin_check = mysqli_query($conn, "SELECT role FROM user WHERE username = '".mysqli_real_escape_string($conn, $_SESSION['username'])."'");
$admin_data = mysqli_fetch_assoc($admin_check);
if(!$admin_data || $admin_data['role'] !== 'admin'){
    header("Location: dashboard.php");
    exit();
}

$message = "";

if(isset($_POST['add_coins'])){
    $search_type = $_POST['search_type'];
    $search_value = mysqli_real_escape_string($conn, trim($_POST['search_value']));
    $coin_amount = (int)$_POST['coin_amount'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    if($coin_amount <= 0) {
        $message = "<div class='msg-box error-msg'>❌ Amount must be greater than 0!</div>";
    } else {
        $user_query = null;
        if($search_type == 'uid') {
            $user_query = mysqli_query($conn, "SELECT * FROM user WHERE id = '$search_value'");
        } else {
            $user_query = mysqli_query($conn, "SELECT * FROM user WHERE email = '$search_value' OR username = '$search_value'");
        }
        
        if($user_query && mysqli_num_rows($user_query) > 0) {
            $user = mysqli_fetch_assoc($user_query);
            $user_name = $user['username'];
            $user_id = $user['id'];
            
            $add_query = mysqli_query($conn, "UPDATE user SET coin = coin + $coin_amount WHERE id = $user_id");
            
            $tx_desc = "Admin Credit: $reason";
            $tx_query = "INSERT INTO transactions (username, description, type, amount, status, date) 
                         VALUES ('$user_name', '$tx_desc', 'credit', $coin_amount, 'completed', NOW())";
            mysqli_query($conn, $tx_query);
            
            // Notification
            $notif_title = "💎 Coins Added!";
            $notif_msg = "Admin added $coin_amount coins. Reason: $reason";
            mysqli_query($conn, "INSERT INTO notifications (user_id, type, title, msg, is_read, created_at) 
                                 VALUES ($user_id, 'success', '$notif_title', '$notif_msg', 0, NOW())");
            
            if($add_query) {
                $message = "<div class='msg-box success-msg'>✅ Success! 🪙 $coin_amount coins added to <b>$user_name</b></div>";
            } else {
                $message = "<div class='msg-box error-msg'>❌ Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div class='msg-box error-msg'>❌ User not found!</div>";
        }
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Add Coins — PTA Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
        --bg: #080b11; --card: #0e1322; --cyan: #00E5FF; --purple: #7B2EFF;
        --text: #fff; --muted: #6c7a9c; --border: rgba(255,255,255,0.05);
        --success: #2ed573; --danger: #ff4757;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:var(--bg); color:var(--text); font-family:'Rajdhani',sans-serif; overflow-x:hidden; }

    @keyframes fadeUp { from{opacity:0;transform:translateY(15px);} to{opacity:1;transform:translateY(0);} }
    .anim { animation:fadeUp 0.5s ease forwards; }

    /* TOP BAR */
    .topbar {
        position:fixed; top:0; left:0; right:0; height:56px; background:var(--card);
        border-bottom:1px solid var(--border); display:flex; align-items:center;
        justify-content:space-between; padding:0 16px; z-index:1000;
    }
    .topbar-logo { font-size:20px; font-weight:800; color:#fff; text-decoration:none; }
    .topbar-logo span { color:var(--purple); }
    .hamburger { background:none; border:none; color:#fff; font-size:22px; cursor:pointer; padding:8px; border-radius:6px; }

    /* SIDEBAR */
    .sidebar-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1999; display:none; }
    .sidebar-overlay.active { display:block; }
    .sidebar {
        position:fixed; top:0; left:-270px; width:270px; height:100%; background:var(--card);
        border-right:1px solid var(--border); z-index:2000; transition:left 0.3s; overflow-y:auto; padding:20px;
    }
    .sidebar.active { left:0; }
    .sidebar-close { position:absolute; top:12px; right:12px; background:none; border:none; color:#fff; font-size:20px; cursor:pointer; }
    .sidebar-title { font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); font-weight:700; margin:16px 0 8px; }
    .sidebar-link {
        display:flex; align-items:center; gap:10px; color:#94a3b8; text-decoration:none;
        padding:10px 12px; border-radius:6px; font-size:14px; font-weight:600; margin-bottom:2px;
    }
    .sidebar-link:hover { background:rgba(123,46,255,0.08); color:#fff; }
    .sidebar-link.active { background:var(--purple); color:#fff; }

    /* MAIN */
    .main-container { max-width:700px; margin:0 auto; padding:70px 14px 40px; }

    .page-title { font-size:clamp(22px,4vw,28px); font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--cyan); margin-bottom:4px; }
    .page-sub { color:var(--muted); font-size:13px; margin-bottom:24px; }

    .msg-box { padding:14px; border-radius:8px; font-size:14px; font-weight:600; margin-bottom:20px; text-align:center; }
    .success-msg { background:rgba(46,213,115,0.12); color:var(--success); border:1px solid rgba(46,213,115,0.25); }
    .error-msg { background:rgba(255,71,87,0.12); color:var(--danger); border:1px solid rgba(255,71,87,0.25); }

    .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px 18px; }
    .form-group { margin-bottom:16px; }
    .form-label { display:block; font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:6px; font-weight:700; }
    .form-input, .form-select {
        background:#050714; color:#fff; border:1px solid rgba(0,240,255,0.15);
        padding:12px 14px; border-radius:8px; width:100%; font-size:14px; font-family:'Rajdhani',sans-serif;
    }
    .form-input:focus, .form-select:focus { border-color:var(--cyan); outline:none; }
    .form-select option { background:var(--card); color:#fff; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

    .btn-add {
        background:linear-gradient(135deg,var(--success),#00b894); color:#000; padding:14px;
        border:none; border-radius:10px; font-weight:800; font-size:15px; text-transform:uppercase;
        cursor:pointer; width:100%; font-family:'Rajdhani',sans-serif; letter-spacing:1px; transition:all 0.3s;
    }
    .btn-add:hover { box-shadow:0 0 25px rgba(46,213,115,0.5); transform:translateY(-2px); }

    .quick-info { background:rgba(0,229,255,0.04); border:1px solid rgba(0,229,255,0.12); border-radius:8px; padding:12px; margin-top:16px; font-size:12px; color:var(--muted); }
    .quick-info span { color:var(--cyan); font-weight:600; }

    @media(min-width:769px) {
        .hamburger { display:none; }
        .sidebar { left:0; }
        .sidebar-close { display:none; }
        .sidebar-overlay { display:none !important; }
        .main-container { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
    @media(max-width:480px) {
        .form-row { grid-template-columns:1fr; }
        .card { padding:18px 14px; }
    }
  </style>
</head>
<body>

<!-- TOP BAR -->
<header class="topbar">
    <a href="admin-tournaments.php" class="topbar-logo">⚡ PTA<span>Control</span></a>
    <button class="hamburger" onclick="toggleSidebar()">☰</button>
</header>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
    <div style="font-size:20px;font-weight:800;margin-bottom:20px;">⚡ PTA<span style="color:var(--purple);">Control</span></div>
    
    <div class="sidebar-title">💰 Financials</div>
    <a href="admin.php" class="sidebar-link">🪙 Wallet Requests</a>
    <a href="admin_win.php" class="sidebar-link active">💎 Add Coins</a>

    <div class="sidebar-title">🎮 Game Core</div>
    <a href="admin-tournaments.php" class="sidebar-link">🏆 Manage Tournaments</a>
    <a href="admin_id_pass.php" class="sidebar-link">🔑 Room ID/Pass</a>
    <a href="admin_win.php" class="sidebar-link">👑 Declare Winner</a>
    <a href="admin-live.php" class="sidebar-link">🔴 Live Match</a>
    <a href="admin_view.php" class="sidebar-link">👥 View Participants</a>
    <a href="admin-send-notification.php" class="sidebar-link">🔔 Send Notification</a>

    <div class="sidebar-title">👤 Users</div>
    <a href="admin-users.php" class="sidebar-link">👤 Total Players</a>
    <a href="admin-tickets.php" class="sidebar-link">💬 Support Tickets</a>

    <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px;">
        <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
        <a href="logout.php" class="sidebar-link" style="color:#ff4757;">🚪 Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-container">

    <?php echo $message; ?>

    <div class="page-title anim">💎 Add Coins to User</div>
    <div class="page-sub anim" style="animation-delay:0.1s;">Manually add coins to any user by UID or Email</div>
    
    <div class="card anim" style="animation-delay:0.15s;">
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Search By</label>
                <select class="form-select" name="search_type" required>
                    <option value="uid">🆔 User ID (UID)</option>
                    <option value="email">📧 Email or Username</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">User ID / Email / Username</label>
                <input type="text" class="form-input" name="search_value" placeholder="e.g. 5 ya user@email.com" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">🪙 Coin Amount</label>
                    <input type="number" class="form-input" name="coin_amount" placeholder="e.g. 500" required min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">📝 Reason</label>
                    <input type="text" class="form-input" name="reason" placeholder="e.g. Tournament Winner" required>
                </div>
            </div>
            
            <button type="submit" name="add_coins" class="btn-add">💎 Add Coins Now</button>
        </form>
        
        <div class="quick-info">
            <span>💡 Tip:</span> User ID se search karna fastest hai. Email ya Username se bhi user mil jayega.
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}
document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('overlay').classList.remove('active');
    });
});
</script>
</body>
</html>