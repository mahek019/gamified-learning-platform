<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$module_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get module details
$query = "SELECT * FROM modules WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

if (!$module) {
    header('Location: modules.php');
    exit();
}

// Get quiz questions
$quiz_query = "SELECT * FROM quiz_questions WHERE module_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$questions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($module['title']); ?> - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="quiz-container">
                <div class="content-card">
                    <a href="modules.php" style="color: var(--primary); text-decoration: none; margin-bottom: 15px; display: inline-block;">
                        ← Back to Modules
                    </a>
                    
                    <h2><?php echo htmlspecialchars($module['title']); ?></h2>
                    <span class="badge badge-<?php echo $module['difficulty']; ?>" style="margin: 10px 0; display: inline-block;">
                        <?php echo ucfirst($module['difficulty']); ?>
                    </span>
                    
                    <div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h3 style="margin-bottom: 15px;">📖 Module Content</h3>
                        <p style="line-height: 1.8; color: #495057;">
                            <?php echo nl2br(htmlspecialchars($module['content'])); ?>
                        </p>
                    </div>
                    
                    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p style="margin: 0;">
                            <strong>💡 Ready to test your knowledge?</strong><br>
                            Complete the quiz below to earn <strong><?php echo $module['points']; ?> points</strong>!
                        </p>
                    </div>
                </div>
                
                <form id="quizForm" method="POST" action="submit-quiz.php">
                    <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
                    
                    <?php 
                    $q_num = 1;
                    while ($question = $questions->fetch_assoc()): 
                    ?>
                    <div class="question-card">
                        <div class="question-header">
                            <span style="color: var(--primary); font-weight: bold;">Question <?php echo $q_num; ?></span>
                            <span style="color: #7f8c8d;">+<?php echo $question['points']; ?> pts</span>
                        </div>
                        
                        <div class="question-text">
                            <?php echo htmlspecialchars($question['question']); ?>
                        </div>
                        
                        <ul class="options-list">
                            <li class="option-item">
                                <label>
                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="a" required>
                                    <?php echo htmlspecialchars($question['option_a']); ?>
                                </label>
                            </li>
                            <li class="option-item">
                                <label>
                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="b">
                                    <?php echo htmlspecialchars($question['option_b']); ?>
                                </label>
                            </li>
                            <li class="option-item">
                                <label>
                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="c">
                                    <?php echo htmlspecialchars($question['option_c']); ?>
                                </label>
                            </li>
                            <li class="option-item">
                                <label>
                                    <input type="radio" name="question_<?php echo $question['id']; ?>" value="d">
                                    <?php echo htmlspecialchars($question['option_d']); ?>
                                </label>
                            </li>
                        </ul>
                        
                        <?php if (!empty($question['hint'])): ?>
                        <div style="margin-top: 15px;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showHint(<?php echo $question['id']; ?>)">
                                💡 Show Hint
                            </button>
                            <div id="hint_<?php echo $question['id']; ?>" style="display: none; margin-top: 10px; padding: 12px; background: #fff3cd; border-radius: 5px; color: #856404;">
                                <strong>Hint:</strong> <?php echo htmlspecialchars($question['hint']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                    $q_num++;
                    endwhile; 
                    ?>
                    
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px; padding: 15px;">
                        Submit Quiz 🎯
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showHint(questionId) {
            document.getElementById('hint_' + questionId).style.display = 'block';
        }
        
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit the quiz?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>