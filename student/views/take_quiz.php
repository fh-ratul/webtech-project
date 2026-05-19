<!DOCTYPE html>
<html>

<head>

    <title>Take Quiz</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/quiz.css">

</head>

<body class="student-page quiz-page">

<div class="container">

    <div class="card">

        <h1>
            <?php echo $quiz['title']; ?>
        </h1>

        <p>
            <?php echo $quiz['description']; ?>
        </p>

        <p>
            Total Marks:
            <?php echo $quiz['total_marks']; ?>
        </p>

        <p>
            Pass Mark:
            <?php echo $quiz['pass_mark']; ?>
        </p>

        <div class="timer">
            Time Left:
            <span id="time"></span>
        </div>

    </div>

    <form method="POST" id="quizForm">

        <?php
        $questionNumber = 1;

        foreach ($questions as $question) {
        ?>

            <div class="card">

                <h3>

                    Question
                    <?php echo $questionNumber; ?>:

                    <?php echo $question['question_text']; ?>

                </h3>

                <p>
                    Marks:
                    <?php echo $question['marks']; ?>
                </p>

                <?php foreach ($question['options'] as $option) { ?>

                    <label class="option-item">
                        <input
                        type="radio"
                        name="answer[<?php echo $question['id']; ?>]"
                        value="<?php echo $option['id']; ?>"
                        >
                        <span><?php echo $option['option_text']; ?></span>
                    </label>

                <?php } ?>

            </div>

        <?php
            $questionNumber++;
        }
        ?>

        <button type="submit">
            Submit Quiz
        </button>

    </form>

</div>

<script>

let timeLeft =
<?php echo $quiz['time_limit_minutes'] * 60; ?>;

function updateTimer() {

    let minutes =
    Math.floor(timeLeft / 60);

    let seconds =
    timeLeft % 60;

    document.getElementById("time").innerHTML =
    minutes + ":" + seconds;

    if (timeLeft <= 0) {

        document.getElementById("quizForm").submit();
    }

    timeLeft--;
}

setInterval(updateTimer, 1000);

updateTimer();

</script>

</body>

</html>
