<!DOCTYPE html>
<html>

<head>

    <title>Performance Dashboard</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h1>Performance Dashboard</h1>

        <h2>
            Average Score:
            <span class="score">
                <?php echo round($averageResult['average_score'], 2); ?>
            </span>
        </h2>

        <h2>
            Pass Rate:
            <span class="score">
                <?php echo round($passRate, 2); ?>%
            </span>
        </h2>

    </div>

    <div class="card">

        <h2>Average Score Per Subject</h2>

        <table>

            <tr>

                <th>Subject</th>

                <th>Average Score</th>

            </tr>

            <?php while ($subject = $subjects->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo $subject['subject_name']; ?>
                    </td>

                    <td>
                        <?php echo round($subject['average_score'], 2); ?>
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

    <div class="card">

        <h2>Comparison With Class Average</h2>

        <table>

            <tr>

                <th>Quiz</th>

                <th>Your Score</th>

                <th>Class Average</th>

            </tr>

            <?php while ($comparison = $classComparisons->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo $comparison['title']; ?>
                    </td>

                    <td>
                        <?php echo round($comparison['student_score'], 2); ?>
                    </td>

                    <td>
                        <?php echo round($comparison['class_average'], 2); ?>
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>
