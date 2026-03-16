<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$modules = $conn->query("SELECT * FROM modules ORDER BY difficulty ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Modules - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>📚 Learning Modules</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Explore environmental topics and test your knowledge with quizzes
                </p>
                
                <div class="module-list">
                    <?php while ($module = $modules->fetch_assoc()): 
                        // Check completion status
                        $check = "SELECT completed, score FROM user_progress WHERE user_id = ? AND module_id = ?";
                        $stmt = $conn->prepare($check);
                        $stmt->bind_param("ii", $user_id, $module['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $progress = $result->fetch_assoc();
                        $completed = $progress && $progress['completed'];
                        $score = $progress ? $progress['score'] : 0;
                    ?>
                    <div class="module-item" onclick="location.href='module.php?id=<?php echo $module['id']; ?>'">
                        <div class="module-header">
                            <h4><?php echo htmlspecialchars($module['title']); ?></h4>
                            <div>
                                <span class="badge badge-<?php echo $module['difficulty']; ?>">
                                    <?php echo ucfirst($module['difficulty']); ?>
                                </span>
                                <?php if ($completed): ?>
                                <span class="badge badge-approved" style="margin-left: 5px;">
                                    ✓ Completed (<?php echo $score; ?>%)
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p style="color: #7f8c8d; margin: 10px 0;">
                            <?php echo htmlspecialchars($module['description']); ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <span style="color: var(--primary); font-weight: bold;">
                                +<?php echo $module['points']; ?> points
                            </span>
                            <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); location.href='module.php?id=<?php echo $module['id']; ?>'">
                                <?php echo $completed ? 'Review' : 'Start Learning'; ?>
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>