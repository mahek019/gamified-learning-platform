<?php
require_once 'config/database.php';
requireLogin();

if (!isset($_SESSION['quiz_result'])) {
    header('Location: modules.php');
    exit();
}

$result = $_SESSION['quiz_result'];
$module_id = isset($_GET['module']) ? intval($_GET['module']) : 0;

// Get module name
$query = "SELECT title FROM modules WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

// Clear session
unset($_SESSION['quiz_result']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }
        
        .result-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: bounce 1s;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-30px); }
            60% { transform: translateY(-15px); }
        }
        
        .score-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin: 30px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            box-shadow: var(--shadow-lg);
        }
        
        .pass { background: linear-gradient(135deg, var(--primary), var(--secondary)); }
        .fail { background: linear-gradient(135deg, var(--danger), #c0392b); }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card result-container">
                <div class="result-icon">
                    <?php echo $result['passed'] ? '🎉' : '😔'; ?>
                </div>
                
                <h2><?php echo $result['passed'] ? 'Congratulations!' : 'Keep Trying!'; ?></h2>
                <h3 style="color: #7f8c8d; margin: 10px 0;">
                    <?php echo htmlspecialchars($module['title']); ?>
                </h3>
                
                <div class="score-circle <?php echo $result['passed'] ? 'pass' : 'fail'; ?>">
                    <?php echo round($result['percentage']); ?>%
                </div>
                
                <div style="margin: 30px 0;">
                    <p style="font-size: 1.2rem; margin-bottom: 15px;">
                        You answered <strong><?php echo $result['correct']; ?> out of <?php echo $result['total']; ?></strong> questions correctly
                    </p>
                    
                    <?php if ($result['passed']): ?>
                        <div class="alert alert-success">
                            <strong>🏆 Module Completed!</strong><br>
                            You earned <strong><?php echo $result['points']; ?> points</strong>!
                        </div>
                    <?php else: ?>
                        <div class="alert alert-error">
                            <strong>You need 60% to pass</strong><br>
                            Review the module and try again!
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <?php if (!$result['passed']): ?>
                    <a href="module.php?id=<?php echo $module_id; ?>" class="btn btn-primary">
                        Try Again
                    </a>
                    <?php endif; ?>
                    <a href="modules.php" class="btn btn-secondary">
                        Back to Modules
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary">
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>