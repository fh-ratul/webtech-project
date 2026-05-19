<!DOCTYPE html>
<html>

<head>
    <title>Question Bank</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/instructor.css">
</head>

<body class="ta-page">

<nav class="navbar">
    <a href="<?php echo $baseUrl; ?>/ta/dashboard">Dashboard</a>
    <a href="<?php echo $baseUrl; ?>/ta/assigned-courses">Assigned Courses</a>
    <a href="<?php echo $baseUrl; ?>/ta/doubt-sessions">Doubt Sessions</a>
    <a href="<?php echo $baseUrl; ?>/ta/bookings">Bookings</a>
    <a href="<?php echo $baseUrl; ?>/ta/profile">Profile</a>
    <a href="<?php echo $baseUrl; ?>/auth/logout">Logout</a>
</nav>

<div class="container">
    <div class="page-header">
        <div class="title-block">
            <h1>Question Bank</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Manage practice quiz questions.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
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
            <div class="empty-state">No questions in practice quizzes yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while ($question = $questions->fetch_assoc()) { ?>

                    <?php
                        $optionQuery = "SELECT id, option_text, is_correct FROM options WHERE question_id = ?";
                        $stmtOptions = $GLOBALS['conn']->prepare($optionQuery);
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
