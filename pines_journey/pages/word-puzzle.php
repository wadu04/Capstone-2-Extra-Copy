<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Puzzle - Pines' Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .game-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .crossword-grid {
            display: grid;
            grid-template-columns: repeat(15, 30px);
            gap: 1px;
            background-color: #000;
            padding: 1px;
            margin: 20px auto;
            width: fit-content;
        }
        .cell {
            width: 30px;
            height: 30px;
            background-color: white;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            position: relative;
            user-select: none;
        }
        .cell.black {
            background-color: #000;
        }
        .cell.selected {
            background-color: #e3f2fd;
        }
        .cell.highlighted {
            background-color: #bbdefb;
        }
        .cell.correct {
            background-color: #c8e6c9;
        }
        .number {
            position: absolute;
            top: 1px;
            left: 1px;
            font-size: 10px;
        }
        .word-list {
            margin-top: 20px;
            columns: 2;
        }
        .word-item {
            margin-bottom: 10px;
        }
        .word-item.found {
            color: #4caf50;
            text-decoration: line-through;
        }
        .nav-buttons {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 20px;
        }
        .nav-buttons .btn {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="game-container">
            <div class="text-center mb-4">
                <h2>Baguio Word Puzzle</h2>
                <p>Find all the hidden words related to Baguio City!</p>
            </div>
            <div class="crossword-grid" id="grid"></div>
            <div class="word-list" id="wordList"></div>
            <div class="game-controls text-center mt-4">
                <button class="btn btn-primary" onclick="resetGame()">Try Again</button>
            </div>
        </div>
    </div>

    <script>
        const puzzle = {
            words: [
                { word: 'BURNHAM', clue: 'Famous park in the heart of Baguio' },
                { word: 'MANSION', clue: 'Official summer residence of the Philippine President' },
                { word: 'MINESVIEW', clue: 'Popular viewpoint with silver works' },
                { word: 'PANAGBENGA', clue: 'Annual flower festival' },
                { word: 'CATHEDRAL', clue: 'Pink church in Session Road' },
                { word: 'WRIGHT', clue: 'Park known for horseback riding' },
                { word: 'SESSION', clue: 'Main road in Baguio' },
                { word: 'STRAWBERRY', clue: 'Famous fruit from La Trinidad' },
                { word: 'UKAY', clue: 'Thrift shopping in the city' },
                { word: 'PINE', clue: 'Tree that Baguio is known for' }
            ],
            grid: []
        };

        const gridSize = 15;
        let selectedCells = [];
        let foundWords = new Set();

        function initializeGrid() {
            // Create empty grid
            for (let i = 0; i < gridSize; i++) {
                puzzle.grid[i] = [];
                for (let j = 0; j < gridSize; j++) {
                    puzzle.grid[i][j] = '';
                }
            }

            // Place words in the grid
            puzzle.words.forEach(wordObj => {
                placeWord(wordObj.word);
            });

            // Fill empty cells with random letters
            for (let i = 0; i < gridSize; i++) {
                for (let j = 0; j < gridSize; j++) {
                    if (puzzle.grid[i][j] === '') {
                        puzzle.grid[i][j] = String.fromCharCode(65 + Math.floor(Math.random() * 26));
                    }
                }
            }
        }

        function placeWord(word) {
            const directions = [
                [0, 1],  // horizontal
                [1, 0],  // vertical
                [1, 1],  // diagonal
            ];

            let placed = false;
            while (!placed) {
                const direction = directions[Math.floor(Math.random() * directions.length)];
                const startX = Math.floor(Math.random() * gridSize);
                const startY = Math.floor(Math.random() * gridSize);

                if (canPlaceWord(word, startX, startY, direction)) {
                    placeWordAt(word, startX, startY, direction);
                    placed = true;
                }
            }
        }

        function canPlaceWord(word, startX, startY, direction) {
            const [dx, dy] = direction;
            if (startX + word.length * dx > gridSize || startY + word.length * dy > gridSize) {
                return false;
            }

            for (let i = 0; i < word.length; i++) {
                const x = startX + i * dx;
                const y = startY + i * dy;
                if (puzzle.grid[x][y] !== '' && puzzle.grid[x][y] !== word[i]) {
                    return false;
                }
            }
            return true;
        }

        function placeWordAt(word, startX, startY, direction) {
            const [dx, dy] = direction;
            for (let i = 0; i < word.length; i++) {
                puzzle.grid[startX + i * dx][startY + i * dy] = word[i];
            }
        }

        function createBoard() {
            const grid = document.getElementById('grid');
            const wordList = document.getElementById('wordList');

            // Create grid
            for (let i = 0; i < gridSize; i++) {
                for (let j = 0; j < gridSize; j++) {
                    const cell = document.createElement('div');
                    cell.className = 'cell';
                    cell.textContent = puzzle.grid[i][j];
                    cell.dataset.row = i;
                    cell.dataset.col = j;
                    cell.addEventListener('mousedown', startSelection);
                    cell.addEventListener('mouseover', continueSelection);
                    grid.appendChild(cell);
                }
            }

            // Create word list
            puzzle.words.forEach(wordObj => {
                const wordItem = document.createElement('div');
                wordItem.className = 'word-item';
                wordItem.textContent = `${wordObj.clue} (${wordObj.word.length})`;
                wordItem.dataset.word = wordObj.word;
                wordList.appendChild(wordItem);
            });

            document.addEventListener('mouseup', endSelection);
        }

        function startSelection(e) {
            selectedCells = [e.target];
            e.target.classList.add('selected');
        }

        function continueSelection(e) {
            if (selectedCells.length === 0) return;
            if (e.target.classList.contains('cell') && !selectedCells.includes(e.target)) {
                selectedCells.push(e.target);
                e.target.classList.add('selected');
            }
        }

        function endSelection() {
            const word = selectedCells.map(cell => cell.textContent).join('');
            const reverseWord = word.split('').reverse().join('');

            puzzle.words.forEach(wordObj => {
                if ((word === wordObj.word || reverseWord === wordObj.word) && !foundWords.has(wordObj.word)) {
                    foundWords.add(wordObj.word);
                    selectedCells.forEach(cell => cell.classList.add('correct'));
                    document.querySelector(`[data-word="${wordObj.word}"]`).classList.add('found');

                    if (foundWords.size === puzzle.words.length) {
                        setTimeout(() => {
                            alert('Congratulations! You found all the words!');
                        }, 500);
                    }
                }
            });

            selectedCells.forEach(cell => cell.classList.remove('selected'));
            selectedCells = [];
        }

        function resetGame() {
            // Clear the grid and word list
            document.getElementById('grid').innerHTML = '';
            document.getElementById('wordList').innerHTML = '';
            
            // Reset found words
            foundWords = new Set();
            
            // Reinitialize the game
            initializeGrid();
            createBoard();
        }

        // Initialize and create the game
        initializeGrid();
        createBoard();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>