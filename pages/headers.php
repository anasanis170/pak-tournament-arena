<?php
// Admin Header — Sab Admin Files Mein Include Karo
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit(); }

if(!isset($conn)){ include "config.php"; }

$admin_check = mysqli_query($conn, "SELECT role FROM user WHERE username = '".mysqli_real_escape_string($conn, $_SESSION['username'])."'");
$admin_data = mysqli_fetch_assoc($admin_check);
if(!$admin_data || $admin_data['role'] !== 'admin'){ header("Location: dashboard.php"); exit(); }

$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --bg: #080b11; --card: #0e1322; --cyan: #00E5FF; --purple: #7B2EFF;
        --text: #fff; --muted: #6c7a9c; --border: rgba(255,255,255,0.05);
        --danger: #ff4757;
    }
    .admin-topbar {
        position:fixed; top:0; left:0; right:0; height:56px; background:var(--card);
        border-bottom:1px solid var(--border); display:flex; align-items:center;
        justify-content:space-between; padding:0 16px; z-index:1000;
    }
    .admin-topbar-logo { font-size:20px; font-weight:800; color:#fff; text-decoration:none; }
    .admin-topbar-logo span { color:var(--purple); }
    .admin-hamburger { background:none; border:none; color:#fff; font-size:22px; cursor:pointer; padding:8px; border-radius:6px; }
    .admin-hamburger:hover { background:rgba(123,46,255,0.1); }

    .admin-sidebar-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1999; display:none; }
    .admin-sidebar-overlay.active { display:block; }
    .admin-sidebar {
        position:fixed; top:0; left:-270px; width:270px; height:100%; background:var(--card);
        border-right:1px solid var(--border); z-index:2000; transition:left 0.3s; overflow-y:auto; padding:20px;
    }
    .admin-sidebar.active { left:0; }
    .admin-sidebar-close { position:absolute; top:12px; right:12px; background:none; border:none; color:#fff; font-size:20px; cursor:pointer; }
    .admin-sidebar-title { font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); font-weight:700; margin:16px 0 8px; }
    .admin-sidebar-link {
        display:flex; align-items:center; gap:10px; color:#94a3b8; text-decoration:none;
        padding:10px 12px; border-radius:6px; font-size:14px; font-weight:600; margin-bottom:2px; transition:all 0.2s;
    }
    .admin-sidebar-link:hover { background:rgba(123,46,255,0.08); color:#fff; }
    .admin-sidebar-link.active { background:var(--purple); color:#fff; }

    .admin-main-content { padding:70px 14px 40px; max-width:1000px; margin:0 auto; }

    @media(min-width:769px) {
        .admin-hamburger { display:none; }
        .admin-sidebar { left:0; }
        .admin-sidebar-close { display:none; }
        .admin-sidebar-overlay { display:none !important; }
        .admin-main-content { margin-left:270px; padding:70px 30px 40px; max-width:calc(100% - 270px); }
    }
</style>

<!-- TOP BAR -->
<header class="admin-topbar">
    <a href="admin-tournaments.php" class="admin-topbar-logo">⚡ PTA<span>Control</span></a>
    <button class="admin-hamburger" onclick="toggleAdminSidebar()">☰</button>
</header>

<!-- SIDEBAR OVERLAY -->
<div class="admin-sidebar-overlay" id="adminOverlay" onclick="toggleAdminSidebar()"></div>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
    <button class="admin-sidebar-close" onclick="toggleAdminSidebar()">✕</button>
    <div style="font-size:20px;font-weight:800;margin-bottom:20px;">⚡ PTA<span style="color:var(--purple);">Control</span></div>
    
    <div class="admin-sidebar-title">💰 Financials</div>
    <a href="admin.php" class="admin-sidebar-link <?php echo ($current_page=='admin.php')?'active':''; ?>">🪙 Wallet Requests</a>
    <a href="admin_win.php" class="admin-sidebar-link <?php echo ($current_page=='admin_win.php')?'active':''; ?>">💎 Add Coins</a>

    <div class="admin-sidebar-title">🎮 Game Core</div>
    <a href="admin-tournaments.php" class="admin-sidebar-link <?php echo ($current_page=='admin-tournaments.php')?'active':''; ?>">🏆 Manage Tournaments</a>
    <a href="admin_id_pass.php" class="admin-sidebar-link <?php echo ($current_page=='admin_id_pass.php')?'active':''; ?>">🔑 Room ID/Pass</a>
    <a href="admin_win.php" class="admin-sidebar-link <?php echo ($current_page=='admin_win.php')?'active':''; ?>">👑 Declare Winner</a>
    <a href="admin-live.php" class="admin-sidebar-link <?php echo ($current_page=='admin-live.php')?'active':''; ?>">🔴 Live Match</a>
    <a href="admin_view.php" class="admin-sidebar-link <?php echo ($current_page=='admin_view.php')?'active':''; ?>">👥 View Participants</a>
    <a href="admin-send-notification.php" class="admin-sidebar-link <?php echo ($current_page=='admin-send-notification.php')?'active':''; ?>">🔔 Send Notification</a>

    <div class="admin-sidebar-title">👤 Users</div>
    <a href="admin-users.php" class="admin-sidebar-link <?php echo ($current_page=='admin-users.php')?'active':''; ?>">👤 Total Players</a>
    <a href="admin-tickets.php" class="admin-sidebar-link <?php echo ($current_page=='admin-tickets.php')?'active':''; ?>">💬 Support Tickets</a>

    <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px;">
        <a href="dashboard.php" class="admin-sidebar-link">📊 Dashboard</a>
        <a href="logout.php" class="admin-sidebar-link" style="color:var(--danger);">🚪 Logout</a>
    </div>
</aside>

<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('active');
    document.getElementById('adminOverlay').classList.toggle('active');
}
document.querySelectorAll('.admin-sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('adminSidebar').classList.remove('active');
        document.getElementById('adminOverlay').classList.remove('active');
    });
});
</script>