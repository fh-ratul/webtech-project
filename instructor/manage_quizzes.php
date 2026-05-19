<?php

include("../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'instructor') {
    die("Access Denied");
}

if (!isset($_GET['course_id'])) {
    die("Invalid Course ID");
}

$courseId = $_GET['course_id'];
$instructorId = $_SESSION['user_id'];

$courseQuery = "
SELECT id, title
FROM courses
WHERE id = ?
AND instructor_id = ?
";

$stmtCourse = $conn->prepare($courseQuery);
$stmtCourse->bind_param("ii", $courseId, $instructorId);
$stmtCourse->execute();
$courseResult = $stmtCourse->get_result();

if ($courseResult->num_rows == 0) {
    die("Course Not Found");
}

$course = $courseResult->fetch_assoc();

$success = "";
$error = "";

if (isset($_GET['action'], $_GET['quiz_id'])) {

    $quizId = (int) $_GET['quiz_id'];
    $action = $_GET['action'];

    if ($action == 'publish' || $action == 'unpublish') {

        $newStatus = ($action == 'publish') ? 'published' : 'draft';

        $updateQuery = "
        UPDATE quizzes
        JOIN courses
        ON quizzes.course_id = courses.id
        SET quizzes.status = ?
        WHERE quizzes.id = ?
        AND courses.id = ?
        AND courses.instructor_id = ?
        ";

        $stmtUpdate = $conn->prepare($updateQuery);
        $stmtUpdate->bind_param("siii", $newStatus, $quizId, $courseId, $instructorId);

        if ($stmtUpdate->execute()) {
            $success = "Quiz status updated.";
        } else {
            $error = "Failed to update quiz status.";
        }
    }
}

$quizQuery = "
SELECT
quizzes.id,
quizzes.title,
quizzes.description,
quizzes.time_limit_minutes,
quizzes.total_marks,
quizzes.pass_mark,
quizzes.quiz_type,
quizzes.status,
quizzes.available_from,
quizzes.available_until,
COUNT(attempts.id) AS attempt_count,
AVG(attempts.score) AS avg_score

FROM quizzes

LEFT JOIN attempts
ON attempts.quiz_id = quizzes.id

WHERE quizzes.course_id = ?

GROUP BY quizzes.id
ORDER BY quizzes.id DESC
";

$stmt = $conn->prepare($quizQuery);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$quizzes = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Quizzes</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Quizzes</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Create, publish, and analyze assessments.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="create_quiz.php?course_id=<?php echo $courseId; ?>">Create Quiz</a>
            <a class="btn secondary" href="question_bank.php?course_id=<?php echo $courseId; ?>">Question Bank</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <?php if ($quizzes->num_rows == 0) { ?>
            <div class="empty-state">No quizzes created yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Average</th>
                    <th>Window</th>
                    <th>Actions</th>
                </tr>

                <?php while($quiz = $quizzes->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($quiz['title']); ?></strong><br>
                            <span class="muted"><?php echo htmlspecialchars($quiz['description']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['status']); ?></td>
                        <td><?php echo (int) $quiz['attempt_count']; ?></td>
                        <td>
                            <?php
                                $avg = $quiz['avg_score'];
                                echo $avg !== null ? number_format($avg, 2) : "0.00";
                            ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($quiz['available_from']); ?><br>
                            <?php echo htmlspecialchars($quiz['available_until']); ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="btn" href="quiz_questions.php?quiz_id=<?php echo $quiz['id']; ?>">Questions</a>
                                <a class="btn" href="quiz_attempts.php?quiz_id=<?php echo $quiz['id']; ?>">Attempts</a>
                                <a class="btn" href="quiz_analytics.php?quiz_id=<?php echo $quiz['id']; ?>">Analytics</a>
                                <?php if ($quiz['status'] == 'published') { ?>
                                    <a class="btn secondary" href="manage_quizzes.php?course_id=<?php echo $courseId; ?>&action=unpublish&quiz_id=<?php echo $quiz['id']; ?>">Unpublish</a>
                                <?php } else { ?>
                                    <a class="btn secondary" href="manage_quizzes.php?course_id=<?php echo $courseId; ?>&action=publish&quiz_id=<?php echo $quiz['id']; ?>">Publish</a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>

</html>
