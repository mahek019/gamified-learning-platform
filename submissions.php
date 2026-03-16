<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success = isset($_GET['msg']) ? $_GET['msg'] : '';

// Get all submissions
$submissions = $conn->query("
    SELECT es.*, et.title as task_title, u.full_name, u.username 
    FROM eco_submissions es 
    JOIN eco_tasks et ON es.task_id = et.id 
    JOIN users u ON es.user_id = u.id 
    ORDER BY 
        CASE es.status 
            WHEN 'pending' THEN 1 
            WHEN 'approved' THEN 2 
            WHEN 'rejected' THEN 3 
        END,
        es.submitted_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Submissions - Admin</title>
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
                <li><a href="users.php"><span>👥</span> Users</a></li>
                <li><a href="submissions.php" class="active"><span>📝</span> Submissions</a></li>
                <li><a href="feedback.php"><span>💬</span> Feedback</a></li>
                <li><a href="../logout.php"><span>🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <h2>📝 Eco Task Submissions</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Review and approve student eco-task submissions
                </p>
                
                <?php if ($submissions->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; text-align: left;">Student</th>
                            <th style="padding: 12px; text-align: left;">Task</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Submitted</th>
                            <th style="padding: 12px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sub = $submissions->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($sub['full_name']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($sub['task_title']); ?></td>
                            <td style="padding: 12px;">
                                <span class="badge badge-<?php echo $sub['status']; ?>">
                                    <?php echo ucfirst($sub['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo date('M d, Y', strtotime($sub['submitted_at'])); ?>
                            </td>
                            <td style="padding: 12px;">
                                <a href="review-submission.php?id=<?php echo $sub['id']; ?>" class="btn btn-primary btn-sm">
                                    <?php echo $sub['status'] == 'pending' ? 'Review' : 'View'; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 40px;">
                    No submissions yet.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>