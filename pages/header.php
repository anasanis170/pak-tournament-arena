<?php

include_once "config.php"; // ✅ Yeh line add karo
global $conn;

// ✅ Database se user ka coin balance fetch karo
$coins = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $coin_query = mysqli_query($conn, "SELECT coin FROM user WHERE id = $uid");
    if ($coin_query && mysqli_num_rows($coin_query) > 0) {
        $coin_data = mysqli_fetch_assoc($coin_query);
        $coins = intval($coin_data['coin']);
    }
} elseif (isset($_SESSION['username'])) {
    $uname = mysqli_real_escape_string($conn, $_SESSION['username']);
    $coin_query = mysqli_query($conn, "SELECT coin FROM user WHERE username = '$uname'");
    if ($coin_query && mysqli_num_rows($coin_query) > 0) {
        $coin_data = mysqli_fetch_assoc($coin_query);
        $coins = intval($coin_data['coin']);
    }
}

$unread_notifications = 3; 
?>

<header class="elite-app-header">
    
    <div class="header-brand-cluster">
        <div class="brand-logo-hex">P</div>
        <h1 class="brand-title-top">Pro<span>Arena</span></h1>
    </div>

    <div class="header-status-cluster">
        
        <a href="notifications.php" class="header-action-node <?php echo ($unread_notifications > 0) ? 'noti-active' : ''; ?>">
            <span class="custom-icon noti-bell-icon"></span>
            <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge-vip"><?php echo $unread_notifications; ?></span>
            <?php endif; ?>
        </a>

        <a href="wallet.php" class="header-wallet-card">
            <div class="coin-stack-icon">
                <span class="custom-icon dynamic-coin-hex"></span>
            </div>
            <div class="balance-details">
                <span class="balance-label">ARENA COINS</span>
                <span class="balance-value"><?php echo number_format($coins); ?></span>
            </div>
        </a>
    </div>
</header>

<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;900&display=swap');

.elite-app-header {
    position: fixed !important;
    top: 0 !important; 
    left: 0 !important; 
    right: 0 !important;
    height: 75px !important;
    background: #0a0d24 !important; 
    border-bottom: 2px solid #00f0ff !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 0 24px !important;
    z-index: 9999999 !important;
    box-sizing: border-box !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8) !important;
}

.header-brand-cluster { 
    display: flex !important; 
    align-items: center !important; 
    gap: 12px !important; 
}

.brand-logo-hex {
    width: 36px !important; 
    height: 36px !important;
    background: linear-gradient(135deg, #00f0ff 0%, #7b2eff 100%) !important;
    color: #050714 !important;
    display: flex !important; 
    align-items: center !important; 
    justify-content: center !important;
    font-weight: 900 !important; 
    font-family: 'Rajdhani', sans-serif !important; 
    font-size: 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 0 15px rgba(0, 240, 255, 0.5) !important;
}

.brand-title-top {
    font-family: 'Rajdhani', sans-serif !important;
    font-size: 26px !important; 
    font-weight: 900 !important;
    margin: 0 !important; 
    color: #ffffff !important;
    text-transform: uppercase !important; 
    letter-spacing: 0.5px !important;
    line-height: 1 !important;
}
.brand-title-top span { 
    color: #00f0ff !important; 
    text-shadow: 0 0 10px rgba(0, 240, 255, 0.6) !important; 
}

.header-status-cluster { 
    display: flex !important; 
    align-items: center !important; 
    gap: 16px !important; 
}

.custom-icon { 
    display: block !important; 
    background-size: contain !important; 
    background-repeat: no-repeat !important; 
    background-position: center !important; 
}

.header-action-node {
    position: relative !important;
    width: 42px !important; 
    height: 42px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 12px !important;
    display: flex !important; 
    align-items: center !important; 
    justify-content: center !important;
    text-decoration: none !important;
    transition: all 0.25s ease !important;
}

.noti-bell-icon {
    width: 22px !important; 
    height: 22px !important;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%2300f0ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>') !important;
}

.notification-badge-vip {
    position: absolute !important;
    top: -4px !important; 
    right: -4px !important;
    background: #ff007f !important;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif !important;
    font-weight: 700 !important; 
    font-size: 11px !important;
    width: 18px !important; 
    height: 18px !important;
    border-radius: 50% !important;
    display: flex !important; 
    align-items: center !important; 
    justify-content: center !important;
    border: 2px solid #0a0d24 !important;
}

.header-wallet-card {
    display: flex !important; 
    align-items: center !important; 
    gap: 12px !important;
    background: rgba(123, 46, 255, 0.15) !important; 
    border: 1px solid rgba(0, 240, 255, 0.3) !important;
    padding: 6px 16px !important;
    border-radius: 14px !important;
    text-decoration: none !important;
}

.coin-stack-icon {
    width: 28px !important; 
    height: 28px !important;
    display: flex !important; 
    align-items: center !important; 
    justify-content: center !important;
}

.dynamic-coin-hex {
    width: 26px !important; 
    height: 26px !important;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><linearGradient id="cg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%2300f0ff"/><stop offset="100%" style="stop-color:%237b2eff"/></linearGradient></defs><polygon points="50,5 90,28 90,72 50,95 10,72 10,28" stroke="%2300f0ff" stroke-width="5" fill="rgba(10,13,36,0.95)"/><polygon points="50,15 82,33 82,67 50,85 18,67 18,33" stroke="url(%23cg)" stroke-width="3" fill="url(%23cg)" fill-opacity="0.2"/><text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-size="46" font-family="Rajdhani,sans-serif" font-weight="900" fill="%2300f0ff">P</text></svg>') !important;
    filter: drop-shadow(0 0 6px rgba(0, 240, 255, 0.5)) !important;
}

.balance-details { display: flex !important; flex-direction: column !important; text-align: left !important; }
.balance-label { font-size: 9px !important; color: #8a8fa3 !important; font-weight: 700 !important; font-family: 'Rajdhani', sans-serif !important; letter-spacing: 0.8px !important; }
.balance-value { font-family: 'Rajdhani', sans-serif !important; font-weight: 700 !important; font-size: 18px !important; color: #ffffff !important; }

body { padding-top: 80px !important; }

@media (max-width: 480px) {
    .brand-title-top { font-size: 20px !important; }
    .brand-logo-hex { width: 30px !important; height: 30px !important; font-size: 16px !important; }
    .header-action-node { width: 36px !important; height: 36px !important; }
    .header-wallet-card { padding: 6px 10px !important; gap: 8px !important; }
    .balance-value { font-size: 14px !important; }
}
</style>