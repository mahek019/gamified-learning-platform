<?php
require_once 'config/database.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waste Sorting - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .waste-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 3rem;
            margin: 20px auto;
            max-width: 200px;
            box-shadow: var(--shadow);
            animation: slideIn 0.5s;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .bins-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 30px;
        }
        
        .bin {
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 3px solid transparent;
        }
        
        .bin:hover {
            transform: scale(1.05);
            border-color: #333;
        }
        
        .bin.wet {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }
        
        .bin.dry {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .bin.hazard {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .bin h3 {
            margin-top: 10px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="game-container">
                <div class="game-board">
                    <a href="games.php" style="color: var(--primary); text-decoration: none; margin-bottom: 15px; display: inline-block;">
                        ← Back to Games
                    </a>
                    
                    <h2>♻️ Waste Sorting Challenge</h2>
                    <p style="color: #7f8c8d; margin-bottom: 20px;">
                        Sort the waste items into the correct bins!
                    </p>
                    
                    <div class="game-header">
                        <div class="timer">⏱️ <span id="timer">30</span>s</div>
                        <div class="score-display">🎯 Score: <span id="score">0</span></div>
                        <div style="color: var(--danger);">❌ Wrong: <span id="wrong">0</span></div>
                    </div>
                    
                    <div id="gameArea">
                        <div class="waste-item" id="currentItem">
                            <div id="itemIcon"></div>
                            <div id="itemName" style="font-size: 1rem; margin-top: 10px; color: #333;"></div>
                        </div>
                        
                        <div class="bins-container">
                            <div class="bin wet" onclick="checkAnswer('wet')">
                                <div style="font-size: 3rem;">🍃</div>
                                <h3>Wet Waste</h3>
                                <p style="font-size: 0.9rem; margin-top: 5px;">Organic, biodegradable</p>
                            </div>
                            <div class="bin dry" onclick="checkAnswer('dry')">
                                <div style="font-size: 3rem;">📦</div>
                                <h3>Dry Waste</h3>
                                <p style="font-size: 0.9rem; margin-top: 5px;">Recyclable items</p>
                            </div>
                            <div class="bin hazard" onclick="checkAnswer('hazard')">
                                <div style="font-size: 3rem;">☢️</div>
                                <h3>Hazardous</h3>
                                <p style="font-size: 0.9rem; margin-top: 5px;">Toxic materials</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="result" style="display: none; text-align: center; margin-top: 20px;">
                        <h3 id="resultTitle"></h3>
                        <p>Final Score: <span id="finalScore"></span></p>
                        <p>Correct: <span id="correctCount"></span> | Wrong: <span id="wrongCount"></span></p>
                        <p style="color: var(--secondary); font-weight: bold;">Points Earned: <span id="pointsEarned"></span></p>
                        <button class="btn btn-primary" onclick="startGame()">Play Again</button>
                        <a href="games.php" class="btn btn-secondary">Back to Games</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const wasteItems = [
            { name: 'Banana Peel', icon: '🍌', type: 'wet' },
            { name: 'Plastic Bottle', icon: '🍶', type: 'dry' },
            { name: 'Battery', icon: '🔋', type: 'hazard' },
            { name: 'Food Scraps', icon: '🍽️', type: 'wet' },
            { name: 'Paper', icon: '📄', type: 'dry' },
            { name: 'Medicine', icon: '💊', type: 'hazard' },
            { name: 'Vegetable Peels', icon: '🥕', type: 'wet' },
            { name: 'Glass Bottle', icon: '🍾', type: 'dry' },
            { name: 'Paint Can', icon: '🎨', type: 'hazard' },
            { name: 'Fruit Waste', icon: '🍎', type: 'wet' },
            { name: 'Cardboard', icon: '📦', type: 'dry' },
            { name: 'Cleaning Products', icon: '🧴', type: 'hazard' },
            { name: 'Tea Leaves', icon: '🍵', type: 'wet' },
            { name: 'Aluminum Can', icon: '🥫', type: 'dry' },
            { name: 'Electronics', icon: '📱', type: 'hazard' }
        ];
        
        let score = 0;
        let wrong = 0;
        let timeLeft = 30;
        let currentItem;
        let timerInterval;
        let gameActive = false;
        
        function getRandomItem() {
            return wasteItems[Math.floor(Math.random() * wasteItems.length)];
        }
        
        function showNewItem() {
            currentItem = getRandomItem();
            document.getElementById('itemIcon').textContent = currentItem.icon;
            document.getElementById('itemName').textContent = currentItem.name;
        }
        
        function checkAnswer(selectedType) {
            if (!gameActive) return;
            
            if (selectedType === currentItem.type) {
                score += 10;
                document.getElementById('score').textContent = score;
                showFeedback('✓ Correct!', 'var(--primary)');
            } else {
                wrong++;
                document.getElementById('wrong').textContent = wrong;
                showFeedback('✗ Wrong!', 'var(--danger)');
            }
            
            showNewItem();
        }
        
        function showFeedback(message, color) {
            const feedback = document.createElement('div');
            feedback.textContent = message;
            feedback.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 3rem;
                color: ${color};
                font-weight: bold;
                animation: fadeOut 0.5s;
                pointer-events: none;
            `;
            document.body.appendChild(feedback);
            setTimeout(() => feedback.remove(), 500);
        }
        
        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    endGame();
                }
            }, 1000);
        }
        
        function endGame() {
            gameActive = false;
            clearInterval(timerInterval);
            
            const accuracy = score / (score + (wrong * 10)) * 100 || 0;
            const points = Math.max(Math.floor(score * 0.5), 0);
            
            document.getElementById('gameArea').style.display = 'none';
            document.getElementById('result').style.display = 'block';
            document.getElementById('resultTitle').textContent = score >= 50 ? '🎉 Great Job!' : '👍 Good Try!';
            document.getElementById('resultTitle').style.color = score >= 50 ? 'var(--primary)' : 'var(--accent)';
            document.getElementById('finalScore').textContent = score;
            document.getElementById('correctCount').textContent = score / 10;
            document.getElementById('wrongCount').textContent = wrong;
            document.getElementById('pointsEarned').textContent = points;
            
            // Save score
            saveScore('Waste Sorting', points, 30 - timeLeft);
        }
        
        function saveScore(gameName, score, time) {
            fetch('save-game-score.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `game_name=${gameName}&score=${score}&time=${time}`
            });
        }
        
        function startGame() {
            score = 0;
            wrong = 0;
            timeLeft = 30;
            gameActive = true;
            
            document.getElementById('score').textContent = '0';
            document.getElementById('wrong').textContent = '0';
            document.getElementById('timer').textContent = '30';
            document.getElementById('gameArea').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            
            showNewItem();
            startTimer();
        }
        
        // Start game on load
        startGame();
    </script>
</body>
</html>