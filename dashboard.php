<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user stats
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get module completion count
$module_query = "SELECT COUNT(*) as completed FROM user_progress WHERE user_id = ? AND completed = 1";
$stmt = $conn->prepare($module_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$modules_completed = $stmt->get_result()->fetch_assoc()['completed'];

// Get badges count
$badge_query = "SELECT COUNT(*) as count FROM user_badges WHERE user_id = ?";
$stmt = $conn->prepare($badge_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$badges_count = $stmt->get_result()->fetch_assoc()['count'];

// Get recent notifications
$notif_query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($notif_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h2>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 🌟</h2>
                    <p>Continue your environmental learning journey</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                        <p>Level <?php echo $user['level']; ?> • <?php echo $user['total_points']; ?> pts</p>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green">🏆</div>
                    <div class="stat-details">
                        <h3><?php echo $user['total_points']; ?></h3>
                        <p>Total Points</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon blue">📚</div>
                    <div class="stat-details">
                        <h3><?php echo $modules_completed; ?></h3>
                        <p>Modules Completed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">🔥</div>
                    <div class="stat-details">
                        <h3><?php echo $user['streak_days']; ?></h3>
                        <p>Day Streak</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">🏅</div>
                    <div class="stat-details">
                        <h3><?php echo $badges_count; ?></h3>
                        <p>Badges Earned</p>
                    </div>
                </div>
            </div>
            
            <div class="progress-container">
                <h3>Level Progress</h3>
                <?php 
                $current_level_points = ($user['level'] - 1) * 200;
                $next_level_points = $user['level'] * 200;
                $progress = (($user['total_points'] - $current_level_points) / 200) * 100;
                ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $progress; ?>%">
                        <?php echo round($progress); ?>%
                    </div>
                </div>
                <p style="margin-top: 10px; color: #7f8c8d;">
                    <?php echo $next_level_points - $user['total_points']; ?> points to Level <?php echo $user['level'] + 1; ?>
                </p>
            </div>
            
            <div class="content-card">
                <h3>📖 Learning Modules</h3>
                <div class="module-list">
                    <?php
                    $modules = $conn->query("SELECT * FROM modules LIMIT 4");
                    while ($module = $modules->fetch_assoc()):
                        // Check if completed
                        $check = "SELECT completed FROM user_progress WHERE user_id = ? AND module_id = ?";
                        $stmt = $conn->prepare($check);
                        $stmt->bind_param("ii", $user_id, $module['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $completed = $result->num_rows > 0 && $result->fetch_assoc()['completed'];
                    ?>
                    <div class="module-item" onclick="location.href='module.php?id=<?php echo $module['id']; ?>'">
                        <div class="module-header">
                            <h4><?php echo htmlspecialchars($module['title']); ?></h4>
                            <div>
                                <span class="badge badge-<?php echo $module['difficulty']; ?>">
                                    <?php echo ucfirst($module['difficulty']); ?>
                                </span>
                                <?php if ($completed): ?>
                                <span class="badge badge-approved" style="margin-left: 5px;">✓ Completed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p style="color: #7f8c8d;"><?php echo htmlspecialchars($module['description']); ?></p>
                        <p style="margin-top: 10px; color: var(--primary); font-weight: bold;">
                            +<?php echo $module['points']; ?> points
                        </p>
                    </div>
                    <?php endwhile; ?>
                </div>
                <a href="modules.php" class="btn btn-primary" style="margin-top: 15px;">View All Modules</a>
            </div>
            
            <div class="content-card">
                <h3>🌿 Recent Notifications</h3>
                <?php if ($notifications->num_rows > 0): ?>
                    <div style="display: grid; gap: 10px;">
                        <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <div class="alert alert-<?php echo $notif['type']; ?>">
                            <strong><?php echo htmlspecialchars($notif['title']); ?></strong><br>
                            <?php echo htmlspecialchars($notif['message']); ?>
                            <small style="display: block; margin-top: 5px; opacity: 0.8;">
                                <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                            </small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #7f8c8d;">No notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>