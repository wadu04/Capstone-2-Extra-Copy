<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baguio City Quiz - Pines' Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .option-btn {
            width: 100%;
            margin: 8px 0;
            padding: 12px 20px;
            text-align: left;
            white-space: normal;
            transition: all 0.3s ease;
            border: 2px solid #dee2e6;
        }
        .option-btn:hover {
            transform: translateX(5px);
            background-color: #f8f9fa;
        }
        .option-btn.correct {
            background-color: #d4edda !important;
            border-color: #c3e6cb;
            color: #155724;
        }
        .option-btn.incorrect {
            background-color: #f8d7da !important;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .progress-bar {
            height: 10px;
            margin-bottom: 2rem;
        }
        .result-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .result-item.correct {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .result-item.incorrect {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .quiz-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .quiz-header img {
            max-width: 200px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="quiz-container">
            <div class="quiz-header">
                <img src="../assets/images/games/trivia.jpg" alt="Baguio Quiz" class="mb-3">
                <h2>Baguio City Quiz</h2>
                <p class="text-muted">Test your knowledge about the Summer Capital of the Philippines!</p>
            </div>
            
            <div id="startScreen" class="text-center">
                
                <button class="btn btn-primary btn-lg" onclick="startQuiz()">Start Quiz</button>
            </div>

            <div id="quizContent" style="display: none;">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                
                <div id="questionContainer" class="mb-4">
                    <h4 id="questionText" class="mb-3"></h4>
                    <div id="optionsContainer"></div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span id="progressText">Question 1/30</span>
                    <button id="nextBtn" class="btn btn-primary" style="display: none;" onclick="nextQuestion()">Next Question</button>
                </div>
            </div>

            <div id="resultsContainer" style="display: none;">
                <h3 class="mb-4 text-center">Quiz Results</h3>
                <div id="resultsList"></div>
                <div class="text-center mt-4">
                    <button class="btn btn-primary me-2" onclick="restartQuiz()">Try Again</button>
                    <a href="../pages/games.php" class="btn btn-outline-primary">Back to Games</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let quizQuestions = [];
        let currentQuestion = 0;
        let userAnswers = [];

        async function startQuiz() {
            try {
                const response = await fetch('quiz.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                if (data.success) {
                    quizQuestions = data.questions;
                    currentQuestion = 0;
                    userAnswers = [];
                    document.getElementById('startScreen').style.display = 'none';
                    document.getElementById('quizContent').style.display = 'block';
                    // Reset the next button text when starting
                    document.getElementById('nextBtn').textContent = 'Next Question';
                    showQuestion();
                } else {
                    throw new Error(data.error || 'Failed to load questions');
                }
            } catch (error) {
                console.error('Error loading quiz:', error);
                alert('Failed to load quiz questions. Please try again later.');
            }
        }

        function showQuestion() {
            const question = quizQuestions[currentQuestion];
            document.getElementById('questionText').textContent = question.question;
            document.getElementById('progressText').textContent = `Question ${currentQuestion + 1}/30`;
            
            // Update progress bar
            const progress = ((currentQuestion + 1) / quizQuestions.length) * 100;
            document.querySelector('.progress-bar').style.width = `${progress}%`;
            
            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';
            
            question.options.forEach((option, index) => {
                const button = document.createElement('button');
                button.className = 'btn option-btn';
                button.textContent = option;
                button.onclick = () => selectAnswer(option);
                optionsContainer.appendChild(button);
            });
            
            document.getElementById('nextBtn').style.display = 'none';
        }

        function selectAnswer(answer) {
            const question = quizQuestions[currentQuestion];
            userAnswers.push({
                question: question.question,
                userAnswer: answer,
                correctAnswer: question.correct_answer,
                isCorrect: answer === question.correct_answer
            });

            const buttons = document.querySelectorAll('.option-btn');
            buttons.forEach(button => {
                button.disabled = true;
                if (button.textContent === answer) {
                    button.classList.remove('btn-outline-primary');
                    if (answer === question.correct_answer) {
                        button.classList.add('correct');
                    } else {
                        button.classList.add('incorrect');
                    }
                } else if (button.textContent === question.correct_answer) {
                    button.classList.add('correct');
                }
            });

            document.getElementById('nextBtn').style.display = 'block';
            if (currentQuestion === quizQuestions.length - 1) {
                document.getElementById('nextBtn').textContent = 'Show Results';
            }
        }

        function nextQuestion() {
            if (currentQuestion < quizQuestions.length - 1) {
                currentQuestion++;
                showQuestion();
            } else {
                showResults();
            }
        }

        function showResults() {
            document.getElementById('quizContent').style.display = 'none';
            document.getElementById('resultsContainer').style.display = 'block';
            
            const resultsList = document.getElementById('resultsList');
            resultsList.innerHTML = '';
            
            const correctAnswers = userAnswers.filter(a => a.isCorrect).length;
            resultsList.innerHTML = `
                <div class="text-center mb-4">
                    <h4>You got ${correctAnswers} out of ${quizQuestions.length} questions correct!</h4>
                </div>
            `;
            
            userAnswers.forEach((answer, index) => {
                const resultItem = document.createElement('div');
                resultItem.className = `result-item ${answer.isCorrect ? 'correct' : 'incorrect'}`;
                resultItem.innerHTML = `
                    <strong>Question ${index + 1}:</strong> ${answer.question}<br>
                    Your answer: ${answer.userAnswer}<br>
                    ${!answer.isCorrect ? `Correct answer: ${answer.correctAnswer}` : ''}
                `;
                resultsList.appendChild(resultItem);
            });
        }

        function restartQuiz() {
            document.getElementById('resultsContainer').style.display = 'none';
            document.getElementById('startScreen').style.display = 'block';
            document.querySelector('.progress-bar').style.width = '0%';
            // Reset the next button text
            document.getElementById('nextBtn').textContent = 'Next Question';
            // Clear previous answers
            userAnswers = [];
            currentQuestion = 0;
        }
    </script>
</body>
</html>
