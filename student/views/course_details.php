<!DOCTYPE html>
<html>

<head>

    <title>Course Details</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h1>
            <?php echo $course['title']; ?>
        </h1>

        <p>
            <?php echo $course['description']; ?>
        </p>

        <p>
            <strong>Subject:</strong>
            <?php echo $course['subject_name']; ?>
        </p>

        <p>
            <strong>Instructor:</strong>
            <?php echo $course['instructor_name']; ?>
        </p>

    </div>

    <div class="card">

        <h2>Assigned Teaching Assistants</h2>

        <?php while ($ta = $tas->fetch_assoc()) { ?>
            <p>
                <?php echo $ta['name']; ?>
            </p>
        <?php } ?>

    </div>

    <div class="card">

        <h2>Announcements</h2>

        <?php while ($announcement = $announcements->fetch_assoc()) { ?>

            <div>

                <h3>
                    <?php echo $announcement['title']; ?>
                </h3>

                <?php if ($announcement['posted_role'] == 'ta') { ?>
                    <span class="pill">From TA</span>
                <?php } ?>

                <p>
                    <?php echo $announcement['body']; ?>
                </p>

                <small>
                    <?php echo $announcement['created_at']; ?>
                </small>

                <hr>

            </div>

        <?php } ?>

    </div>

    <div class="card">

        <h2>Course Materials</h2>

        <?php while ($material = $materials->fetch_assoc()) { ?>

            <div>

                <h3>
                    <?php echo $material['title']; ?>
                </h3>

                <p>
                    Type:
                    <?php echo $material['material_type']; ?>
                </p>

                <?php if ($material['material_type'] == 'link') { ?>

                    <a
                    class="btn"
                    href="<?php echo htmlspecialchars($material['file_path']); ?>"
                    target="_blank"
                    >
                        Open Link
                    </a>

                <?php } else { ?>

                    <a
                    class="btn"
                    href="<?php echo APP_BASE_URL; ?>/student/materials?material_id=<?php echo $material['id']; ?>"
                    target="_blank"
                    >
                        Open Material
                    </a>

                <?php } ?>

                <hr>

            </div>

        <?php } ?>

    </div>

    <div class="card">

        <h2>Published Quizzes</h2>

        <?php while ($quiz = $quizzes->fetch_assoc()) { ?>

            <div>

                <h3>
                    <?php echo $quiz['title']; ?>
                </h3>

                <p>
                    Quiz Type:
                    <?php echo $quiz['quiz_type']; ?>
                </p>

                <p>
                    Total Marks:
                    <?php echo $quiz['total_marks']; ?>
                </p>

                <p>
                    Deadline:
                    <?php echo $quiz['available_until']; ?>
                </p>

                <a
                class="btn"
                href="<?php echo APP_BASE_URL; ?>/student/take-quiz?id=<?php echo $quiz['id']; ?>"
                >
                    Take Quiz
                </a>

                <hr>

            </div>

        <?php } ?>

    </div>

    <div class="card">

        <h2>Q&A Board</h2>

        <a
        class="btn"
        href="<?php echo APP_BASE_URL; ?>/student/qa-board?course_id=<?php echo $courseId; ?>"
        >
            Open Q&A Board
        </a>

    </div>

</div>

</body>

</html>
