<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learn Baguio Languages</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <style>
 
    .page-title {
      color: #2C3E50;
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: 600;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    /* Make language buttons smaller and simpler */
    .language-selection {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 15px;
      margin: 20px 0;
    }
    .language-btn {
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      background: linear-gradient(145deg, #4CAF50, #45a049);
      color: white;
      border: none;
      border-radius: 8px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      min-width: 120px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .language-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
      background: linear-gradient(145deg, #45a049, #4CAF50);
    }
    .language-btn:active {
      transform: translateY(1px);
    }
    /* Remove card-style container (no extra shadow or border-radius) */
    .quiz-container {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 40px;
      /* Removed border-radius and box-shadow */
      display: none;
      animation: slideIn 0.5s ease;
    }
    .progress {
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 20px auto;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 50px;
      font-size: 18px;
      color: #2C3E50;
      font-weight: 600;
      box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
      max-width: 300px;
    }
    .progress-bar {
      height: 10px;
      background: #e9ecef;
      border-radius: 5px;
      margin-top: 10px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #4CAF50, #45a049);
      transition: width 0.3s ease;
    }
    #question {
      font-size: 28px;
      margin: 30px 0;
      color: #2C3E50;
      text-align: center;
      line-height: 1.5;
      font-weight: 500;
      padding: 0 20px;
    }
    .options-container {
      display: grid;
      gap: 15px;
      margin: 25px 0;
    }
    .option-btn {
      display: block;
      width: 100%;
      padding: 20px;
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 18px;
      text-align: left;
      position: relative;
      overflow: hidden;
    }
    .option-btn:hover {
      background: #e9ecef;
      transform: translateX(5px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .option-btn.correct {
      background: #d4edda;
      border-color: #c3e6cb;
      color: #155724;
      pointer-events: none;
    }
    .option-btn.incorrect {
      background: #f8d7da;
      border-color: #f5c6cb;
      color: #721c24;
      pointer-events: none;
    }
    .feedback {
      margin: 25px 0;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      font-size: 20px;
      animation: fadeInUp 0.5s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .feedback.correct {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .feedback.incorrect {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .next-btn {
      display: none;
      margin: 30px auto;
      padding: 15px 35px;
      background: linear-gradient(145deg, #007bff, #0056b3);
      color: white;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      font-size: 18px;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,123,255,0.3);
    }
    .next-btn:hover {
      background: linear-gradient(145deg, #0056b3, #007bff);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,123,255,0.4);
    }
    .quiz-complete {
      text-align: center;
      animation: fadeIn 0.8s ease;
      padding: 40px 20px;
    }
    .quiz-complete h2 {
      color: #28a745;
      margin-bottom: 25px;
      font-size: 36px;
      font-weight: 600;
    }
    .quiz-complete p {
      color: #2C3E50;
      font-size: 20px;
      margin-bottom: 30px;
    }
    /* Place the try again button at the top */
    .quiz-complete .try-again-btn {
      margin-bottom: 30px;
      font-size: 20px;
    }
    @keyframes slideIn {
      from { transform: translateY(-20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes fadeInUp {
      from { 
        transform: translateY(10px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    .score-display {
      font-size: 24px;
      color: #2C3E50;
      margin: 20px 0;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .language-icon {
      font-size: 24px;
      margin-right: 10px;
    }
    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }
      .page-title {
        font-size: 2rem;
      }
      .language-btn {
        padding: 10px 15px;
        font-size: 14px;
        min-width: 100px;
      }
      .quiz-container {
        padding: 25px;
      }
      #question {
        font-size: 22px;
      }
      .option-btn {
        padding: 15px;
        font-size: 16px;
      }
    }

    /* Modal styling */
    .modal-dialog {
      display: flex;
      align-items: center;
      min-height: calc(100% - 1rem);
    }
    .modal-content {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      border: none;
    }
    .modal-header {
      background: linear-gradient(145deg, #4CAF50, #45a049);
      color: white;
      border-radius: 15px 15px 0 0;
      padding: 1.5rem;
      border-bottom: none;
    }
    .modal-title {
      font-weight: 600;
      font-size: 1.5rem;
    }
    .modal-body {
      padding: 2rem;
    }
    .pronunciation-text {
      font-size: 1.25rem;
      color: #2C3E50;
      margin-bottom: 1rem;
      font-weight: 500;
    }
    .modal-footer {
      border-top: none;
      padding: 1.5rem;
    }
    .btn-close {
      color: white;
      opacity: 1;
    }
  </style>
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <div class="container">
    <h1 class="page-title">Learn Baguio Native Languages</h1>
    <!-- Language Selection Buttons -->
    <div id="language-selection" class="language-selection animate__animated animate__fadeIn">
      <button class="language-btn" onclick="startQuiz('ilocano')">
        <span class="language-icon"></span>Ilocano
      </button>
      <button class="language-btn" onclick="startQuiz('ibaloy')">
        <span class="language-icon"></span>Ibaloy
      </button>
      <button class="language-btn" onclick="startQuiz('kankanaey')">
        <span class="language-icon"></span>Kankanaey
      </button>
    </div>
    <!-- Quiz Container -->
    <div id="quiz-container" class="quiz-container">
      <div class="progress">
        Question <span id="current-question">1</span>/20
        <div class="progress-bar">
          <div class="progress-fill" style="width: 5%"></div>
        </div>
      </div>
      <div id="question"></div>
      <div id="options" class="options-container"></div>
      <div id="feedback" class="feedback"></div>
      <button id="next-btn" class="next-btn" onclick="nextQuestion()">Next Question →</button>
    </div>
  </div>

  <!-- Pronunciation Modal (Bootstrap) -->
  <div class="modal fade" id="pronunciationModal" tabindex="-1" aria-labelledby="pronunciationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pronunciationModalLabel">Pronunciation Guide</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="pronunciationInfo">
            <p class="pronunciation-text">Pronunciation: <span></span></p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    <?php
    $questions = [
      'ilocano' => [
        ['question' => 'What is "Hello" in Ilocano?', 'options' => ['Kumusta', 'Naimbag', 'Adda', 'Wen'], 'correct' => 'Naimbag'],
        ['question' => 'How do you say "Thank you" in Ilocano?', 'options' => ['Agyamanak', 'Salamat', 'Dios ti agngina', 'Naimbag'], 'correct' => 'Agyamanak'],
        ['question' => 'What is "Good morning" in Ilocano?', 'options' => ['Naimbag a bigat', 'Naimbag nga aldaw', 'Naimbag a rabii', 'Kumusta'], 'correct' => 'Naimbag a bigat'],
        ['question' => 'How do you say "Yes" in Ilocano?', 'options' => ['Wen', 'Saan', 'Agyamanak', 'Kumusta'], 'correct' => 'Wen'],
        ['question' => 'What is "Water" in Ilocano?', 'options' => ['Danum', 'Tawen', 'Balay', 'Aso'], 'correct' => 'Danum'],
        ['question' => 'How do you say "Beautiful" in Ilocano?', 'options' => ['Napintas', 'Nalaing', 'Dakkel', 'Bassit'], 'correct' => 'Napintas'],
        ['question' => 'What is "House" in Ilocano?', 'options' => ['Balay', 'Danum', 'Tawen', 'Aso'], 'correct' => 'Balay'],
        ['question' => 'How do you say "No" in Ilocano?', 'options' => ['Saan', 'Wen', 'Agyamanak', 'Kumusta'], 'correct' => 'Saan'],
        ['question' => 'What is "Food" in Ilocano?', 'options' => ['Makan', 'Danum', 'Balay', 'Aso'], 'correct' => 'Makan'],
        ['question' => 'How do you say "Good night" in Ilocano?', 'options' => ['Naimbag a rabii', 'Naimbag a bigat', 'Naimbag nga aldaw', 'Kumusta'], 'correct' => 'Naimbag a rabii'],
        ['question' => 'What is "Friend" in Ilocano?', 'options' => ['Gayyem', 'Kabsat', 'Ina', 'Ama'], 'correct' => 'Gayyem'],
        ['question' => 'How do you say "I love you" in Ilocano?', 'options' => ['Ay-ayaten ka', 'Kumusta', 'Agyamanak', 'Naimbag'], 'correct' => 'Ay-ayaten ka'],
        ['question' => 'What is "Child" in Ilocano?', 'options' => ['Ubing', 'Lakay', 'Baket', 'Gayyem'], 'correct' => 'Ubing'],
        ['question' => 'How do you say "Delicious" in Ilocano?', 'options' => ['Naimas', 'Napintas', 'Nalaing', 'Dakkel'], 'correct' => 'Naimas'],
        ['question' => 'What is "Mother" in Ilocano?', 'options' => ['Ina', 'Ama', 'Kabsat', 'Gayyem'], 'correct' => 'Ina'],
        ['question' => 'How do you say "Father" in Ilocano?', 'options' => ['Ama', 'Ina', 'Kabsat', 'Gayyem'], 'correct' => 'Ama'],
        ['question' => 'What is "Brother/Sister" in Ilocano?', 'options' => ['Kabsat', 'Gayyem', 'Ina', 'Ama'], 'correct' => 'Kabsat'],
        ['question' => 'How do you say "Big" in Ilocano?', 'options' => ['Dakkel', 'Bassit', 'Napintas', 'Nalaing'], 'correct' => 'Dakkel'],
        ['question' => 'What is "Small" in Ilocano?', 'options' => ['Bassit', 'Dakkel', 'Napintas', 'Nalaing'], 'correct' => 'Bassit'],
        ['question' => 'How do you say "Good afternoon" in Ilocano?', 'options' => ['Naimbag a malem', 'Naimbag a bigat', 'Naimbag a rabii', 'Kumusta'], 'correct' => 'Naimbag a malem']
      ],
      'ibaloy' => [
        ['question' => 'What is "Hello" in Ibaloy?', 'options' => ['Mebedin', 'Salamat', 'Kareedjaw', 'Mapteng'], 'correct' => 'Kareedjaw'],
        ['question' => 'How do you say "Thank you" in Ibaloy?', 'options' => ['Salamat', 'Kareedjaw', 'Mapteng', 'Mebedin'], 'correct' => 'Salamat'],
        ['question' => 'What is "Good morning" in Ibaloy?', 'options' => ['Mapteng nga agsapa', 'Kareedjaw', 'Salamat', 'Mebedin'], 'correct' => 'Mapteng nga agsapa'],
        ['question' => 'How do you say "Yes" in Ibaloy?', 'options' => ['Owen', 'Enshi', 'Salamat', 'Kareedjaw'], 'correct' => 'Owen'],
        ['question' => 'What is "Water" in Ibaloy?', 'options' => ['Shanom', 'Baley', 'Makan', 'Aso'], 'correct' => 'Shanom'],
        ['question' => 'How do you say "Beautiful" in Ibaloy?', 'options' => ['Mapteng', 'Makedsel', 'Olay', 'Agpayso'], 'correct' => 'Mapteng'],
        ['question' => 'What is "House" in Ibaloy?', 'options' => ['Baley', 'Shanom', 'Makan', 'Aso'], 'correct' => 'Baley'],
        ['question' => 'How do you say "No" in Ibaloy?', 'options' => ['Enshi', 'Owen', 'Salamat', 'Kareedjaw'], 'correct' => 'Enshi'],
        ['question' => 'What is "Food" in Ibaloy?', 'options' => ['Makan', 'Shanom', 'Baley', 'Aso'], 'correct' => 'Makan'],
        ['question' => 'How do you say "Good night" in Ibaloy?', 'options' => ['Mapteng nga dabi', 'Mapteng nga agsapa', 'Kareedjaw', 'Salamat'], 'correct' => 'Mapteng nga dabi'],
        ['question' => 'What is "Friend" in Ibaloy?', 'options' => ['Kajem', 'Agi', 'Ina', 'Ama'], 'correct' => 'Kajem'],
        ['question' => 'How do you say "I love you" in Ibaloy?', 'options' => ['Piyan taka', 'Kareedjaw', 'Salamat', 'Mapteng'], 'correct' => 'Piyan taka'],
        ['question' => 'What is "Child" in Ibaloy?', 'options' => ['Nga nga', 'Lakay', 'Baket', 'Kajem'], 'correct' => 'Nga nga'],
        ['question' => 'How do you say "Delicious" in Ibaloy?', 'options' => ['Mapteng', 'Makedsel', 'Olay', 'Agpayso'], 'correct' => 'Mapteng'],
        ['question' => 'What is "Mother" in Ibaloy?', 'options' => ['Ina', 'Ama', 'Agi', 'Kajem'], 'correct' => 'Ina'],
        ['question' => 'How do you say "Father" in Ibaloy?', 'options' => ['Ama', 'Ina', 'Agi', 'Kajem'], 'correct' => 'Ama'],
        ['question' => 'What is "Brother/Sister" in Ibaloy?', 'options' => ['Agi', 'Kajem', 'Ina', 'Ama'], 'correct' => 'Agi'],
        ['question' => 'How do you say "Big" in Ibaloy?', 'options' => ['Makedsel', 'Olay', 'Mapteng', 'Agpayso'], 'correct' => 'Makedsel'],
        ['question' => 'What is "Small" in Ibaloy?', 'options' => ['Olay', 'Makedsel', 'Mapteng', 'Agpayso'], 'correct' => 'Olay'],
        ['question' => 'How do you say "Truth" in Ibaloy?', 'options' => ['Agpayso', 'Mapteng', 'Makedsel', 'Olay'], 'correct' => 'Agpayso']
      ],
      'kankanaey' => [
        ['question' => 'What is "Hello" in Kankanaey?', 'options' => ['Matago-tago', 'Iyaman', 'Gawis', 'Wen'], 'correct' => 'Matago-tago'],
        ['question' => 'How do you say "Thank you" in Kankanaey?', 'options' => ['Iyaman', 'Matago-tago', 'Gawis', 'Wen'], 'correct' => 'Iyaman'],
        ['question' => 'What is "Good morning" in Kankanaey?', 'options' => ['Gawis ay agsapa', 'Matago-tago', 'Iyaman', 'Wen'], 'correct' => 'Gawis ay agsapa'],
        ['question' => 'How do you say "Yes" in Kankanaey?', 'options' => ['Wen', 'Adi', 'Iyaman', 'Matago-tago'], 'correct' => 'Wen'],
        ['question' => 'What is "Water" in Kankanaey?', 'options' => ['Danom', 'Baey', 'Makan', 'Aso'], 'correct' => 'Danom'],
        ['question' => 'How do you say "Beautiful" in Kankanaey?', 'options' => ['Gawis', 'Dakdake', 'Bassit', 'Tet-ewa'], 'correct' => 'Gawis'],
        ['question' => 'What is "House" in Kankanaey?', 'options' => ['Baey', 'Danom', 'Makan', 'Aso'], 'correct' => 'Baey'],
        ['question' => 'How do you say "No" in Kankanaey?', 'options' => ['Adi', 'Wen', 'Iyaman', 'Matago-tago'], 'correct' => 'Adi'],
        ['question' => 'What is "Food" in Kankanaey?', 'options' => ['Makan', 'Danom', 'Baey', 'Aso'], 'correct' => 'Makan'],
        ['question' => 'How do you say "Good night" in Kankanaey?', 'options' => ['Gawis ay labi', 'Gawis ay agsapa', 'Matago-tago', 'Iyaman'], 'correct' => 'Gawis ay labi'],
        ['question' => 'What is "Friend" in Kankanaey?', 'options' => ['Gayyem', 'Agi', 'Ina', 'Ama'], 'correct' => 'Gayyem'],
        ['question' => 'How do you say "I love you" in Kankanaey?', 'options' => ['Laylaydek sik-a', 'Matago-tago', 'Iyaman', 'Gawis'], 'correct' => 'Laylaydek sik-a'],
        ['question' => 'What is "Child" in Kankanaey?', 'options' => ['Anak', 'Lakay', 'Baket', 'Gayyem'], 'correct' => 'Anak'],
        ['question' => 'How do you say "Delicious" in Kankanaey?', 'options' => ['Mam-is', 'Gawis', 'Dakdake', 'Bassit'], 'correct' => 'Mam-is'],
        ['question' => 'What is "Mother" in Kankanaey?', 'options' => ['Ina', 'Ama', 'Agi', 'Gayyem'], 'correct' => 'Ina'],
        ['question' => 'How do you say "Father" in Kankanaey?', 'options' => ['Ama', 'Ina', 'Agi', 'Gayyem'], 'correct' => 'Ama'],
        ['question' => 'What is "Brother/Sister" in Kankanaey?', 'options' => ['Agi', 'Gayyem', 'Ina', 'Ama'], 'correct' => 'Agi'],
        ['question' => 'How do you say "Big" in Kankanaey?', 'options' => ['Dakdake', 'Bassit', 'Gawis', 'Tet-ewa'], 'correct' => 'Dakdake'],
        ['question' => 'What is "Small" in Kankanaey?', 'options' => ['Bassit', 'Dakdake', 'Gawis', 'Tet-ewa'], 'correct' => 'Bassit'],
        ['question' => 'How do you say "Truth" in Kankanaey?', 'options' => ['Tet-ewa', 'Gawis', 'Dakdake', 'Bassit'], 'correct' => 'Tet-ewa']
      ]
    ];
    echo 'const questions = ' . json_encode($questions) . ';';
    ?>

    // Placeholder details for pronunciation and origin.
    // Ideally, you would fill these in with accurate information.
    const wordDetails = {
      ilocano: {
        "Naimbag": { pronunciation: "nai-im-bag", origin: "Ilocano for 'good'" },
        "Agyamanak": { pronunciation: "ag-ya-ma-nak", origin: "Ilocano for 'thank you'" },
        "Wen": { pronunciation: "when", origin: "Ilocano for 'yes'" },
        "Danum": { pronunciation: "da-num", origin: "Ilocano for 'water'" },
        "Napintas": { pronunciation: "na-pin-tas", origin: "Ilocano for 'beautiful'" },
        "Balay": { pronunciation: "ba-lay", origin: "Ilocano for 'house'" },
        "Saan": { pronunciation: "saan", origin: "Ilocano for 'no'" },
        "Makan": { pronunciation: "ma-kan", origin: "Ilocano for 'food'" },
        "Naimbag a rabii": { pronunciation: "nai-im-bag a ra-bi-i", origin: "Ilocano for 'good night'" },
        "Gayyem": { pronunciation: "gay-yem", origin: "Ilocano for 'friend'" },
        "Ay-ayaten ka": { pronunciation: "ay-ay-a-ten ka", origin: "Ilocano for 'I love you'" },
        "Ubing": { pronunciation: "u-bing", origin: "Ilocano for 'child'" },
        "Naimas": { pronunciation: "nai-mas", origin: "Ilocano for 'delicious'" },
        "Ina": { pronunciation: "i-na", origin: "Ilocano for 'mother'" },
        "Ama": { pronunciation: "a-ma", origin: "Ilocano for 'father'" },
        "Kabsat": { pronunciation: "kab-sat", origin: "Ilocano for 'sibling'" },
        "Dakkel": { pronunciation: "dak-kel", origin: "Ilocano for 'big'" },
        "Bassit": { pronunciation: "bas-sit", origin: "Ilocano for 'small'" },
        "Naimbag a malem": { pronunciation: "nai-im-bag a ma-lem", origin: "Ilocano for 'good afternoon'" }
      },
      ibaloy: {
        "Kareedjaw": { pronunciation: "ka-reed-jaw", origin: "Ibaloy for 'hello'" },
        "Salamat": { pronunciation: "sa-la-mat", origin: "Ibaloy for 'thank you'" },
        "Mapteng nga agsapa": { pronunciation: "map-teng nga ag-sa-pa", origin: "Ibaloy for 'good morning'" },
        "Owen": { pronunciation: "o-wen", origin: "Ibaloy for 'yes'" },
        "Shanom": { pronunciation: "sha-nom", origin: "Ibaloy for 'water'" },
        // ... additional details for Ibaloy words
      },
      kankanaey: {
        "Matago-tago": { pronunciation: "ma-ta-go ta-go", origin: "Kankanaey for 'hello'" },
        "Iyaman": { pronunciation: "i-ya-man", origin: "Kankanaey for 'thank you'" },
        "Gawis ay agsapa": { pronunciation: "ga-wis ay ag-sa-pa", origin: "Kankanaey for 'good morning'" },
        "Wen": { pronunciation: "when", origin: "Kankanaey for 'yes'" },
        "Danom": { pronunciation: "da-nom", origin: "Kankanaey for 'water'" },
        // ... additional details for Kankanaey words
      }
    };

    let currentLanguage = '';
    let currentQuestionIndex = 0;
    let shuffledQuestions = [];

    function shuffleArray(array) {
      for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
      }
      return array;
    }

    function startQuiz(language) {
      currentLanguage = language;
      currentQuestionIndex = 0;
      shuffledQuestions = shuffleArray([...questions[language]]);
      // Hide language selection buttons once a dialect is chosen.
      document.getElementById('language-selection').style.display = 'none';
      document.getElementById('quiz-container').style.display = 'block';
      document.getElementById('quiz-container').classList.add('animate__animated', 'animate__fadeIn');
      showQuestion();
    }

    function updateProgress() {
      const progress = ((currentQuestionIndex + 1) / shuffledQuestions.length) * 100;
      document.querySelector('.progress-fill').style.width = `${progress}%`;
    }

    function showQuestion() {
      const questionData = shuffledQuestions[currentQuestionIndex];
      document.getElementById('current-question').textContent = currentQuestionIndex + 1;
      document.getElementById('question').textContent = questionData.question;
      updateProgress();

      const optionsContainer = document.getElementById('options');
      optionsContainer.innerHTML = '';

      questionData.options.forEach(option => {
        const button = document.createElement('button');
        button.className = 'option-btn animate__animated animate__fadeIn';
        button.textContent = option;
        button.onclick = () => checkAnswer(option);
        optionsContainer.appendChild(button);
      });

      document.getElementById('feedback').textContent = '';
      document.getElementById('feedback').className = 'feedback';
      document.getElementById('next-btn').style.display = 'none';
    }

    function showPronunciationModal(word) {
      // Look up word details; if not found, use default message.
      const details = (wordDetails[currentLanguage] && wordDetails[currentLanguage][word]) || { pronunciation: "N/A" };

      document.querySelector('#pronunciationInfo span').textContent = details.pronunciation;
      
      // Show the modal
      const modal = new bootstrap.Modal(document.getElementById('pronunciationModal'));
      modal.show();
    }

    function checkAnswer(selectedOption) {
      const questionData = shuffledQuestions[currentQuestionIndex];
      const feedback = document.getElementById('feedback');
      const options = document.querySelectorAll('.option-btn');

      options.forEach(button => {
        button.disabled = true;
        if (button.textContent === questionData.correct) {
          button.classList.add('correct', 'animate__animated', 'animate__pulse');
        } else if (button.textContent === selectedOption && selectedOption !== questionData.correct) {
          button.classList.add('incorrect', 'animate__animated', 'animate__shakeX');
        }
      });

      if (selectedOption === questionData.correct) {
        feedback.innerHTML = '<span style="font-size: 24px">🎉</span> Correct!';
        feedback.className = 'feedback correct animate__animated animate__bounceIn';
      } else {
        feedback.innerHTML = `<span style="font-size: 24px">💡</span> The correct answer is: ${questionData.correct}`;
        feedback.className = 'feedback incorrect animate__animated animate__bounceIn';
      }

      // Show the pronunciation/origin modal for the correct answer word.
      showPronunciationModal(questionData.correct);

      document.getElementById('next-btn').style.display = 'block';
      document.getElementById('next-btn').className = 'next-btn animate__animated animate__fadeIn';
    }

    function nextQuestion() {
      currentQuestionIndex++;
      if (currentQuestionIndex < shuffledQuestions.length) {
        showQuestion();
      } else {
        // When quiz is complete, show a completion message with try again button at the top.
        document.getElementById('quiz-container').innerHTML = `
          <div class="quiz-complete">
            <button class="language-btn try-again-btn" onclick="location.reload()">
               Try Another Language
            </button>
            <h2>🎊 Quiz Completed! 🎊</h2>
            <p>Congratulations on completing the ${currentLanguage.charAt(0).toUpperCase() + currentLanguage.slice(1)} language quiz!</p>
            <div class="score-display">
              <p>You've learned ${shuffledQuestions.length} new words!</p>
            </div>
          </div>
        `;
      }
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
