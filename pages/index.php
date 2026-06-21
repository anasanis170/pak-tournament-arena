<?php
// 1. Session start standard configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}

// 2. Database connection include
if (file_exists("config.php")) {
    include "config.php";
} elseif (file_exists("../config.php")) {
    include "../config.php";
} elseif (file_exists("../../config.php")) {
    include "../../config.php";
} else {
    $conn = false;
}

global $conn;

// 3. Fetch dynamic counter stats from database safely
$live_count = 12;      // Fallback default numbers
$upcoming_count = 28;
$completed_count = 156;

if ($conn) {
    // Live counters filter execution
    $live_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM tournaments WHERE LOWER(status) IN ('live', 'active', 'open')");
    $live_count = ($live_q) ? mysqli_fetch_assoc($live_q)['total'] : $live_count;

    // Upcoming counters filter execution
    $up_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM tournaments WHERE LOWER(status) = 'upcoming'");
    $upcoming_count = ($up_q) ? mysqli_fetch_assoc($up_q)['total'] : $upcoming_count;

    // Completed counters filter execution
    $comp_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM tournaments WHERE LOWER(status) = 'completed'");
    $completed_count = ($comp_q) ? mysqli_fetch_assoc($comp_q)['total'] : $completed_count;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="google-site-verification" content="EpaDGNvYKSMaYMV2oLLdvKt4OppKvMA6462KycfSLlg" />

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — PTA Arena Pro</title>
    <link rel="stylesheet" href="style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-glow: #7B2EFF;
            --secondary-glow: #00E5FF;
            --panel-bg: #050714;
            --card-bg: #0c0f24;
            --neon-pink: #ff4757;
            --neon-green: #2ed573;
            --upcoming-yellow: #feca57;
        }

        body {
            background-color: #02040a;
            color: #ffffff;
            font-family: 'Space Grotesk', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        #cursor,
        #cursor-ring {
            pointer-events: none !important;
            z-index: 99999;
        }

        /* App Layout Main Container */
        .pta-dashboard-wrapper {
            max-width: 1140px;
            margin: 0 auto;
            padding: 40px 20px 120px 20px;
            box-sizing: border-box;
        }

        /* ─── PREMIUM GAMING HERO BANNER ─── */
        .pta-hero-banner {
            width: 100%;
            height: 280px;
            background: linear-gradient(rgba(5, 7, 20, 0.4), #02040a), url('https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop') center center;
            background-size: cover;
            border: 1px solid rgba(123, 46, 255, 0.3);
            border-radius: 16px;
            box-sizing: border-box;
            position: relative;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .banner-title-hud {
            font-family: 'Rajdhani', sans-serif;
            font-size: 44px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.1;
            letter-spacing: 2px;
            text-shadow: 0 0 25px rgba(0, 229, 255, 0.6), 0 0 10px rgba(123, 46, 255, 0.8);
        }

        /* ─── COUNTER HUB CARDS ─── */
        .stats-counter-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stats-counter-grid a,
        .categories-flex-grid a {
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer !important;
        }

        .stat-hud-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 22px;
            text-align: center;
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            height: 100%;
        }

        .stats-counter-grid a:hover .stat-hud-card {
            transform: translateY(-5px);
            border-color: var(--primary-glow);
            box-shadow: 0 10px 20px rgba(123, 46, 255, 0.15);
        }

        .stat-num {
            font-family: 'Rajdhani', sans-serif;
            font-size: 36px;
            font-weight: 700;
            margin: 5px 0;
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #73788e;
            letter-spacing: 1px;
        }

        .stat-btn {
            margin-top: 12px;
            display: inline-block;
            font-size: 11px;
            color: var(--secondary-glow);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ─── POPULAR CATEGORIES ─── */
        .section-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .section-header-row h2 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 22px;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .view-all-link {
            color: #73788e;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .categories-flex-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 45px;
        }

        /* Category Card Pro Styling */
        .category-premium-card {
            height: 160px;
            border-radius: 14px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 15px;
            background-size: cover !important;
            background-position: center !important;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .cat-title-hud {
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            z-index: 3;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.8);
            color: #fff;
        }

        .cat-count {
            font-size: 11px;
            color: #00E5FF;
            font-weight: bold;
            z-index: 3;
            position: relative;
            text-transform: uppercase;
        }

        .categories-flex-grid a:hover .category-premium-card {
            transform: translateY(-5px) scale(1.03);
            border-color: var(--secondary-glow);
            box-shadow: 0 10px 20px rgba(0, 229, 255, 0.2);
        }

        /* ─── FEATURED TOURNAMENTS CARD ROW ─── */
        .featured-list-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .premium-row-card {
            background: var(--card-bg);
            border: 1px solid rgba(123, 46, 255, 0.12);
            border-radius: 14px;
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.25s ease;
        }

        .premium-row-card:hover {
            border-color: var(--secondary-glow);
            box-shadow: 0 8px 25px rgba(0, 229, 255, 0.1);
            transform: translateY(-2px);
        }

        .row-left-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            width: 60%;
        }

        .row-game-badge {
            width: 90px;
            height: 90px;
            border-radius: 10px;
            background: #050714;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
        }

        .row-main-details h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 19px;
            margin: 0 0 6px 0;
            color: #fff;
        }

        .row-spec-sub {
            font-size: 12px;
            color: #73788e;
            display: flex;
            flex-wrap: wrap; 
            gap: 12px;
            margin-bottom: 8px;
        }

        .row-financials-hud {
            display: flex;
            flex-wrap: wrap; 
            gap: 24px;
        }

        .row-fin-item {
            display: flex;
            flex-direction: column;
        }

        .row-fin-lbl {
            font-size: 10px;
            text-transform: uppercase;
            color: #51566e;
        }

        .row-fin-val {
            font-family: 'Rajdhani', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #ffb800;
        }

        .row-right-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            width: 25%;
        }

        .btn-premium-action {
            background: linear-gradient(90deg, var(--primary-glow), var(--secondary-glow));
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            border: none;
            box-shadow: 0 4px 15px rgba(123, 46, 255, 0.3);
            cursor: pointer;
            width: 100%;
            max-width: 150px;
            text-align: center;
        }

        /* ─── ENHANCED RESPONSIVE RULES ─── */
        @media (max-width: 992px) {
            .pta-hero-banner { height: 220px; }
            .banner-title-hud { font-size: 34px; }
        }

        @media (max-width: 768px) {
            /* Stats: 3 in one row even on tablet/mobile */
            .stats-counter-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .stat-hud-card { padding: 15px 8px; }
            .stat-num { font-size: 24px; }
            .stat-label { font-size: 10px; }
            
            /* Bottom list layout for tablet */
            .row-right-actions { flex-direction: row; justify-content: space-between; align-items: center; width: 100%; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px; margin-top: 15px; }
            .premium-row-card { flex-direction: column; align-items: flex-start; }
            .row-left-meta { width: 100%; }
            .btn-premium-action { max-width: 100%; }
        }

        @media (max-width: 576px) {
            .pta-dashboard-wrapper { padding: 20px 15px 100px 15px; }
            .pta-hero-banner { height: auto; padding: 40px 20px; }
            .banner-title-hud { font-size: 22px; }
            .banner-title-hud span[style*="font-size:38px;"] { font-size: 24px !important; }
            
            /* Stats tweaks for very small screens */
            .stat-num { font-size: 20px; }
            .stat-label { font-size: 9px; letter-spacing: 0; }
            .stat-btn { font-size: 9px; }

            /* Categories: 2 in one row on mobile */
            .categories-flex-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .category-premium-card { height: 120px; padding: 12px; }
            .cat-title-hud { font-size: 14px; }
            .cat-count { font-size: 9px; }
            
            /* Featured Cards Mobile Refinement */
            .premium-row-card { padding: 16px; }
            .row-left-meta { flex-direction: row; gap: 12px; }
            .row-game-badge { width: 65px; height: 65px; font-size: 28px; }
            .row-main-details h3 { font-size: 15px; }
            .row-spec-sub { font-size: 10px; gap: 8px; }
            .row-financials-hud { gap: 12px; margin-top: 6px; }
            .row-fin-val { font-size: 13px; }
            .btn-premium-action { padding: 8px 16px; font-size: 12px; width: auto; }
        }
    </style>
