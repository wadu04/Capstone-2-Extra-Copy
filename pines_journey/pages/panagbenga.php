<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Baguio Cultural Treasure Hunt</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7f7f7;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 700px;
      margin: 20px auto;
      padding: 10px;
    }
    .game-header {
      text-align: center;
      margin-bottom: 10px;
    }
    .object-list {
      background-color: #fff;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
      border: 1px solid #ddd;
    }
    .object-list ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .object-item {
      font-size: 16px;
      margin: 3px 0;
    }
    .object-item.found {
      text-decoration: line-through;
      color: gray;
    }
    .game-container {
      position: relative;
      width: 100%;
      max-width: 700px;
      height: 400px;
      margin: 0 auto;
      border: 2px solid #333;
      border-radius: 5px;
      background-size: cover;
      background-position: center;
    }
    .hidden-object {
      position: absolute;
      width: 40px;
      height: 40px;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .hidden-object:hover {
      transform: scale(1.1);
    }
    .hidden-object.found {
      opacity: 0.5;
      pointer-events: none;
    }
    #tryAgain {
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="game-header">
      <h2>Baguio Cultural Treasure Hunt</h2>
      <p>Search for 10 hidden artifacts from Baguio’s cultural heritage!</p>
    </div>
    <!-- List of items to find -->
    <div class="object-list">
      <h5>Items to Find:</h5>
      <ul id="objectList"></ul>
    </div>
    <!-- Game Scene -->
    <div class="game-container" id="gameContainer"></div>
    <!-- Try Again Button -->
    <div class="text-center">
      <button id="tryAgain" class="btn btn-success" style="display: none;">Try Again</button>
    </div>
  </div>

  <!-- Bootstrap Modal for Artifact Info -->
  <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="infoModalLabel">Artifact Info</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalBody">
          <!-- Artifact details will appear here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it!</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Array of background images (URLs). Replace with actual images if available.
    const backgrounds = [
      '../assets/images/games/guess.jpg',
      '../assets/images/games/memory.jpg',
      '../assets/images/games/trivia.jpg'
    ];

    // Array of 10 artifacts with unique images.
    const artifactsData = [
      { id: 'artifact1', name: 'Artifact 1', info: "Represents traditional weaving techniques.", img: "../assets/images/games/guess.jpg" },
      { id: 'artifact2', name: 'Artifact 2', info: "Symbol of Baguio's indigenous heritage.", img: "../assets/images/games/guess.jpg" },
      { id: 'artifact3', name: 'Artifact 3', info: "Highlights local bamboo carving artistry.", img: "../assets/images/games/guess.jpg" },
      { id: 'artifact4', name: 'Artifact 4', info: "Represents the rich history of Baguio's festivals.", img: "../assets/images/games/trivia.jpg" },
      { id: 'artifact5', name: 'Artifact 5', info: "A traditional tool used in local craftsmanship.", img: "../assets/images/games/memory.jpg" },
      { id: 'artifact6', name: 'Artifact 6', info: "Symbolizes the vibrant culture of the region.", img: "../assets/images/games/memory.jpg" },
      { id: 'artifact7', name: 'Artifact 7', info: "An object passed down through generations.", img: "../assets/images/games/trivia.jpg" },
      { id: 'artifact8', name: 'Artifact 8', info: "Reflects the artistic heritage of local artisans.", img: "../assets/images/games/memory.jpg" },
      { id: 'artifact9', name: 'Artifact 9', info: "Used in traditional ceremonies and cultural events.", img: "../assets/images/games/memory.jpg" },
      { id: 'artifact10', name: 'Artifact 10', info: "Represents Baguio's evolving culture.", img: "../assets/images/games/trivia.jpg" }
    ];

    let artifacts = []; // This will hold the game objects with found status

    const gameContainer = document.getElementById('gameContainer');
    const objectListEl = document.getElementById('objectList');
    const tryAgainBtn = document.getElementById('tryAgain');

    // Randomize a number between min and max.
    function getRandom(min, max) {
      return Math.floor(Math.random() * (max - min)) + min;
    }

    // Initialize or reset the game.
    function initGame() {
      // Clear container and reset try again button.
      gameContainer.innerHTML = '';
      tryAgainBtn.style.display = 'none';

      // Randomly choose a background.
      const bg = backgrounds[getRandom(0, backgrounds.length)];
      gameContainer.style.backgroundImage = `url('${bg}')`;

      // Create a deep copy of artifactsData and add found property.
      artifacts = artifactsData.map(item => ({ ...item, found: false }));

      // Update the object list.
      updateObjectList();

      // Get container dimensions.
      const containerRect = gameContainer.getBoundingClientRect();
      const containerWidth = containerRect.width;
      const containerHeight = containerRect.height;
      const objSize = 40; // 40x40 px

      // For each artifact, create an image element and randomize its position.
      artifacts.forEach(artifact => {
        const img = document.createElement('img');
        img.src = artifact.img;
        img.alt = artifact.name;
        img.className = 'hidden-object';
        img.id = artifact.id;
        // Random positions: ensuring the image stays fully inside.
        const left = getRandom(0, containerWidth - objSize);
        const top = getRandom(0, containerHeight - objSize);
        img.style.left = left + 'px';
        img.style.top = top + 'px';
        // Set data attributes for modal.
        img.setAttribute('data-name', artifact.name);
        img.setAttribute('data-info', artifact.info);

        // Add click event listener.
        img.addEventListener('click', function() {
          const art = artifacts.find(a => a.id === artifact.id);
          if (!art.found) {
            art.found = true;
            img.classList.add('found');
            updateObjectList();
            showArtifactModal(img.getAttribute('data-name'), img.getAttribute('data-info'));
            if (artifacts.every(a => a.found)) {
              // When all artifacts are found, show Try Again button.
              setTimeout(() => {
                tryAgainBtn.style.display = 'inline-block';
              }, 500);
            }
          }
        });

        // Append to game container.
        gameContainer.appendChild(img);
      });
    }

    // Update the list of objects to find.
    function updateObjectList() {
      objectListEl.innerHTML = '';
      artifacts.forEach(art => {
        const li = document.createElement('li');
        li.textContent = art.name;
        li.className = 'object-item' + (art.found ? ' found' : '');
        objectListEl.appendChild(li);
      });
    }

    // Show Bootstrap modal with artifact info.
    function showArtifactModal(name, info) {
      document.getElementById('infoModalLabel').textContent = name;
      document.getElementById('modalBody').textContent = info;
      const modal = new bootstrap.Modal(document.getElementById('infoModal'));
      modal.show();
    }

    // Set up Try Again button.
    tryAgainBtn.addEventListener('click', function() {
      initGame();
    });

    // Start the game on page load.
    window.addEventListener('load', initGame);
  </script>
  
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
