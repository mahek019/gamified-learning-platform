<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user info
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check eligibility for certificates
$modules_completed = $conn->query("SELECT COUNT(*) as count FROM user_progress WHERE user_id = $user_id AND completed = 1")->fetch_assoc()['count'];
$total_modules = $conn->query("SELECT COUNT(*) as count FROM modules")->fetch_assoc()['count'];

// Generate certificate if eligible and not already generated
if ($modules_completed >= $total_modules && $modules_completed > 0) {
    $check_cert = "SELECT * FROM certificates WHERE user_id = ? AND certificate_type = 'completion'";
    $stmt = $conn->prepare($check_cert);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows == 0) {
        $code = generateCertificateCode();
        $insert = "INSERT INTO certificates (user_id, certificate_type, certificate_code) VALUES (?, 'completion', ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("is", $user_id, $code);
        $stmt->execute();
        
        createNotification($user_id, 'Certificate Earned!', 'Congratulations! You earned a completion certificate!', 'success');
    }
}

// Get user certificates
$certificates = $conn->query("SELECT * FROM certificates WHERE user_id = $user_id ORDER BY issued_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .certificate {
            background: linear-gradient(135deg, #fff5e1, #ffe4b5);
            border: 5px solid goldenrod;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .certificate-header {
            font-size: 2.5rem;
            color: #8b4513;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .certificate-body {
            font-size: 1.2rem;
            line-height: 2;
            color: #333;
        }
        
        .certificate-name {
            font-size: 2rem;
            color: var(--primary);
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        
        .certificate-footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-around;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>📜 My Certificates</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    Your achievements and certificates
                </p>
                
                <?php if ($modules_completed < $total_modules): ?>
                <div class="alert alert-info">
                    <strong>Complete all modules to earn your certificate!</strong><br>
                    Progress: <?php echo $modules_completed; ?> / <?php echo $total_modules; ?> modules completed
                    <div class="progress-bar" style="margin-top: 10px;">
                        <div class="progress-fill" style="width: <?php echo ($modules_completed / $total_modules) * 100; ?>%">
                            <?php echo round(($modules_completed / $total_modules) * 100); ?>%
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($certificates->num_rows > 0): ?>
                    <?php while ($cert = $certificates->fetch_assoc()): ?>
                    <div class="certificate">
                        <div style="font-size: 4rem; margin-bottom: 20px;">🏆</div>
                        <div class="certificate-header">Certificate of Achievement</div>
                        <div class="certificate-body">
                            <p>This is to certify that</p>
                            <div class="certificate-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <p>has successfully completed the</p>
                            <h3 style="color: var(--secondary); margin: 15px 0;">
                                <?php echo ucfirst($cert['certificate_type']); ?> Program
                            </h3>
                            <p>in Environmental Education and Sustainability</p>
                            <p style="margin-top: 20px;">
                                <strong>Certificate Code:</strong> <?php echo $cert['certificate_code']; ?>
                            </p>
                        </div>
                        <div class="certificate-footer">
                            <div>
                                <p>Date: <?php echo date('F d, Y', strtotime($cert['issued_at'])); ?></p>
                            </div>
                            <div>
                                <p>EcoLearn Platform</p>
                            </div>
                        </div>
                        <button class="btn btn-primary" onclick="window.print()" style="margin-top: 20px;">
                            🖨️ Print Certificate
                        </button>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 5rem; margin-bottom: 20px;">📜</div>
                        <h3 style="color: #7f8c8d;">No Certificates Yet</h3>
                        <p style="color: #7f8c8d; margin: 15px 0;">
                            Complete all learning modules to earn your first certificate!
                        </p>
                        <a href="modules.php" class="btn btn-primary" style="margin-top: 20px;">
                            Start Learning
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>