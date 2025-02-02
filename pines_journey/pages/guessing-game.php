<?php
include '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Guessing Game - Pines Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --hint-color: #f1c40f;
            --background-color: #ecf0f1;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .game-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        .letter-box {
            width: 35px;
            height: 35px;
            border: 2px solid #dfe6e9;
            margin: 2px;
            text-align: center;
            text-transform: uppercase;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.2s ease;
            border-radius: 5px;
        }

        @media (max-width: 576px) {
            .letter-box {
                width: 28px;
                height: 28px;
                font-size: 16px;
            }
        }

        .game-image {
    width: 100%;
    max-height: 400px; /* Increased from 250px */
    min-height: 250px;
    object-fit: contain; /* Changed from cover to prevent cropping */
    margin: 20px 0;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

        .hint-text {
            min-height: 50px;
            font-size: 1rem;
            margin: 15px 0;
            color: var(--primary-color);
            padding: 12px;
            background: rgba(241, 196, 15, 0.1);
            border-radius: 8px;
        }

        .correct { 
            border-color: var(--success-color);
            background-color: rgba(39, 174, 96, 0.15);
        }

        .revealed {
            background-color: rgba(241, 196, 15, 0.2);
            border-color: var(--hint-color);
        }

        #questionCounter {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .btn-custom {
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-hint {
            background-color: var(--hint-color);
            border-color: var(--hint-color);
            color: var(--primary-color);
        }

        .btn-reveal {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }

        .btn-next {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .status-circle {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 3px;
            background-color: #dfe6e9;
        }

        .status-circle.active {
            background-color: var(--secondary-color);
        }

        .status-circle.correct {
            background-color: var(--success-color);
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="game-card">
                    <img src="" alt="Guess the image" class="game-image" id="questionImage">
                    <div class="card-body text-center pt-4">
                        <div id="questionCounter" class="mb-3"></div>
                        <div class="mb-3" id="statusIndicators"></div>
                        
                        <?php
                          $questions = [
                            [
                                'image' => '../assets/images/games/strawberry.jpg',
                                'answer' => 'STRAWBERRY',
                                'hints' => ['Red and sweet fruit', 'Commonly found in La Trinidad', 'Baguio\'s famous fruit']
                            ],
                            [
                                'image' => '../assets/images/games/market.jpg',
                                'answer' => 'BAGUIO PUBLIC MARKET',
                                'hints' => ['A bustling commercial center', 'Where locals buy fresh produce', 'Located in the heart of the city']
                            ],
                            [
                                'image' => '../assets/images/games/sunflower.jpg',
                                'answer' => 'SUNFLOWER',
                                'hints' => ['Yellow petals facing the sun', 'Popular flower in gardens', 'Grows tall and bright']
                            ],
                            [
                                'image' => '../assets/images/games/botanicall.png',
                                'answer' => 'BOTANICAL',
                                'hints' => ['Garden with various plants', 'Research and conservation site', 'Educational nature park']
                            ],
                            [
                                'image' => '../assets/images/games/diplomat.jpg',
                                'answer' => 'DIPLOMAT HOTEL',
                                'hints' => ['Historic abandoned building', 'Former vacation house and monastery', 'Known for supernatural stories']
                            ],
                            [
                                'image' => '../assets/images/games/pine.webp',
                                'answer' => 'PINE TREE',
                                'hints' => ['Symbol of Baguio City', 'Evergreen coniferous tree', 'Gives the city fresh air']
                            ],
                            [
                                'image' => '../assets/images/games/pinuneg.png',
                                'answer' => 'PINUNEG',
                                'hints' => ['Traditional Cordilleran blood sausage & delicacy', 'Made from pork blood & intestines', 'Popular in Benguet & Baguio']
                            ],
                            [
                                'image' => '../assets/images/games/gangsa.jpg',
                                'answer' => 'GANGSA',
                                'hints' => ['Traditional musical instrument', 'Made of bronze or brass', 'Used in indigenous ceremonies']
                            ],
                            [
                                'image' => '../assets/images/games/taho.jpg',
                                'answer' => 'STRAWBERRY TAHO',
                                'hints' => ['Popular street food', 'Sweet drink with soft tofu', 'Pink-colored version of a Filipino favorite']
                            ],
                            [
                                'image' => '../assets/images/games/pinikpikan.jpg',
                                'answer' => 'PINIKPIKAN',
                                'hints' => ['Traditional Cordilleran dish', 'Type of chicken soup', 'Known for its unique preparation method']
                            ]
                        ];
                        shuffle($questions);
                        ?>

                        <div id="gameContainer">
                            <div id="answerBoxes" class="mb-4 d-flex justify-content-center flex-wrap"></div>
                            <div class="hint-text" id="hintText"></div>
                            <div class="mb-4">
                                <button class="btn btn-custom btn-hint me-2" onclick="showHint()">Hint</button>
                                <button class="btn btn-custom btn-reveal" onclick="revealLetter()">Reveal Letter</button>
                            </div>
                            <div id="message" class="alert d-none"></div>
                            <button class="btn btn-custom btn-next" onclick="nextQuestion()" id="nextBtn" style="display: none;">Next</button>
                        </div>
                        <div id="gameComplete" style="display: none;">
                            <h3 class="text-success mb-3">Congratulations! 🎉</h3>
                            <p class="mb-4">You've completed all questions!</p>
                            <button class="btn btn-custom btn-next" onclick="location.reload()">Play Again</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        let currentQuestion = 0;
        let currentHintIndex = -1;
        let revealedLetters = new Set();

        function initializeQuestion() {
            if (currentQuestion >= questions.length) {
                document.getElementById('gameContainer').style.display = 'none';
                document.getElementById('gameComplete').style.display = 'block';
                return;
            }

            const question = questions[currentQuestion];
            document.getElementById('questionImage').src = question.image;
            document.getElementById('hintText').textContent = '';
            document.getElementById('message').className = 'alert d-none';
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('questionCounter').textContent = `Question ${currentQuestion + 1} of ${questions.length}`;
            currentHintIndex = -1;
            revealedLetters.clear();
            
            // Update status indicators
            const statusIndicators = document.getElementById('statusIndicators');
            statusIndicators.innerHTML = questions.map((_, i) => 
                `<span class="status-circle ${i === currentQuestion ? 'active' : ''} ${i < currentQuestion ? 'correct' : ''}"></span>`
            ).join('');

            const answerBoxes = document.getElementById('answerBoxes');
            answerBoxes.innerHTML = '';
            const answerLetters = question.answer.split('');

            answerLetters.forEach((letter, index) => {
                const input = document.createElement('input');
                input.type = 'text';
                input.maxLength = 1;
                input.className = 'letter-box';
                input.dataset.index = index;
                if (letter === ' ') {
                    input.value = ' ';
                    input.disabled = true;
                    input.classList.add('space-box');
                } else {
                    input.addEventListener('input', handleInput);
                    input.addEventListener('keydown', handleKeyDown);
                }
                answerBoxes.appendChild(input);
            });
        }

        function handleInput(e) {
    const input = e.target;
    input.value = input.value.toUpperCase();
    
    if (input.value) {
        let nextIndex = parseInt(input.dataset.index) + 1;
        // Find next enabled input
        while (nextIndex < document.querySelectorAll('.letter-box').length) {
            const nextInput = document.querySelector(`[data-index="${nextIndex}"]`);
            if (!nextInput.disabled) {
                nextInput.focus();
                break;
            }
            nextIndex++;
        }
    }
    autoCheckAnswer();
}
        function handleKeyDown(e) {
    if (e.key === 'Backspace' && !e.target.value) {
        let currentIndex = parseInt(e.target.dataset.index);
        // Move backward skipping disabled inputs
        do {
            currentIndex--;
            const prevInput = document.querySelector(`[data-index="${currentIndex}"]`);
            if (prevInput && !prevInput.disabled) {
                prevInput.focus();
                break;
            }
        } while (currentIndex > 0);
    }
}

        function showHint() {
            const question = questions[currentQuestion];
            currentHintIndex = (currentHintIndex + 1) % question.hints.length;
            document.getElementById('hintText').textContent = `Hint: ${question.hints[currentHintIndex]}`;
        }

        function revealLetter() {
            const question = questions[currentQuestion];
            const answerLetters = question.answer.split('');
            const inputs = document.querySelectorAll('.letter-box');
            const unrevealedIndices = [];
            
            inputs.forEach((input, index) => {
                if (answerLetters[index] !== ' ' && !input.value) {
                    unrevealedIndices.push(index);
                }
            });

            if (unrevealedIndices.length > 0) {
        const randomIndex = unrevealedIndices[Math.floor(Math.random() * unrevealedIndices.length)];
        const input = inputs[randomIndex];
        input.value = answerLetters[randomIndex];
        input.disabled = true;
        input.classList.add('revealed');
        
        // Focus on next empty input
        let nextIndex = randomIndex + 1;
        while (nextIndex < inputs.length) {
            const nextInput = inputs[nextIndex];
            if (!nextInput.disabled && !nextInput.value) {
                nextInput.focus();
                break;
            }
            nextIndex++;
        }
        
        autoCheckAnswer();
    }
}

        function autoCheckAnswer() {
            const question = questions[currentQuestion];
            const inputs = Array.from(document.querySelectorAll('.letter-box'));
            const userAnswer = inputs.map(input => input.value).join('');

            if (userAnswer === question.answer) {
                inputs.forEach(input => input.classList.add('correct'));
                document.getElementById('nextBtn').style.display = 'inline-block';
            }
        }

        function nextQuestion() {
            currentQuestion++;
            initializeQuestion();
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && document.getElementById('nextBtn').style.display === 'inline-block') {
                nextQuestion();
            }
        });

        initializeQuestion();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>