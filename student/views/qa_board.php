<!DOCTYPE html>
<html>

<head>

    <title>Q&A Board</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h2>Ask Question</h2>

        <form method="POST">

            <input
            type="text"
            name="title"
            placeholder="Question Title"
            required
            >

            <textarea
            name="body"
            placeholder="Write your question..."
            rows="5"
            required
            ></textarea>

            <button type="submit">
                Post Question
            </button>

        </form>

    </div>

    <div class="card">

        <h2>All Questions</h2>

        <?php while ($question = $questions->fetch_assoc()) { ?>

            <div>

                <h3>
                    <?php echo $question['title']; ?>
                </h3>

                <p>
                    <?php echo $question['body']; ?>
                </p>

                <p>
                    Asked By:
                    <?php echo $question['name']; ?>
                </p>

                <p>
                    Date:
                    <?php echo $question['created_at']; ?>
                </p>

                <?php if ($question['is_resolved'] == 1) { ?>

                    <p class="resolved">
                        Resolved
                    </p>

                <?php } else { ?>

                    <p class="pending">
                        Pending
                    </p>

                <?php } ?>

                <a
                class="btn"
                href="<?php echo APP_BASE_URL; ?>/student/view-answers?question_id=<?php echo $question['id']; ?>"
                >
                    View Answers
                </a>

                <hr>

            </div>

        <?php } ?>

    </div>

</div>

</body>

</html>
