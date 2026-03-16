<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$submission_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $feedback = sanitize($_POST['feedback']);
    
    $query = "SELECT es.*, et.points, et.impact_value FROM eco_submissions es JOIN eco_tasks et ON es.task_id = et.id WHERE es.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
    
    if ($action == 'approve') {
        // Update submission
        $update = "UPDATE eco_submissions SET status = 'approved', admin_feedback = ?, reviewed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("si", $feedback, $submission_id);
        $stmt->execute();
        
        // Award points
        addPoints($submission['user_id'], $submission['points']);
        
        // Update eco impact
        $impact_update = "UPDATE users SET eco_impact_score = eco_impact_score + ? WHERE id = ?";
        $stmt = $conn->prepare($impact_update);
        $stmt->bind_param("ii", $submission['impact_value'], $submission['user_id']);
        $stmt->execute();
        
        // Notify user
        createNotification($submission['user_id'], 'Task Approved!', 'Your eco-task has been approved! You earned ' . $submission['points'] . ' points.', 'success');
        
        $success = 'Task approved successfully!';
    } else {
        // Reject submission
        $update = "UPDATE eco_submissions SET status = 'rejected', admin_feedback = ?, reviewed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("si", $feedback, $submission_id);
        $stmt->execute();
        
        createNotification($submission['user_id'], 'Task Rejected', 'Your eco-task submission needs revision. Check feedback.', 'warning');
        
        $success = 'Task rejected.';
    }
    
    header('Location: submissions.php?msg=' . urlencode($success));
    exit();
}

// Get submission details
$query = "
    SELECT es.*, et.title as task_title, et.description, et.points, et.impact_value,
    u.full_name, u.username, u.email
    FROM eco_submissions es
    JOIN eco_tasks et ON es.task_id = et.id
    JOIN users u ON es.user_id = u.id
    WHERE es.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();

if (!$submission) {
    header('Location: submissions.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submission - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>🛡️ Admin Panel</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span>📊</span> Dashboard</a></li>
                <li><a href="submissions.php" class="active"><span>📝</span> Submissions</a></li>
                <li><a href="../logout.php"><span>🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="content-card">
                <a href="submissions.php" style="color: var(--primary); text-decoration: none; margin-bottom: 15px; display: inline-block;">
                    ← Back to Submissions
                </a>
                
                <h2>📝 Review Task Submission</h2>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <h3>Student Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($submission['full_name']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($submission['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($submission['email']); ?></p>
                </div>
                
                <div style="background: #e8f5e9; padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <h3>Task Details</h3>
                    <p><strong>Task:</strong> <?php echo htmlspecialchars($submission['task_title']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($submission['description']); ?></p>
                    <p><strong>Points:</strong> <?php echo $submission['points']; ?></p>
                    <p><strong>Impact Value:</strong> <?php echo $submission['impact_value']; ?></p>
                </div>
                
                <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <h3>Submission Details</h3>
                    <p><strong>Submitted:</strong> <?php echo date('M d, Y h:i A', strtotime($submission['submitted_at'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-<?php echo $submission['status']; ?>">
                            <?php echo ucfirst($submission['status']); ?>
                        </span>
                    </p>
                    <h4 style="margin-top: 20px;">Student's Description:</h4>
                    <p style="white-space: pre-wrap; background: white; padding: 15px; border-radius: 5px;">
                        <?php echo htmlspecialchars($submission['submission_text']); ?>
                    </p>
                    
                    <?php if ($submission['image_path']): ?>
                    <h4 style="margin-top: 20px;">Submitted Image:</h4>
                    <img src="../<?php echo $submission['image_path']; ?>" alt="Submission" style="max-width: 100%; height: auto; border-radius: 10px; margin-top: 10px;">
                    <?php endif; ?>
                </div>
                
                <?php if ($submission['status'] == 'pending'): ?>
                <form method="POST" style="margin-top: 30px;">
                    <div class="form-group">
                        <label>Admin Feedback</label>
                        <textarea name="feedback" rows="4" placeholder="Provide feedback to the student..." required></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" name="action" value="approve" class="btn btn-primary">
                            ✅ Approve & Award Points
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            ❌ Reject Submission
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                    <strong>This submission has already been reviewed.</strong><br>
                    Status: <?php echo ucfirst($submission['status']); ?><br>
                    <?php if ($submission['admin_feedback']): ?>
                    Feedback: <?php echo htmlspecialchars($submission['admin_feedback']); ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>