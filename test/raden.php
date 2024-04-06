<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隨機問題</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        #question-container {
            font-size: 18px;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="question-container"></div>
    <button onclick="randomQuestion()">下一個問題</button>

    <script>
        function randomQuestion() {
            const questions = [
                "是不是要開團購",
                "要不要跟楊家寧在一起",
                "要不要太在意楊家寧會消失",
                "要不要錄音",
                "要不要做podcast",
                "要不要吃飯",
                "要不要跟NONO合作"
            ];

            const randomIndex = Math.floor(Math.random() * questions.length);
            const randomDate = getRandomDate();
            const questionContainer = document.getElementById('question-container');
            questionContainer.innerHTML = `<p>${randomDate}，${questions[randomIndex]}</p>`;
        }

        function getRandomDate() {
            const today = new Date();
            const randomDays = Math.floor(Math.random() * 3);
            const futureDate = new Date(today);
            futureDate.setDate(today.getDate() + randomDays);

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return futureDate.toLocaleDateString('zh-TW', options);
        }
    </script>
</body>
</html>
