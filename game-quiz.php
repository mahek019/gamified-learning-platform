<?php
require_once 'config/database.php';
requireLogin();

// Get random quiz questions from all modules
$questions = $conn->query("SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Quiz - EcoLearn</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="quiz-container">
                <div class="game-board">
                    <a href="games.php" style="color: var(--primary); text-decoration: none; margin-bottom: 15px; display: inline-block;">
                        ← Back to Games
                    </a>
                    
                    <h2>🧠 Quick Quiz Challenge</h2>
                    <p style="color: #7f8c8d; margin-bottom: 20px;">
                        Answer 10 random questions as fast as you can!
                    </p>
                    
                    <div class="game-header">
                        <div class="timer">⏱️ <span id="timer">60</span>s</div>
                        <div class="score-display">🎯 Score: <span id="score">0</span></div>
                        <div>Question: <span id="questionNum">1</span>/10</div>
                    </div>
                    
                    <div id="quizGame">
                        <div class="question-card" id="questionCard">
                            <h3 id="questionText" style="margin-bottom: 25px; font-size: 1.3rem;"></h3>
                            <ul class="options-list" id="optionsList"></ul>
                        </div>
                    </div>
                    
                    <div id="result" style="display: none; text-align: center; margin-top: 20px;">
                        <h3 id="resultTitle"></h3>
                        <p>Correct Answers: <span id="correctCount"></span>/10</p>
                        <p>Time Taken: <span id="timeTaken"></span>s</p>
                        <p style="color: var(--secondary); font-weight: bold;">Points Earned: <span id="pointsEarned"></span></p>
                        <button class="btn btn-primary" onclick="location.reload()">Play Again</button>
                        <a href="games.php" class="btn btn-secondary">Back to Games</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const quizData = <?php 
            $quiz_array = [];
            while ($q = $questions->fetch_assoc()) {
                $quiz_array[] = [
                    'question' => $q['question'],
                    'options' => [
                        $q['option_a'],
                        $q['option_b'],
                        $q['option_c'],
                        $q['option_d']
                    ],
                    'correct' => ['a', 'b', 'c', 'd'][ord($q['correct_option']) - ord('a')]
                ];
            }
            echo json_encode($quiz_array);
        ?>;
        
        let currentQuestion = 0;
        let score = 0;
        let timeLeft = 60;
        let timerInterval;
        let startTime;
        
        function startGame() {
            startTime = Date.now();
            showQuestion();
            startTimer();
        }
        
        function showQuestion() {
            if (currentQuestion >= quizData.length) {
                endGame();
                return;
            }
            
            const q = quizData[currentQuestion];
            document.getElementById('questionNum').textContent = currentQuestion + 1;
            document.getElementById('questionText').textContent = q.question;
            
            const optionsList = document.getElementById('optionsList');
            optionsList.innerHTML = '';
            
            q.options.forEach((option, index) => {
                const li = document.createElement('li');
                li.className = 'option-item';
                li.innerHTML = `
                    <label onclick="checkAnswer(${index})">
                        <input type="radio" name="answer">
                        ${option}
                    </label>
                `;
                optionsList.appendChild(li);
            });
        }
        
        function checkAnswer(selectedIndex) {
            const q = quizData[currentQuestion];
            const correctIndex = ['a', 'b', 'c', 'd'].indexOf(q.correct);
            
            if (selectedIndex === correctIndex) {
                score += 10;
                document.getElementById('score').textContent = score;
                showFeedback('✓ Correct!', 'var(--primary)');
            } else {
                showFeedback('✗ Wrong!', 'var(--danger)');
            }
            
            currentQuestion++;
            setTimeout(() => {
                showQuestion();
            }, 500);
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
                z-index: 1000;
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
            clearInterval(timerInterval);
            const timeTaken = Math.floor((Date.now() - startTime) / 1000);
            const correctAnswers = score / 10;
            const points = Math.max(Math.floor(score * 0.8), 0);
            
            document.getElementById('quizGame').style.display = 'none';
            document.getElementById('result').style.display = 'block';
            document.getElementById('resultTitle').textContent = score >= 50 ? '🎉 Excellent!' : '👍 Good Try!';
            document.getElementById('resultTitle').style.color = score >= 50 ? 'var(--primary)' : 'var(--accent)';
            document.getElementById('correctCount').textContent = correctAnswers;
            document.getElementById('timeTaken').textContent = timeTaken;
            document.getElementById('pointsEarned').textContent = points;
            
            // Save score
            saveScore('Quick Quiz', points, timeTaken);
        }
        
        // function saveScore(gameName, score, time) {
        //     fetch('save-game-score.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        //         body: `game_name=${gameName}&score=${score}&time=${time}`
        //     });
        // }
        
        // Start game
        startGame();
    </script>
</body>
</html>