<?php
require_once 'config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $game_name = sanitize($_POST['game_name']);
    $score = intval($_POST['score']);
    $time = isset($_POST['time']) ? intval($_POST['time']) : null;
    
    // Insert score
    $query = "INSERT INTO game_scores (user_id, game_name, score, time_taken) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isii", $user_id, $game_name, $score, $time);
    $stmt->execute();
    
    // Add points to user
    if ($score > 0) {
        addPoints($user_id, $score);
        createNotification($user_id, 'Game Points Earned!', "You earned $score points playing $game_name!", 'success');
    }
    
    echo json_encode(['success' => true, 'points' => $score]);
}
?> 