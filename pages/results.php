<?php 
session_start(); 

include_once "config.php";
global $conn;

// 1. Database se saare completed tournaments fetch karna
$results_query = "SELECT * FROM tournament_results ORDER BY match_date DESC";
$results_res = mysqli_query($conn, $results_query);

// 2. Recent Champions showcase ke liye alag se data fetch karna (Top 3 unique winners)
$champions_query = "SELECT DISTINCT winner_name, match_type_label, prize_pool FROM tournament_results ORDER BY id DESC LIMIT 3";
$champions_res = mysqli_query($conn, $champions_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Results — PTA Arena</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="cursor" id="cursor"></div><div class="cursor-ring" id="cursor-ring"></div>

<nav class="navbar" id="navbar">
  <a href="index.php" class="nav-logo"><div class="logo-icon">⚡</div>PTA<span>Arena</span></a>
  <div class="nav-actions">
    <a href="dashboard.php" class="btn btn-outline btn-sm">← Dashboard</a>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-section"><div class="sidebar-title">Main</div>
      <a href="dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="tournaments.php" class="sidebar-link"><span class="s-icon">🏆</span> Tournaments</a>
      <a href="live-match.php" class="sidebar-link"><span class="s-icon">🔴</span> Live Match</a>
      <a href="results.php" class="sidebar-link active"><span class="s-icon">📋</span> Match Results</a>
    </div>
    <div class="sidebar-section"><div class="sidebar-title">My Account</div>
      <a href="wallet.php" class="sidebar-link"><span class="s-icon">🪙</span> Wallet</a>
      <a href="teams.php" class="sidebar-link"><span class="s-icon">👥</span> Teams</a>
      <a href="notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
    <div class="sidebar-section"><div class="sidebar-title">Explore</div>
      <a href="leaderboard.php" class="sidebar-link"><span class="s-icon">📊</span> Leaderboard</a>
      <a href="logout.php" class="sidebar-link"><span class="s-icon">🚪</span> Logout</a>
    </div>
  </aside>

  <main class="dashboard-content">
    <div class="page-title">📋 Match Results</div>
    <div class="page-sub">View results from completed tournaments and your performance stats.</div>
    
    <div class="data-table-wrap reveal">
      <div style="padding:20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:18px">Completed Tournaments</h3>
        <input type="text" id="search-input" class="form-input" style="width:220px" placeholder="🔍 Search results..." onkeyup="searchTable()">
      </div>
      
      <table class="data-table" id="results-table">
        <thead>
          <tr>
            <th>Tournament</th>
            <th>Game</th>
            <th>Date</th>
            <th>Winner</th>
            <th>Prize</th>
            <th>Your Result</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($results_res && mysqli_num_rows($results_res) > 0): 
              while($row = mysqli_fetch_assoc($results_res)):
          ?>
            <tr>
              <td style="font-weight:500"><?php echo htmlspecialchars($row['tournament_name']); ?></td>
              <td style="color:var(--muted)"><?php echo htmlspecialchars($row['game_name']); ?></td>
              <td style="color:var(--muted)"><?php echo date("Y-m-d", strtotime($row['match_date'])); ?></td>
              <td><span style="color:var(--accent)">🥇 <?php echo htmlspecialchars($row['winner_name']); ?></span></td>
              <td style="color:var(--accent);font-weight:700">🪙 <?php echo number_format($row['prize_pool']); ?></td>
              <td>
                <span class="badge badge-<?php echo htmlspecialchars($row['badge_type']); ?>">
                  <?php echo htmlspecialchars($row['user_result_badge']); ?>
                </span>
              </td>
            </tr>
          <?php 
              endwhile; 
          else: 
          ?>
            <tr>
              <td colspan="6" style="text-align:center; color:var(--muted); padding: 20px;">No completed tournaments found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <h3 style="font-family:'Rajdhani',sans-serif;font-weight:600;font-size:20px;margin:32px 0 16px">🏆 Recent Champions</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
      <?php 
      if ($champions_res && mysqli_num_rows($champions_res) > 0):
          while($champ = mysqli_fetch_assoc($champions_res)):
      ?>
          <div style="background:linear-gradient(135deg,rgba(255,184,0,.1),rgba(255,184,0,.05));border:1px solid rgba(255,184,0,.3);padding:24px;text-align:center">
            <div style="font-size:48px;margin-bottom:8px">🔥</div>
            <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:6px">
                <?php echo htmlspecialchars($champ['match_type_label']); ?>
            </div>
            <div style="font-family:'Rajdhani',sans-serif;font-weight:700;font-size:18px;color:var(--accent)">
                <?php echo htmlspecialchars($champ['winner_name']); ?>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:4px">
                🪙 <?php echo number_format($champ['prize_pool']); ?> Won
            </div>
          </div>
      <?php 
          endwhile;
      else:
      ?>
        <div style="color:var(--muted); font-size:14px;">No recent champions to display.</div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
function searchTable() {
  let input = document.getElementById("search-input").value.toLowerCase();
  let table = document.getElementById("results-table");
  let tr = table.getElementsByTagName("tr");

  for (let i = 1; i < tr.length; i++) {
    let tdTournament = tr[i].getElementsByTagName("td")[0];
    let tdWinner = tr[i].getElementsByTagName("td")[3];
    if (tdTournament || tdWinner) {
      let txtValue1 = tdTournament.textContent || tdTournament.innerText;
      let txtValue2 = tdWinner.textContent || tdWinner.innerText;
      if (txtValue1.toLowerCase().indexOf(input) > -1 || txtValue2.toLowerCase().indexOf(input) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}
</script>
<script src="app.js"></script>
</body>
</html>