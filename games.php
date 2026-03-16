<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Games - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>🎮 Mini Games</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Learn while having fun! Play educational games and earn points.
                </p>
                
                <div class="stats-grid">
                    <div class="stat-card" style="cursor: pointer;" onclick="location.href='game-quiz.php'">
                        <div class="stat-icon blue">🧠</div>
                        <div class="stat-details">
                            <h3>Quick Quiz</h3>
                            <p>Test your knowledge</p>
                            <button class="btn btn-primary btn-sm" style="margin-top: 10px;">Play Now</button>
                        </div>
                    </div>
                    
                    <div class="stat-card" style="cursor: pointer;" onclick="location.href='game-memory.php'">
                        <div class="stat-icon green">🃏</div>
                        <div class="stat-details">
                            <h3>Memory Match</h3>
                            <p>Match eco symbols</p>
                            <button class="btn btn-primary btn-sm" style="margin-top: 10px;">Play Now</button>
                        </div>
                    </div>
                    
                    <div class="stat-card" style="cursor: pointer;" onclick="location.href='game-waste.php'">
                        <div class="stat-icon orange">♻️</div>
                        <div class="stat-details">
                            <h3>Waste Sort</h3>
                            <p>Segregate waste items</p>
                            <button class="btn btn-primary btn-sm" style="margin-top: 10px;">Play Now</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <h3>🏆 Recent High Scores</h3>
                <?php
                $scores = $conn->query("
                    SELECT gs.*, u.username, u.full_name 
                    FROM game_scores gs 
                    JOIN users u ON gs.user_id = u.id 
                    ORDER BY gs.played_at DESC 
                    LIMIT 10
                ");
                
                if ($scores->num_rows > 0):
                ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; text-align: left;">Player</th>
                            <th style="padding: 12px; text-align: left;">Game</th>
                            <th style="padding: 12px; text-align: left;">Score</th>
                            <th style="padding: 12px; text-align: left;">Time</th>
                            <th style="padding: 12px; text-align: left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($score = $scores->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($score['full_name']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($score['game_name']); ?></td>
                            <td style="padding: 12px; color: var(--primary); font-weight: bold;">
                                <?php echo $score['score']; ?> pts
                            </td>
                            <td style="padding: 12px;">
                                <?php echo $score['time_taken'] ? $score['time_taken'] . 's' : '-'; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo date('M d, Y', strtotime($score['played_at'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">
                    No games played yet. Be the first!
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>