</head>

<body>

    <?php if (file_exists("header.php")) {
        include "header.php";
    } ?>
    <?php if (file_exists("navbar.php")) {
        include "navbar.php";
    } ?>

    <div class="pta-dashboard-wrapper">

        <div class="pta-hero-banner">
            <div class="banner-title-hud">
                <span style="color:#fff;">Play Tournaments</span><br>
                <span style="color:var(--secondary-glow); font-size:38px;">& Earn Real Money</span>
            </div>
        </div>

        <div class="stats-counter-grid">
            <a href="tournaments.php?status=live">
                <div class="stat-hud-card" style="border-bottom: 2px solid var(--neon-pink);">
                    <div style="font-size:20px;">🔴</div>
                    <div class="stat-num"><?php echo $live_count; ?></div>
                    <div class="stat-label">Live Now</div>
                    <span class="stat-btn">View All →</span>
                </div>
            </a>

            <a href="tournaments.php?status=upcoming">
                <div class="stat-hud-card" style="border-bottom: 2px solid var(--secondary-glow);">
                    <div style="font-size:20px;">📅</div>
                    <div class="stat-num"><?php echo $upcoming_count; ?></div>
                    <div class="stat-label">Upcoming</div>
                    <span class="stat-btn">View All →</span>
                </div>
            </a>

            <a href="tournaments.php?status=completed">
                <div class="stat-hud-card" style="border-bottom: 2px solid #a0a0b0;">
                    <div style="font-size:20px;">🏆</div>
                    <div class="stat-num"><?php echo $completed_count; ?></div>
                    <div class="stat-label">Completed</div>
                    <span class="stat-btn">View All →</span>
                </div>
            </a>
        </div>

        <div class="section-header-row">
            <h2>Popular Categories</h2>
            <a href="categories.php" class="view-all-link">View All →</a>
        </div>

        <div class="categories-flex-grid">
            <a href="tournaments.php?game=FreeFire&cat=Survival">
                <div class="category-premium-card" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=600');">
                    <span class="cat-title-hud">BR Survival</span>
                    <span class="cat-count">Battle Royale Zone</span>
                </div>
            </a>
            
            <a href="tournaments.php?game=CSS">
                <div class="category-premium-card" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1509198397868-27595e08c691?q=80&w=600');">
                    <span class="cat-title-hud">CS & LW</span>
                    <span class="cat-count">Tactical Operations</span>
                </div>
            </a>
            
            <a href="tournaments.php?game=FreeFire&cat=PerKill">
                <div class="category-premium-card" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=600');">            
                    <span class="cat-title-hud">BR Per Kill</span>
                    <span class="cat-count">High Intensity Combat</span>
                </div>
            </a>
            
            <a href="tournaments.php?game=CS2&cat=Sniper">
                <div class="category-premium-card" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1589241062272-c8a79047976e?q=80&w=600');">
                    <span class="cat-title-hud">1V1 Sniper</span>
                    <span class="cat-count">Precision Dueling</span>
                </div>
            </a>
        </div>
        
        <div class="section-header-row">
            <h2>Featured Tournaments</h2>
            <a href="tournaments.php" class="view-all-link">View All →</a>
        </div>

        <div class="featured-list-container">
            
            <a href="tournament-details.php?id=feat_1" class="premium-row-card">
                <div class="row-left-meta">
                    <div class="row-game-badge">🔥</div>
                    <div class="row-main-details">
                        <h3>Grand Championship Season 5</h3>
                        <div class="row-spec-sub">
                            <span style="color:var(--secondary-glow); font-weight:bold;">🎮 Free Fire</span>
                            <span>👥 Squad</span>
                            <span>🗺️ Bermuda</span>
                            <span style="color:var(--upcoming-yellow); font-weight:bold;">⏳ UPCOMING</span>
                        </div>
                        <div class="row-financials-hud">
                            <div class="row-fin-item"><span class="row-fin-lbl">Prize Pool</span><span class="row-fin-val">🪙 50,000</span></div>
                            <div class="row-fin-item"><span class="row-fin-lbl">Entry Fee</span><span class="row-fin-val" style="color:#00E5FF;">🪙 100</span></div>
                        </div>
                    </div>
                </div>
                <div class="row-right-actions">
                    <div style="font-size:12px; color:#73788e;">Slots filled: <span style="color:#fff; font-weight:700;">10/48</span></div>
                    <button class="btn-premium-action">Pre-Register</button>
                </div>
            </a>

            <a href="tournament-details.php?id=feat_2" class="premium-row-card">
                <div class="row-left-meta">
                    <div class="row-game-badge">🎯</div>
                    <div class="row-main-details">
                        <h3>CS2 Pro Showdown 2026</h3>
                        <div class="row-spec-sub">
                            <span style="color:var(--secondary-glow); font-weight:bold;">🎮 CS2</span>
                            <span>👥 5v5</span>
                            <span>🗺️ Dust II</span>
                            <span style="color:var(--upcoming-yellow); font-weight:bold;">⏳ UPCOMING</span>
                        </div>
                        <div class="row-financials-hud">
                            <div class="row-fin-item"><span class="row-fin-lbl">Prize Pool</span><span class="row-fin-val">🪙 30,000</span></div>
                            <div class="row-fin-item"><span class="row-fin-lbl">Entry Fee</span><span class="row-fin-val" style="color:#00E5FF;">🪙 50</span></div>
                        </div>
                    </div>
                </div>
                <div class="row-right-actions">
                    <div style="font-size:12px; color:#73788e;">Slots filled: <span style="color:#fff; font-weight:700;">4/20</span></div>
                    <button class="btn-premium-action">Pre-Register</button>
                </div>
            </a>

            <a href="tournament-details.php?id=feat_3" class="premium-row-card">
                <div class="row-left-meta">
                    <div class="row-game-badge">👑</div>
                    <div class="row-main-details">
                        <h3>Survival King - Solo Brawl</h3>
                        <div class="row-spec-sub">
                            <span style="color:var(--secondary-glow); font-weight:bold;">🎮 Free Fire</span>
                            <span>👥 Solo</span>
                            <span>🗺️ Purgatory</span>
                            <span style="color:var(--upcoming-yellow); font-weight:bold;">⏳ UPCOMING</span>
                        </div>
                        <div class="row-financials-hud">
                            <div class="row-fin-item"><span class="row-fin-lbl">Prize Pool</span><span class="row-fin-val">🪙 15,000</span></div>
                            <div class="row-fin-item"><span class="row-fin-lbl">Entry Fee</span><span class="row-fin-val" style="color:#00E5FF;">🪙 20</span></div>
                        </div>
                    </div>
                </div>
                <div class="row-right-actions">
                    <div style="font-size:12px; color:#73788e;">Slots filled: <span style="color:#fff; font-weight:700;">12/50</span></div>
                    <button class="btn-premium-action">Pre-Register</button>
                </div>
            </a>

        </div>

    </div>

    <script src="app.js"></script>
</body>

</html>