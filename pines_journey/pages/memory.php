<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Match - Pines'Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .game-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .memory-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .card {
            aspect-ratio: 1;
            perspective: 1000px;
            cursor: pointer;
        }
        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.5s;
        }
        .card.flipped .card-inner {
            transform: rotateY(180deg);
        }
        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .card-front {
            background-color: #2c3e50;
            color: white;
        }
        .card-front img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        .card-back {
            background-color: white;
            transform: rotateY(180deg);
        }
        .card-back img {
            width: 80%;
            height: 80%;
            object-fit: cover;
        }
        #timer, #moves {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .game-controls {
            text-align: center;
            margin: 20px 0;
        }
        .btn-try-again {
            display: none;
        }
        #winModal .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            text-align: center;
            padding: 30px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        #winModal img.celebration-gif {
            max-width: 200px;
            margin: 0 auto 25px;
            border-radius: 10px;
        }
        #winModal h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 2rem;
        }
        #winModal p {
            font-size: 1.2rem;
            color: #495057;
            margin-bottom: 10px;
        }
        #winModal .modal-footer {
            border: none;
            justify-content: center;
            padding-top: 20px;
        }
        #winModal .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        #winModal .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .modal-backdrop.show {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="game-container">
            <div class="text-center mb-4">
                <h2>Memory Match Game</h2>
                <p>Match the Baguio landmarks and cultural icons!</p>
                <div id="timer">Time: 0s</div>
                <div id="moves">Moves: 0</div>
            </div>
            <div class="memory-grid" id="gameBoard"></div>
        </div>
    </div>
    <div class="modal fade" id="winModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="../assets/images/celebration.gif" class="celebration-gif" alt="Celebration">
                    <h3>Congratulations! You Won!</h3>
                    <p>Moves: <span id="winMoves">0</span></p>
                    <p>Time: <span id="winTime">0</span> seconds</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="resetGame()" data-bs-dismiss="modal">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const images = [
            '../assets/images/landmarks/mansion.jpg',
            '../assets/images/landmarks/minesview.jpg',
            '../assets/images/landmarks/burnhamm.jpg',
            '../assets/images/landmarks/cathedral.jpg',
            '../assets/images/landmarks/wright-park.jpg',
            '../assets/images/landmarks/botanical.jpg',
        ];

        let cards = [...images, ...images];
        let moves = 0;
        let timer = 0;
        let timerInterval;
        let flippedCards = [];
        let matchedPairs = 0;

        function resetGame() {
            // Reset variables
            moves = 0;
            timer = 0;
            flippedCards = [];
            matchedPairs = 0;
            
            // Clear timer
            clearInterval(timerInterval);
            
            // Update display
            document.getElementById('moves').textContent = 'Moves: 0';
            document.getElementById('timer').textContent = 'Time: 0s';
            
            // Clear and recreate game board
            const gameBoard = document.getElementById('gameBoard');
            gameBoard.innerHTML = '';
            
            // Reinitialize game
            createBoard();
            
            // Start the timer
            startTimer();
        }

        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function createBoard() {
            const gameBoard = document.getElementById('gameBoard');
            shuffle(cards);
            
            cards.forEach((card, index) => {
                const cardElement = document.createElement('div');
                cardElement.className = 'card';
                cardElement.innerHTML = `
                    <div class="card-inner">
                        <div class="card-front"><img src="../assets/images/games/front.jpg" alt="Card Front"></div>
                        <div class="card-back">
                            <img src="${card}" alt="Baguio Landmark">
                        </div>
                    </div>
                `;
                cardElement.dataset.cardIndex = index;
                cardElement.addEventListener('click', flipCard);
                gameBoard.appendChild(cardElement);
            });
        }

        function flipCard() {
            if (flippedCards.length === 2) return;
            if (this.classList.contains('flipped')) return;

            this.classList.add('flipped');
            flippedCards.push(this);

            if (flippedCards.length === 2) {
                moves++;
                document.getElementById('moves').textContent = `Moves: ${moves}`;
                checkMatch();
            }
        }

        function checkMatch() {
            const [card1, card2] = flippedCards;
            const match = cards[card1.dataset.cardIndex] === cards[card2.dataset.cardIndex];

            if (match) {
                matchedPairs++;
                flippedCards = [];
                if (matchedPairs === cards.length / 2) {
                    setTimeout(() => {
                        clearInterval(timerInterval);
                        // Update win statistics
                        document.getElementById('winMoves').textContent = moves;
                        document.getElementById('winTime').textContent = timer;
                        // Show modal
                        const winModal = new bootstrap.Modal(document.getElementById('winModal'));
                        winModal.show();
                    }, 500);
                }
            } else {
                setTimeout(() => {
                    card1.classList.remove('flipped');
                    card2.classList.remove('flipped');
                    flippedCards = [];
                }, 1000);
            }
        }

        function startTimer() {
            timerInterval = setInterval(() => {
                timer++;
                document.getElementById('timer').textContent = `Time: ${timer}s`;
            }, 1000);
        }

        // Initialize the game
        createBoard();
        startTimer();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>