<?php

include("../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'instructor') {
    die("Access Denied");
}

if (!isset($_GET['quiz_id'])) {
    die("Invalid Quiz ID");
}

$quizId = $_GET['quiz_id'];
$instructorId = $_SESSION['user_id'];

$quizQuery = "
SELECT
quizzes.title,
quizzes.pass_mark,
quizzes.total_marks,
quizzes.course_id,
courses.title AS course_title

FROM quizzes

JOIN courses
ON quizzes.course_id = courses.id

WHERE quizzes.id = ?
AND courses.instructor_id = ?
";

$stmtQuiz = $conn->prepare($quizQuery);
$stmtQuiz->bind_param("ii", $quizId, $instructorId);
$stmtQuiz->execute();
$quizResult = $stmtQuiz->get_result();

if ($quizResult->num_rows == 0) {
    die("Quiz Not Found");
}

$quiz = $quizResult->fetch_assoc();

$attemptQuery = "
SELECT
attempts.id,
attempts.score,
attempts.started_at,
attempts.completed_at,
users.name AS student_name,
TIMESTAMPDIFF(MINUTE, attempts.started_at, attempts.completed_at) AS duration_minutes

FROM attempts

JOIN users
ON attempts.student_id = users.id

WHERE attempts.quiz_id = ?
ORDER BY attempts.completed_at DESC
";

$stmt = $conn->prepare($attemptQuery);
$stmt->bind_param("i", $quizId);
$stmt->execute();
$attempts = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Attempts</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Quiz Attempts</h1>
            <p><?php echo htmlspecialchars($quiz['course_title']); ?> • <?php echo htmlspecialchars($quiz['title']); ?></p>
        </div>
        <div class="action-row">
            <a class="btn" href="manage_quizzes.php?course_id=<?php echo $quiz['course_id']; ?>">Back to Quizzes</a>
        </div>
    </div>

    <div class="card">
        <?php if ($attempts->num_rows == 0) { ?>
            <div class="empty-state">No student attempts yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Student</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Duration (min)</th>
                    <th>Completed At</th>
                </tr>

                <?php while($attempt = $attempts->fetch_assoc()) { ?>
                    <?php $passed = ($attempt['score'] >= $quiz['pass_mark']); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attempt['student_name']); ?></td>
                        <td><?php echo $attempt['score']; ?> / <?php echo $quiz['total_marks']; ?></td>
                        <td>
                            <?php if ($passed) { ?>
                                <span class="pass">PASS</span>
                            <?php } else { ?>
                                <span class="fail">FAIL</span>
                            <?php } ?>
                        </td>
                        <td><?php echo (int) $attempt['duration_minutes']; ?></td>
                        <td><?php echo htmlspecialchars($attempt['completed_at']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>

</html>
