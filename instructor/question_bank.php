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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = $_POST['action'];

    if ($action == 'update_question') {

        $questionId = (int) $_POST['question_id'];
        $questionText = trim($_POST['question_text']);
        $marks = (int) $_POST['marks'];
        $correctOptionId = (int) $_POST['correct_option_id'];

        $updateQuestion = "
        UPDATE questions
        JOIN quizzes
        ON questions.quiz_id = quizzes.id
        SET questions.question_text = ?, questions.marks = ?
        WHERE questions.id = ?
        AND quizzes.course_id = ?
        ";

        $stmtUpdate = $conn->prepare($updateQuestion);
        $stmtUpdate->bind_param("siii", $questionText, $marks, $questionId, $courseId);

        if ($stmtUpdate->execute()) {
            if (isset($_POST['option_id']) && isset($_POST['option_text'])) {
                foreach ($_POST['option_id'] as $index => $optionId) {
                    $optionText = trim($_POST['option_text'][$index]);
                    $isCorrect = ($optionId == $correctOptionId) ? 1 : 0;

                    $updateOption = "
                    UPDATE options
                    SET option_text = ?, is_correct = ?
                    WHERE id = ?
                    AND question_id = ?
                    ";

                    $stmtOption = $conn->prepare($updateOption);
                    $stmtOption->bind_param("siii", $optionText, $isCorrect, $optionId, $questionId);
                    $stmtOption->execute();
                }
            }

            $success = "Question updated.";
        } else {
            $error = "Failed to update question.";
        }
    }

    if ($action == 'delete_question') {

        $questionId = (int) $_POST['question_id'];

        $deleteOptions = "DELETE FROM options WHERE question_id = ?";
        $stmtDelOpt = $conn->prepare($deleteOptions);
        $stmtDelOpt->bind_param("i", $questionId);
        $stmtDelOpt->execute();

        $deleteQuestion = "
        DELETE questions
        FROM questions
        JOIN quizzes
        ON questions.quiz_id = quizzes.id
        WHERE questions.id = ?
        AND quizzes.course_id = ?
        ";

        $stmtDelQ = $conn->prepare($deleteQuestion);
        $stmtDelQ->bind_param("ii", $questionId, $courseId);

        if ($stmtDelQ->execute()) {
            $success = "Question deleted.";
        } else {
            $error = "Failed to delete question.";
        }
    }

    if ($action == 'reuse_question') {

        $sourceQuestionId = (int) $_POST['source_question_id'];
        $targetQuizId = (int) $_POST['target_quiz_id'];

        $quizCheck = "
        SELECT id
        FROM quizzes
        WHERE id = ?
        AND course_id = ?
        ";

        $stmtQuizCheck = $conn->prepare($quizCheck);
        $stmtQuizCheck->bind_param("ii", $targetQuizId, $courseId);
        $stmtQuizCheck->execute();

        if ($stmtQuizCheck->get_result()->num_rows == 0) {
            $error = "Target quiz not found.";
        } else {

            $sourceQuery = "
            SELECT questions.question_text, questions.marks
            FROM questions
            JOIN quizzes
            ON questions.quiz_id = quizzes.id
            WHERE questions.id = ?
            AND quizzes.course_id = ?
            ";

            $stmtSource = $conn->prepare($sourceQuery);
            $stmtSource->bind_param("ii", $sourceQuestionId, $courseId);
            $stmtSource->execute();
            $sourceResult = $stmtSource->get_result();

            if ($sourceResult->num_rows > 0) {
                $source = $sourceResult->fetch_assoc();

                $orderQuery = "SELECT COALESCE(MAX(order_index), 0) AS max_order FROM questions WHERE quiz_id = ?";
                $stmtOrder = $conn->prepare($orderQuery);
                $stmtOrder->bind_param("i", $targetQuizId);
                $stmtOrder->execute();
                $orderResult = $stmtOrder->get_result()->fetch_assoc();
                $orderIndex = (int) $orderResult['max_order'] + 1;

                $insertQuestion = "
                INSERT INTO questions
                (quiz_id, question_text, marks, order_index)
                VALUES
                (?, ?, ?, ?)
                ";

                $stmtInsert = $conn->prepare($insertQuestion);
                $stmtInsert->bind_param("isii", $targetQuizId, $source['question_text'], $source['marks'], $orderIndex);

                if ($stmtInsert->execute()) {
                    $newQuestionId = $conn->insert_id;

                    $optionsQuery = "SELECT option_text, is_correct FROM options WHERE question_id = ?";
                    $stmtOptions = $conn->prepare($optionsQuery);
                    $stmtOptions->bind_param("i", $sourceQuestionId);
                    $stmtOptions->execute();
                    $optionsResult = $stmtOptions->get_result();

                    while ($option = $optionsResult->fetch_assoc()) {
                        $insertOption = "
                        INSERT INTO options
                        (question_id, option_text, is_correct)
                        VALUES
                        (?, ?, ?)
                        ";

                        $stmtOption = $conn->prepare($insertOption);
                        $stmtOption->bind_param("isi", $newQuestionId, $option['option_text'], $option['is_correct']);
                        $stmtOption->execute();
                    }

                    $success = "Question reused in target quiz.";
                } else {
                    $error = "Failed to reuse question.";
                }
            }
        }
    }
}

