<?php
require_once 'config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: modules.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$module_id = intval($_POST['module_id']);

// Get all questions for this module
$query = "SELECT * FROM quiz_questions WHERE module_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$questions = $stmt->get_result();

$total_questions = 0;
$correct_answers = 0;
$total_points = 0;
$earned_points = 0;

while ($question = $questions->fetch_assoc()) {
    $total_questions++;
    $total_points += $question['points'];
    
    $user_answer = isset($_POST['question_' . $question['id']]) ? $_POST['question_' . $question['id']] : '';
    
    if ($user_answer == $question['correct_option']) {
        $correct_answers++;
        $earned_points += $question['points'];
    }
}

$percentage = ($correct_answers / $total_questions) * 100;

// Get module points
$module_query = "SELECT points FROM modules WHERE id = ?";
$stmt = $conn->prepare($module_query);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

// Check if already completed
$check = "SELECT id, completed FROM user_progress WHERE user_id = ? AND module_id = ?";
$stmt = $conn->prepare($check);
$stmt->bind_param("ii", $user_id, $module_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

$is_passing = $percentage >= 60;

if ($existing) {
    // Update existing record
    $update = "UPDATE user_progress SET score = ?, completed = ?, completed_at = NOW() WHERE user_id = ? AND module_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("iiii", $percentage, $is_passing, $user_id, $module_id);
    $stmt->execute();
    
    // Only award points if not previously completed
    if ($is_passing && !$existing['completed']) {
        addPoints($user_id, $module['points']);
        createNotification($user_id, 'Module Completed!', 'You earned ' . $module['points'] . ' points!', 'success');
    }
} else {
    // Insert new record
    $insert = "INSERT INTO user_progress (user_id, module_id, score, completed, completed_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("iiii", $user_id, $module_id, $percentage, $is_passing);
    $stmt->execute();
    
    if ($is_passing) {
        addPoints($user_id, $module['points']);
        createNotification($user_id, 'Module Completed!', 'You earned ' . $module['points'] . ' points!', 'success');
    }
}

// Store result in session
$_SESSION['quiz_result'] = [
    'total' => $total_questions,
    'correct' => $correct_answers,
    'percentage' => $percentage,
    'points' => $is_passing ? $module['points'] : 0,
    'passed' => $is_passing
];

header('Location: quiz-result.php?module=' . $module_id);
exit();
?> 