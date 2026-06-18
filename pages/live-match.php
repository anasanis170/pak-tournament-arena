<?php
session_start();
include "config.php"; // Database connection

// Security check: Agar user login nahi hai toh login page par bhejen
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Database se active live match uthane ki query (Jo sabse latest live chal raha ho)
$match_query = mysqli_query($conn, "SELECT * FROM live_matches WHERE status='live' ORDER BY id DESC LIMIT 1");
$live_match = mysqli_fetch_assoc($match_query);

// Agar koi match live hai, toh uski duration calculation ke liye seconds nikaalna
$elapsed_seconds = 0;
if ($live_match) {
    $start_time = strtotime($live_match['start_time']);
    $current_time = time();
    $elapsed_seconds = max(0, $current_time - $start_time);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Live Match — PTA Arena</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Custom CSS animations and dynamic variables placement */
    .live-pulse { animation: livePulse2 1s infinite; }
    @keyframes livePulse2 { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
    
    /* Strict custom gaming cursor fix */
    #cursor, #cursor-ring { pointer-events: none !important; }
    body, html, button, .btn, a, select, option, input, textarea { cursor: none !important; }
    
    /* Scoreboard bar custom setup */
    .score-progress-bar { height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); border-radius: 3px; }
  </style>
</head>
<body>
<div class="cursor" id="cursor"></div><div class="cursor-ring" id="cursor-ring"></div>

<nav class="navbar" id="navbar">
  <a href="../index.php" class="nav-logo"><div class="logo-icon">⚡</div>PTA<span>Arena</span></a>
  <div class="nav-actions">
    <span style="color:var(--muted);font-size:13px;margin-right:15px;">👤 <?php echo htmlspecialchars($username); ?></span>
    <a href="dashboard.php" class="btn btn-outline btn-sm">← Dashboard</a>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-section"><div class="sidebar-title">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="tournaments.html" class="sidebar-link"><span class="s-icon">🏆</span> Tournaments</a>
      <a href="live-match.php" class="sidebar-link active"><span class="s-icon">🔴</span> Live Match</a>
      <a href="results.html" class="sidebar-link"><span class="s-icon">📋</span> Results</a>
    </div>
    <div class="sidebar-section"><div class="sidebar-title">My Account</div>
      <a href="wallet.php" class="sidebar-link"><span class="s-icon">🪙</span> Wallet</a>
      <a href="teams.html" class="sidebar-link"><span class="s-icon">👥</span> Teams</a>
      <a href="notifications.html" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="profile.html" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
    <div class="sidebar-section"><div class="sidebar-title">Explore</div>
      <a href="leaderboard.php" class="sidebar-link"><span class="s-icon">📊</span> Leaderboard</a>
      <a href="logout.php" class="sidebar-link"><span class="s-icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="dashboard-content">
    
    <?php if (!$live_match): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
        <div class="page-title">🔴 Live Match</div>
      </div>
      <div style="background: var(--surface); border: 1px solid var(--border); padding: 48px; text-align: center; border-radius: 4px;">
         <div style="font-size: 50px; margin-bottom: 16px;">💤</div>
         <h3 style="font-family:'Rajdhani',sans-serif; font-size: 24px; font-weight: 700; margin-bottom: 8px;">No Live Matches Right Now</h3>
         <p style="color: var(--muted); font-size: 14px;">Currently, there are no matches in progress. Check active tournaments to join the next battle!</p>
         <a href="tournaments.html" class="btn btn-primary btn-sm" style="margin-top: 20px; display: inline-flex;">Browse Tournaments</a>
      </div>

    <?php else: ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
        <div class="page-title">🔴 Live Match</div>
        <span style="background:rgba(255,59,92,.15);color:var(--danger);border:1px solid rgba(255,59,92,.3);font-size:11px;letter-spacing:1px;padding:4px 10px;animation:livePulse2 1.5s infinite; font-weight:700; border-radius:3px;">● LIVE NOW</span>
      </div>
      <div class="page-sub"><?php echo htmlspecialchars($live_match['tournament_name']); ?> — Currently in progress</div>

      <div style="background:linear-gradient(135deg,rgba(255,59,92,.1),rgba(123,46,255,.1));border:1px solid rgba(255,59,92,.3);padding:32px;margin-bottom:24px;position:relative;overflow:hidden">
        <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--danger),var(--primary));"></div>
        <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:24px;align-items:center;text-align:center">
          
          <div>
            <div style="font-size:48px;margin-bottom:8px">🎯</div>
            <div style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:22px"><?php echo htmlspecialchars($live_match['team1_name']); ?></div>
            <div style="font-size:13px;color:var(--muted);margin-top:4px;"><?php echo htmlspecialchars($live_match['team1_players']); ?></div>
          </div>
          
          <div>
            <div style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:52px;background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">VS</div>
            <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--danger);margin-top:8px;font-weight:700">● LIVE</div>
            <div id="match-timer" style="font-family:'Rajdhani',sans-serif;font-size:16px;color:var(--muted);margin-top:4px">00:00 elapsed</div>
          </div>
          
          <div>
            <div style="font-size:48px;margin-bottom:8px">🔥</div>
            <div style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:22px"><?php echo htmlspecialchars($live_match['team2_name']); ?></div>
            <div style="font-size:13px;color:var(--muted);margin-top:4px;"><?php echo htmlspecialchars($live_match['team2_players']); ?></div>
          </div>
          
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
        
        <div style="background:var(--surface);border:1px solid var(--border);padding:20px">
          <h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:16px;margin-bottom:14px">Match Details</h3>
          <div style="display:flex;flex-direction:column;gap:10px;font-size:14px">
            <div style="display:flex;justify-content:space-between"><span style="color:var(--muted)">Tournament</span><span><?php echo htmlspecialchars($live_match['tournament_name']); ?></span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--muted)">Mode</span><span><?php echo htmlspecialchars($live_match['match_mode']); ?></span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--muted)">Map</span><span><?php echo htmlspecialchars($live_match['match_map']); ?></span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--muted)">Round</span><span style="color:var(--secondary)"><?php echo htmlspecialchars($live_match['match_round']); ?></span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:var(--muted)">Status</span><span style="color:var(--danger)">● In Progress</span></div>
          </div>
        </div>
        
        <div style="background:var(--surface);border:1px solid var(--border);padding:20px">
          <h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:16px;margin-bottom:14px">Live Score</h3>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <span style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:36px;color:var(--primary)"><?php echo $live_match['team1_score']; ?></span>
            <span style="font-size:14px;color:var(--muted)">Rounds</span>
            <span style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:36px;color:var(--danger)"><?php echo $live_match['team2_score']; ?></span>
          </div>
          
          <?php 
            $t1 = $live_match['team1_score'];
            $t2 = $live_match['team2_score'];
            $total = $t1 + $t2;
            $percentage = ($total > 0) ? ($t1 / $total) * 100 : 50; 
          ?>
          <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;margin-bottom:8px">
             <div class="score-progress-bar" style="width: <?php echo $percentage; ?>%;"></div>
          </div>
          
          <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted)">
             <span>
               <?php 
                 if($t1 > $t2) echo htmlspecialchars($live_match['team1_name'])." leads"; 
                 elseif($t2 > $t1) echo htmlspecialchars($live_match['team2_name'])." leads";
                 else echo "Scores are tied";
               ?>
             </span>
             <span><?php echo "$t1 - $t2"; ?></span>
          </div>
        </div>
        
      </div>

      <div style="background:rgba(123,46,255,.08);border:1px solid rgba(123,46,255,.3);padding:20px; margin-bottom: 10px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
           <span style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:16px">🔐 Room Credentials</span>
           <span class="badge badge-danger" style="background:red; font-size:10px; padding:2px 6px; border-radius:3px; font-weight:700;">CONFIDENTIAL</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="background:rgba(255,255,255,.04);padding:12px">
             <div style="font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Room ID</div>
             <div style="font-family:'JetBrains Mono',monospace;font-weight:600;font-size:16px;letter-spacing:2px; color:var(--accent);"><?php echo htmlspecialchars($live_match['room_id']); ?></div>
          </div>
          <div style="background:rgba(255,255,255,.04);padding:12px">
             <div style="font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Password</div>
             <div style="font-family:'JetBrains Mono',monospace;font-weight:600;font-size:16px;letter-spacing:2px; color:var(--accent);"><?php echo htmlspecialchars($live_match['room_password']); ?></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

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

// Live Match Time Track Counter
<?php if ($live_match): ?>
  let sec = <?php echo $elapsed_seconds; ?>;
  const timerElement = document.getElementById('match-timer');
  
  if (timerElement) {
      setInterval(() => {
          sec++;
          const m = Math.floor(sec / 60);
          const s = sec % 60;
          timerElement.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')} elapsed`;
      }, 1000);
  }
<?php endif; ?>
</script>
</body>
</html>