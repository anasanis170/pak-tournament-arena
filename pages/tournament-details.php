<?php 
session_start(); 
include_once "config.php";
global $conn;

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$t_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$is_admin = (isset($_SESSION['username']) && $_SESSION['username'] === 'admin');

$tournament = null;
if ($conn && $t_id > 0) {
    $t_res = mysqli_query($conn, "SELECT * FROM tournaments WHERE id = $t_id");
    if ($t_res && mysqli_num_rows($t_res) > 0) {
        $tournament = mysqli_fetch_assoc($t_res);
    }
}

if ($is_admin && isset($_POST['update_prize_dist'])) {
    $new_dist = mysqli_real_escape_string($conn, $_POST['prize_distribution']);
    mysqli_query($conn, "UPDATE tournaments SET prize_distribution = '$new_dist' WHERE id = $t_id");
    header("Location: tournament_details.php?id=" . $t_id);
    exit();
}

$user_coins = 0;
$already_joined = false;

if ($conn && $user_id) {
    $u_res = mysqli_query($conn, "SELECT coin FROM user WHERE id = $user_id");
    if ($u_res && mysqli_num_rows($u_res) > 0) {
        $u_data = mysqli_fetch_assoc($u_res);
        $user_coins = intval($u_data['coin']);
    }
    
    $check_joined = mysqli_query($conn, "SELECT id FROM tournament_participants WHERE tournament_id = $t_id AND user_id = $user_id");
    if ($check_joined && mysqli_num_rows($check_joined) > 0) {
        $already_joined = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_registration'])) {
    if (!$user_id) {
        $_SESSION['msg'] = "Please login first! ❌";
        $_SESSION['msg_type'] = "error";
        header("Location: login.php");
        exit();
    }

    if ($tournament) {
        $entry_fee = intval($tournament['entry_fee']);
        $slots_filled = intval($tournament['slots_joined']);
        $total_slots = intval($tournament['slots_total']);
        
        $game_uid = mysqli_real_escape_string($conn, trim($_POST['game_uid']));
        $game_name_ingame = mysqli_real_escape_string($conn, trim($_POST['game_name_ingame']));
        $email_ingame = mysqli_real_escape_string($conn, trim($_POST['email_ingame']));

        $recheck = mysqli_query($conn, "SELECT id FROM tournament_participants WHERE tournament_id = $t_id AND user_id = $user_id");
        if ($recheck && mysqli_num_rows($recheck) > 0) {
            $_SESSION['msg'] = "You have already joined this tournament! ✅";
            $_SESSION['msg_type'] = "info";
        } elseif ($slots_filled >= $total_slots) {
            $_SESSION['msg'] = "Tournament is already full! ⛔";
            $_SESSION['msg_type'] = "error";
        } elseif ($user_coins < $entry_fee) {
            $_SESSION['msg'] = "Not enough coins! You need $entry_fee coins. ❌";
            $_SESSION['msg_type'] = "error";
        } else {
            mysqli_begin_transaction($conn);
            $deduct_ok = true;
            if ($entry_fee > 0) {
                $deduct_ok = mysqli_query($conn, "UPDATE user SET coin = coin - $entry_fee WHERE id = $user_id");
            }
            $slot_ok = mysqli_query($conn, "UPDATE tournaments SET slots_joined = slots_joined + 1 WHERE id = $t_id AND slots_joined < slots_total");
            $participant_ok = mysqli_query($conn, 
                "INSERT INTO tournament_participants (tournament_id, user_id, game_uid, game_name_ingame, email, joined_at) 
                 VALUES ($t_id, $user_id, '$game_uid', '$game_name_ingame', '$email_ingame', NOW())"
            );
            if ($deduct_ok && $slot_ok && $participant_ok) {
                mysqli_commit($conn);
                $_SESSION['msg'] = "Successfully registered! 🎉";
                $_SESSION['msg_type'] = "success";
            } else {
                mysqli_rollback($conn);
                $_SESSION['msg'] = "Something went wrong. Try again. ❌";
                $_SESSION['msg_type'] = "error";
            }
        }
    }
header("Location: tournament-details.php?id=" . $t_id);
    exit();
}

$message = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : "";
unset($_SESSION['msg']); unset($_SESSION['msg_type']);

$room_info = null;
if ($already_joined && $tournament) {
    $room_info = ['room_id' => !empty($tournament['room_id'])?$tournament['room_id']:'TBA', 'room_pass' => !empty($tournament['room_pass'])?$tournament['room_pass']:'TBA'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tournament Details — PTA Arena</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --bg: #090c15; --card: #141a29; --cyan: #00f0ff; --purple: #7000ff;
        --text: #fff; --muted: #8e9bb2; --border: #1e293b;
        --danger: #ff4757; --success: #2ed573; --orange: #ff9f00;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, sans-serif; }
    body { background: var(--bg); color: var(--text); display: flex; justify-content: center; min-height: 100vh; }
    
    @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes glow { 0%,100% { border-color: rgba(0,240,255,0.2); } 50% { border-color: rgba(0,240,255,0.6); } }
    @keyframes countPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
    .anim-1 { animation: fadeUp 0.5s ease forwards; }
    .anim-2 { animation: fadeUp 0.5s ease 0.1s forwards; opacity: 0; }
    .anim-3 { animation: fadeUp 0.5s ease 0.2s forwards; opacity: 0; }
    .anim-4 { animation: fadeUp 0.5s ease 0.3s forwards; opacity: 0; }

    .app-container { width: 100%; max-width: 480px; padding: 10px 0 90px; display: flex; flex-direction: column; }

    .main-card {
        background: linear-gradient(135deg, #141a29 0%, #1c263f 100%);
        margin: 0 12px 12px; border-radius: 16px; padding: 20px; text-align: center;
        border: 1px solid rgba(255,255,255,0.05); animation: glow 3s infinite;
    }
    .status-badge {
        display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.7rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;
        background: rgba(0,240,255,0.1); border: 1px solid var(--cyan); color: var(--cyan);
    }
    .status-badge.ended { border-color: var(--muted); color: var(--muted); background: rgba(255,255,255,0.03); }
    .trophy-icon { font-size: 2.5rem; margin-bottom: 6px; }
    .trophy-icon i { color: var(--orange); filter: drop-shadow(0 4px 10px rgba(255,159,0,0.5)); }
    .tournament-name { font-size: 1.2rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; }
    .game-name { color: var(--muted); font-size: 0.78rem; margin-top: 3px; }

    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 0 12px; margin-bottom: 12px; }
    .info-box { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 12px; transition: all 0.3s; }
    .info-box:hover { border-color: var(--cyan); transform: translateY(-2px); }
    .info-label { font-size: 0.65rem; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 3px; }
    .info-value { font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 5px; }

    .room-box {
        background: linear-gradient(135deg, rgba(46,213,115,0.08), rgba(46,213,115,0.01));
        border: 1px solid rgba(46,213,115,0.3); margin: 0 12px 12px; padding: 12px;
        border-radius: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
    }
    .room-title { grid-column: 1/-1; color: var(--success); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }

    .prize-box { background: var(--card); border: 1px solid var(--border); margin: 0 12px 12px; padding: 12px; border-radius: 12px; }
    .prize-box .info-label { margin-bottom: 4px; }
    .admin-textarea { width: 100%; background: #050714; color: #fff; border: 1px solid var(--cyan); border-radius: 6px; padding: 8px; font-size: 0.75rem; margin-top: 4px; resize: vertical; min-height: 50px; }
    .admin-save-btn { background: var(--purple); color: #fff; border: none; padding: 5px 12px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; cursor: pointer; margin-top: 4px; text-transform: uppercase; }

    .slots-container { padding: 0 12px; margin-bottom: 10px; }
    .slots-meta { display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 600; margin-bottom: 5px; }
    .progress-bar-bg { background: #1e293b; height: 6px; border-radius: 3px; overflow: hidden; }
    .progress-bar-fill { background: linear-gradient(90deg, var(--purple), var(--cyan)); height: 100%; border-radius: 3px; transition: width 0.5s; }

    /* ✅ TIMER */
    .timer-strip {
        margin: 0 12px 8px; padding: 10px 14px; border-radius: 10px;
        background: rgba(112,0,255,0.08); border: 1px solid rgba(112,0,255,0.25);
        text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .timer-label { font-size: 0.7rem; color: var(--cyan); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .timer-value { font-family: 'Courier New', monospace; font-size: 1rem; font-weight: 800; color: #fff; animation: countPulse 1s infinite; }

    .action-container { padding: 0 12px; margin-bottom: 16px; }
    
    /* ✅ PURPLE THEME BUTTON */
    .join-btn {
        width: 100%; background: linear-gradient(135deg, #7000ff, #5a1fd6); border: none;
        padding: 14px; border-radius: 12px; color: #fff; font-size: 0.95rem; font-weight: 800;
        cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s;
        box-shadow: 0 4px 20px rgba(112,0,255,0.4);
    }
    .join-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(112,0,255,0.6); }
    .join-btn:disabled { background: #334155; color: var(--muted); box-shadow: none; cursor: not-allowed; }
    .joined-badge { width: 100%; background: rgba(46,213,115,0.1); border: 1px solid var(--success); color: var(--success); padding: 14px; border-radius: 12px; text-align: center; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; }

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center; padding: 15px; }
    .modal-overlay.active { display: flex; }
    .modal { background: #0e1322; border: 1px solid var(--border); border-radius: 16px; width: 100%; max-width: 380px; padding: 20px; animation: fadeUp 0.3s ease; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .modal-title { font-size: 1rem; font-weight: 700; }
    .modal-close { background: none; border: none; color: #fff; font-size: 1.3rem; cursor: pointer; }
    .modal-label { display: block; font-size: 0.65rem; color: var(--muted); text-transform: uppercase; font-weight: 700; margin: 7px 0 3px; }
    .modal-input { width: 100%; padding: 10px; background: #050714; border: 1px solid rgba(0,240,255,0.2); border-radius: 8px; color: #fff; font-size: 0.85rem; }
    .modal-input:focus { border-color: var(--cyan); outline: none; }
    .confirm-btn { width: 100%; padding: 12px; border-radius: 10px; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; border: none; cursor: pointer; background: linear-gradient(135deg, var(--purple), #5a1fd6); color: #fff; margin-top: 10px; }

    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 480px; background: #0b0f19; border-top: 1px solid var(--border); display: flex; justify-content: space-around; padding: 8px 0; z-index: 200; }
    .nav-item { display: flex; flex-direction: column; align-items: center; text-decoration: none; color: var(--muted); font-size: 0.6rem; font-weight: 700; text-transform: uppercase; gap: 4px; flex: 1; }
    .nav-item i { font-size: 1.1rem; }
    .nav-item.active { color: var(--cyan); }
    .nav-item.active i { color: var(--cyan); }
</style>
</head>
<body>

<?php if (file_exists("header.php")) { include "header.php"; } ?>

<?php if (!$tournament): ?>
    <div style="padding:100px 20px;text-align:center;width:100%;max-width:480px;">
        <h3 style="color:var(--danger)">Tournament Not Found ❌</h3>
        <a href="index.php" style="color:var(--cyan);text-decoration:none;margin-top:15px;display:inline-block;">Back to Home</a>
    </div>
<?php else: 
    $title = $tournament['title'] ?? 'Tournament';
    $game_name = $tournament['game_name'] ?? 'Game';
    $category = $tournament['category'] ?? 'Category';
    $status = strtoupper($tournament['status'] ?? 'OPEN');
    $slots_total = intval($tournament['slots_total']) > 0 ? intval($tournament['slots_total']) : 1;
    $slots_joined = intval($tournament['slots_joined']);
    $pct = $slots_total > 0 ? round(($slots_joined/$slots_total)*100) : 0;
    $entry_fee = intval($tournament['entry_fee']);
    $prize_pool = $tournament['prize_pool'] ?? '0';
    $prize_dist = $tournament['prize_distribution'] ?? '';
    $match_mode = $tournament['match_mode'] ?? 'N/A';
    $match_date = $tournament['match_date'] ?? 'TBA';
    $match_time = $tournament['match_time'] ?? 'TBA';
    $map_type = !empty($tournament['map_type']) ? $tournament['map_type'] : 'TBA';
?>

<div class="app-container">

    <?php if(!empty($message)): ?>
        <div style="padding:12px;margin:8px 12px;border-radius:8px;font-size:0.8rem;font-weight:600;text-align:center;
            background:<?php echo ($msg_type=='success'||$msg_type=='info')?'rgba(46,213,115,0.1)':'rgba(255,71,87,0.1)'; ?>;
            border:1px solid <?php echo ($msg_type=='success'||$msg_type=='info')?'rgba(46,213,115,0.3)':'rgba(255,71,87,0.3)'; ?>;
            color:<?php echo ($msg_type=='success'||$msg_type=='info')?'#2ed573':'#ff4757'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="main-card anim-1">
        <div class="status-badge <?php echo ($status=='COMPLETED'||$status=='CLOSED')?'ended':''; ?>"><?php echo $status; ?></div>
        <div class="trophy-icon"><i class="fa-solid fa-trophy"></i></div>
        <h1 class="tournament-name"><?php echo htmlspecialchars($title); ?></h1>
        <div class="game-name"><i class="fa-solid fa-gamepad"></i> <?php echo htmlspecialchars($game_name); ?> • <?php echo htmlspecialchars($category); ?></div>
    </div>

    <div class="details-grid anim-2">
        <div class="info-box"><div class="info-label">Entry Fee</div><div class="info-value"><i class="fa-solid fa-coins" style="color:var(--cyan);"></i> <?php echo $entry_fee===0?'FREE':$entry_fee.' PTA'; ?></div></div>
        <div class="info-box"><div class="info-label">Prize Pool</div><div class="info-value"><i class="fa-solid fa-trophy" style="color:var(--orange);"></i> <?php echo htmlspecialchars($prize_pool); ?></div></div>
        <div class="info-box"><div class="info-label">Map</div><div class="info-value"><i class="fa-solid fa-map"></i> <?php echo htmlspecialchars($map_type); ?></div></div>
        <div class="info-box"><div class="info-label">Mode</div><div class="info-value"><i class="fa-solid fa-users"></i> <?php echo htmlspecialchars($match_mode); ?></div></div>
        <div class="info-box"><div class="info-label">Date</div><div class="info-value"><i class="fa-solid fa-calendar"></i> <?php echo htmlspecialchars($match_date); ?></div></div>
        <div class="info-box"><div class="info-label">Time</div><div class="info-value"><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($match_time); ?></div></div>
    </div>

    <?php if ($room_info): ?>
    <div class="room-box anim-2">
        <div class="room-title">🔑 Room Access</div>
        <div class="info-box" style="padding:8px;"><div class="info-label">Room ID</div><div class="info-value"><?php echo htmlspecialchars($room_info['room_id']); ?></div></div>
        <div class="info-box" style="padding:8px;"><div class="info-label">Password</div><div class="info-value"><?php echo htmlspecialchars($room_info['room_pass']); ?></div></div>
    </div>
    <?php endif; ?>

    <div class="prize-box anim-3">
        <div class="info-label"><i class="fa-solid fa-gavel" style="color:var(--cyan);"></i> Prize Distribution</div>
        <?php if ($is_admin): ?>
            <form action="" method="POST">
                <textarea name="prize_distribution" class="admin-textarea"><?php echo htmlspecialchars($prize_dist); ?></textarea>
                <button type="submit" name="update_prize_dist" class="admin-save-btn">Save</button>
            </form>
        <?php else: ?>
            <div style="font-size:0.78rem;color:#fff;white-space:pre-line;line-height:1.5;"><?php echo !empty($prize_dist)?htmlspecialchars($prize_dist):'TBA'; ?></div>
        <?php endif; ?>
    </div>

    <div class="slots-container anim-3">
        <div class="slots-meta"><span style="color:var(--muted);">Slots Filled</span><span style="color:var(--orange);"><?php echo $slots_joined; ?>/<?php echo $slots_total; ?></span></div>
        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width:<?php echo $pct; ?>%;"></div></div>
    </div>

    <!-- ✅ TIMER -->
    <div class="timer-strip anim-4">
        <span class="timer-label">⏳ Starts in</span>
        <span class="timer-value" id="countdown">--</span>
    </div>

    <!-- ✅ ACTION BUTTON -->
    <div class="action-container anim-4">
        <?php if ($already_joined): ?>
            <div class="joined-badge">✅ You Have Joined!</div>
        <?php elseif($status=='COMPLETED'||$status=='CLOSED'): ?>
            <button class="join-btn" disabled>Match Ended</button>
        <?php else: ?>
            <button class="join-btn" onclick="openJoinModal()" <?php echo ($slots_joined>=$slots_total)?'disabled':''; ?>>
                <i class="fa-solid fa-bolt"></i> <?php echo ($slots_joined>=$slots_total)?'Full':(($entry_fee===0)?'Join Free':'Join Now'); ?>
            </button>
        <?php endif; ?>
    </div>

<?php include"navbar.php";?>
</div>

<div class="modal-overlay" id="join-modal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Confirm Registration</div>
            <button type="button" class="modal-close" onclick="closeJoinModal()">&times;</button>
        </div>
        <form action="" method="POST">
            <p style="font-size:0.8rem;color:var(--muted);margin-bottom:8px;">Joining: <strong style="color:#fff;"><?php echo htmlspecialchars($title); ?></strong></p>
            <label class="modal-label">Game UID</label>
            <input type="text" name="game_uid" class="modal-input" placeholder="e.g. 549302118" required>
            <label class="modal-label">In-Game Name</label>
            <input type="text" name="game_name_ingame" class="modal-input" placeholder="e.g. V1PER_YT" required>
            <label class="modal-label">Email</label>
            <input type="email" name="email_ingame" class="modal-input" placeholder="e.g. arena@domain.com" required>
            <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:8px;padding:10px;margin-top:10px;font-size:0.78rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="color:var(--muted);">Entry:</span><span><?php echo $entry_fee===0?'FREE':$entry_fee.' PTA'; ?></span></div>
                <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Balance:</span><span style="color:var(--orange);font-weight:700;"><?php echo number_format($user_coins); ?> PTA</span></div>
            </div>
            <button type="submit" name="confirm_registration" class="confirm-btn">Confirm & Join</button>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
var matchDateStr = "<?php echo $match_date; ?>";
var matchTimeStr = "<?php echo $match_time; ?>";
var matchDateTime = matchDateStr + ' ' + matchTimeStr;
var matchDate = new Date(matchDateTime).getTime();

var countdown = setInterval(function() {
    var now = new Date().getTime();
    var distance = matchDate - now;
    if (distance < 0 || isNaN(distance)) { clearInterval(countdown); document.getElementById("countdown").innerHTML = "🔴 LIVE"; return; }
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    var display = "";
    if(days > 0) display += days + "d ";
    display += hours + "h " + minutes + "m " + seconds + "s";
    document.getElementById("countdown").innerHTML = display;
}, 1000);

function openJoinModal() {
    <?php if (!$user_id): ?>
        if(confirm("Please login first! Go to login page?")) window.location.href = "login.php";
        return;
    <?php endif; ?>
    document.getElementById('join-modal').classList.add('active');
}
function closeJoinModal() { document.getElementById('join-modal').classList.remove('active'); }
document.getElementById('join-modal').addEventListener('click', function(e) { if(e.target===this) closeJoinModal(); });
var matchDateStr = "<?php echo $match_date; ?>";
var matchTimeStr = "<?php echo $match_time; ?>";
var matchDateTime = matchDateStr + ' ' + matchTimeStr;
var matchDate = new Date(matchDateTime).getTime();
var matchStatus = "<?php echo $status; ?>";

var countdown = setInterval(function() {
    var now = new Date().getTime();
    var distance = matchDate - now;

    if (distance < 0 || isNaN(distance)) {
        clearInterval(countdown);
        if (matchStatus === 'COMPLETED' || matchStatus === 'CLOSED') {
            document.getElementById("countdown").innerHTML = "🏁 ENDED";
        } else {
            document.getElementById("countdown").innerHTML = "🔴 LIVE";
        }
        document.getElementById("countdown").style.color = "#ff4757";
        return;
    }

    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

    var display = "";
    if(days > 0) display += days + "d ";
    display += hours + "h " + minutes + "m " + seconds + "s";
    document.getElementById("countdown").innerHTML = display;
}, 1000);
</script>
</body>
</html>