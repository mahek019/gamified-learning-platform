<?php
require_once 'config/database.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Match - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .memory-grid {
            display: grid;
            grid-template-columns: repeat(4, 100px);
            gap: 10px;
            margin: 30px auto;
            max-width: 450px;
        }
        
        .memory-card {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            cursor: pointer;
            transition: all 0.3s;
            color: transparent;
            user-select: none;
        }
        
        .memory-card:hover:not(.flipped):not(.matched) {
            transform: scale(1.05);
        }
        
        .memory-card.flipped,
        .memory-card.matched {
            background: white;
            color: #333;
            border: 3px solid var(--primary);
        }
        
        .memory-card.matched {
            opacity: 0.6;
            cursor: default;
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
                    
                    <h2>🃏 Memory Match</h2>
                    <p style="color: #7f8c8d; margin-bottom: 20px;">
                        Match all the eco symbols to win!
                    </p>
                    
                    <div class="game-header">
                        <div class="timer">⏱️ <span id="timer">0</span>s</div>
                        <div class="score-display">🎯 Moves: <span id="moves">0</span></div>
                        <button class="btn btn-secondary btn-sm" onclick="resetGame()">New Game</button>
                    </div>
                    
                    <div class="memory-grid" id="gameGrid"></div>
                    
                    <div id="result" style="display: none; text-align: center; margin-top: 20px;">
                        <h3 style="color: var(--primary);">🎉 Congratulations!</h3>
                        <p>Time: <span id="finalTime"></span>s | Moves: <span id="finalMoves"></span></p>
                        <p style="color: var(--secondary); font-weight: bold;">Points Earned: <span id="pointsEarned"></span></p>
                        <button class="btn btn-primary" onclick="resetGame()">Play Again</button>
                        <a href="games.php" class="btn btn-secondary">Back to Games</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const symbols = ['🌍', '♻️', '🌱', '💧', '⚡', '🌳', '☀️', '🌬️'];
        let cards = [...symbols, ...symbols];
        let flippedCards = [];
        let matchedPairs = 0;
        let moves = 0;
        let timer = 0;
        let timerInterval;
        
        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }
        
        function createBoard() {
            const grid = document.getElementById('gameGrid');
            grid.innerHTML = '';
            shuffle(cards);
            
            cards.forEach((symbol, index) => {
                const card = document.createElement('div');
                card.className = 'memory-card';
                card.dataset.symbol = symbol;
                card.dataset.index = index;
                card.textContent = symbol;
                card.addEventListener('click', flipCard);
                grid.appendChild(card);
            });
        }
        
        function flipCard() {
            if (flippedCards.length >= 2) return;
            if (this.classList.contains('flipped') || this.classList.contains('matched')) return;
            
            if (!timerInterval) {
                startTimer();
            }
            
            this.classList.add('flipped');
            flippedCards.push(this);
            
            if (flippedCards.length === 2) {
                moves++;
                document.getElementById('moves').textContent = moves;
                checkMatch();
            }
        }
        
        function checkMatch() {
            const [card1, card2] = flippedCards;
            
            if (card1.dataset.symbol === card2.dataset.symbol) {
                card1.classList.add('matched');
                card2.classList.add('matched');
                matchedPairs++;
                flippedCards = [];
                
                if (matchedPairs === symbols.length) {
                    gameWon();
                }
            } else {
                setTimeout(() => {
                    card1.classList.remove('flipped');
                    card2.classList.remove('flipped');
                    flippedCards = [];
                }, 800);
            }
        }
        
        function startTimer() {
            timerInterval = setInterval(() => {
                timer++;
                document.getElementById('timer').textContent = timer;
            }, 1000);
        }
        
        function gameWon() {
            clearInterval(timerInterval);
            
            // Calculate points (max 100, decreases with time and moves)
            const basePoints = 100;
            const timeDeduction = Math.min(timer * 0.5, 30);
            const moveDeduction = Math.min((moves - 16) * 2, 30);
            const points = Math.max(Math.floor(basePoints - timeDeduction - moveDeduction), 20);
            
            document.getElementById('finalTime').textContent = timer;
            document.getElementById('finalMoves').textContent = moves;
            document.getElementById('pointsEarned').textContent = points;
            document.getElementById('result').style.display = 'block';
            
            // Save score
            saveScore('Memory Match', points, timer);
        }
        
        function saveScore(gameName, score, time) {
            fetch('save-game-score.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `game_name=${gameName}&score=${score}&time=${time}`
            });
        }
        
        function resetGame() {
            clearInterval(timerInterval);
            timerInterval = null;
            timer = 0;
            moves = 0;
            matchedPairs = 0;
            flippedCards = [];
            
            document.getElementById('timer').textContent = '0';
            document.getElementById('moves').textContent = '0';
            document.getElementById('result').style.display = 'none';
            
            createBoard();
        }
        
        // Initialize game
        createBoard();
    </script>
</body>
</html>