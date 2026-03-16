<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get earned badges
$earned = $conn->query("
    SELECT b.*, ub.earned_at 
    FROM badges b 
    JOIN user_badges ub ON b.id = ub.badge_id 
    WHERE ub.user_id = $user_id 
    ORDER BY ub.earned_at DESC
");

// Get available badges (not earned)
$available = $conn->query("
    SELECT b.* 
    FROM badges b 
    WHERE b.id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = $user_id)
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Badges - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>🏅 My Badges</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Collect badges by completing achievements!
                </p>
                
                <h3 style="margin-bottom: 20px;">🎖️ Earned Badges (<?php echo $earned->num_rows; ?>)</h3>
                
                <?php if ($earned->num_rows > 0): ?>
                <div class="badges-grid">
                    <?php while ($badge = $earned->fetch_assoc()): ?>
                    <div class="badge-card">
                        <div class="badge-icon"><?php echo $badge['icon']; ?></div>
                        <h4><?php echo htmlspecialchars($badge['name']); ?></h4>
                        <p><?php echo htmlspecialchars($badge['description']); ?></p>
                        <small style="color: var(--primary); margin-top: 10px; display: block;">
                            Earned: <?php echo date('M d, Y', strtotime($badge['earned_at'])); ?>
                        </small>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 40px;">
                    No badges earned yet. Keep learning and completing tasks!
                </p>
                <?php endif; ?>
                
                <h3 style="margin: 40px 0 20px;">🔒 Available Badges</h3>
                
                <?php if ($available->num_rows > 0): ?>
                <div class="badges-grid">
                    <?php while ($badge = $available->fetch_assoc()): ?>
                    <div class="badge-card" style="opacity: 0.5;">
                        <div class="badge-icon">🔒</div>
                        <h4><?php echo htmlspecialchars($badge['name']); ?></h4>
                        <p><?php echo htmlspecialchars($badge['description']); ?></p>
                        <small style="color: #7f8c8d; margin-top: 10px; display: block;">
                            <?php 
                            if ($badge['criteria_type'] == 'points') {
                                echo 'Reach ' . $badge['criteria_value'] . ' points';
                            } elseif ($badge['criteria_type'] == 'level') {
                                echo 'Reach Level ' . $badge['criteria_value'];
                            } elseif ($badge['criteria_type'] == 'streak') {
                                echo 'Login for ' . $badge['criteria_value'] . ' days';
                            } else {
                                echo 'Complete special tasks';
                            }
                            ?>
                        </small>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 40px;">
                    🎉 Congratulations! You've earned all available badges!
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>