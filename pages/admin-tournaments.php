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

if(isset($_GET['msg']) && $_GET['msg'] == 'added'){
    $message = "<div class='msg-box success-msg'>✅ Tournament added successfully!</div>";
}
if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'){
    $message = "<div class='msg-box info-msg'>🗑️ Tournament deleted!</div>";
}

if(isset($_POST['add_tournament'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $game_name = mysqli_real_escape_string($conn, $_POST['game_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $match_mode = mysqli_real_escape_string($conn, $_POST['match_mode']);
    $prize_pool = mysqli_real_escape_string($conn, $_POST['prize_pool']);
    $prize_distribution = mysqli_real_escape_string($conn, $_POST['prize_distribution']);
    $entry_fee = isset($_POST['entry_fee']) ? (int)$_POST['entry_fee'] : 0;
    $slots_total = isset($_POST['slots_total']) ? (int)$_POST['slots_total'] : 0;
    $match_date = mysqli_real_escape_string($conn, $_POST['match_date']);
    $match_time = mysqli_real_escape_string($conn, $_POST['match_time']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $match_datetime = $match_date . ' & ' . $match_time;
    
    $insert_query = "INSERT INTO tournaments (title, game_name, category, match_mode, prize_pool, prize_distribution, entry_fee, slots_total, slots_joined, status, match_date, created_at) 
                     VALUES ('$title', '$game_name', '$category', '$match_mode', '$prize_pool', '$prize_distribution', $entry_fee, $slots_total, 0, '$status', '$match_datetime', NOW())";
    
    if(mysqli_query($conn, $insert_query)){
        header("Location: admin-tournaments.php?msg=added");
        exit();
    } else {
        $message = "<div class='msg-box error-msg'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
}

if(isset($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM tournaments WHERE id=$del_id");
    header("Location: admin-tournaments.php?msg=deleted");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Tournaments — PTA Arena</title>
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

    .card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 16px; margin-bottom:20px; }
    .card-title { font-size:18px; font-weight:700; color:var(--cyan); margin-bottom:16px; }

    .form-group { margin-bottom:14px; }
    .form-label { display:block; font-size:10px; text-transform:uppercase; letter-spacing:1px; color:var(--muted); margin-bottom:4px; font-weight:700; }
    .form-input, .form-select, .form-textarea {
        background:#050714; color:#fff; border:1px solid rgba(0,240,255,0.15);
        padding:11px 13px; border-radius:8px; width:100%; font-size:14px; font-family:'Rajdhani',sans-serif;
    }
    .form-textarea { resize:vertical; min-height:80px; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:var(--cyan); outline:none; }
    .form-select option { background:var(--card); color:#fff; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }

    .btn-submit {
        background:linear-gradient(135deg,var(--purple),#5a1fd6); color:#fff; border:none;
        padding:14px; border-radius:10px; font-weight:800; font-size:15px; text-transform:uppercase;
        cursor:pointer; width:100%; font-family:'Rajdhani',sans-serif; letter-spacing:1px; transition:all 0.3s;
    }
    .btn-submit:hover { box-shadow:0 0 25px rgba(123,46,255,0.5); }

    .table-wrap { background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
    .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    table { width:100%; border-collapse:collapse; min-width:700px; }
    th { padding:12px 10px; color:var(--muted); text-align:left; border-bottom:1px solid var(--border); font-size:10px; text-transform:uppercase; letter-spacing:1px; }
    td { padding:10px; border-bottom:1px solid rgba(255,255,255,0.02); font-size:12px; }
    tr:hover td { background:rgba(255,255,255,0.01); }
    .btn-delete { background:var(--danger); color:#fff; padding:4px 10px; border-radius:4px; font-size:10px; text-decoration:none; font-weight:600; }

    .datetime-row { display:flex; gap:12px; }
    .datetime-row .form-group { flex:1; }

    @media(min-width:769px) {
        .hamburger { display:none; }
        .sidebar { left:0; }
        .sidebar-close { display:none; }
        .sidebar-overlay { display:none !important; }
        .main-container { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
    @media(max-width:480px) {
        .form-row, .form-row-3 { grid-template-columns:1fr; }
        .datetime-row { flex-direction:column; }
        .card { padding:16px 12px; }
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
    <a href="admin-tournaments.php" class="sidebar-link active">🏆 Manage Tournaments</a>
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

    <div class="page-title anim">🏆 Manage Tournaments</div>
    <div class="page-sub anim" style="animation-delay:0.1s;">Add or delete tournament matches</div>
    
    <!-- ADD FORM -->
    <div class="card anim" style="animation-delay:0.15s;">
        <div class="card-title">➕ Add New Tournament</div>
        <form action="" method="POST" autocomplete="off">
            <div class="form-group"><label class="form-label">Title</label><input type="text" class="form-input" name="title" placeholder="e.g. CSS 1v1 Championship" required></div>
            <div class="form-row-3">
                <div class="form-group"><label class="form-label">Game Name</label><input type="text" class="form-input" name="game_name" placeholder="e.g. CS2" required></div>
                <div class="form-group"><label class="form-label">Category</label><select class="form-select" name="category" required><option value="">Select</option><option value="css">CSS</option><option value="sniper">Sniper</option><option value="br per kill">BR Per Kill</option><option value="br survival">BR Survival</option></select></div>
                <div class="form-group"><label class="form-label">Mode</label><select class="form-select" name="match_mode" required><option value="">Select</option><option value="1v1">1v1</option><option value="2v2">2v2</option><option value="4v4">4v4</option><option value="solo">Solo</option><option value="duo">Duo</option><option value="squad">Squad</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">🏆 Prize Pool</label><textarea class="form-textarea" name="prize_pool" rows="3" placeholder="1st: 400&#10;2nd: 200&#10;3rd: 100" required></textarea></div>
                <div class="form-group"><label class="form-label">📊 Prize Distribution (%)</label><textarea class="form-textarea" name="prize_distribution" rows="3" placeholder="1st: 40%&#10;2nd: 30%"></textarea></div>
            </div>
            <div class="form-row-3">
                <div class="form-group"><label class="form-label">Entry Fee</label><input type="number" class="form-input" name="entry_fee" value="50" required></div>
                <div class="form-group"><label class="form-label">Total Slots</label><input type="number" class="form-input" name="slots_total" value="2" required></div>
                <div class="form-group"><label class="form-label">Status</label><select class="form-select" name="status" required><option value="open">Open</option><option value="live">Live</option><option value="upcoming">Upcoming</option><option value="completed">Completed</option></select></div>
            </div>
            <div class="datetime-row">
                <div class="form-group"><label class="form-label">📅 Date</label><input type="date" class="form-input" name="match_date" required></div>
                <div class="form-group"><label class="form-label">⏰ Time</label><input type="time" class="form-input" name="match_time" required></div>
            </div>
            <button type="submit" name="add_tournament" class="btn-submit" style="margin-top:12px;">➕ Add Tournament</button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-wrap anim" style="animation-delay:0.2s;">
        <div class="table-scroll">
        <table>
            <thead>
                <tr><th>ID</th><th>Title</th><th>Game</th><th>Cat</th><th>Mode</th><th>Prize</th><th>Fee</th><th>Slots</th><th>Date</th><th>Status</th><th>Act</th></tr>
            </thead>
            <tbody>
                <?php
                $tour_query = mysqli_query($conn, "SELECT * FROM tournaments ORDER BY id DESC");
                if(mysqli_num_rows($tour_query) == 0) {
                    echo '<tr><td colspan="11" style="text-align:center;color:var(--muted);padding:40px;">No tournaments found</td></tr>';
                } else {
                    while($row = mysqli_fetch_assoc($tour_query)) {
                        $status_color = ($row['status'] == 'open') ? '#2ed573' : (($row['status'] == 'live') ? '#ff4757' : '#ffb800');
                        echo "<tr>";
                        echo "<td style='color:var(--muted);'>#".$row['id']."</td>";
                        echo "<td style='color:#fff;font-weight:600;'>".htmlspecialchars($row['title'])."</td>";
                        echo "<td>".htmlspecialchars($row['game_name'])."</td>";
                        echo "<td>".htmlspecialchars($row['category'])."</td>";
                        echo "<td style='color:var(--cyan);font-weight:600;'>".htmlspecialchars($row['match_mode'])."</td>";
                        echo "<td style='color:var(--orange);font-size:11px;'>".htmlspecialchars(substr($row['prize_pool'],0,20))."...</td>";
                        echo "<td>🪙 ".$row['entry_fee']."</td>";
                        echo "<td>".$row['slots_joined']."/".$row['slots_total']."</td>";
                        echo "<td style='font-size:11px;'>".htmlspecialchars($row['match_date'])."</td>";
                        echo "<td style='color:$status_color;font-weight:600;'>".ucfirst($row['status'])."</td>";
                        echo "<td><a href='?delete=".$row['id']."' class='btn-delete' onclick=\"return confirm('Delete?')\">🗑️</a></td>";
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