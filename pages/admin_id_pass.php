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

if(isset($_POST['update_room'])){
    $tournament_id = (int)$_POST['tournament_id'];
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $room_pass = mysqli_real_escape_string($conn, $_POST['room_pass']);
    
    $update_query = "UPDATE tournaments SET room_id = '$room_id', room_pass = '$room_pass' WHERE id = $tournament_id";
    
    if(mysqli_query($conn, $update_query)){
        $message = "<div class='msg-box success-msg'>✅ Room info updated successfully!</div>";
    } else {
        $message = "<div class='msg-box error-msg'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Room ID/Pass — PTA Admin</title>
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
    .hamburger:hover { background:rgba(123,46,255,0.1); }

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
    .main-container { max-width:800px; margin:0 auto; padding:70px 14px 40px; }

    .page-title { font-size:clamp(22px,4vw,28px); font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--cyan); margin-bottom:4px; }
    .page-sub { color:var(--muted); font-size:13px; margin-bottom:24px; }

    .msg-box { padding:14px; border-radius:8px; font-size:14px; font-weight:600; margin-bottom:20px; text-align:center; }
    .success-msg { background:rgba(46,213,115,0.12); color:var(--success); border:1px solid rgba(46,213,115,0.25); }
    .error-msg { background:rgba(255,71,87,0.12); color:var(--danger); border:1px solid rgba(255,71,87,0.25); }

    .card-list { display:flex; flex-direction:column; gap:14px; }
    .room-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; transition:all 0.3s; }
    .room-card:hover { border-color:rgba(0,229,255,0.3); }
    .card-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
    .card-title { font-size:17px; font-weight:700; color:#fff; }
    .card-mode { font-size:11px; color:var(--cyan); background:rgba(0,229,255,0.1); padding:4px 10px; border-radius:4px; font-weight:600; }
    .card-current { font-size:11px; color:var(--muted); margin-bottom:14px; }
    .card-current span { color:var(--success); font-weight:600; }

    .form-row { display:grid; grid-template-columns:1fr 1fr auto; gap:10px; align-items:end; }
    .form-group { display:flex; flex-direction:column; gap:4px; }
    .form-label { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:var(--muted); font-weight:700; }
    .form-input {
        background:#050714; color:#fff; border:1px solid rgba(0,240,255,0.2);
        padding:10px 12px; border-radius:6px; font-size:14px; font-family:'Rajdhani',sans-serif;
    }
    .form-input:focus { border-color:var(--cyan); outline:none; }
    .btn-save {
        background:linear-gradient(135deg,var(--purple),#5a1fd6); color:#fff; border:none;
        padding:10px 18px; border-radius:6px; font-weight:700; font-size:13px; text-transform:uppercase;
        cursor:pointer; font-family:'Rajdhani',sans-serif; white-space:nowrap; transition:all 0.3s;
    }
    .btn-save:hover { box-shadow:0 0 20px rgba(123,46,255,0.5); }

    .no-data { text-align:center; color:var(--muted); padding:40px; }

    @media(min-width:769px) {
        .hamburger { display:none; }
        .sidebar { left:0; }
        .sidebar-close { display:none; }
        .sidebar-overlay { display:none !important; }
        .main-container { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
    @media(max-width:480px) {
        .form-row { grid-template-columns:1fr; }
        .card-title { font-size:15px; }
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
    <a href="admin-transactions.php" class="sidebar-link <?php echo $current_page=='admin-transactions.php'?'active':''; ?>">🪙 Wallet Requests</a>
    <a href="admin-add-coins.php" class="sidebar-link <?php echo $current_page=='admin-add-coins.php'?'active':''; ?>">💎 Add Coins</a>

    <div class="sidebar-title">🎮 Game Core</div>
    <a href="admin-tournaments.php" class="sidebar-link <?php echo $current_page=='admin-tournaments.php'?'active':''; ?>">🏆 Manage Tournaments</a>
    <a href="admin_id_pass.php" class="sidebar-link active">🔑 Room ID/Pass</a>
    <a href="admin-win.php" class="sidebar-link <?php echo $current_page=='admin-win.php'?'active':''; ?>">👑 Declare Winner</a>
    <a href="admin_view.php" class="sidebar-link <?php echo $current_page=='admin_view.php'?'active':''; ?>">👥 View Participants</a>
    <a href="admin-send-notification.php" class="sidebar-link <?php echo $current_page=='admin-send-notification.php'?'active':''; ?>">📨 Send Notification</a>

    <div class="sidebar-title">👤 Users</div>
    <a href="admin-users.php" class="sidebar-link <?php echo $current_page=='admin-users.php'?'active':''; ?>">👤 Total Players</a>
    <a href="admin-tickets.php" class="sidebar-link <?php echo $current_page=='admin-tickets.php'?'active':''; ?>">💬 Support Tickets</a>

    <div style="margin-top:auto;border-top:1px solid var(--border);padding-top:16px;margin-top:20px;">
        <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
        <a href="logout.php" class="sidebar-link" style="color:#ff4757;">🚪 Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-container">

    <?php echo $message; ?>

    <div class="page-title anim">🔑 Room ID & Password</div>
    <div class="page-sub anim" style="animation-delay:0.1s;">Assign Room ID and Password for each tournament</div>
    
    <div class="card-list">
        <?php
        $tour_query = mysqli_query($conn, "SELECT * FROM tournaments WHERE status != 'completed' ORDER BY id DESC");
        
        if(mysqli_num_rows($tour_query) == 0) {
            echo '<div class="no-data">No active tournaments found</div>';
        } else {
            $i = 0;
            while($row = mysqli_fetch_assoc($tour_query)) {
                $current_room_id = $row['room_id'] ?? '';
                $current_room_pass = $row['room_pass'] ?? '';
                $delay = 0.1 + ($i * 0.05);
        ?>
            <div class="room-card anim" style="animation-delay:<?php echo $delay; ?>s;">
                <div class="card-header">
                    <div>
                        <div class="card-title">🏆 <?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="card-current">
                            ID: <span><?php echo $current_room_id ? $current_room_id : 'Not Set'; ?></span> | 
                            Pass: <span><?php echo $current_room_pass ? $current_room_pass : 'Not Set'; ?></span>
                        </div>
                    </div>
                    <span class="card-mode"><?php echo htmlspecialchars($row['match_mode'] ?? 'N/A'); ?></span>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="tournament_id" value="<?php echo $row['id']; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Room ID</label>
                            <input type="text" class="form-input" name="room_id" placeholder="e.g. 12345678" value="<?php echo htmlspecialchars($current_room_id); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="text" class="form-input" name="room_pass" placeholder="e.g. pta2024" value="<?php echo htmlspecialchars($current_room_pass); ?>" required>
                        </div>
                        <button type="submit" name="update_room" class="btn-save">💾 Save</button>
                    </div>
                </form>
            </div>
        <?php 
                $i++;
            }
        }
        ?>
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