<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    
    $update = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssi", $full_name, $email, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $success = 'Profile updated successfully!';
    } else {
        $error = 'Failed to update profile.';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = 'New passwords do not match!';
    } else {
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("si", $hashed, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            }
        } else {
            $error = 'Current password is incorrect!';
        }
    }
}

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get stats
$stats = [
    'modules' => $conn->query("SELECT COUNT(*) as count FROM user_progress WHERE user_id = $user_id AND completed = 1")->fetch_assoc()['count'],
    'badges' => $conn->query("SELECT COUNT(*) as count FROM user_badges WHERE user_id = $user_id")->fetch_assoc()['count'],
    'tasks' => $conn->query("SELECT COUNT(*) as count FROM eco_submissions WHERE user_id = $user_id AND status = 'approved'")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <h2>👤 My Profile</h2>
                
                <div style="text-align: center; margin: 30px 0;">
                    <div class="user-avatar" style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto;">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <h3 style="margin-top: 15px;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p style="color: #7f8c8d;">@<?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                
                <div class="stats-grid" style="margin: 30px 0;">
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
                            <h3><?php echo $stats['modules']; ?></h3>
                            <p>Modules Completed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple">🏅</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['badges']; ?></h3>
                            <p>Badges Earned</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange">🌿</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['tasks']; ?></h3>
                            <p>Tasks Completed</p>
                        </div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <div>
                        <p><strong>Level:</strong> <?php echo $user['level']; ?></p>
                        <p><strong>Streak:</strong> <?php echo $user['streak_days']; ?> days 🔥</p>
                        <p><strong>Eco Impact Score:</strong> <?php echo $user['eco_impact_score']; ?></p>
                    </div>
                    <div>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                        <p><strong>Last Login:</strong> <?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <h3>✏️ Edit Profile</h3>
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Username (Cannot be changed)</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <div class="content-card">
                <h3>🔒 Change Password</h3>
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" minlength="6" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>