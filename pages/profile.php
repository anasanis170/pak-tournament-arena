<?php 
session_start(); 

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

include_once "config.php";
global $conn;

$user_data = [];
if ($conn && $user_id) {
    $res = mysqli_query($conn, "SELECT * FROM user WHERE id = $user_id");
    if ($res && mysqli_num_rows($res) > 0) {
        $user_data = mysqli_fetch_assoc($res);
    }
}

if (empty($user_data)) {
    $user_data = [
        'name' => 'Player', 'username' => 'player', 'email' => '', 'avatar' => '🎮',
        'role' => 'user', 'matches' => 0, 'wins' => 0, 'kills' => 0, 'coin' => 0
    ];
}

// Dynamic stats
$total_matches = 0; $total_wins = 0;
if ($conn && $user_id) {
    $m_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM tournament_participants WHERE user_id = $user_id");
    $total_matches = mysqli_fetch_assoc($m_q)['total'] ?? 0;
    $w_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE username='".($user_data['username']??'')."' AND description LIKE '%Won%' AND type='credit'");
    $total_wins = mysqli_fetch_assoc($w_q)['total'] ?? 0;
}

$message = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : "";
unset($_SESSION['msg']); unset($_SESSION['msg_type']);

$pcoin_gold = '<span class="pcoin pcoin-gold"></span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>VIP Profile — PTA Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><style>
    :root {
        --primary: #8B5CF6; --secondary: #00F0FF; --danger: #FF4655;
        --success: #00FF87; --gold: #FFD700; --muted: #94A3B8;
        --border: rgba(255,255,255,0.08); --card-bg: rgba(10,14,35,0.75); --bg-dark: #040817;
        --whatsapp: #25D366;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:var(--bg-dark); color:#fff; font-family:'Space Grotesk',sans-serif; padding-bottom:90px; overflow-x:hidden;
        background-image: radial-gradient(circle at 50% 0%, rgba(139,92,246,0.25) 0%, transparent 50%),
        radial-gradient(circle at 0% 100%, rgba(0,240,255,0.05) 0%, transparent 40%); }

    .app-container { max-width:480px; margin:0 auto; padding:20px; }

    /* P-COIN */
    .pcoin {
        display:inline-block; width:18px; height:18px; vertical-align:middle; margin-right:4px;
        background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300F0FF" stroke-width="5" fill="rgba(5,7,20,0.95)"/><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="46" font-family="Rajdhani,sans-serif" font-weight="900" fill="%2300F0FF">P</text></svg>');
        background-size:contain; background-repeat:no-repeat; flex-shrink:0; filter:drop-shadow(0 0 4px rgba(0,240,255,0.5));
    }
    .pcoin.pcoin-gold {
        background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%23FFD700" stroke-width="5" fill="rgba(5,7,20,0.95)"/><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="46" font-family="Rajdhani,sans-serif" font-weight="900" fill="%23FFD700">P</text></svg>');
        filter:drop-shadow(0 0 6px rgba(255,215,0,0.5));
    }

    @keyframes fadeUp { from{opacity:0;transform:translateY(15px);} to{opacity:1;transform:translateY(0);} }
    @keyframes glow { 0%,100%{box-shadow:0 0 20px rgba(139,92,246,0.4);} 50%{box-shadow:0 0 35px rgba(0,240,255,0.5);} }
    @keyframes neonPulse { 0%,100%{border-color:rgba(37,211,102,0.3);box-shadow:0 0 10px rgba(37,211,102,0.1);} 50%{border-color:rgba(37,211,102,0.7);box-shadow:0 0 20px rgba(37,211,102,0.3);} }
    @keyframes slideIn { from{opacity:0;transform:translateX(-10px);} to{opacity:1;transform:translateX(0);} }
    .anim-1{animation:fadeUp 0.4s ease forwards;}
    .anim-2{animation:fadeUp 0.4s ease 0.05s forwards;opacity:0;}
    .anim-3{animation:fadeUp 0.4s ease 0.1s forwards;opacity:0;}
    .anim-4{animation:fadeUp 0.4s ease 0.15s forwards;opacity:0;}

    .profile-hero{text-align:center;padding:20px 0 10px;}
    .avatar-wrapper{position:relative;width:110px;height:110px;margin:0 auto 12px;}
    .profile-avatar{width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,#0b0f26,#1e264f);display:flex;align-items:center;justify-content:center;font-size:50px;border:3px solid var(--primary);animation:glow 3s infinite;overflow:hidden;}
    .edit-avatar-badge{position:absolute;bottom:2px;right:2px;background:linear-gradient(135deg,var(--primary),#6d28d9);width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;cursor:pointer;border:2px solid var(--bg-dark);}
    .profile-name{font-family:'Rajdhani',sans-serif;font-size:32px;font-weight:800;color:#fff;}
    .vip-pass-badge{display:inline-flex;align-items:center;gap:4px;background:linear-gradient(90deg,#5b21b6,#7c3aed);padding:3px 10px;border-radius:6px;font-size:10px;font-weight:800;color:#fff;font-family:'Rajdhani',sans-serif;text-transform:uppercase;margin-top:5px;border:1px solid rgba(255,215,0,0.4);}
    .profile-username{font-size:14px;color:var(--secondary);font-weight:600;margin-top:4px;}
    .profile-uid{font-size:11px;color:#fff;margin-top:6px;font-weight:700;font-family:'Rajdhani',sans-serif;background:rgba(0,240,255,0.08);display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;border:1px solid rgba(0,240,255,0.2);}

    .vip-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:15px 0;}
    .stat-card{background:rgba(16,22,50,0.6);border:1px solid var(--border);border-radius:12px;padding:12px 4px;text-align:center;transition:all 0.25s;}
    .stat-card:hover{transform:translateY(-2px);border-color:rgba(0,240,255,0.4);}
    .stat-icon{font-size:18px;margin-bottom:3px;}
    .stat-num{font-family:'Rajdhani',sans-serif;font-size:20px;font-weight:800;color:#fff;display:flex;align-items:center;justify-content:center;gap:3px;}
    .stat-card.coins .stat-num{color:var(--gold);text-shadow:0 0 8px rgba(255,215,0,0.3);}
    .stat-card.wins .stat-num{color:var(--success);text-shadow:0 0 8px rgba(0,255,135,0.3);}
    .stat-label{font-size:9px;color:var(--muted);text-transform:uppercase;font-weight:700;margin-top:2px;}

    /* WHATSAPP */
    .whatsapp-card{background:rgba(10,14,35,0.6);border:1.5px solid rgba(37,211,102,0.3);border-radius:14px;padding:14px 16px;margin:5px 0 12px;display:flex!important;align-items:center;justify-content:space-between;text-decoration:none;color:#fff;animation:neonPulse 3s infinite;}
    .whatsapp-card:hover{transform:translateY(-3px);border-color:rgba(37,211,102,0.8);}
    .whatsapp-left{display:flex;align-items:center;gap:12px;}
    .whatsapp-icon{background:#25D366;color:#fff;width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 4px 15px rgba(37,211,102,0.4);}
    .whatsapp-info h4{font-family:'Rajdhani',sans-serif;font-size:16px;font-weight:800;color:#25D366;margin:0;}
    .whatsapp-info p{font-size:12px;color:var(--muted);margin-top:1px;}
    .whatsapp-right{background:rgba(37,211,102,0.15);color:#25D366;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;}

    /* MENU */
    .menu-list{background:var(--card-bg);border-radius:16px;overflow:hidden;border:1px solid var(--border);}
    .menu-item{display:flex;align-items:center;justify-content:space-between;padding:16px;border-bottom:1px solid rgba(255,255,255,0.02);text-decoration:none;color:#fff;font-size:14px;font-weight:600;transition:all 0.2s;}
    .menu-item:last-child{border-bottom:none;}
    .menu-item:hover{background:rgba(139,92,246,0.08);padding-left:20px;}
    .menu-item-left{display:flex;align-items:center;gap:12px;}
    .menu-icon-box{width:34px;height:34px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;transition:all 0.2s;}
    .menu-item:hover .menu-icon-box{background:rgba(0,240,255,0.12);border-color:rgba(0,240,255,0.3);}
    .menu-arrow{color:rgba(255,255,255,0.15);font-size:11px;transition:all 0.2s;}
    .menu-item:hover .menu-arrow{transform:translateX(4px);color:var(--secondary);}

    .switch{position:relative;display:inline-block;width:40px;height:22px;}
    .switch input{opacity:0;width:0;height:0;}
    .slider{position:absolute;cursor:pointer;inset:0;background-color:#161c36;border-radius:24px;transition:.2s;}
    .slider:before{position:absolute;content:"";height:14px;width:14px;left:3px;bottom:3px;background-color:#94a3b8;border-radius:50%;transition:.2s;}
    input:checked+.slider{background-color:var(--primary);}
    input:checked+.slider:before{transform:translateX(18px);background-color:#fff;}

    .edit-profile-section{background:var(--card-bg);border:1px solid rgba(0,240,255,0.2);border-radius:16px;padding:20px;margin-top:15px;display:none;}
    .edit-profile-section.active{display:block;animation:fadeUp 0.3s ease forwards;}
    .form-group{margin-bottom:14px;display:flex;flex-direction:column;gap:5px;}
    .form-label{font-size:11px;font-weight:700;text-transform:uppercase;color:var(--secondary);font-family:'Rajdhani',sans-serif;}
    .form-input{background:#060919;color:#fff;border:1px solid rgba(255,255,255,0.08);padding:12px;border-radius:10px;font-size:14px;width:100%;}
    .form-input:focus{border-color:var(--secondary);outline:none;box-shadow:0 0 12px rgba(0,240,255,0.2);}
    .btn-save{background:linear-gradient(135deg,var(--primary),#6d28d9);color:#fff;font-weight:700;width:100%;border:none;padding:14px;border-radius:10px;cursor:pointer;font-family:'Rajdhani',sans-serif;text-transform:uppercase;letter-spacing:1px;font-size:15px;margin-top:5px;}
    .btn-save:hover{box-shadow:0 0 20px rgba(139,92,246,0.5);}
    .logout-item{color:var(--danger)!important;}
    .logout-item .menu-icon-box{background:rgba(255,70,85,0.08);color:var(--danger);}
    .whatsapp-card {
    background: rgba(10,14,35,0.8) !important;
    border: 2px solid #25D366 !important;
    border-radius: 14px;
    padding: 14px 16px;
    margin: 10px 0 15px;
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    text-decoration: none;
    color: #fff;
    visibility: visible !important;
    opacity: 1 !important;
    height: auto !important;
    min-height: 60px;
}
.whatsapp-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.whatsapp-icon {
    background: #25D366 !important;
    color: #fff !important;
    width: 44px !important;
    height: 44px !important;
    min-width: 44px;
    border-radius: 12px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    font-size: 24px !important;
}
.whatsapp-info h4 {
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 800;
    color: #25D366 !important;
    margin: 0;
}
.whatsapp-info p {
    font-size: 13px;
    color: #94A3B8;
    margin-top: 2px;
}
.whatsapp-right {
    background: rgba(37,211,102,0.2);
    color: #25D366;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}
</style>
</head>
<body>

<?php if (file_exists("header.php")) { include "header.php"; } ?>

<div class="app-container">

    <?php if(!empty($message)): ?>
        <div style="padding:12px;border-radius:10px;font-size:14px;text-align:center;margin-bottom:15px;font-weight:600;
            background:<?php echo $msg_type=='success'?'rgba(0,255,135,0.12)':'rgba(255,70,85,0.12)'; ?>;
            border:1px solid <?php echo $msg_type=='success'?'rgba(0,255,135,0.25)':'rgba(255,70,85,0.25)'; ?>;
            color:<?php echo $msg_type=='success'?'#00FF87':'#FF4655'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="profile-hero anim-1">
        <div class="avatar-wrapper" onclick="toggleEditForm()">
            <div class="profile-avatar"><?php echo htmlspecialchars($user_data['avatar'] ?? '🎮'); ?></div>
            <div class="edit-avatar-badge">✏️</div>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($user_data['name']); ?></div>
        <div class="vip-pass-badge">👑 VIP PASS</div>
        <div class="profile-username">@<?php echo htmlspecialchars($user_data['username']); ?></div>
        <div class="profile-uid">🎫 UID: PTA-<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></div>
    </div>

    <!-- VIP STATS -->
    <div class="vip-stats-grid anim-2">
        <div class="stat-card"><div class="stat-icon">🎮</div><div class="stat-num"><?php echo number_format($total_matches); ?></div><div class="stat-label">Matches</div></div>
        <div class="stat-card wins"><div class="stat-icon">🏆</div><div class="stat-num"><?php echo number_format($total_wins); ?></div><div class="stat-label">Wins</div></div>
        <div class="stat-card"><div class="stat-icon">💀</div><div class="stat-num"><?php echo number_format($user_data['kills'] ?? 0); ?></div><div class="stat-label">Kills</div></div>
        <div class="stat-card coins"><div class="stat-icon"><?php echo $pcoin_gold; ?></div><div class="stat-num"><?php echo number_format($user_data['coin'] ?? 0); ?></div><div class="stat-label">Coins</div></div>
    </div>

    <!-- WHATSAPP CARD -->
    <a href="https://wa.me/030303030" target="_blank" class="whatsapp-card anim-3">
        <div class="whatsapp-left">
            <div class="whatsapp-icon"><i class="fa-brands fa-whatsapp"></i></div>
            <div class="whatsapp-info">
                <h4>WhatsApp Support</h4>
                <p>+92 300 0000000</p>
            </div>
        </div>
        <div class="whatsapp-right"><i class="fa-brands fa-whatsapp"></i> Chat</div>
    </a>

    <!-- MENU -->
    <div class="menu-list anim-4">
        <div class="menu-item">
            <div class="menu-item-left"><div class="menu-icon-box">🔔</div><span>Notifications</span></div>
            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
        </div>
        <a href="#" class="menu-item" onclick="toggleEditForm();return false;">
            <div class="menu-item-left"><div class="menu-icon-box">⚙️</div><span>Edit Profile</span></div><div class="menu-arrow">❯</div>
        </a>
        <a href="tournaments.php" class="menu-item">
            <div class="menu-item-left"><div class="menu-icon-box">🏆</div><span>Tournaments</span></div><div class="menu-arrow">❯</div>
        </a>
        <a href="leaderboard.php" class="menu-item">
            <div class="menu-item-left"><div class="menu-icon-box">🏅</div><span>Leaderboard</span></div><div class="menu-arrow">❯</div>
        </a>
        <a href="contact.php" class="menu-item">
            <div class="menu-item-left"><div class="menu-icon-box"><i class="fa-solid fa-headset"></i></div><span>Contact Us</span></div><div class="menu-arrow">❯</div>
        </a>
        <a href="privacy.php" class="menu-item">
            <div class="menu-item-left"><div class="menu-icon-box">🛡️</div><span>Privacy Policy</span></div><div class="menu-arrow">❯</div>
        </a>
        <a href="terms.php" class="menu-item">
            <div class="menu-item-left"><div class="menu-icon-box">📋</div><span>Terms & Conditions</span></div><div class="menu-arrow">❯</div>
        </a>
        <a href="logout.php" class="menu-item logout-item">
            <div class="menu-item-left"><div class="menu-icon-box">🚪</div><span>Logout</span></div><div class="menu-arrow">❯</div>
        </a>
    </div>

    <!-- EDIT FORM -->
    <div class="edit-profile-section" id="edit-form-block">
        <h3 style="font-family:'Rajdhani',sans-serif;font-size:20px;font-weight:800;margin-bottom:15px;color:var(--secondary);">📝 Edit Details</h3>
        <form action="profile_action.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user_data['name']); ?>" required></div>
            <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($user_data['username']); ?>" required></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user_data['email']); ?>" required></div>
            <button type="submit" name="update_profile" class="btn-save">💾 Save Changes</button>
        </form>
    </div>

</div>

<?php if (file_exists("navbar.php")) { include "navbar.php"; } ?>

<script>
function toggleEditForm() {
    var form = document.getElementById('edit-form-block');
    form.classList.contains('active') ? form.classList.remove('active') : form.classList.add('active');
    if(form.classList.contains('active')) setTimeout(() => form.scrollIntoView({ behavior: 'smooth' }), 100);
}
</script>
</body>
</html>