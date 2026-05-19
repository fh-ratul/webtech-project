<!DOCTYPE html>
<html>

<head>

    <title>Attempt History</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h1>Quiz Attempt History</h1>

        <table>

            <tr>

                <th>#</th>

                <th>Course</th>

                <th>Quiz</th>

                <th>Type</th>

                <th>Score</th>

                <th>Status</th>

                <th>Completed At</th>

                <th>Action</th>

            </tr>

            <?php
            $serial = 1;

            while ($attempt = $attempts->fetch_assoc()) {
                $passed = $attempt['score'] >= $attempt['pass_mark'];
            ?>

                <tr>

                    <td>
                        <?php echo $serial; ?>
                    </td>

                    <td>
                        <?php echo $attempt['course_title']; ?>
                    </td>

                    <td>
                        <?php echo $attempt['title']; ?>
                    </td>

                    <td>
                        <?php echo $attempt['quiz_type']; ?>
                    </td>

                    <td>
                        <?php echo $attempt['score']; ?>
                        /
                        <?php echo $attempt['total_marks']; ?>
                    </td>

                    <td>
                        <?php if ($passed) { ?>
                            <span class="pass">
                                PASS
                            </span>
                        <?php } else { ?>
                            <span class="fail">
                                FAIL
                            </span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php echo $attempt['completed_at']; ?>
                    </td>

                    <td>
                        <a
                        class="btn"
                        href="<?php echo APP_BASE_URL; ?>/student/quiz-result?attempt_id=<?php echo $attempt['id']; ?>"
                        >
                            View Result
                        </a>
                    </td>

                </tr>

            <?php
                $serial++;
            } ?>

        </table>

    </div>

</div>

</body>

</html>
