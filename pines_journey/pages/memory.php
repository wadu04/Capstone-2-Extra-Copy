<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Match - Pines'Jo</title>
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
            font-size: 24px;
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
            <div class="game-controls">
                <button class="btn btn-primary btn-try-again" id="tryAgainBtn" onclick="resetGame()">Try Again</button>
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
            
            // Hide try again button
            document.querySelector('.btn-try-again').style.display = 'none';
            
            // Reinitialize game
            createBoard();
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
                        <div class="card-front">?</div>
                        <div class="card-back">
                            <img src="${card}" alt="Baguio Landmark">
                        </div>
                    </div>
                `;
                cardElement.dataset.cardIndex = index;
                cardElement.addEventListener('click', flipCard);
                gameBoard.appendChild(cardElement);
            });

            startTimer();
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
                        alert(`Congratulations! You won in ${moves} moves and ${timer} seconds!`);
                        clearInterval(timerInterval);
                        document.querySelector('.btn-try-again').style.display = 'inline-block';
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>