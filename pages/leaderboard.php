<?php
session_start();

if (file_exists("config.php")) {
    include "config.php";
} else {
    $conn = false;
}

global $conn;

$sort_by = "wins"; 
if (isset($_GET['sort'])) {
    $allowed_sorts = ['wins', 'coin', 'matches'];
    if (in_array($_GET['sort'], $allowed_sorts)) {
        $sort_by = $_GET['sort'];
    }
}

$players = [];
if ($conn) {
    $query_string = "SELECT name, username, coin, kills, wins, matches, role FROM user ORDER BY $sort_by DESC LIMIT 50";
    $result = mysqli_query($conn, $query_string);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $players[] = $row;
        }
    }
}

$pcoin = '<span class="pcoin"></span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard — PTA Arena</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #02040a; --card: #0c0f24; --cyan: #00E5FF; --purple: #7B2EFF;
            --text: #fff; --muted: #8a8fa3; --border: rgba(255,255,255,0.06);
            --gold: #ffaa00; --silver: #c0c0c0; --bronze: #cd7f32;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:var(--bg); color:var(--text); font-family:'Space Grotesk',sans-serif; overflow-x:hidden; }
        
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }
        @keyframes glowPulse { 0%,100%{box-shadow:0 0 20px rgba(255,170,0,0.3);} 50%{box-shadow:0 0 40px rgba(255,170,0,0.6);} }
        .anim { animation:fadeUp 0.5s ease forwards; }

        .pcoin {
            display:inline-block; width:16px; height:16px; vertical-align:middle; margin-right:4px;
            background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300E5FF" stroke-width="5" fill="rgba(5,7,20,0.95)"/><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="46" font-family="Rajdhani,sans-serif" font-weight="900" fill="%2300E5FF">P</text></svg>');
            background-size:contain; background-repeat:no-repeat; flex-shrink:0;
        }

        .lb-container { max-width:600px; margin:0 auto; padding:80px 12px 100px; }

        .lb-header { text-align:center; margin-bottom:24px; }
        .lb-header h2 { font-family:'Rajdhani',sans-serif; font-size:clamp(24px,5vw,32px); font-weight:800; color:var(--cyan); text-transform:uppercase; letter-spacing:1px; }
        .lb-header p { color:var(--muted); font-size:13px; }

        /* ✅ VIP PODIUM */
        .podium { display:flex; justify-content:center; align-items:flex-end; gap:12px; margin-bottom:28px; padding:20px 10px 0; }
        .podium-col { text-align:center; flex:1; max-width:150px; position:relative; }
        .podium-avatar {
            width:55px; height:55px; border-radius:50%; margin:0 auto 10px;
            display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800;
            border:3px solid transparent;
        }
        .podium-col.gold .podium-avatar { 
            background:linear-gradient(135deg,#ffaa00,#ff6b00); color:#000; 
            border-color:var(--gold); animation:glowPulse 2s infinite; width:65px; height:65px; font-size:26px;
        }
        .podium-col.silver .podium-avatar { background:linear-gradient(135deg,#c0c0c0,#999); color:#000; border-color:var(--silver); }
        .podium-col.bronze .podium-avatar { background:linear-gradient(135deg,#cd7f32,#a0522d); color:#fff; border-color:var(--bronze); }
        .podium-name { font-size:12px; font-weight:700; color:#fff; margin:6px 0 2px; }
        .podium-score { font-size:10px; color:var(--muted); }
        .podium-bar {
            padding:10px 0; border-radius:8px 8px 0 0; font-weight:800; font-size:18px; color:#fff;
            font-family:'Rajdhani',sans-serif; margin-top:8px;
        }

        /* ✅ FILTER TABS */
        .filter-tabs { display:flex; gap:8px; margin-bottom:20px; overflow-x:auto; padding-bottom:4px; }
        .filter-tab {
            padding:10px 18px; border-radius:10px; font-size:12px; font-weight:700; text-transform:uppercase;
            text-decoration:none; color:var(--muted); background:var(--card); border:1px solid var(--border);
            font-family:'Rajdhani',sans-serif; white-space:nowrap; transition:all 0.3s;
        }
        .filter-tab.active { background:linear-gradient(135deg,var(--purple),#5a1fd6); color:#fff; border-color:var(--purple); }
        .filter-tab:hover { border-color:var(--cyan); }

        /* ✅ PLAYER LIST */
        .player-list { background:var(--card); border:1px solid var(--border); border-radius:16px; overflow:hidden; }
        .player-row {
            display:flex; align-items:center; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border);
            transition:all 0.2s;
        }
        .player-row:last-child { border-bottom:none; }
        .player-row:hover { background:rgba(123,46,255,0.05); }
        .player-rank { 
            width:35px; font-weight:900; font-family:'Rajdhani',sans-serif; font-size:16px; 
            color:var(--muted); text-align:center; 
        }
        .player-rank.top3 { font-size:22px; }
        .player-avatar {
            width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:15px; color:#fff; flex-shrink:0;
        }
        .player-info { flex:1; min-width:0; }
        .player-name { font-weight:600; font-size:14px; color:#fff; }
        .player-role { font-size:10px; color:var(--muted); }
        .player-stats { display:flex; gap:14px; font-size:13px; font-weight:600; }
        .player-stat { text-align:center; min-width:40px; }
        .player-stat-val { color:#fff; }
        .player-stat-lbl { font-size:9px; color:var(--muted); text-transform:uppercase; }

        .no-data { text-align:center; padding:40px; color:var(--muted); }

        @media(max-width:480px) {
            .lb-container { padding:70px 8px 100px; }
            .player-stats { gap:8px; font-size:11px; }
            .player-row { padding:10px; gap:6px; }
            .podium-col { max-width:100px; }
            .podium-avatar { width:45px; height:45px; font-size:18px; }
            .podium-col.gold .podium-avatar { width:55px; height:55px; font-size:22px; }
        }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="lb-container">

    <div class="lb-header anim">
        <h2>🏆 Leaderboard</h2>
        <p>Top warriors ranked by performance</p>
    </div>

    <!-- ✅ VIP PODIUM - 1st, 2nd, 3rd -->
  <!-- ✅ VIP PODIUM WITH MEDALS -->
<?php if(count($players) >= 3): ?>
<div class="podium anim" style="animation-delay:0.1s;">
    <!-- 🥈 2nd Place -->
    <div class="podium-col silver">
        <div class="podium-avatar"><?php echo strtoupper(substr($players[1]['name'],0,1)); ?></div>
        <div style="font-size:28px;margin:4px 0;">🥈</div>
        <div class="podium-name"><?php echo htmlspecialchars($players[1]['name']); ?></div>
        <div class="podium-score"><?php echo number_format($players[1][$sort_by]); ?> <?php echo $sort_by; ?></div>
        <div class="podium-bar" style="background:linear-gradient(135deg,#c0c0c0,#888);height:80px;">
            🎖️ 2nd
        </div>
    </div>
    <!-- 🥇 1st Place -->
    <div class="podium-col gold">
        <div style="font-size:40px;margin-bottom:2px;">👑</div>
        <div class="podium-avatar"><?php echo strtoupper(substr($players[0]['name'],0,1)); ?></div>
        <div style="font-size:32px;margin:4px 0;">🥇</div>
        <div class="podium-name"><?php echo htmlspecialchars($players[0]['name']); ?></div>
        <div class="podium-score"><?php echo number_format($players[0][$sort_by]); ?> <?php echo $sort_by; ?></div>
        <div class="podium-bar" style="background:linear-gradient(135deg,#ffaa00,#ff6b00);height:120px;">
            🏆 CHAMPION
        </div>
    </div>
    <!-- 🥉 3rd Place -->
    <div class="podium-col bronze">
        <div class="podium-avatar"><?php echo strtoupper(substr($players[2]['name'],0,1)); ?></div>
        <div style="font-size:28px;margin:4px 0;">🥉</div>
        <div class="podium-name"><?php echo htmlspecialchars($players[2]['name']); ?></div>
        <div class="podium-score"><?php echo number_format($players[2][$sort_by]); ?> <?php echo $sort_by; ?></div>
        <div class="podium-bar" style="background:linear-gradient(135deg,#cd7f32,#a0522d);height:60px;">
            🎖️ 3rd
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- ✅ FILTER TABS (By Kills Hata Diya) -->
    <div class="filter-tabs anim" style="animation-delay:0.2s;">
        <a href="?sort=wins" class="filter-tab <?php echo $sort_by=='wins'?'active':''; ?>">🏆 Wins</a>
        <a href="?sort=coin" class="filter-tab <?php echo $sort_by=='coin'?'active':''; ?>"><?php echo $pcoin; ?> Coins</a>
        <a href="?sort=matches" class="filter-tab <?php echo $sort_by=='matches'?'active':''; ?>">🎮 Matches</a>
    </div>

    <!-- ✅ PLAYER LIST -->
    <div class="player-list anim" style="animation-delay:0.3s;">
        <?php if(empty($players)): ?>
            <div class="no-data">No warriors found</div>
        <?php else: ?>
            <?php foreach($players as $i => $p): 
                $rank = $i + 1;
                $rank_class = ($rank<=3)?'top3':'';
                $emoji = ($rank==1)?'🥇':(($rank==2)?'🥈':(($rank==3)?'🥉':'#'.$rank));
                $colors = ['#00f0ff','#7b2eff','#ff007f','#ffaa00','#00e676','#ff4757','#2ed573'];
                $bg = $colors[$i % count($colors)];
            ?>
            <div class="player-row">
                <div class="player-rank <?php echo $rank_class; ?>"><?php echo $emoji; ?></div>
                <div class="player-avatar" style="background:<?php echo $bg; ?>;"><?php echo strtoupper(substr($p['name'],0,1)); ?></div>
                <div class="player-info">
                    <div class="player-name"><?php echo htmlspecialchars($p['name']); ?></div>
                    <div class="player-role"><?php echo $p['role']=='admin'?'👑 Admin':'🎮 Player'; ?></div>
                </div>
                <div class="player-stats">
                    <div class="player-stat"><div class="player-stat-val"><?php echo $p['wins']; ?></div><div class="player-stat-lbl">Wins</div></div>
                    <div class="player-stat"><div class="player-stat-val"><?php echo $p['matches']; ?></div><div class="player-stat-lbl">Match</div></div>
                    <div class="player-stat"><div class="player-stat-val" style="color:var(--gold);"><?php echo number_format($p['coin']); ?></div><div class="player-stat-lbl">Coins</div></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php include "navbar.php"; ?>

</body>
</html>