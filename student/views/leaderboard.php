<!DOCTYPE html>
<html>

<head>

    <title>Quiz Leaderboard</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h1>
            Leaderboard
        </h1>

        <h2>
            <?php echo $quiz['title']; ?>
        </h2>

        <table>

            <tr>

                <th>Rank</th>

                <th>Student</th>

                <th>Highest Score</th>

                <th>Total Marks</th>

            </tr>

            <?php
            $rank = 1;
            while ($leader = $leaders->fetch_assoc()) {
                $class = "";

                if ($rank == 1) {
                    $class = "rank1";
                } elseif ($rank == 2) {
                    $class = "rank2";
                } elseif ($rank == 3) {
                    $class = "rank3";
                }
            ?>

                <tr class="<?php echo $class; ?>">

                    <td>
                        <?php echo $rank; ?>
                    </td>

                    <td>
                        <?php echo $leader['name']; ?>
                    </td>

                    <td>
                        <?php echo $leader['highest_score']; ?>
                    </td>

                    <td>
                        <?php echo $quiz['total_marks']; ?>
                    </td>

                </tr>

            <?php
                $rank++;
            } ?>

        </table>

    </div>

</div>

</body>

</html>