$quizListQuery = "
SELECT id, title
FROM quizzes
WHERE course_id = ?
ORDER BY title
";

$stmtQuizList = $conn->prepare($quizListQuery);
$stmtQuizList->bind_param("i", $courseId);
$stmtQuizList->execute();
$quizList = $stmtQuizList->get_result();

$reuseQuery = "
SELECT questions.id, questions.question_text, quizzes.title AS quiz_title
FROM questions
JOIN quizzes
ON questions.quiz_id = quizzes.id
WHERE quizzes.course_id = ?
ORDER BY quizzes.title, questions.id DESC
";

$stmtReuse = $conn->prepare($reuseQuery);
$stmtReuse->bind_param("i", $courseId);
$stmtReuse->execute();
$reuseQuestions = $stmtReuse->get_result();

$questionQuery = "
SELECT questions.id, questions.question_text, questions.marks, quizzes.title AS quiz_title
FROM questions
JOIN quizzes
ON questions.quiz_id = quizzes.id
WHERE quizzes.course_id = ?
ORDER BY quizzes.title, questions.id DESC
";

$stmtQuestions = $conn->prepare($questionQuery);
$stmtQuestions->bind_param("i", $courseId);
$stmtQuestions->execute();
$questions = $stmtQuestions->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Question Bank</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Question Bank</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Manage reusable quiz questions.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="manage_quizzes.php?course_id=<?php echo $courseId; ?>">Back to Quizzes</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <h2>Reuse Question</h2>
        <form method="POST" class="form-grid">
            <input type="hidden" name="action" value="reuse_question">

            <div class="form-field">
                <label for="source_question_id">Select Question</label>
                <select id="source_question_id" name="source_question_id" required>
                    <?php while ($q = $reuseQuestions->fetch_assoc()) { ?>
                        <option value="<?php echo $q['id']; ?>">
                            <?php echo htmlspecialchars($q['quiz_title']); ?> • <?php echo htmlspecialchars($q['question_text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-field">
                <label for="target_quiz_id">Target Quiz</label>
                <select id="target_quiz_id" name="target_quiz_id" required>
                    <?php while ($quiz = $quizList->fetch_assoc()) { ?>
                        <option value="<?php echo $quiz['id']; ?>">
                            <?php echo htmlspecialchars($quiz['title']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-footer">
                <button type="submit">Reuse Question</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>All Questions</h2>

        <?php if ($questions->num_rows == 0) { ?>
            <div class="empty-state">No questions in this course yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while($question = $questions->fetch_assoc()) { ?>

                    <?php
                        $optionQuery = "SELECT id, option_text, is_correct FROM options WHERE question_id = ?";
                        $stmtOptions = $conn->prepare($optionQuery);
                        $stmtOptions->bind_param("i", $question['id']);
                        $stmtOptions->execute();
                        $options = $stmtOptions->get_result();
                    ?>

                    <div class="list-item">
                        <p class="pill">Quiz: <?php echo htmlspecialchars($question['quiz_title']); ?></p>

                        <form method="POST" class="inline-form">
                            <input type="hidden" name="action" value="update_question">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">

                            <div class="form-field">
                                <label>Question Text</label>
                                <textarea name="question_text" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                            </div>

                            <div class="form-field">
                                <label>Marks</label>
                                <input type="number" min="1" name="marks" value="<?php echo (int) $question['marks']; ?>" required>
                            </div>

                            <div class="option-grid">
                                <?php
                                    $correctId = 0;
                                    while ($option = $options->fetch_assoc()) {
                                        if ($option['is_correct'] == 1) {
                                            $correctId = $option['id'];
                                        }
                                ?>
                                    <div class="form-field">
                                        <label>Option</label>
                                        <input type="hidden" name="option_id[]" value="<?php echo $option['id']; ?>">
                                        <input type="text" name="option_text[]" value="<?php echo htmlspecialchars($option['option_text']); ?>" required>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="form-field">
                                <label>Correct Option</label>
                                <select name="correct_option_id" required>
                                    <?php
                                        $stmtOptions->execute();
                                        $optionsReset = $stmtOptions->get_result();
                                        while ($opt = $optionsReset->fetch_assoc()) {
                                    ?>
                                        <option value="<?php echo $opt['id']; ?>" <?php if ($opt['id'] == $correctId) { echo "selected"; } ?>>
                                            <?php echo htmlspecialchars($opt['option_text']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-footer">
                                <button type="submit">Save Changes</button>
                            </div>
                        </form>

                        <form method="POST" class="form-footer" onsubmit="return confirm('Delete this question?');">
                            <input type="hidden" name="action" value="delete_question">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                            <button type="submit" class="btn secondary">Delete Question</button>
                        </form>
                    </div>

                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

</body>

</html>
