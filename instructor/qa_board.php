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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answer_question'])) {

    $questionId = (int) $_POST['question_id'];
    $body = trim($_POST['body']);

    if (empty($body)) {
        $error = "Answer body is required.";
    } else {

        $insertQuery = "
        INSERT INTO qa_answers
        (qa_question_id, author_id, body, is_endorsed, created_at)
        VALUES
        (?, ?, ?, 0, NOW())
        ";

        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iis", $questionId, $instructorId, $body);

        if ($stmt->execute()) {
            $success = "Answer posted.";
        } else {
            $error = "Failed to post answer.";
        }
    }
}

if (isset($_GET['endorse'])) {

    $answerId = (int) $_GET['endorse'];

    $endorseQuery = "
    UPDATE qa_answers
    JOIN qa_questions
    ON qa_answers.qa_question_id = qa_questions.id
    SET qa_answers.is_endorsed = 1
    WHERE qa_answers.id = ?
    AND qa_questions.course_id = ?
    ";

    $stmtEndorse = $conn->prepare($endorseQuery);
    $stmtEndorse->bind_param("ii", $answerId, $courseId);

    if ($stmtEndorse->execute()) {
        $success = "Answer endorsed.";
    } else {
        $error = "Failed to endorse answer.";
    }
}

if (isset($_GET['resolve'])) {

    $questionId = (int) $_GET['resolve'];

    $resolveQuery = "
    UPDATE qa_questions
    SET is_resolved = 1
    WHERE id = ?
    AND course_id = ?
    ";

    $stmtResolve = $conn->prepare($resolveQuery);
    $stmtResolve->bind_param("ii", $questionId, $courseId);

    if ($stmtResolve->execute()) {
        $success = "Question marked as resolved.";
    } else {
        $error = "Failed to resolve question.";
    }
}

$questionQuery = "
SELECT
qa_questions.id,
qa_questions.title,
qa_questions.body,
qa_questions.is_resolved,
qa_questions.created_at,
users.name AS student_name

FROM qa_questions

JOIN users
ON qa_questions.student_id = users.id

WHERE qa_questions.course_id = ?

ORDER BY qa_questions.created_at DESC
";

$stmtQuestions = $conn->prepare($questionQuery);
$stmtQuestions->bind_param("i", $courseId);
$stmtQuestions->execute();
$questions = $stmtQuestions->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Course Q&A Board</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Q&A Board</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Answer and endorse student questions.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="course_details.php?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <?php if ($questions->num_rows == 0) { ?>
            <div class="empty-state">No questions submitted yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while($question = $questions->fetch_assoc()) { ?>

                    <?php
                        $answerQuery = "
                        SELECT qa_answers.id, qa_answers.body, qa_answers.is_endorsed, qa_answers.created_at, users.name, users.role
                        FROM qa_answers
                        JOIN users
                        ON qa_answers.author_id = users.id
                        WHERE qa_answers.qa_question_id = ?
                        ORDER BY qa_answers.created_at ASC
                        ";

                        $stmtAnswers = $conn->prepare($answerQuery);
                        $stmtAnswers->bind_param("i", $question['id']);
                        $stmtAnswers->execute();
                        $answers = $stmtAnswers->get_result();
                    ?>

                    <div class="list-item">
                        <h3><?php echo htmlspecialchars($question['title']); ?></h3>
                        <p><?php echo htmlspecialchars($question['body']); ?></p>
                        <p class="muted">Asked by <?php echo htmlspecialchars($question['student_name']); ?> • <?php echo htmlspecialchars($question['created_at']); ?></p>

                        <?php if ($question['is_resolved'] == 1) { ?>
                            <span class="pill">Resolved</span>
                        <?php } else { ?>
                            <a class="btn secondary" href="qa_board.php?course_id=<?php echo $courseId; ?>&resolve=<?php echo $question['id']; ?>">Mark Resolved</a>
                        <?php } ?>

                        <div class="list-stack">
                            <?php while($answer = $answers->fetch_assoc()) { ?>
                                <div class="answer-box">
                                    <p><?php echo htmlspecialchars($answer['body']); ?></p>
                                    <p class="muted">Answered by <?php echo htmlspecialchars($answer['name']); ?> (<?php echo htmlspecialchars($answer['role']); ?>)</p>
                                    <p class="muted"><?php echo htmlspecialchars($answer['created_at']); ?></p>
                                    <?php if ($answer['is_endorsed'] == 1) { ?>
                                        <span class="pill">Endorsed</span>
                                    <?php } else { ?>
                                        <a class="btn" href="qa_board.php?course_id=<?php echo $courseId; ?>&endorse=<?php echo $answer['id']; ?>">Endorse</a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <form method="POST" class="inline-form">
                            <input type="hidden" name="answer_question" value="1">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                            <div class="form-field">
                                <label>Post an Answer</label>
                                <textarea name="body" rows="3" required></textarea>
                            </div>
                            <button type="submit">Submit Answer</button>
                        </form>
                    </div>

                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

</body>

</html>
