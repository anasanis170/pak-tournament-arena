<?php 
// 1. Session start aur Login check
session_start(); 
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Database Connection
if (file_exists("config.php")) {
    include "config.php";
} else if (file_exists("../config.php")) {
    include "../config.php";
} else {
    include_once "../pages/config.php";
}

global $conn;

// ─── AJAX REQUESTS HANDLING ───
if(isset($_GET['action'])) {
    if($_GET['action'] == 'mark_read' && isset($_GET['id'])) {
        $notif_id = intval($_GET['id']);
        mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $user_id");
        echo json_encode(['success' => true]);
        exit();
    }
    if($_GET['action'] == 'mark_all_read') {
        mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
        echo json_encode(['success' => true]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Notifications — PTA Arena</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #7B2EFF; --secondary: #00E5FF; --danger: #ff4757;
        --success: #2ed573; --muted: #8a8fa3;
        --border: rgba(255,255,255,0.08); --card-bg: #0c0f24; --surface: #0e1322;
    }
    body { background: #02040a; color: #fff; font-family: 'Space Grotesk', sans-serif; }
    
    .notif-container { max-width: 700px; margin: 0 auto; padding: 100px 20px 60px; }
    
    .page-header { 
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 24px; flex-wrap: wrap; gap: 12px; 
    }
    .page-title { 
        font-family: 'Rajdhani', sans-serif; font-size: 28px; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 1px; color: var(--secondary);
        text-shadow: 0 0 20px rgba(0,229,255,0.3);
    }
    .page-sub { color: var(--muted); font-size: 13px; }
    
    .btn-mark-all {
        background: rgba(123,46,255,0.1); color: var(--secondary); 
        border: 1px solid rgba(123,46,255,0.3); padding: 10px 18px; 
        border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px;
        transition: all 0.3s ease; font-family: 'Rajdhani', sans-serif;
    }
    .btn-mark-all:hover { background: rgba(123,46,255,0.2); }
    
    .notif-list { display: flex; flex-direction: column; gap: 8px; }
    
    .notif-card {
        background: var(--surface); border: 1px solid var(--border); 
        padding: 16px; border-radius: 12px; display: flex; gap: 14px; 
        cursor: pointer; transition: all 0.25s ease;
    }
    .notif-card:hover { border-color: rgba(123,46,255,0.4); }
    .notif-card.unread { 
        background: rgba(123,46,255,0.04); 
        border-color: rgba(123,46,255,0.25); 
    }
    
    .notif-icon { font-size: 28px; flex-shrink: 0; width: 40px; text-align: center; }
    .notif-content { flex: 1; }
    .notif-title-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; }
    .notif-title { font-weight: 600; font-size: 15px; }
    .notif-badge { 
        background: var(--primary); color: #fff; font-size: 9px; padding: 2px 8px; 
        border-radius: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;
    }
    .notif-msg { font-size: 13px; color: var(--muted); line-height: 1.6; }
    .notif-time { font-size: 11px; color: var(--muted); margin-top: 6px; }
    
    .empty-state { text-align: center; padding: 80px 20px; }
    .empty-icon { font-size: 64px; margin-bottom: 16px; }
    .empty-title { font-family: 'Rajdhani', sans-serif; font-size: 24px; margin-bottom: 8px; }
    .empty-text { color: var(--muted); }
</style>
</head>
<body>

<?php if (file_exists("header.php")) { include "header.php"; } ?>
<?php if (file_exists("navbar.php")) { include "navbar.php"; } ?>

<div class="notif-container">
    
    <div class="page-header">
        <div>
            <div class="page-title">🔔 Notifications</div>
            <div class="page-sub">Stay updated with arena events and alerts.</div>
        </div>
        <button class="btn-mark-all" onclick="markAllRead()">✅ Mark All Read</button>
    </div>
    
    <div class="notif-list" id="notif-list">
        <?php
        if($conn) {
            $res = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY id DESC");
            
            if($res && mysqli_num_rows($res) > 0) {
                $icons = ['info'=>'ℹ️', 'warning'=>'⚠️', 'success'=>'✅', 'danger'=>'🚨'];
                
                while($row = mysqli_fetch_assoc($res)) {
                    $is_read = $row['is_read'];
                    $notif_id = $row['id'];
                    $type = $row['type'] ?? 'info';
                    $emoji = isset($icons[$type]) ? $icons[$type] : 'ℹ️';
                    $time = date("d M, h:i A", strtotime($row['created_at']));
                    $card_class = $is_read ? '' : 'unread';
                    ?>
                    <div id="notif-card-<?php echo $notif_id; ?>" 
                         class="notif-card <?php echo $card_class; ?>"
                         onclick="markRead(<?php echo $notif_id; ?>)">
                        
                        <div class="notif-icon"><?php echo $emoji; ?></div>
                        
                        <div class="notif-content">
                            <div class="notif-title-row">
                                <div class="notif-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                <?php if(!$is_read): ?>
                                    <span class="notif-badge" id="badge-<?php echo $notif_id; ?>">NEW</span>
                                <?php endif; ?>
                            </div>
                            <div class="notif-msg"><?php echo htmlspecialchars($row['msg']); ?></div>
                            <div class="notif-time">🕐 <?php echo $time; ?></div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="empty-state">
                    <div class="empty-icon">🔔</div>
                    <div class="empty-title">No Notifications</div>
                    <div class="empty-text">You're all caught up!</div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<script>
function markRead(id) {
    fetch(`notifications.php?action=mark_read&id=${id}`)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const card = document.getElementById(`notif-card-${id}`);
            const badge = document.getElementById(`badge-${id}`);
            if(card) {
                card.classList.remove('unread');
            }
            if(badge) {
                badge.remove();
            }
        }
    });
}

function markAllRead() {
    fetch('notifications.php?action=mark_all_read')
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            window.location.reload();
        }
    });
}
</script>
<script src="app.js"></script>
</body>
</html>