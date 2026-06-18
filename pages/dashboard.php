<?php
session_start();
include "config.php";

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$query = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
$user = mysqli_fetch_assoc($query);
$coins = $user['coin'] ?? 0;
$name = $user['name'] ?? $username;
$user_id = $user['id'];

$matches_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tournament_participants WHERE user_id = $user_id");
$total_matches = mysqli_fetch_assoc($matches_query)['total'] ?? 0;

$wins_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE username='$username' AND description LIKE '%Won%' AND type='credit'");
$total_wins = mysqli_fetch_assoc($wins_query)['total'] ?? 0;

$active_query = mysqli_query($conn, "SELECT * FROM tournaments WHERE status IN ('open','live','upcoming') ORDER BY id DESC LIMIT 5");

// ✅ P-Coin icon
$pcoin = '<span class="pcoin"></span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Dashboard — PTA Arena</title>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
        --bg: #020206; --card: #0e1322; --cyan: #00E5FF; --purple: #7B2EFF;
        --text: #fff; --muted: #8e9bb2; --border: rgba(255,255,255,0.05);
        --danger: #ff4757; --success: #2ed573; --orange: #ff9f00;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:var(--bg); color:var(--text); font-family:'Space Grotesk',sans-serif; overflow-x:hidden; }
    
    @keyframes fadeUp { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }
    .anim { animation:fadeUp 0.5s ease forwards; }

    /* P-COIN */
    .pcoin {
        display:inline-block; width:18px; height:18px; vertical-align:middle; margin-right:5px;
        background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300E5FF" stroke-width="5" fill="rgba(5,7,20,0.95)"/><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="46" font-family="Rajdhani,sans-serif" font-weight="900" fill="%2300E5FF">P</text></svg>');
        background-size:contain; background-repeat:no-repeat; flex-shrink:0;
    }
    
    .dash-container { max-width:800px; margin:0 auto; padding:80px 14px 100px; }
    
    .dash-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; }
    .greeting h2 { font-family:'Rajdhani',sans-serif; font-size:clamp(22px,4vw,28px); font-weight:800; color:#fff; }
    .greeting p { color:var(--muted); font-size:13px; }
    
    /* 3 CARDS ONE LINE */
    .stats-row { display:flex; gap:12px; margin-bottom:20px; }
    .stat-card {
        flex:1; background:var(--card); border:1px solid var(--border); border-radius:14px;
        padding:18px 12px; text-align:center; transition:all 0.3s; min-width:0;
    }
    .stat-card:hover { transform:translateY(-3px); border-color:var(--purple); }
    .stat-icon { font-size:24px; margin-bottom:4px; }
    .stat-val { font-family:'Rajdhani',sans-serif; font-size:clamp(18px,3vw,24px); font-weight:800; color:#fff; display:flex; align-items:center; justify-content:center; gap:4px; }
    .stat-label { font-size:9px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; margin-top:2px; }

    .section-title { font-family:'Rajdhani',sans-serif; font-size:16px; font-weight:700; margin-bottom:12px; color:var(--cyan); text-transform:uppercase; letter-spacing:1px; }

    .card-panel { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px; margin-bottom:18px; }

    .match-row { display:grid; grid-template-columns:1fr auto auto auto; gap:10px; align-items:center; padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.03); font-size:12px; }
    .match-row:last-child { border-bottom:none; }

    .bar-chart { display:flex; align-items:flex-end; gap:5px; height:70px; padding:0 2px; }
    .bar { flex:1; background:linear-gradient(to top,var(--purple),var(--cyan)); border-radius:3px 3px 0 0; min-width:10px; }

    .btn { padding:10px 18px; border-radius:8px; font-weight:700; font-size:12px; text-transform:uppercase; cursor:pointer; border:none; font-family:'Rajdhani',sans-serif; letter-spacing:0.5px; text-decoration:none; display:inline-block; text-align:center; transition:all 0.3s; }
    .btn-primary { background:var(--purple); color:#fff; }
    .btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(123,46,255,0.4); }

    .status-live { background:var(--danger); padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }
    .status-upcoming { background:var(--cyan); color:#000; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }
    .status-open { background:var(--success); padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }

    @media(max-width:480px) {
        .dash-container { padding:70px 8px 100px; }
        .stats-row { gap:8px; }
        .stat-card { padding:14px 8px; }
        .stat-val { font-size:16px; }
        .stat-icon { font-size:20px; }
        .match-row { grid-template-columns:1fr auto; font-size:11px; }
    }
  </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="dash-container">

    <div class="dash-header anim">
        <div class="greeting">
            <h2>Hello, <?php echo htmlspecialchars($name); ?> 👋</h2>
            <p>Welcome back to your arena!</p>
        </div>
        <a href="tournaments.php" class="btn btn-primary">Join Tournament 🚀</a>
    </div>

    <!-- ✅ 3 CARDS ONE LINE -->
    <div class="stats-row anim" style="animation-delay:0.1s;">
        <div class="stat-card">
            <div class="stat-icon">🎮</div>
            <div class="stat-val"><?php echo $total_matches; ?></div>
            <div class="stat-label">Matches</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-val"><?php echo $total_wins; ?></div>
            <div class="stat-label">Wins</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><?php echo $pcoin; ?></div>
            <div class="stat-val"><?php echo number_format($coins); ?></div>
            <div class="stat-label">Balance</div>
        </div>
    </div>

    <!-- ✅ PERFORMANCE -->
    <h3 class="section-title anim" style="animation-delay:0.2s;">📊 Performance</h3>
    <div class="card-panel anim" style="animation-delay:0.2s;">
        <div class="bar-chart" id="perf-chart"></div>
        <div style="display:flex;justify-content:space-around;margin-top:6px;font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;">
            <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
        </div>
    </div>

    <!-- ✅ ACTIVE TOURNAMENTS -->
    <h3 class="section-title anim" style="animation-delay:0.3s;">🏆 Active Tournaments</h3>
    <div class="card-panel anim" style="animation-delay:0.3s;">
        <?php if(mysqli_num_rows($active_query) > 0): ?>
            <?php while($t = mysqli_fetch_assoc($active_query)): 
                $status_class = ($t['status'] == 'live') ? 'status-live' : (($t['status'] == 'upcoming') ? 'status-upcoming' : 'status-open');
            ?>
            <div class="match-row">
                <span style="color:#fff;font-weight:600;"><?php echo htmlspecialchars($t['title']); ?></span>
                <span style="font-size:11px;color:var(--muted);"><?php echo htmlspecialchars($t['game_name']); ?></span>
                <span class="<?php echo $status_class; ?>"><?php echo strtoupper($t['status']); ?></span>
                <span style="color:var(--orange);font-weight:700;font-size:11px;">🪙 <?php echo htmlspecialchars(substr($t['prize_pool'],0,15)); ?></span>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center;color:var(--muted);padding:20px;">No active tournaments</div>
        <?php endif; ?>
    </div>

</div>

<?php include "navbar.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chart = document.getElementById('perf-chart');
    [40,65,30,80,55,90,70].forEach(v => { chart.innerHTML += `<div class="bar" style="height:${v}%;"></div>`; });
});
</script>
</body>
</html>