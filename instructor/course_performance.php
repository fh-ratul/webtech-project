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

$enrolledQuery = "
SELECT
SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) AS dropped_count,
COUNT(*) AS total_count
FROM enrollments
WHERE course_id = ?
";

$stmtEnrolled = $conn->prepare($enrolledQuery);
$stmtEnrolled->bind_param("i", $courseId);
$stmtEnrolled->execute();
$enrollmentStats = $stmtEnrolled->get_result()->fetch_assoc();

$quizReportQuery = "
SELECT
quizzes.id,
quizzes.title,
quizzes.total_marks,
COUNT(DISTINCT attempts.student_id) AS students_attempted,
AVG(attempts.score) AS avg_score

FROM quizzes
LEFT JOIN attempts
ON attempts.quiz_id = quizzes.id

WHERE quizzes.course_id = ?

GROUP BY quizzes.id
ORDER BY quizzes.id DESC
";

$stmtQuizReport = $conn->prepare($quizReportQuery);
$stmtQuizReport->bind_param("i", $courseId);
$stmtQuizReport->execute();
$quizReports = $stmtQuizReport->get_result();

$activeCount = (int) $enrollmentStats['active_count'];
$droppedCount = (int) $enrollmentStats['dropped_count'];
$totalCount = (int) $enrollmentStats['total_count'];
$dropRate = ($activeCount + $droppedCount) > 0 ? ($droppedCount / ($activeCount + $droppedCount)) * 100 : 0;

?>

<!DOCTYPE html>
<html>

<head>
    <title>Course Performance</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Course Performance</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Overview of engagement and results.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="course_details.php?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card split thirds">
        <div class="stat-card">
            <h3>Enrolled Students</h3>
            <div class="stat-value"><?php echo $activeCount; ?></div>
        </div>
        <div class="stat-card">
            <h3>Dropouts</h3>
            <div class="stat-value"><?php echo $droppedCount; ?></div>
        </div>
        <div class="stat-card">
            <h3>Drop-out Rate</h3>
            <div class="stat-value"><?php echo number_format($dropRate, 2); ?>%</div>
        </div>
    </div>

    <div class="card">
        <h2>Quiz Completion & Averages</h2>

        <?php if ($quizReports->num_rows == 0) { ?>
            <div class="empty-state">No quizzes created yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Quiz</th>
                    <th>Students Attempted</th>
                    <th>Completion Rate</th>
                    <th>Average Score</th>
                </tr>

                <?php while($quiz = $quizReports->fetch_assoc()) { ?>
                    <?php
                        $attempted = (int) $quiz['students_attempted'];
                        $completionRate = $activeCount > 0 ? ($attempted / $activeCount) * 100 : 0;
                        $avgScore = $quiz['avg_score'] !== null ? number_format($quiz['avg_score'], 2) : "0.00";
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo $attempted; ?></td>
                        <td><?php echo number_format($completionRate, 2); ?>%</td>
                        <td><?php echo $avgScore; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>

</html>
