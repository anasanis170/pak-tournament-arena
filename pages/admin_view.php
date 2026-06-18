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

$tournament_id = isset($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>View Participants — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
        --bg: #080b11; --card: #0e1322; --cyan: #00E5FF; --purple: #7B2EFF;
        --text: #fff; --muted: #6c7a9c; --border: rgba(255,255,255,0.05);
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

    .btn-back {
        display:inline-block; background:rgba(255,255,255,0.05); color:#fff; padding:8px 16px;
        border-radius:8px; text-decoration:none; font-weight:600; font-size:13px; margin-bottom:20px;
        border:1px solid var(--border); transition:all 0.3s;
    }
    .btn-back:hover { background:rgba(255,255,255,0.1); }

    .card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; margin-bottom:16px; }
    .card-title { font-size:18px; font-weight:700; color:#fff; margin-bottom:4px; }
    .card-info { font-size:12px; color:var(--muted); margin-bottom:14px; display:flex; flex-wrap:wrap; gap:8px; }
    .card-info span { color:#fff; font-weight:600; }

    table { width:100%; border-collapse:collapse; }
    th { padding:12px 10px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); font-size:10px; text-transform:uppercase; letter-spacing:1px; }
    td { padding:10px; border-bottom:1px solid rgba(255,255,255,0.03); font-size:13px; }
    
    .badge { display:inline-block; padding:3px 10px; border-radius:4px; font-size:10px; font-weight:600; }
    .badge-info { background:rgba(0,229,255,0.15); color:var(--cyan); }
    .badge-success { background:rgba(46,213,115,0.15); color:#2ed573; }

    .btn-view {
        background:var(--purple); color:#fff; padding:8px 16px; border-radius:8px;
        text-decoration:none; font-weight:600; font-size:13px; display:inline-block; transition:all 0.3s;
    }
    .btn-view:hover { background:#8b3fff; }

    .count-big { font-size:32px; font-weight:800; color:var(--cyan); }
    .no-data { text-align:center; padding:40px; color:var(--muted); }

    .tournament-row {
        display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;
    }

    .table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }

    @media(min-width:769px) {
        .hamburger { display:none; }
        .sidebar { left:0; }
        .sidebar-close { display:none; }
        .sidebar-overlay { display:none !important; }
        .main-container { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
    @media(max-width:480px) {
        .card-title { font-size:15px; }
        .count-big { font-size:24px; }
        td { font-size:11px; }
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
    <a href="admin-live.php" class="sidebar-link">🔴 Live Match</a>
    <a href="admin_view.php" class="sidebar-link active">👥 View Participants</a>
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

    <div class="page-title anim">👥 View Participants</div>
    <div class="page-sub anim" style="animation-delay:0.1s;">All joined players with UID, Game Name & Email</div>

    <?php if ($tournament_id > 0): 
        $t_query = mysqli_query($conn, "SELECT * FROM tournaments WHERE id = $tournament_id");
        $t_data = mysqli_fetch_assoc($t_query);
        
        if ($t_data):
    ?>
        <a href="admin_view.php" class="btn-back anim" style="animation-delay:0.15s;">← Back to All Tournaments</a>
        
        <div class="card anim" style="animation-delay:0.2s;">
            <div class="card-title">🏆 <?php echo htmlspecialchars($t_data['title']); ?></div>
            <div class="card-info">
                <span>🎮 <?php echo htmlspecialchars($t_data['game_name']); ?></span>
                <span>👥 <?php echo htmlspecialchars($t_data['match_mode']); ?></span>
                <span>📅 <?php echo htmlspecialchars($t_data['match_date']); ?></span>
                <span>🎫 <?php echo $t_data['slots_joined']; ?>/<?php echo $t_data['slots_total']; ?> slots</span>
            </div>
            
            <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>User</th><th>🎮 Game UID</th><th>👤 In-Game</th><th>📧 Email</th><th>Joined</th></tr>
                </thead>
                <tbody>
                    <?php
                    $p_query = mysqli_query($conn, 
                        "SELECT u.username, u.email as user_email, tp.game_uid, tp.game_name_ingame, tp.email as tp_email, tp.joined_at 
                         FROM tournament_participants tp JOIN user u ON tp.user_id = u.id 
                         WHERE tp.tournament_id = $tournament_id ORDER BY tp.joined_at ASC"
                    );
                    
                    if(mysqli_num_rows($p_query) > 0) {
                        $count = 1;
                        while($p = mysqli_fetch_assoc($p_query)) {
                            $game_uid = !empty($p['game_uid']) ? htmlspecialchars($p['game_uid']) : '-';
                            $game_name = !empty($p['game_name_ingame']) ? htmlspecialchars($p['game_name_ingame']) : '-';
                            $email = !empty($p['tp_email']) ? htmlspecialchars($p['tp_email']) : htmlspecialchars($p['user_email']);
                            echo "<tr><td style='color:var(--muted);'>$count</td><td style='color:#fff;font-weight:600;'>{$p['username']}</td><td style='color:var(--cyan);font-weight:600;'>$game_uid</td><td>$game_name</td><td style='font-size:11px;'>$email</td><td style='font-size:11px;color:var(--muted);'>".date('d M, h:i A',strtotime($p['joined_at']))."</td></tr>";
                            $count++;
                        }
                    } else {
                        echo '<tr><td colspan="6" class="no-data">⚠️ No participants yet</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
        <?php else: ?>
            <div class="no-data">❌ Tournament not found</div>
        <?php endif; ?>

    <?php else: ?>
        <?php
        $all_t = mysqli_query($conn, "SELECT * FROM tournaments ORDER BY id DESC");
        if(mysqli_num_rows($all_t) > 0):
            $i = 0;
            while($t = mysqli_fetch_assoc($all_t)): 
                $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM tournament_participants WHERE tournament_id = ".$t['id']);
                $count_data = mysqli_fetch_assoc($count_q);
                $p_count = $count_data['total'];
                $delay = 0.1 + ($i * 0.05);
        ?>
            <div class="card anim" style="animation-delay:<?php echo $delay; ?>s;">
                <div class="tournament-row">
                    <div style="flex:1;">
                        <div class="card-title">🏆 <?php echo htmlspecialchars($t['title']); ?></div>
                        <div class="card-info">
                            <span>🎮 <?php echo htmlspecialchars($t['game_name']); ?></span>
                            <span>👥 <?php echo htmlspecialchars($t['match_mode']); ?></span>
                            <span>📅 <?php echo htmlspecialchars($t['match_date']); ?></span>
                        </div>
                    </div>
                    <div style="text-align:center;min-width:50px;">
                        <div class="count-big"><?php echo $p_count; ?></div>
                        <div style="font-size:9px;color:var(--muted);text-transform:uppercase;">Players</div>
                    </div>
                    <a href="admin_view.php?tournament_id=<?php echo $t['id']; ?>" class="btn-view">View Details →</a>
                </div>
            </div>
        <?php 
                $i++;
            endwhile; 
        else: ?>
            <div class="no-data">No tournaments found</div>
        <?php endif; ?>
    <?php endif; ?>

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