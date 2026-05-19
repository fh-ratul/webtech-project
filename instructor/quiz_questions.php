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
quizzes.id,
quizzes.title,
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
$courseId = $quiz['course_id'];

$success = "";
$error = "";

function get_next_order_index($conn, $quizId) {
    $orderQuery = "SELECT COALESCE(MAX(order_index), 0) AS max_order FROM questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return (int) $result['max_order'] + 1;
}

function copy_question_to_quiz($conn, $sourceQuestionId, $quizId, $courseId) {
    $sourceQuery = "
    SELECT questions.question_text, questions.marks, quizzes.course_id
    FROM questions
    JOIN quizzes
    ON questions.quiz_id = quizzes.id
    WHERE questions.id = ?
    ";

    $stmtSource = $conn->prepare($sourceQuery);
    $stmtSource->bind_param("i", $sourceQuestionId);
    $stmtSource->execute();
    $sourceResult = $stmtSource->get_result();

    if ($sourceResult->num_rows == 0) {
        return false;
    }

    $source = $sourceResult->fetch_assoc();

    if ((int) $source['course_id'] !== (int) $courseId) {
        return false;
    }

    $orderIndex = get_next_order_index($conn, $quizId);

    $insertQuestion = "
    INSERT INTO questions
    (quiz_id, question_text, marks, order_index)
    VALUES
    (?, ?, ?, ?)
    ";

    $stmtInsert = $conn->prepare($insertQuestion);
    $stmtInsert->bind_param("isii", $quizId, $source['question_text'], $source['marks'], $orderIndex);

    if (!$stmtInsert->execute()) {
        return false;
    }

    $newQuestionId = $conn->insert_id;

    $optionsQuery = "
    SELECT option_text, is_correct
    FROM options
    WHERE question_id = ?
    ";

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

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = $_POST['action'];

    if ($action == 'add_question') {

        $questionText = trim($_POST['question_text']);
        $marks = (int) $_POST['marks'];
        $options = $_POST['options'];
        $correctIndex = (int) $_POST['correct_option'];

        if (empty($questionText) || $marks <= 0) {
            $error = "Question text and marks are required.";
        } else {
            $orderIndex = get_next_order_index($conn, $quizId);

            $insertQuestion = "
            INSERT INTO questions
            (quiz_id, question_text, marks, order_index)
            VALUES
            (?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($insertQuestion);
            $stmt->bind_param("isii", $quizId, $questionText, $marks, $orderIndex);

            if ($stmt->execute()) {
                $questionId = $conn->insert_id;

                for ($i = 0; $i < 4; $i++) {
                    $optionText = trim($options[$i]);
                    $isCorrect = ($i + 1 == $correctIndex) ? 1 : 0;

                    $insertOption = "
                    INSERT INTO options
                    (question_id, option_text, is_correct)
                    VALUES
                    (?, ?, ?)
                    ";

                    $stmtOption = $conn->prepare($insertOption);
                    $stmtOption->bind_param("isi", $questionId, $optionText, $isCorrect);
                    $stmtOption->execute();
                }

                $success = "Question added.";
            } else {
                $error = "Failed to add question.";
            }
        }
    }

    if ($action == 'update_question') {

        $questionId = (int) $_POST['question_id'];
        $questionText = trim($_POST['question_text']);
        $marks = (int) $_POST['marks'];
        $correctOptionId = (int) $_POST['correct_option_id'];

        $updateQuestion = "
        UPDATE questions
        SET question_text = ?, marks = ?
        WHERE id = ?
        AND quiz_id = ?
        ";

        $stmt = $conn->prepare($updateQuestion);
        $stmt->bind_param("siii", $questionText, $marks, $questionId, $quizId);

        if ($stmt->execute()) {
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

        $deleteQuestion = "DELETE FROM questions WHERE id = ? AND quiz_id = ?";
        $stmtDelQ = $conn->prepare($deleteQuestion);
        $stmtDelQ->bind_param("ii", $questionId, $quizId);

        if ($stmtDelQ->execute()) {
            $success = "Question deleted.";
        } else {
            $error = "Failed to delete question.";
        }
    }

    if ($action == 'copy_question') {

        $sourceQuestionId = (int) $_POST['source_question_id'];
        if (copy_question_to_quiz($conn, $sourceQuestionId, $quizId, $courseId)) {
            $success = "Question copied to this quiz.";
        } else {
            $error = "Failed to copy question.";
        }
    }

    if ($action == 'bulk_copy') {

        if (!isset($_POST['bank_question_ids']) || count($_POST['bank_question_ids']) == 0) {
            $error = "Select at least one question to add.";
        } else {
            $added = 0;

            foreach ($_POST['bank_question_ids'] as $sourceId) {
                if (copy_question_to_quiz($conn, (int) $sourceId, $quizId, $courseId)) {
                    $added++;
                }
            }

            if ($added > 0) {
                $success = $added . " question(s) added to this quiz.";
            } else {
                $error = "No questions were added.";
            }
        }
    }
}

$questionsQuery = "
SELECT id, question_text, marks, order_index
FROM questions
WHERE quiz_id = ?
ORDER BY order_index ASC
";

$stmtQuestions = $conn->prepare($questionsQuery);
$stmtQuestions->bind_param("i", $quizId);
$stmtQuestions->execute();
$questions = $stmtQuestions->get_result();

$bankQuery = "
SELECT questions.id, questions.question_text, quizzes.title AS quiz_title
FROM questions
JOIN quizzes
ON questions.quiz_id = quizzes.id
WHERE quizzes.course_id = ?
AND quizzes.id != ?
ORDER BY quizzes.title
";

$stmtBank = $conn->prepare($bankQuery);
$stmtBank->bind_param("ii", $courseId, $quizId);
$stmtBank->execute();
$bankQuestions = $stmtBank->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Questions</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Quiz Questions</h1>
            <p><?php echo htmlspecialchars($quiz['course_title']); ?> • <?php echo htmlspecialchars($quiz['title']); ?></p>
        </div>
        <div class="action-row">
            <a class="btn" href="manage_quizzes.php?course_id=<?php echo $courseId; ?>">Back to Quizzes</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <h2>Add MCQ Question</h2>

        <form class="inline-form" method="POST">
            <input type="hidden" name="action" value="add_question">

            <div class="form-field">
                <label for="question_text">Question Text</label>
                <textarea id="question_text" name="question_text" rows="4" required></textarea>
            </div>

            <div class="form-field">
                <label for="marks">Marks</label>
                <input id="marks" type="number" min="1" name="marks" required>
            </div>

            <div class="option-grid">
                <?php for ($i = 1; $i <= 4; $i++) { ?>
                    <div class="form-field">
                        <label>Option <?php echo $i; ?></label>
                        <input type="text" name="options[]" required>
                    </div>
                <?php } ?>
            </div>

            <div class="form-field">
                <label for="correct_option">Correct Option (1-4)</label>
                <select id="correct_option" name="correct_option" required>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                    <option value="4">Option 4</option>
                </select>
            </div>

            <button type="submit">Add Question</button>
        </form>
    </div>

    <div class="card">
        <div class="section-row">
            <h2>Course Question Bank</h2>
            <span class="pill">Select multiple questions to add</span>
        </div>

        <?php if ($bankQuestions->num_rows == 0) { ?>
            <div class="empty-state">No questions available in the course bank.</div>
        <?php } else { ?>
            <form method="POST" class="inline-form" id="bank-form">
                <input type="hidden" name="action" value="bulk_copy">

                <div class="form-field">
                    <label for="bank_filter">Filter Questions</label>
                    <input id="bank_filter" type="text" placeholder="Search question text or quiz title">
                </div>

                <div class="list-stack" id="bank-list">
                    <?php while($bank = $bankQuestions->fetch_assoc()) { ?>
                        <label class="list-item" data-filter="<?php echo htmlspecialchars(strtolower($bank['quiz_title'] . " " . $bank['question_text'])); ?>">
                            <div>
                                <input type="checkbox" name="bank_question_ids[]" value="<?php echo $bank['id']; ?>">
                                <strong><?php echo htmlspecialchars($bank['quiz_title']); ?></strong>
                                <span class="muted">• <?php echo htmlspecialchars($bank['question_text']); ?></span>
                            </div>
                        </label>
                    <?php } ?>
                </div>

                <div class="form-footer">
                    <button type="submit">Add Selected to Quiz</button>
                </div>
            </form>
        <?php } ?>
    </div>

    <div class="card">
        <h2>Existing Questions</h2>

        <?php if ($questions->num_rows == 0) { ?>
            <div class="empty-state">No questions added yet.</div>
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

<script>
const filterInput = document.getElementById('bank_filter');
const bankList = document.getElementById('bank-list');

if (filterInput && bankList) {
    filterInput.addEventListener('input', () => {
        const value = filterInput.value.trim().toLowerCase();
        const items = bankList.querySelectorAll('[data-filter]');

        items.forEach(item => {
            const haystack = item.getAttribute('data-filter') || '';
            item.style.display = haystack.includes(value) ? 'block' : 'none';
        });
    });
}
</script>

</body>

</html>
