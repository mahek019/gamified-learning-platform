<div class="sidebar">
    <div class="sidebar-header">
        <h3>🌱 EcoLearn</h3>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="modules.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'modules.php' ? 'active' : ''; ?>">
                <span>📚</span>
                <span>Learning Modules</span>
            </a>
        </li>
        <li>
            <a href="eco-tasks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'eco-tasks.php' ? 'active' : ''; ?>">
                <span>🌿</span>
                <span>Eco Tasks</span>
            </a>
        </li>
        <li>
            <a href="games.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'games.php' ? 'active' : ''; ?>">
                <span>🎮</span>
                <span>Mini Games</span>
            </a>
        </li>
        <li>
            <a href="leaderboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : ''; ?>">
                <span>🏆</span>
                <span>Leaderboard</span>
            </a>
        </li>
        <li>
            <a href="badges.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'badges.php' ? 'active' : ''; ?>">
                <span>🏅</span>
                <span>My Badges</span>
            </a>
        </li>
        <li>
            <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <span>👤</span>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="certificates.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'certificates.php' ? 'active' : ''; ?>">
                <span>📜</span>
                <span>Certificates</span>
            </a>
        </li>
        <li>
            <a href="feedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
                <span>💬</span>
                <span>Feedback</span>
            </a>
        </li>
        <li style="margin-top: 20px;">
            <a href="logout.php">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
