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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_announcement'])) {

    $title = trim($_POST['title']);
    $body = trim($_POST['body']);

    if (empty($title) || empty($body)) {
        $error = "Title and body are required.";
    } else {

        $insertQuery = "
        INSERT INTO announcements
        (course_id, title, body, created_at)
        VALUES
        (?, ?, ?, NOW())
        ";

        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iss", $courseId, $title, $body);

        if ($stmt->execute()) {
            $success = "Announcement posted.";
        } else {
            $error = "Failed to post announcement.";
        }
    }
}

if (isset($_GET['delete'])) {
    $announcementId = (int) $_GET['delete'];

    $deleteQuery = "
    DELETE announcements
    FROM announcements
    JOIN courses
    ON announcements.course_id = courses.id
    WHERE announcements.id = ?
    AND courses.id = ?
    AND courses.instructor_id = ?
    ";

    $stmtDelete = $conn->prepare($deleteQuery);
    $stmtDelete->bind_param("iii", $announcementId, $courseId, $instructorId);

    if ($stmtDelete->execute()) {
        $success = "Announcement deleted.";
    } else {
        $error = "Failed to delete announcement.";
    }
}

$listQuery = "
SELECT id, title, body, created_at
FROM announcements
WHERE course_id = ?
ORDER BY created_at DESC
";

$stmtList = $conn->prepare($listQuery);
$stmtList->bind_param("i", $courseId);
$stmtList->execute();
$announcements = $stmtList->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Course Announcements</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Announcements</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Broadcast updates to enrolled students.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="course_details.php?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <form method="POST" class="inline-form">
            <input type="hidden" name="create_announcement" value="1">

            <div class="form-field">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" required>
            </div>

            <div class="form-field">
                <label for="body">Announcement Body</label>
                <textarea id="body" name="body" rows="4" required></textarea>
            </div>

            <button type="submit">Post Announcement</button>
        </form>
    </div>

    <div class="card">
        <h2>Past Announcements</h2>

        <?php if ($announcements->num_rows == 0) { ?>
            <div class="empty-state">No announcements posted yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while($announcement = $announcements->fetch_assoc()) { ?>
                    <div class="list-item">
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <p><?php echo htmlspecialchars($announcement['body']); ?></p>
                        <p class="muted">Posted: <?php echo htmlspecialchars($announcement['created_at']); ?></p>
                        <a class="btn secondary" href="announcements.php?course_id=<?php echo $courseId; ?>&delete=<?php echo $announcement['id']; ?>" onclick="return confirm('Delete this announcement?');">Delete</a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

</body>

</html>
