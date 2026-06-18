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

if(isset($_POST['approve_tx'])){
    $tx_id = (int)$_POST['tx_id'];
    $tx_query = mysqli_query($conn, "SELECT username, amount, type, status FROM transactions WHERE id=$tx_id");
    $tx_data = mysqli_fetch_assoc($tx_query);
    
    if($tx_data) {
        $user_name = $tx_data['username'];
        $coins_amount = (int)$tx_data['amount'];
        $current_status = $tx_data['status'];
        $tx_type = $tx_data['type']; 
        
        if($current_status == 'pending') {
            if($tx_type == 'credit') {
                $user_update = "UPDATE user SET coin = coin + $coins_amount WHERE username='$user_name'";
                if(mysqli_query($conn, $user_update)) {
                    mysqli_query($conn, "UPDATE transactions SET status='completed' WHERE id=$tx_id");
                    $message = "<div class='msg-box success-msg'>✅ Deposit Approved! $coins_amount Coins added to $user_name.</div>";
                }
            } else if($tx_type == 'debit') {
                mysqli_query($conn, "UPDATE transactions SET status='completed' WHERE id=$tx_id");
                $message = "<div class='msg-box success-msg'>✅ Withdraw Approved!</div>";
            }
        } else {
            $message = "<div class='msg-box error-msg'>⚠️ Already processed!</div>";
        }
    }
}

