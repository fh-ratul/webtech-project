<!DOCTYPE html>
<html>

<head>

    <title>View Answers</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h2>
            <?php echo $question['title']; ?>
        </h2>

        <p>
            <?php echo $question['body']; ?>
        </p>

        <p>
            Asked By:
            <?php echo $question['name']; ?>
        </p>

        <?php if ($question['is_resolved'] == 1) { ?>

            <p class="resolved">
                Resolved
            </p>

        <?php } else { ?>

            <p class="pending">
                Pending
            </p>

        <?php } ?>

        <?php
        if (
            $question['student_id'] == $studentId &&
            $question['is_resolved'] == 0
        ) {
        ?>

            <a
            class="btn"
            href="<?php echo APP_BASE_URL; ?>/student/view-answers?question_id=<?php echo $questionId; ?>&resolve=1"
            >
                Mark as Resolved
            </a>

        <?php } ?>

    </div>

    <div class="card">

        <h2>Answers</h2>

        <?php while ($answer = $answers->fetch_assoc()) { ?>

            <div class="answer-box">

                <p>
                    <?php echo $answer['body']; ?>
                </p>

                <p>
                    Answered By:
                    <?php echo $answer['name']; ?>
                    (
                    <?php echo $answer['role']; ?>
                    )
                </p>

                <p>
                    Date:
                    <?php echo $answer['created_at']; ?>
                </p>

                <?php if ($answer['is_endorsed'] == 1) { ?>

                    <p class="endorsed">
                        Instructor/TA Endorsed Answer
                    </p>

                <?php } ?>

            </div>

        <?php } ?>

    </div>

</div>

</body>

</html>
