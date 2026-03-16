<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];
$total_modules = $conn->query("SELECT COUNT(*) as count FROM modules")->fetch_assoc()['count'];
$pending_tasks = $conn->query("SELECT COUNT(*) as count FROM eco_submissions WHERE status = 'pending'")->fetch_assoc()['count'];
$total_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];

// Get recent activities
$recent_users = $conn->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5");
$pending_submissions = $conn->query("
    SELECT es.*, et.title as task_title, u.full_name, u.username 
    FROM eco_submissions es 
    JOIN eco_tasks et ON es.task_id = et.id 
    JOIN users u ON es.user_id = u.id 
    WHERE es.status = 'pending' 
    ORDER BY es.submitted_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EcoLearn</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>🛡️ Admin Panel</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><span>📊</span> Dashboard</a></li>
                <li><a href="users.php"><span>👥</span> Users</a></li>
                <li><a href="modules.php"><span>📚</span> Modules</a></li>
                <li><a href="eco-tasks.php"><span>🌿</span> Eco Tasks</a></li>
                <li><a href="submissions.php"><span>📝</span> Submissions</a></li>
                <li><a href="feedback.php"><span>💬</span> Feedback</a></li>
                <li><a href="../dashboard.php"><span>👤</span> User View</a></li>
                <li><a href="../logout.php"><span>🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h2>Admin Dashboard</h2>
                    <p>Manage the EcoLearn platform</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <div class="user-details">
                        <h4>Admin</h4>
                        <p>Administrator</p>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">📚</div>
                    <div class="stat-details">
                        <h3><?php echo $total_modules; ?></h3>
                        <p>Learning Modules</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $pending_tasks; ?></h3>
                        <p>Pending Tasks</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">💬</div>
                    <div class="stat-details">
                        <h3><?php echo $total_feedback; ?></h3>
                        <p>Feedback Received</p>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <h3>📝 Pending Task Submissions</h3>
                <?php if ($pending_submissions->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; text-align: left;">Student</th>
                            <th style="padding: 12px; text-align: left;">Task</th>
                            <th style="padding: 12px; text-align: left;">Submitted</th>
                            <th style="padding: 12px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sub = $pending_submissions->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($sub['full_name']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($sub['task_title']); ?></td>
                            <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($sub['submitted_at'])); ?></td>
                            <td style="padding: 12px;">
                                <a href="review-submission.php?id=<?php echo $sub['id']; ?>" class="btn btn-primary btn-sm">
                                    Review
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="submissions.php" class="btn btn-secondary" style="margin-top: 15px;">View All Submissions</a>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">No pending submissions.</p>
                <?php endif; ?>
            </div>
            
            <div class="content-card">
                <h3>👥 Recent Registrations</h3>
                <?php if ($recent_users->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; text-align: left;">Name</th>
                            <th style="padding: 12px; text-align: left;">Username</th>
                            <th style="padding: 12px; text-align: left;">Email</th>
                            <th style="padding: 12px; text-align: left;">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">No recent users.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
