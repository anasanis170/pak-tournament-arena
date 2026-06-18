<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}

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
$where_clauses = [];
$page_title = "All Tournaments";

$selected_game = isset($_GET['game']) ? strtolower($_GET['game']) : '';
$selected_cat = isset($_GET['cat']) ? strtolower($_GET['cat']) : '';

if ($conn) {
    // 1. Game Filter
    if (!empty($selected_game)) {
        $game = mysqli_real_escape_string($conn, $selected_game);
        if ($game == 'css' || $game == 'cs2' || $game == 'cs') {
            $where_clauses[] = "LOWER(category) IN ('css', 'cs2', 'cs', 'lonewolf')";
            $page_title = "CSS & LW Tournaments";
            $selected_game = 'css';
        } elseif ($game == 'freefire' || $game == 'pubg' || $game == 'cod') {
            $where_clauses[] = "LOWER(category) IN ('br per kill', 'br survival', 'freefire', 'pubg', 'cod')";
            $page_title = "BR Tournaments";
            $selected_game = 'br';
        } else {
            $where_clauses[] = "LOWER(category) = '$game'";
            $page_title = strtoupper($game) . " Tournaments";
            $selected_game = $game;
        }
    }

    // 2. Category Filter
    if (!empty($selected_cat)) {
        $cat = mysqli_real_escape_string($conn, $selected_cat);
        if ($cat == 'survival') {
            $where_clauses[] = "LOWER(category) = 'br survival'";
            $page_title = "BR Survival Tournaments";
            $selected_game = 'br';
        } elseif ($cat == 'perkill') {
            $where_clauses[] = "LOWER(category) = 'br per kill'";
            $page_title = "BR Per Kill Tournaments";
            $selected_game = 'br';
        } elseif ($cat == 'sniper') {
            $where_clauses[] = "LOWER(category) = 'sniper'";
            $page_title = "Sniper Tournaments";
            $selected_game = 'sniper';
        }
    }

    // 3. Status Filter
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = mysqli_real_escape_string($conn, strtolower($_GET['status']));
        if ($status == 'live') {
            $where_clauses[] = "LOWER(status) IN ('live', 'active', 'open')";
        } else {
            $where_clauses[] = "LOWER(status) = '$status'";
        }
    }

    // 4. Mode Filter
    if (isset($_GET['mode']) && !empty($_GET['mode'])) {
        $mode = mysqli_real_escape_string($conn, strtolower($_GET['mode']));
        $where_clauses[] = "LOWER(match_mode) = '$mode'";
        $page_title = strtoupper($mode) . " Mode";
    }
}

$where_sql = (count($where_clauses) > 0) ? "WHERE " . implode(" AND ", $where_clauses) : "";
$sql_query = "SELECT * FROM tournaments $where_sql ORDER BY id DESC";

