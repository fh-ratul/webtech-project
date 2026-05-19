<?php

include("../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'instructor') {
    die("Access Denied");
}

$success = "";
$error = "";

if (!isset($_GET['course_id'])) {
    die("Invalid Course ID");
}

$courseId = $_GET['course_id'];
$instructorId = $_SESSION['user_id'];

$courseCheck = "
SELECT id, title
FROM courses
WHERE id = ?
AND instructor_id = ?
";

$stmtCheck = $conn->prepare($courseCheck);
$stmtCheck->bind_param("ii", $courseId, $instructorId);
$stmtCheck->execute();
$courseResult = $stmtCheck->get_result();

if ($courseResult->num_rows == 0) {
    die("Course Not Found");
}

$course = $courseResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $timeLimit = (int) $_POST['time_limit'];
    $totalMarks = (int) $_POST['total_marks'];
    $passMark = (int) $_POST['pass_mark'];
    $quizType = $_POST['quiz_type'];
    $status = $_POST['status'];
    $from = $_POST['available_from'];
    $until = $_POST['available_until'];

    if (
        empty($title) ||
        empty($description) ||
        empty($quizType) ||
        empty($status) ||
        $timeLimit <= 0 ||
        $totalMarks <= 0 ||
        $passMark < 0
    ) {

        $error = "All fields are required.";
    }

    else {

        $query = "
        INSERT INTO quizzes
        (
            course_id,
            created_by,
            title,
            description,
            time_limit_minutes,
            total_marks,
            pass_mark,
            quiz_type,
            status,
            available_from,
            available_until
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,?
        )
        ";

        $stmt = $conn->prepare($query);

        $stmt->bind_param(
            "iissiisssss",
            $courseId,
            $instructorId,
            $title,
            $description,
            $timeLimit,
            $totalMarks,
            $passMark,
            $quizType,
            $status,
            $from,
            $until
        );

        if ($stmt->execute()) {

            $success = "Quiz created successfully.";
        }

        else {
            $error = "Failed to create quiz.";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Quiz</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Create Quiz</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Configure graded or practice assessments.</p>
        </div>
    </div>

    <div class="card">

        <p class="success">
            <?php echo $success; ?>
        </p>

        <p class="error">
            <?php echo $error; ?>
        </p>

        <form method="POST">

            <div class="form-grid">
                <div class="form-field">
                    <label for="title">Quiz Title</label>
                    <input id="title" type="text" name="title" required>
                </div>
                <div class="form-field">
                    <label for="quiz_type">Quiz Type</label>
                    <select id="quiz_type" name="quiz_type" required>
                        <option value="graded">Graded</option>
                        <option value="practice">Practice</option>
                    </select>
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" required></textarea>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="time_limit">Time Limit (minutes)</label>
                    <input id="time_limit" type="number" min="1" name="time_limit" required>
                </div>
                <div class="form-field">
                    <label for="total_marks">Total Marks</label>
                    <input id="total_marks" type="number" min="1" name="total_marks" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="pass_mark">Pass Mark</label>
                    <input id="pass_mark" type="number" min="0" name="pass_mark" required>
                </div>
                <div class="form-field">
                    <label for="status">Publish Status</label>
                    <select id="status" name="status" required>
                        <option value="draft">Save as Draft</option>
                        <option value="published">Publish Now</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="available_from">Available From</label>
                    <input id="available_from" type="datetime-local" name="available_from">
                </div>
                <div class="form-field">
                    <label for="available_until">Available Until</label>
                    <input id="available_until" type="datetime-local" name="available_until">
                </div>
            </div>

            <div class="form-footer">
                <button type="submit">Create Quiz</button>
                <a class="btn secondary" href="manage_quizzes.php?course_id=<?php echo $courseId; ?>">Manage Quizzes</a>
            </div>

        </form>

    </div>

</div>

</body>

</html>