if(isset($_POST['reject_tx'])){
    $tx_id = (int)$_POST['tx_id'];
    $tx_query = mysqli_query($conn, "SELECT username, amount, type, status FROM transactions WHERE id=$tx_id");
    $tx_data = mysqli_fetch_assoc($tx_query);
    
    if($tx_data) {
        $user_name = $tx_data['username'];
        $coins_amount = (int)$tx_data['amount'];
        $current_status = $tx_data['status'];
        $tx_type = $tx_data['type'];
        
        if($current_status == 'pending') {
            if($tx_type == 'debit') {
                $refund_query = "UPDATE user SET coin = coin + $coins_amount WHERE username='$user_name'";
                if(mysqli_query($conn, $refund_query)) {
                    mysqli_query($conn, "UPDATE transactions SET status='rejected' WHERE id=$tx_id");
                    $message = "<div class='msg-box info-msg'>❌ Withdraw Rejected. $coins_amount Coins Refunded!</div>";
                }
            } else {
                mysqli_query($conn, "UPDATE transactions SET status='rejected' WHERE id=$tx_id");
                $message = "<div class='msg-box info-msg'>❌ Deposit Rejected.</div>";
            }
        } else {
            $message = "<div class='msg-box error-msg'>⚠️ Already processed!</div>";
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
  <title>Wallet Requests — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
        --bg: #080b11; --card: #0e1322; --cyan: #00E5FF; --purple: #7B2EFF;
        --text: #fff; --muted: #6c7a9c; --border: rgba(255,255,255,0.05);
        --success: #2ed573; --danger: #ff4757; --orange: #ffb800;
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
    .main-container { max-width:1000px; margin:0 auto; padding:70px 14px 40px; }

    .page-title { font-size:clamp(22px,4vw,28px); font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--cyan); margin-bottom:4px; }
    .page-sub { color:var(--muted); font-size:13px; margin-bottom:20px; }

    .msg-box { padding:14px; border-radius:8px; font-size:14px; font-weight:600; margin-bottom:20px; text-align:center; }
    .success-msg { background:rgba(46,213,115,0.12); color:var(--success); border:1px solid rgba(46,213,115,0.25); }
    .error-msg { background:rgba(255,71,87,0.12); color:var(--danger); border:1px solid rgba(255,71,87,0.25); }
    .info-msg { background:rgba(255,184,0,0.12); color:var(--orange); border:1px solid rgba(255,184,0,0.25); }

    .table-wrap { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
    .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    table { width:100%; border-collapse:collapse; min-width:650px; }
    th { padding:12px 10px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); font-size:10px; text-transform:uppercase; letter-spacing:1px; }
    td { padding:10px; border-bottom:1px solid rgba(255,255,255,0.02); font-size:13px; }
    tr:hover td { background:rgba(255,255,255,0.01); }

    .badge { display:inline-block; padding:3px 8px; border-radius:4px; font-size:10px; font-weight:700; text-transform:uppercase; }
    .badge-success { background:rgba(46,213,115,0.2); color:var(--success); }
    .badge-danger { background:rgba(255,71,87,0.2); color:var(--danger); }
    .badge-warning { background:rgba(255,184,0,0.2); color:var(--orange); }

    .btn-approve { background:var(--success); color:#fff; border:none; padding:5px 10px; border-radius:4px; font-size:10px; font-weight:700; cursor:pointer; margin-right:4px; }
    .btn-reject { background:var(--danger); color:#fff; border:none; padding:5px 10px; border-radius:4px; font-size:10px; font-weight:700; cursor:pointer; }
    .btn-approve:hover { background:#26af5f; }
    .btn-reject:hover { background:#d43b48; }
    .processed-text { color:var(--muted); font-size:11px; }

    .no-data { text-align:center; padding:40px; color:var(--muted); }

    @media(min-width:769px) {
        .hamburger { display:none; }
        .sidebar { left:0; }
        .sidebar-close { display:none; }
        .sidebar-overlay { display:none !important; }
        .main-container { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
    @media(max-width:480px) {
        td { font-size:11px; padding:8px 6px; }
        th { padding:10px 6px; }
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
    <a href="admin.php" class="sidebar-link active">🪙 Wallet Requests</a>
    <a href="admin_win.php" class="sidebar-link">💎 Add Coins</a>

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

    <div class="page-title anim">⚙️ Transactions System</div>
    <div class="page-sub anim" style="animation-delay:0.1s;">Review, approve, or reject wallet transactions</div>

    <div class="table-wrap anim" style="animation-delay:0.15s;">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>proof</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $admin_query = mysqli_query($conn, "SELECT * FROM transactions ORDER BY id DESC");
                
                if(mysqli_num_rows($admin_query) == 0) {
                    echo '<tr><td colspan="7" class="no-data">No transactions found</td></tr>';
                } else {
                    while($row = mysqli_fetch_assoc($admin_query)) {
                        $tx_id = $row['id'];
                        $type_badge = ($row['type'] == 'credit') ? '<span class="badge badge-success">Deposit</span>' : '<span class="badge badge-danger">Withdraw</span>';
                        
                        if($row['status'] == 'completed') $status_badge = '<span class="badge badge-success">Completed</span>';
                        elseif($row['status'] == 'pending') $status_badge = '<span class="badge badge-warning">Pending</span>';
                        else $status_badge = '<span class="badge badge-danger">Rejected</span>';
                        
                        echo "<tr>";
                        echo "<td style='color:var(--muted);'>#$tx_id</td>";
                        echo "<td style='font-weight:600;color:#fff;'>".htmlspecialchars($row['username'])."</td>";
                        echo "<td style='color:#94a3b8;'>".htmlspecialchars($row['description'])."</td>";
                        echo "<td>$type_badge</td>";
                        echo "<td style='font-weight:700;color:var(--cyan);'>".$row['amount']." 🪙</td>";
                        
echo "<td>$status_badge</td>";

// ✅ Proof column
$proof_file = $row['proof'] ?? '';
if (!empty($proof_file)) {
    $proof_url = "uploads/proofs/" . $proof_file;
    echo "<td><a href='$proof_url' target='_blank' style='color:#00E5FF;font-size:12px;text-decoration:underline;'>📷 View</a></td>";
} else {
    echo "<td style='color:var(--muted);font-size:11px;'>-</td>";
}

echo "<td style='text-align:center;'>";
                        if($row['status'] == 'pending') {
                            echo "<form action='' method='POST' style='display:inline;margin:0;'><input type='hidden' name='tx_id' value='$tx_id'><button type='submit' name='approve_tx' class='btn-approve'>Approve</button></form> ";
                            echo "<form action='' method='POST' style='display:inline;margin:0;'><input type='hidden' name='tx_id' value='$tx_id'><button type='submit' name='reject_tx' class='btn-reject'>Reject</button></form>";
                        } else {
                            echo "<span class='processed-text'>Processed</span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
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