$is_br = ($selected_game == 'br' || $selected_game == 'freefire' || $selected_game == 'pubg' || $selected_game == 'cod');
$is_cs_sniper = ($selected_game == 'css' || $selected_game == 'sniper' || $selected_game == 'cs2' || $selected_game == 'cs');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> — PTA Arena Pro</title>
    <link rel="stylesheet" href="style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-glow: #7B2EFF;
            --secondary-glow: #00E5FF;
            --card-bg: #0c0f24;
            --gold: #ffb800;
        }

        body {
            background-color: #02040a;
            color: #ffffff;
            font-family: 'Space Grotesk', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .pta-dashboard-wrapper {
            max-width: 1140px;
            margin: 0 auto;
            padding: 40px 20px 120px 20px;
            box-sizing: border-box;
        }

        .section-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 15px;
        }

        .section-header-row h2 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 28px;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 1px;
            color: var(--secondary-glow);
            text-shadow: 0 0 10px rgba(0, 229, 255, 0.3);
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
            align-items: center;
        }

        .filter-link {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            font-family: 'Rajdhani', sans-serif;
            transition: all 0.3s ease;
            background: #0c0f24;
            color: #73788e;
            border: 1px solid rgba(255, 255, 255, 0.08);
            letter-spacing: 0.5px;
        }

        .filter-link.active {
            background: var(--primary-glow);
            color: #fff;
            border-color: var(--primary-glow);
            box-shadow: 0 4px 15px rgba(123, 46, 255, 0.4);
        }

        .filter-link:hover {
            border-color: var(--secondary-glow);
            color: #fff;
            transform: translateY(-2px);
        }

        .featured-list-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ✅ ORIGINAL ROW CARD STYLE — FIXED */
        .premium-row-card {
            background: var(--card-bg);
            border: 1px solid rgba(123, 46, 255, 0.15);
            border-radius: 14px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .premium-row-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary-glow), var(--secondary-glow));
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .premium-row-card:hover {
            border-color: var(--secondary-glow);
            box-shadow: 0 10px 30px rgba(0, 229, 255, 0.1);
            transform: translateX(3px);
        }

        .premium-row-card:hover::before {
            opacity: 1;
        }

        .row-left-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
        }

        .row-game-badge {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: #050714;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            flex-shrink: 0;
        }

        .row-main-details {
            flex: 1;
        }

        .row-main-details h3 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .row-spec-sub {
            font-size: 11px;
            color: #73788e;
            display: flex;
            gap: 14px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .row-spec-sub span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .row-financials-hud {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .row-fin-item {
            display: flex;
            flex-direction: column;
        }

        .row-fin-lbl {
            font-size: 9px;
            text-transform: uppercase;
            color: #51566e;
            letter-spacing: 1px;
        }

        .row-fin-val {
            font-family: 'Rajdhani', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: var(--gold);
        }

        .row-right-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            flex-shrink: 0;
            margin-left: 20px;
        }

        .row-slots-info {
            font-size: 11px;
            color: #73788e;
            text-align: right;
        }

        .row-slots-info span {
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }

        .btn-premium-action {
            background: linear-gradient(135deg, var(--primary-glow), #5a1fd6);
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-premium-action:hover {
            box-shadow: 0 0 25px rgba(123, 46, 255, 0.5);
            background: linear-gradient(135deg, #8b3fff, var(--primary-glow));
        }

        .no-results {
            text-align: center;
            padding: 50px 20px;
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px dashed rgba(255, 255, 255, 0.1);
            color: #73788e;
        }

        @media (max-width: 768px) {
            .premium-row-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
                padding: 18px 16px;
            }

            .row-left-meta {
                width: 100%;
                gap: 14px;
            }

            .row-game-badge {
                width: 55px;
                height: 55px;
                font-size: 26px;
                border-radius: 10px;
            }

            .row-main-details h3 {
                font-size: 17px;
            }

            .row-spec-sub {
                font-size: 10px;
                gap: 8px;
            }

            .row-financials-hud {
                gap: 14px;
            }

            .row-fin-val {
                font-size: 13px;
            }

            .row-right-actions {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-left: 0;
                gap: 10px;
            }

            .row-slots-info {
                font-size: 10px;
            }

            .btn-premium-action {
                padding: 10px 18px;
                font-size: 12px;
            }

            .pta-dashboard-wrapper {
                padding: 20px 10px 100px 10px;
            }

            .filter-bar {
                gap: 6px;
            }

            .filter-link {
                padding: 7px 12px;
                font-size: 11px;
            }

            .section-header-row h2 {
                font-size: 20px;
            }
        }

        @media (max-width: 768px) {
            .premium-row-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 16px;
            }

            .row-right-actions {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-left: 0;
            }

            .row-left-meta {
                width: 100%;
            }

            .row-game-badge {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
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

        <div class="section-header-row">
            <h2><?php echo htmlspecialchars($page_title); ?></h2>
        </div>

        <!-- FILTER BAR -->
        <div class="filter-bar">
            <?php
            if ($is_br) {
                $modes = [
                    ['solo', '🧑 Solo'],
                    ['duo', '👥 Duo'],
                    ['squad', '🛡️ Squad']
                ];
            } elseif ($is_cs_sniper) {
                $modes = [
                    ['1v1', '⚔️ 1v1'],
                    ['2v2', '⚔️ 2v2'],
                    ['4v4', '⚔️ 4v4']
                ];
            } else {
                $modes = [
                    ['1v1', '⚔️ 1v1'],
                    ['2v2', '⚔️ 2v2'],
                    ['4v4', '⚔️ 4v4'],
                    ['solo', '🧑 Solo'],
                    ['duo', '👥 Duo'],
                    ['squad', '🛡️ Squad']
                ];
            }

            foreach ($modes as $m) {
                $mode_val = $m[0];
                $mode_label = $m[1];
                $params = [];
                if ($selected_game && $selected_game != 'br')
                    $params[] = "game=$selected_game";
                if ($selected_cat)
                    $params[] = "cat=$selected_cat";
                $params[] = "mode=$mode_val";
                $url = "tournaments.php?" . implode("&", $params);
                $active_class = (isset($_GET['mode']) && $_GET['mode'] == $mode_val) ? 'active' : '';
                echo '<a href="' . $url . '" class="filter-link ' . $active_class . '">' . $mode_label . '</a>';
            }
            ?>
        </div>

        <div class="featured-list-container">
            <?php
            if ($conn) {
                $res = mysqli_query($conn, $sql_query);

                if ($res && mysqli_num_rows($res) > 0) {
                    while ($t_row = mysqli_fetch_assoc($res)) {
                        $f_id = $t_row['id'];
                        $f_game = isset($t_row['game_name']) ? $t_row['game_name'] : (isset($t_row['game']) ? $t_row['game'] : $t_row['category']);
                        $f_name = isset($t_row['title']) ? $t_row['title'] : (isset($t_row['name']) ? $t_row['name'] : 'Tournament');
                        $f_mode = isset($t_row['match_mode']) ? $t_row['match_mode'] : (isset($t_row['mode']) ? $t_row['mode'] : 'N/A');
                        $f_fee = isset($t_row['entry_fee']) ? intval($t_row['entry_fee']) : 0;
                        $f_joined = isset($t_row['slots_joined']) ? intval($t_row['slots_joined']) : (isset($t_row['filled']) ? intval($t_row['filled']) : 0);
                        $f_total = isset($t_row['slots_total']) ? intval($t_row['slots_total']) : (isset($t_row['slots']) ? intval($t_row['slots']) : 0);

                        // ✅ PRIZE POOL — RAW TEXT (1st 200 2nd 100 etc)
                        $f_prize_raw = isset($t_row['prize_pool']) ? $t_row['prize_pool'] : '0';
                        $prize_display = htmlspecialchars($f_prize_raw);

                        $icon_img = (stripos($f_game, 'cs') !== false || stripos($f_game, 'counter') !== false || stripos($f_game, 'sniper') !== false) ? '🎯' : '🔥';

                        echo '
                        <a href="tournament-details.php?id=' . $f_id . '" class="premium-row-card">
                            <div class="row-left-meta">
                                <div class="row-game-badge">' . $icon_img . '</div>
                                <div class="row-main-details">
                                    <h3>' . htmlspecialchars($f_name) . '</h3>
                                    <div class="row-spec-sub">
                                        <span style="color:var(--secondary-glow); font-weight:bold;">🎮 ' . htmlspecialchars($f_game) . '</span>
                                        <span>👥 ' . htmlspecialchars($f_mode) . '</span>
                                    </div>
                                    <div class="row-financials-hud">
                                        <div class="row-fin-item">
                                            <span class="row-fin-lbl">🏆 Prize Pool</span>
                                            <span class="row-fin-val">🪙 ' . $prize_display . '</span>
                                        </div>
                                        <div class="row-fin-item">
                                            <span class="row-fin-lbl">🎫 Entry Fee</span>
                                            <span class="row-fin-val" style="color:#00E5FF;">🪙 ' . $f_fee . '</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row-right-actions">
                                <div class="row-slots-info">Slots: <span>' . $f_joined . '/' . $f_total . '</span></div>
                                <button class="btn-premium-action">View Details</button>
                            </div>
                        </a>';
                    }
                } else {
                    echo '<div class="no-results"><h3>No Tournaments Found</h3><p>Abhi is category ya status mein koi tournament available nahi hai.</p><a href="tournaments.php" style="color: var(--secondary-glow); text-decoration: none; margin-top:10px; display:inline-block;">← Back to Tournaments</a></div>';
                }
            } else {
                echo '<div class="no-results"><h3>Database Connection Error</h3><p>Check your config.php file.</p></div>';
            }
            ?>
        </div>

    </div>

    <script src="app.js"></script>
</body>

</html>