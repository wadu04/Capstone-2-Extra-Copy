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
    <style>
    
        .language-selection {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }
        .language-btn {
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .language-btn:hover {
            background-color: #45a049;
        }
        .quiz-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: none;
        }
        .option-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .option-btn:hover {
            background-color: #e9ecef;
        }
        .feedback {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .correct {
            background-color: #d4edda;
            color: #155724;
        }
        .incorrect {
            background-color: #f8d7da;
            color: #721c24;
        }
        .next-btn {
            display: none;
            margin: 10px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .next-btn:hover {
            background-color: #0056b3;
        }
        .progress {
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
    <h1 style="text-align: center;">Learn Baguio Native Languages</h1>
    <div class="language-selection">
        <button class="language-btn" onclick="startQuiz('ilocano')">Ilocano</button>
        <button class="language-btn" onclick="startQuiz('ibaloy')">Ibaloy</button>
        <button class="language-btn" onclick="startQuiz('kankanaey')">Kankanaey</button>
    </div>
    <div id="quiz-container" class="quiz-container">
        <div class="progress">Question <span id="current-question">1</span>/20</div>
        <div id="question"></div>
        <div id="options"></div>
        <div id="feedback" class="feedback"></div>
        <button id="next-btn" class="next-btn" onclick="nextQuestion()">Next Question</button>
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
        document.getElementById('quiz-container').style.display = 'block';
        showQuestion();
    }

    function showQuestion() {
        const questionData = shuffledQuestions[currentQuestionIndex];
        document.getElementById('current-question').textContent = currentQuestionIndex + 1;
        document.getElementById('question').textContent = questionData.question;
        
        const optionsContainer = document.getElementById('options');
        optionsContainer.innerHTML = '';
        
        questionData.options.forEach(option => {
            const button = document.createElement('button');
            button.className = 'option-btn';
            button.textContent = option;
            button.onclick = () => checkAnswer(option);
            optionsContainer.appendChild(button);
        });

        document.getElementById('feedback').textContent = '';
        document.getElementById('feedback').className = 'feedback';
        document.getElementById('next-btn').style.display = 'none';
    }

    function checkAnswer(selectedOption) {
        const questionData = shuffledQuestions[currentQuestionIndex];
        const feedback = document.getElementById('feedback');
        const options = document.querySelectorAll('.option-btn');
        
        options.forEach(button => {
            button.disabled = true;
            if (button.textContent === questionData.correct) {
                button.style.backgroundColor = '#d4edda';
            }
        });

        if (selectedOption === questionData.correct) {
            feedback.textContent = 'Correct!';
            feedback.className = 'feedback correct';
        } else {
            feedback.textContent = 'Incorrect. The correct answer is: ' + questionData.correct;
            feedback.className = 'feedback incorrect';
        }

        document.getElementById('next-btn').style.display = 'block';
    }

    function nextQuestion() {
        currentQuestionIndex++;
        if (currentQuestionIndex < shuffledQuestions.length) {
            showQuestion();
        } else {
            document.getElementById('quiz-container').innerHTML = `
                <h2>Quiz Completed!</h2>
                <button class="language-btn" onclick="location.reload()">Try Another Language</button>
            `;
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>