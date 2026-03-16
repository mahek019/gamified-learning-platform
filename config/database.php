<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gamified_learning');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper Functions
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function updateStreak($user_id) {
    global $conn;
    
    $query = "SELECT last_login, streak_days FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $today = date('Y-m-d');
    $last_login = $user['last_login'];
    $streak = $user['streak_days'];
    
    if ($last_login != $today) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        if ($last_login == $yesterday) {
            $streak++;
        } else if ($last_login != null) {
            $streak = 1;
        } else {
            $streak = 1;
        }
        
        $update = "UPDATE users SET last_login = ?, streak_days = ? WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sii", $today, $streak, $user_id);
        $stmt->execute();
        
        // Check for streak badges
        checkBadges($user_id);
    }
}

function addPoints($user_id, $points, $update_level = true) {
    global $conn;
    
    $query = "UPDATE users SET total_points = total_points + ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $points, $user_id);
    $stmt->execute();
    
    if ($update_level) {
        updateLevel($user_id);
        checkBadges($user_id);
    }
}

function updateLevel($user_id) {
    global $conn;
    
    $query = "SELECT total_points FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $points = $user['total_points'];
    $new_level = floor($points / 200) + 1;
    
    $update = "UPDATE users SET level = ? WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ii", $new_level, $user_id);
    $stmt->execute();
}

function checkBadges($user_id) {
    global $conn;
    
    // Get user stats
    $query = "SELECT total_points, streak_days, level FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Check all badges
    $badge_query = "SELECT id, criteria_type, criteria_value FROM badges";
    $badges = $conn->query($badge_query);
    
    while ($badge = $badges->fetch_assoc()) {
        $earned = false;
        
        switch ($badge['criteria_type']) {
            case 'points':
                $earned = $user['total_points'] >= $badge['criteria_value'];
                break;
            case 'streak':
                $earned = $user['streak_days'] >= $badge['criteria_value'];
                break;
            case 'level':
                $earned = $user['level'] >= $badge['criteria_value'];
                break;
        }
        
        if ($earned) {
            // Check if already awarded
            $check = "SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?";
            $stmt = $conn->prepare($check);
            $stmt->bind_param("ii", $user_id, $badge['id']);
            $stmt->execute();
            $exists = $stmt->get_result();
            
            if ($exists->num_rows == 0) {
                $insert = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insert);
                $stmt->bind_param("ii", $user_id, $badge['id']);
                $stmt->execute();
                
                // Create notification
                createNotification($user_id, 'New Badge Earned!', 'You earned a new badge!', 'success');
            }
        }
    }
}

function createNotification($user_id, $title, $message, $type = 'info') {
    global $conn;
    
    $query = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    $stmt->execute();
}

function generateCertificateCode() {
    return 'CERT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
}
?>
