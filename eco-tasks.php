<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle task submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = intval($_POST['task_id']);
    $submission_text = sanitize($_POST['submission_text']);
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] == 0) {
        $upload_dir = 'uploads/tasks/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['task_image']['name'], PATHINFO_EXTENSION);
        $file_name = $user_id . '_' . $task_id . '_' . time() . '.' . $file_ext;
        $image_path = $upload_dir . $file_name;
        
        move_uploaded_file($_FILES['task_image']['tmp_name'], $image_path);
    }
    
    // Insert submission
    $insert = "INSERT INTO eco_submissions (user_id, task_id, submission_text, image_path) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("iiss", $user_id, $task_id, $submission_text, $image_path);
    
    if ($stmt->execute()) {
        createNotification($user_id, 'Task Submitted!', 'Your eco-task submission is pending review.', 'info');
        $success = 'Task submitted successfully! Awaiting admin approval.';
    } else {
        $error = 'Failed to submit task. Please try again.';
    }
}

// Get all tasks
$tasks = $conn->query("SELECT * FROM eco_tasks ORDER BY category, difficulty");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Tasks - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>🌿 Eco Tasks</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Complete real-world environmental activities and make an impact!
                </p>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="task-list">
                    <?php while ($task = $tasks->fetch_assoc()): 
                        // Check if already submitted
                        $check = "SELECT status FROM eco_submissions WHERE user_id = ? AND task_id = ? ORDER BY submitted_at DESC LIMIT 1";
                        $stmt = $conn->prepare($check);
                        $stmt->bind_param("ii", $user_id, $task['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $submission = $result->fetch_assoc();
                    ?>
                    <div class="task-item">
                        <div class="task-header">
                            <div>
                                <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                                <p style="color: #7f8c8d; margin: 5px 0;">
                                    <?php echo htmlspecialchars($task['description']); ?>
                                </p>
                            </div>
                            <div>
                                <span class="badge badge-<?php echo $task['difficulty']; ?>">
                                    <?php echo ucfirst($task['difficulty']); ?>
                                </span>
                                <?php if ($submission): ?>
                                <span class="badge badge-<?php echo $submission['status']; ?>" style="margin-left: 5px;">
                                    <?php echo ucfirst($submission['status']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                            <div>
                                <span style="color: var(--primary); font-weight: bold; margin-right: 15px;">
                                    +<?php echo $task['points']; ?> points
                                </span>
                                <span style="color: var(--secondary); font-weight: bold;">
                                    +<?php echo $task['impact_value']; ?> impact
                                </span>
                            </div>
                            
                            <?php if (!$submission || $submission['status'] == 'rejected'): ?>
                            <button class="btn btn-primary btn-sm" onclick="openTaskModal(<?php echo $task['id']; ?>, '<?php echo htmlspecialchars($task['title']); ?>')">
                                Submit Task
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="content-card">
                <h3>📊 My Submissions</h3>
                <?php
                $my_submissions = $conn->query("
                    SELECT es.*, et.title, et.points, et.impact_value 
                    FROM eco_submissions es 
                    JOIN eco_tasks et ON es.task_id = et.id 
                    WHERE es.user_id = $user_id 
                    ORDER BY es.submitted_at DESC 
                    LIMIT 10
                ");
                
                if ($my_submissions->num_rows > 0):
                ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 12px; text-align: left;">Task</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Submitted</th>
                            <th style="padding: 12px; text-align: left;">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sub = $my_submissions->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($sub['title']); ?></td>
                            <td style="padding: 12px;">
                                <span class="badge badge-<?php echo $sub['status']; ?>">
                                    <?php echo ucfirst($sub['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo date('M d, Y', strtotime($sub['submitted_at'])); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo $sub['status'] == 'approved' ? '+' . $sub['points'] : '-'; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">
                    No submissions yet. Start completing eco-tasks!
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Task Submission Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeTaskModal()">&times;</span>
            <h3 id="modalTitle">Submit Task</h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="task_id" id="taskId">
                
                <div class="form-group">
                    <label>Describe your activity</label>
                    <textarea name="submission_text" rows="5" placeholder="Explain what you did, when, and how it helped the environment..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Photo (Optional)</label>
                    <input type="file" name="task_image" accept="image/*">
                    <small style="color: #7f8c8d;">Upload proof of your eco-activity</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Submit for Review
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openTaskModal(taskId, taskTitle) {
            document.getElementById('taskId').value = taskId;
            document.getElementById('modalTitle').textContent = 'Submit: ' + taskTitle;
            document.getElementById('taskModal').classList.add('active');
        }
        
        function closeTaskModal() {
            document.getElementById('taskModal').classList.remove('active');
        }
    </script>
</body>
</html>