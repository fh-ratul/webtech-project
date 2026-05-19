<!DOCTYPE html>
<html>

<head>
    <title>Q&A Board</title>
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
            <h1>Q&A Board</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Answer and endorse student questions.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success" id="qa-success"><?php echo $success; ?></p>
        <p class="error" id="qa-error"><?php echo $error; ?></p>

        <?php if ($questions->num_rows == 0) { ?>
            <div class="empty-state">No questions submitted yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while ($question = $questions->fetch_assoc()) { ?>

                    <?php
                        $answerQuery = "
                        SELECT qa_answers.id, qa_answers.body, qa_answers.is_endorsed, qa_answers.created_at, users.name, users.role
                        FROM qa_answers
                        JOIN users
                        ON qa_answers.author_id = users.id
                        WHERE qa_answers.qa_question_id = ?
                        ORDER BY qa_answers.created_at ASC
                        ";

                        $stmtAnswers = $GLOBALS['conn']->prepare($answerQuery);
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
                            <a class="btn secondary" href="<?php echo $baseUrl; ?>/ta/qa-board?course_id=<?php echo $courseId; ?>&resolve=<?php echo $question['id']; ?>">Mark Resolved</a>
                        <?php } ?>

                        <div class="list-stack">
                            <?php while ($answer = $answers->fetch_assoc()) { ?>
                                <div class="answer-box">
                                    <p><?php echo htmlspecialchars($answer['body']); ?></p>
                                    <p class="muted">Answered by <?php echo htmlspecialchars($answer['name']); ?> (<?php echo htmlspecialchars($answer['role']); ?>)</p>
                                    <p class="muted"><?php echo htmlspecialchars($answer['created_at']); ?></p>
                                    <?php if ($answer['is_endorsed'] == 1) { ?>
                                        <span class="pill">Endorsed</span>
                                    <?php } else { ?>
                                        <a class="btn" href="<?php echo $baseUrl; ?>/ta/qa-board?course_id=<?php echo $courseId; ?>&endorse=<?php echo $answer['id']; ?>">Endorse</a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <form class="inline-form js-qa-answer" data-question-id="<?php echo $question['id']; ?>">
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

<script>
    const forms = document.querySelectorAll(".js-qa-answer");
    const successEl = document.getElementById("qa-success");
    const errorEl = document.getElementById("qa-error");

    forms.forEach((form) => {
        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            successEl.textContent = "";
            errorEl.textContent = "";

            const questionId = form.getAttribute("data-question-id");
            const formData = new FormData();
            formData.append("question_id", questionId);
            formData.append("body", form.querySelector("textarea").value);

            try {
                const response = await fetch("<?php echo $baseUrl; ?>/ta/qa-answer", {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                if (result.ok) {
                    successEl.textContent = "Answer posted.";
                    window.location.reload();
                } else {
                    errorEl.textContent = result.message || "Failed to post answer.";
                }
            } catch (err) {
                errorEl.textContent = "Network error while posting answer.";
            }
        });
    });
</script>

</body>

</html>
