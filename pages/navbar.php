<div class="vip-bottom-hud">
    
    <a href="wallet.php" class="hud-item <?php echo (basename($_SERVER['PHP_SELF']) == 'wallet.php') ? 'active' : ''; ?>">
        <span class="hud-icon">🪙</span>
        <span class="hud-label">Wallet</span>
    </a>

    <a href="dashboard.php" class="hud-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
        <span class="hud-icon">📋</span>
        <span class="hud-label">Dasboard</span>
    </a>

    <div class="hud-center-wrapper">
        <a href="index.php" class="hud-center-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'tournaments.php') ? 'active-center' : ''; ?>">
            <span class="center-icon">🏆</span>
        </a>
        <span class="center-label">Match</span>
    </div>

    <a href="leaderboard.php" class="hud-item <?php echo (basename($_SERVER['PHP_SELF']) == 'leaderboard.php') ? 'active' : ''; ?>">
        <span class="hud-icon">📈</span>
        <span class="hud-label">Leaderboard</span>
    </a>
    
    <a href="profile.php" class="hud-item <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
        <span class="hud-icon">👤</span>
        <span class="hud-label">Profile</span>
    </a>
</div>

<style>
:root {
    --bg-vip: #050714;
    --panel-vip: #0a0d24;
    --neon-cyan: #00f0ff;
    --neon-gold: #ffaa00;
    --text-muted: #7e84a3;
}

/* VIP FIXED BOTTOM HUD STYLING (Universal for Desktop & Mobile) */
.vip-bottom-hud {
    position: fixed;
    bottom: 0; 
    left: 0; 
    right: 0;
    height: 75px;
    background: linear-gradient(180deg, #0a0d24 0%, #050714 100%);
    border-top: 2px solid rgba(114, 36, 255, 0.3);
    display: flex;
    justify-content: space-around;
    align-items: center;
    z-index: 999999; /* Is se navbar har cheez ke upar dikhegi */
    padding-bottom: env(safe-area-inset-bottom);
    box-sizing: border-box;
    box-shadow: 0 -10px 35px rgba(5, 7, 20, 0.95);
}

/* NAVIGATION ITEMS SETUP */
.hud-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: var(--text-muted);
    width: 15%; /* Spacing adjusted for 6 items */
    height: 100%;
    position: relative;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.hud-icon { 
    font-size: 22px; 
    transition: transform 0.2s; 
}

.hud-label { 
    font-size: 11px; 
    font-weight: 700; 
    font-family: 'Rajdhani', sans-serif; 
    text-transform: uppercase; 
    letter-spacing: 0.5px; 
    margin-top: 4px; 
}

/* ACTIVE TABS CYBER GLOW SYSTEM */
.hud-item.active {
    color: var(--neon-cyan);
}

.hud-item.active .hud-icon {
    transform: translateY(-4px) scale(1.15);
    /* Neon Glow Glow Glow */
    text-shadow: 0 0 15px rgba(0, 240, 255, 0.9), 0 0 30px rgba(0, 240, 255, 0.5);
}

.hud-item.active .hud-label {
    color: #fff;
    text-shadow: 0 0 8px rgba(0, 240, 255, 0.6);
}

/* Electric Top Indicator Light */
.hud-item.active::before {
    content: '';
    position: absolute;
    top: -2px;
    width: 24px;
    height: 4px;
    background: var(--neon-cyan);
    border-radius: 0 0 4px 4px;
    box-shadow: 0 0 12px var(--neon-cyan), 0 0 25px var(--neon-cyan);
}

/* FLOATING TOURNAMENT CENTER HUD (Upar Utha Hua Button) */
.hud-center-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 18%;
    z-index: 9999999;
}

.hud-center-btn {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #141938 0%, #0a0d24 100%);
    border: 2px solid rgba(114, 36, 255, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    /* Is se button baki list se upar lift ho jata hai */
    transform: translateY(-24px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7), 0 0 0 5px #050714;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.center-icon { 
    font-size: 28px; 
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); 
}

.center-label {
    font-size: 14px;
    font-weight: 700;
    font-family: 'Rajdhani', sans-serif;
    color: var(--neon-gold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: absolute;
    bottom: -3px;
    white-space: nowrap;
}

/* ACTIVE TOURNAMENT IN VIP MODE */
.hud-center-btn.active-center {
    background: linear-gradient(135deg, #ffd700 0%, #ffaa00 100%);
    border-color: #ffffff;
    box-shadow: 0 12px 25px rgba(255, 170, 0, 0.5), 0 0 20px rgba(255, 215, 0, 0.7), 0 0 0 6px #050714;
}

.hud-center-btn.active-center .center-icon {
    animation: vipPulse 1.5s infinite alternate;
}

.hud-center-btn:hover {
    transform: translateY(-28px) scale(1.05);
}

@keyframes vipPulse {
    from { transform: scale(1); }
    to { transform: scale(1.12); }
}

/* RESPONSIVE PADDING ADJUSTMENT FOR CONTENT */
body {
    padding-bottom: 90px !important; /* Taake website ka content navbar ke piche na chupe */
}

@media (max-width: 768px) {
    .hud-center-btn {
        width: 56px;
        height: 56px;
        transform: translateY(-20px);
    }
    .center-icon { font-size: 26px; }
    .hud-icon { font-size: 18px; }
    .hud-label { font-size: 9px; }
}
</style>