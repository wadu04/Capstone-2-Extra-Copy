<?php
require_once '../includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Baguio Cultural Treasure Hunt</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../assets/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    
  
    .game-header {
      text-align: center;
      margin-bottom: 10px;
    }
    .game-layout {
      display: grid;
      grid-template-columns: 75% 25%;
      gap: 20px;
      margin-bottom: 20px;
      max-width: 9000px;
      margin-left: auto;
      margin-right: auto;
    }
    .object-list {
      background-color: #fff;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #ddd;
      font-size: 14px;
    }
    .object-list h5 {
      font-size: 16px;
      margin-bottom: 12px;
    }
    .object-list ul {
      list-style: none;
      padding: 0;
      margin: 0;
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
    }
    .object-item {
      font-size: 14px;
      margin: 0;
      padding: 8px;
      background-color: #f8f9fa;
      border-radius: 5px;
      text-align: center;
      border: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }
    .object-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .object-item.found {
      color: white;
      font-weight: bold;
    }
    .found-color-1 { background-color: #FF9AA2; }
    .found-color-2 { background-color: #FFB7B2; }
    .found-color-3 { background-color: #FFDAC1; }
    .found-color-4 { background-color: #E2F0CB; }
    .found-color-5 { background-color: #B5EAD7; }
    .found-color-6 { background-color: #C7CEEA; }
    .found-color-7 { background-color: #9BB7D4; }
    .found-color-8 { background-color: #85C1E9; }
    .found-color-9 { background-color: #BB8FCE; }
    .found-color-10 { background-color: #F1948A; }
    .game-container {
      position: relative;
      width: 100%;
      height: 550px;
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
      transition: all 0.3s ease;
    }
    .hidden-object:hover {
      transform: scale(1.1);
    }
    .hidden-object.found {
      opacity: 0.2;
      pointer-events: none;
      transform: scale(0.9);
      filter: grayscale(100%);
    }
    .modal-content {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .modal-header {
      background-color: #f8f9fa;
      border-radius: 15px 15px 0 0;
      border-bottom: 2px solid #e9ecef;
    }
    .modal-body {
      padding: 25px;
    }
    .artifact-info {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .artifact-image {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #dee2e6;
    }
    .artifact-details {
      flex: 1;
    }
    .artifact-details h4 {
      margin-bottom: 10px;
      color: #2c3e50;
    }
    .artifact-details p {
      color: #6c757d;
      margin-bottom: 0;
      line-height: 1.5;
    }
    .completion-message {
      text-align: center;
      padding: 20px 0;
    }
    .completion-message h3 {
      color: #28a745;
      margin-bottom: 15px;
    }
    .completion-message p {
      color: #6c757d;
      margin-bottom: 20px;
    }
    .btn-try-again {
      background-color: #28a745;
      color: white;
      padding: 10px 30px;
      border-radius: 25px;
      border: none;
      transition: all 0.3s ease;
    }
    .btn-try-again:hover {
      background-color: #218838;
      transform: translateY(-2px);
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
  <div class="container">
    <div class="game-header">
      <h2>Baguio Cultural Treasure Hunt</h2>
      <p>Search for 10 hidden artifacts from Baguio's cultural heritage!</p>
    </div>
    <div class="game-layout">
      <!-- Game Scene -->
      <div class="game-container" id="gameContainer"></div>
      <!-- List of items to find -->
      <div class="object-list">
        <h5 class="text-center mb-4">Items to Find:</h5>
        <ul id="objectList" class="mb-0"></ul>
      </div>
    </div>
    <!-- Artifact Found Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="infoModalLabel">Artifact Found!</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="artifact-info">
              <img id="modalArtifactImage" class="artifact-image" src="" alt="">
              <div class="artifact-details">
                <h4 id="modalArtifactName"></h4>
                <p id="modalArtifactInfo"></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Completion Modal -->
    <div class="modal fade" id="completionModal" tabindex="-1" aria-labelledby="completionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="completionModalLabel">Congratulations!</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="completion-message">
              <h3>🎉 Amazing Job! 🎉</h3>
              <p>You've found all the artifacts in the Baguio Cultural Treasure Hunt!</p>
              <button type="button" class="btn btn-try-again" onclick="restartGame()">Try Again</button>
            </div>
          </div>
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
      { id: 'artifact1', name: 'Bango', info: "A traditional rain gear handwoven from rattan, nito fiber, and pine needles. Used for protection against the region’s unpredictable mountain rains, it embodies the resourcefulness of Benguet’s people.", img: "../assets/images/panagbenga/bangoo.png" },
      { id: 'artifact2', name: 'Latok', info: "A set of delicately crafted serving plates traditionally used during communal feasts. These items underscore the importance of hospitality and shared traditions in local society.", img: "../assets/images/panagbenga/latokk.png" },
      { id: 'artifact3', name: 'kayabang', info: "A headbasket intricately woven by Ibaloi women, used to carry goods through busy markets. Beyond its everyday function, the kayabang is a proud emblem of indigenous weaving artistry.", img: "../assets/images/panagbenga/kayabangg.png" },
      { id: 'artifact4', name: 'Woodcarving Totem Pole', info: "A monumental woodcarving displayed in and around the Baguio Museum. Carved by local artisans (like the celebrated Manong Ernesto), it represents a timeline of local history and the revered art of wood carving in Baguio.", img: "../assets/images/panagbenga/totem.png" },
      { id: 'artifact5', name: 'Barrel Man Figurine', info: "A quirky souvenir doll carved out of wood and partially hidden in a miniature barrel. Popular in local gift shops, it has evolved into a tongue-in-cheek icon of Baguio’s cultural dialogue between indigenous traditions and modern influences.", img: "../assets/images/panagbenga/barel.png" },
      { id: 'artifact6', name: 'Museum Mummy Artifact', info: "Housed in the Baguio Museum, this indigenous mummy is a rare remnant of ancient mummification practices found in the cool highlands of Benguet, reflecting both spiritual beliefs and sophisticated ancestral rituals.", img: "../assets/images/panagbenga/mommy.png" },
      { id: 'artifact7', name: 'Artifact 7', info: "An object passed down through generations.", img: "../assets/images/panagbenga/trivia.jpg" },
      { id: 'artifact8', name: 'Artifact 8', info: "Reflects the artistic heritage of local artisans.", img: "../assets/images/panagbenga/memory.jpg" },
      { id: 'artifact9', name: 'Artifact 9', info: "Used in traditional ceremonies and cultural events.", img: "../assets/images/panagbenga/memory.jpg" },
      { id: 'artifact10', name: 'Artifact 10', info: "Represents Baguio's evolving culture.", img: "../assets/images/panagbenga/trivia.jpg" }
    ];

    let artifacts = []; // This will hold the game objects with found status

    const gameContainer = document.getElementById('gameContainer');
    const objectListEl = document.getElementById('objectList');

    // Randomize a number between min and max.
    function getRandom(min, max) {
      return Math.floor(Math.random() * (max - min)) + min;
    }

    // Initialize or reset the game.
    function initGame() {
      // Clear container and reset try again button.
      gameContainer.innerHTML = '';
      const tryAgainBtn = document.getElementById('tryAgain');
      if (tryAgainBtn) {
        tryAgainBtn.style.display = 'none';
      }

      // Randomly choose a background.
      const bg = backgrounds[getRandom(0, backgrounds.length)];
      gameContainer.style.backgroundImage = `url('${bg}')`;

      // Reset artifacts array with fresh data.
      artifacts = artifactsData.map(a => ({ ...a, found: false }));

      // Create the list of objects to find.
      objectListEl.innerHTML = artifacts.map((artifact, index) => 
        `<li id="${artifact.id}" class="object-item">${artifact.name}</li>`
      ).join('');

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
        img.setAttribute('data-id', artifact.id);
        img.setAttribute('data-name', artifact.name);
        img.setAttribute('data-info', artifact.info);
        // Random positions: ensuring the image stays fully inside.
        const left = getRandom(0, containerWidth - objSize);
        const top = getRandom(0, containerHeight - objSize);
        img.style.left = left + 'px';
        img.style.top = top + 'px';

        // Add click event listener.
        img.addEventListener('click', function() {
          const art = artifacts.find(a => a.id === artifact.id);
          if (!art.found) {
            handleArtifactFound(art);
          }
        });

        // Append to game container.
        gameContainer.appendChild(img);
      });
    }

    // Handle when an artifact is found.
    function handleArtifactFound(artifact) {
      artifact.found = true;
      
      // Update the list item with found status and unique color
      const listItem = document.getElementById(artifact.id);
      const colorIndex = artifactsData.findIndex(a => a.id === artifact.id) + 1;
      listItem.classList.add('found', `found-color-${colorIndex}`);

      // Find and update the image element
      const imgElement = document.querySelector(`img[data-id="${artifact.id}"]`);
      if (imgElement) {
        imgElement.classList.add('found');
      }

      // Show artifact found modal
      showArtifactModal(artifact);

      // Check if all artifacts are found
      if (artifacts.every(a => a.found)) {
        setTimeout(() => {
          showCompletionModal();
        }, 1000);
      }
    }

    // Show Bootstrap modal with artifact info.
    function showArtifactModal(artifact) {
      document.getElementById('modalArtifactName').textContent = artifact.name;
      document.getElementById('modalArtifactInfo').textContent = artifact.info;
      document.getElementById('modalArtifactImage').src = artifact.img;
      const modal = new bootstrap.Modal(document.getElementById('infoModal'));
      modal.show();
    }

    // Show completion modal
    function showCompletionModal() {
      const modal = new bootstrap.Modal(document.getElementById('completionModal'));
      modal.show();
    }

    // Restart the game
    function restartGame() {
      // Hide completion modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('completionModal'));
      if (modal) {
        modal.hide();
      }
      // Initialize new game
      initGame();
    }

    // Start the game on page load.
    window.addEventListener('load', initGame);
  </script>
  
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
