<?php
require_once 'config/database.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    $rating = intval($_POST['rating']);
    
    if (empty($subject) || empty($message)) {
        $error = 'Please fill all fields!';
    } else {
        $insert = "INSERT INTO feedback (user_id, subject, message, rating) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("issi", $user_id, $subject, $message, $rating);
        
        if ($stmt->execute()) {
            $success = 'Thank you for your feedback! We appreciate your input.';
        } else {
            $error = 'Failed to submit feedback. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .rating-stars {
            display: flex;
            gap: 10px;
            font-size: 2rem;
            margin: 15px 0;
        }
        
        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.3s;
        }
        
        .star.active,
        .star:hover {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-card">
                <h2>💬 Feedback</h2>
                <p style="color: #7f8c8d; margin-bottom: 25px;">
                    We value your feedback! Help us improve the platform.
                </p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" style="max-width: 600px; margin: 0 auto;">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" placeholder="What is your feedback about?" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Your Feedback</label>
                        <textarea name="message" rows="6" placeholder="Tell us your thoughts, suggestions, or report issues..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Rate Your Experience</label>
                        <div class="rating-stars" id="ratingStars">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="5" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Submit Feedback
                    </button>
                </form>
            </div>
            
            <div class="content-card">
                <h3>📊 Quick Stats</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon green">💚</div>
                        <div class="stat-details">
                            <h3>Help Us Grow</h3>
                            <p>Your feedback shapes the platform</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon blue">🎯</div>
                        <div class="stat-details">
                            <h3>Be Heard</h3>
                            <p>Every suggestion matters</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('ratingInput');
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
        
        // Set default 5 stars
        stars.forEach(s => s.classList.add('active'));
    </script>
</body>
</html>