<!DOCTYPE html>
<html>

<head>

    <title>Quiz Result</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/quiz.css">

</head>

<body class="student-page quiz-page">

<div class="container">

    <div class="card">

        <h1>
            <?php echo $data['title']; ?>
        </h1>

        <h2>
            Score:
            <?php echo $data['score']; ?>
            /
            <?php echo $data['total_marks']; ?>
        </h2>

        <p>
            Pass Mark:
            <?php echo $data['pass_mark']; ?>
        </p>

        <p>
            Completed At:
            <?php echo $data['completed_at']; ?>
        </p>

        <?php if ($passed) { ?>

            <h2 class="pass">
                PASS
            </h2>

        <?php } else { ?>

            <h2 class="fail">
                FAIL
            </h2>

        <?php } ?>

    </div>

    <div class="card">

        <h2>Question Breakdown</h2>

        <?php
        $questionNumber = 1;

        while ($row = $breakdowns->fetch_assoc()) {
        ?>

            <div>

                <h3>
                    Question
                    <?php echo $questionNumber; ?>
                </h3>

                <p>
                    <?php echo $row['question_text']; ?>
                </p>

                <p>
                    Your Answer:
                    <?php echo $row['option_text']; ?>
                </p>

                <?php if ($row['is_correct'] == 1) { ?>

                    <p class="correct">
                        Correct Answer
                    </p>

                <?php } else { ?>

                    <p class="wrong">
                        Wrong Answer
                    </p>

                <?php } ?>

                <p>
                    Marks:
                    <?php echo $row['marks']; ?>
                </p>

                <hr>

            </div>

        <?php
            $questionNumber++;
        }
        ?>

    </div>

    <a
    class="btn"
    href="<?php echo APP_BASE_URL; ?>/student/dashboard"
    >
        Back To Dashboard
    </a>

</div>

</body>

</html>
