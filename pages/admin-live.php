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

if(isset($_POST['start_live'])){
    $tournament_name = mysqli_real_escape_string($conn, $_POST['tournament_name']);
    $team1_name = mysqli_real_escape_string($conn, $_POST['team1_name']);
    $team1_players = mysqli_real_escape_string($conn, $_POST['team1_players']);
    $team2_name = mysqli_real_escape_string($conn, $_POST['team2_name']);
    $team2_players = mysqli_real_escape_string($conn, $_POST['team2_players']);
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $room_password = mysqli_real_escape_string($conn, $_POST['room_password']);
    $match_map = mysqli_real_escape_string($conn, $_POST['match_map']);
    $match_round = mysqli_real_escape_string($conn, $_POST['match_round']);

    mysqli_query($conn, "UPDATE live_matches SET status='ended' WHERE status='live'");

    $insert = "INSERT INTO live_matches (tournament_name, team1_name, team1_players, team2_name, team2_players, room_id, room_password, match_map, match_round, status, start_time) 
               VALUES ('$tournament_name', '$team1_name', '$team1_players', '$team2_name', '$team2_players', '$room_id', '$room_password', '$match_map', '$match_round', 'live', NOW())";
               
    if(mysqli_query($conn, $insert)){
        $message = "<div class='msg-box success-msg'>🔴 New Match is now LIVE!</div>";
    } else {
        $message = "<div class='msg-box error-msg'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
}

if(isset($_POST['update_score'])){
    $match_id = (int)$_POST['match_id'];
    $team1_score = (int)$_POST['team1_score'];
    $team2_score = (int)$_POST['team2_score'];
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $room_password = mysqli_real_escape_string($conn, $_POST['room_password']);

    $update = "UPDATE live_matches SET team1_score=$team1_score, team2_score=$team2_score, room_id='$room_id', room_password='$room_password' WHERE id=$match_id";
    
    if(mysqli_query($conn, $update)){
        $message = "<div class='msg-box success-msg'>🔄 Match updated!</div>";
    }
}

if(isset($_POST['end_match'])){
    $match_id = (int)$_POST['match_id'];
    if(mysqli_query($conn, "UPDATE live_matches SET status='ended' WHERE id=$match_id")){
        $message = "<div class='msg-box error-msg'>🏁 Match Ended.</div>";
    }
}

$current_live = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_matches WHERE status='live' LIMIT 1"));
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Live Match — PTA Admin</title>
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
    .main-container { max-width:900px; margin:0 auto; padding:70px 14px 40px; }

    .page-title { font-size:clamp(22px,4vw,28px); font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--cyan); margin-bottom:4px; }
    .page-sub { color:var(--muted); font-size:13px; margin-bottom:24px; }

    .msg-box { padding:14px; border-radius:8px; font-size:14px; font-weight:600; margin-bottom:20px; text-align:center; }
    .success-msg { background:rgba(46,213,115,0.12); color:var(--success); border:1px solid rgba(46,213,115,0.25); }
    .error-msg { background:rgba(255,71,87,0.12); color:var(--danger); border:1px solid rgba(255,71,87,0.25); }

    .card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 16px; margin-bottom:20px; }
    .card-title { font-size:16px; font-weight:700; color:var(--cyan); margin-bottom:16px; text-transform:uppercase; }
    .live-badge { display:inline-block; background:rgba(255,71,87,0.15); color:var(--danger); padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; }

    .form-group { margin-bottom:14px; display:flex; flex-direction:column; gap:4px; }
    .form-label { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:var(--muted); font-weight:700; }
    .form-input {
        background:#050714; color:#fff; border:1px solid rgba(0,240,255,0.15);
        padding:11px 13px; border-radius:8px; font-size:14px; font-family:'Rajdhani',sans-serif;
    }
    .form-input:focus { border-color:var(--cyan); outline:none; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

    .score-row { display:flex; align-items:center; gap:16px; background:var(--card); padding:16px; border-radius:10px; margin-bottom:14px; }
    .score-col { flex:1; text-align:center; }
    .score-team { font-size:13px; font-weight:700; margin-bottom:6px; color:#fff; }
    .score-input { font-size:26px; text-align:center; font-weight:800; color:var(--cyan); width:80px; padding:8px; }
    .score-vs { font-size:20px; font-weight:800; color:var(--muted); }

    .btn {
        padding:10px 18px; border-radius:8px; font-weight:700; font-size:13px; text-transform:uppercase;
        cursor:pointer; border:none; font-family:'Rajdhani',sans-serif; letter-spacing:0.5px; transition:all 0.3s;
    }
    .btn-update { background:linear-gradient(135deg,var(--purple),#5a1fd6); color:#fff; }
    .btn-end { background:var(--danger); color:#fff; }
    .btn-live { background:linear-gradient(135deg,var(--danger),#ff6b00); color:#fff; width:100%; padding:14px; font-size:15px; }
    .btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(123,46,255,0.4); }

    @media(min-width:769px) {
        .hamburger { display:none; }
        .sidebar { left:0; }
        .sidebar-close { display:none; }
        .sidebar-overlay { display:none !important; }
        .main-container { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
    @media(max-width:480px) {
        .form-grid { grid-template-columns:1fr; }
        .score-row { flex-direction:column; gap:10px; }
        .score-vs { display:none; }
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
    <a href="admin_win.php" class="sidebar-link">💎 Add Coins</a>

    <div class="sidebar-title">🎮 Game Core</div>
    <a href="admin-tournaments.php" class="sidebar-link">🏆 Manage Tournaments</a>
    <a href="admin_id_pass.php" class="sidebar-link">🔑 Room ID/Pass</a>
    <a href="admin_win.php" class="sidebar-link">👑 Declare Winner</a>
    <a href="admin-live.php" class="sidebar-link active">🔴 Live Match</a>
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

    <div class="page-title anim">🔴 Live Match Controller</div>
    <div class="page-sub anim" style="animation-delay:0.1s;">Update scores, change room passwords, or launch new streams</div>

    <!-- CURRENT LIVE MATCH -->
    <?php if ($current_live): ?>
    <div class="card anim" style="animation-delay:0.15s;border-color:rgba(255,71,87,0.3);">
        <div class="card-title">⚡ Live: <?php echo htmlspecialchars($current_live['tournament_name']); ?> <span class="live-badge">LIVE</span></div>
        
        <form action="" method="POST">
            <input type="hidden" name="match_id" value="<?php echo $current_live['id']; ?>">
            
            <div class="score-row">
                <div class="score-col">
                    <div class="score-team"><?php echo htmlspecialchars($current_live['team1_name']); ?></div>
                    <input type="number" name="team1_score" class="form-input score-input" value="<?php echo $current_live['team1_score']; ?>" required>
                </div>
                <div class="score-vs">VS</div>
                <div class="score-col">
                    <div class="score-team"><?php echo htmlspecialchars($current_live['team2_name']); ?></div>
                    <input type="number" name="team2_score" class="form-input score-input" value="<?php echo $current_live['team2_score']; ?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group"><label class="form-label">Room ID</label><input type="text" name="room_id" class="form-input" value="<?php echo htmlspecialchars($current_live['room_id']); ?>" required></div>
                <div class="form-group"><label class="form-label">Password</label><input type="text" name="room_password" class="form-input" value="<?php echo htmlspecialchars($current_live['room_password']); ?>" required></div>
            </div>

            <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
                <button type="submit" name="update_score" class="btn btn-update">🔄 Update</button>
                <button type="submit" name="end_match" class="btn btn-end">🏁 End Match</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- NEW MATCH -->
    <div class="card anim" style="animation-delay:0.2s;">
        <div class="card-title">📢 Launch New Match</div>
        <form action="" method="POST">
            <div class="form-group"><label class="form-label">Tournament Title</label><input type="text" name="tournament_name" class="form-input" placeholder="e.g. CS2 Pro League" required></div>
            
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Team 1</label><input type="text" name="team1_name" class="form-input" placeholder="Team Alpha" required></div>
                <div class="form-group"><label class="form-label">Team 1 Players</label><input type="text" name="team1_players" class="form-input" placeholder="Player1, Player2"></div>
            </div>
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Team 2</label><input type="text" name="team2_name" class="form-input" placeholder="Team Blaze" required></div>
                <div class="form-group"><label class="form-label">Team 2 Players</label><input type="text" name="team2_players" class="form-input" placeholder="FireStorm, Ghost"></div>
            </div>
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Room ID</label><input type="text" name="room_id" class="form-input" placeholder="7X9KP4" required></div>
                <div class="form-group"><label class="form-label">Password</label><input type="text" name="room_password" class="form-input" placeholder="ARENA25" required></div>
            </div>
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Map</label><input type="text" name="match_map" class="form-input" value="Dust2" required></div>
                <div class="form-group"><label class="form-label">Round</label><input type="text" name="match_round" class="form-input" value="Quarterfinals" required></div>
            </div>

            <button type="submit" name="start_live" class="btn btn-live" style="margin-top:14px;">🔴 Broadcast Live Now</button>
        </form>
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