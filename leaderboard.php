<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get top users
$leaderboard = $conn->query("
    SELECT id, username, full_name, total_points, level, eco_impact_score 
    FROM users 
    WHERE role = 'student'
    ORDER BY total_points DESC 
    LIMIT 50
");

// Get current user rank
$rank_query = "
    SELECT COUNT(*) + 1 as rank 
    FROM users 
    WHERE total_points > (SELECT total_points FROM users WHERE id = ?) 
    AND role = 'student'
";
$stmt = $conn->prepare($rank_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_rank = $stmt->get_result()->fetch_assoc()['rank'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>🏆 Leaderboard</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    See how you rank against other eco-learners!
                </p>
                
                <div style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; text-align: center;">
                    <h3 style="margin-bottom: 10px;">Your Rank</h3>
                    <div style="font-size: 3rem; font-weight: bold;">#<?php echo $my_rank; ?></div>
                </div>
                
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player</th>
                            <th>Level</th>
                            <th>Points</th>
                            <th>Eco Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while ($user = $leaderboard->fetch_assoc()): 
                            $is_current_user = $user['id'] == $user_id;
                        ?>
                        <tr style="<?php echo $is_current_user ? 'background: #e8f5e9; font-weight: bold;' : ''; ?>">
                            <td>
                                <span class="rank-badge <?php 
                                    if ($rank == 1) echo 'rank-1';
                                    elseif ($rank == 2) echo 'rank-2';
                                    elseif ($rank == 3) echo 'rank-3';
                                    else echo 'rank-other';
                                ?>">
                                    <?php echo $rank; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                                <?php if ($is_current_user): ?>
                                    <span style="color: var(--primary); margin-left: 5px;">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td>Level <?php echo $user['level']; ?></td>
                            <td style="color: var(--primary); font-weight: bold;">
                                <?php echo number_format($user['total_points']); ?> pts
                            </td>
                            <td style="color: var(--secondary);">
                                <?php echo number_format($user['eco_impact_score']); ?>
                            </td>
                        </tr>
                        <?php 
                        $rank++;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>