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

if (!isset($_GET['id'])) {
    die("Invalid Course ID");
}

$courseId = (int) $_GET['id'];
$instructorId = $_SESSION['user_id'];

$subjectsQuery = "SELECT id, name FROM subjects ORDER BY name";
$subjects = $conn->query($subjectsQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['update_course'])) {

        $title = trim($_POST['title']);
        $subjectId = (int) $_POST['subject_id'];
        $description = trim($_POST['description']);
        $enrollmentType = $_POST['enrollment_type'];
        $maxStudents = (int) $_POST['max_students'];
        $status = $_POST['status'];

        if (
            empty($title) ||
            empty($description) ||
            empty($enrollmentType) ||
            empty($status) ||
            $subjectId <= 0 ||
            $maxStudents <= 0
        ) {

            $error = "All fields are required.";
        }

        else {

            $updateQuery = "
            UPDATE courses
            SET
            title = ?,
            subject_id = ?,
            description = ?,
            enrollment_type = ?,
            max_students = ?,
            status = ?
            WHERE id = ?
            AND instructor_id = ?
            ";

            $stmtUpdate = $conn->prepare($updateQuery);
            $stmtUpdate->bind_param(
                "sissisii",
                $title,
                $subjectId,
                $description,
                $enrollmentType,
                $maxStudents,
                $status,
                $courseId,
                $instructorId
            );

            if ($stmtUpdate->execute()) {
                $success = "Course updated successfully.";
            }

            else {
                $error = "Failed to update course.";
            }
        }
    }

    if (isset($_POST['archive_course'])) {

        $archiveQuery = "
        UPDATE courses
        SET status = 'archived'
        WHERE id = ?
        AND instructor_id = ?
        ";

        $stmtArchive = $conn->prepare($archiveQuery);
        $stmtArchive->bind_param("ii", $courseId, $instructorId);

        if ($stmtArchive->execute()) {
            $success = "Course archived.";
        }

        else {
            $error = "Failed to archive course.";
        }
    }
}

$courseQuery = "
SELECT *
FROM courses
WHERE id = ?
AND instructor_id = ?
";

$stmt = $conn->prepare($courseQuery);
$stmt->bind_param("ii", $courseId, $instructorId);
$stmt->execute();

$course = $stmt
->get_result()
->fetch_assoc();

if (!$course) {
    die("Course not found.");
}

$studentQuery = "
SELECT
users.name,
users.email,
enrollments.status

FROM enrollments

JOIN users
ON enrollments.student_id = users.id

WHERE enrollments.course_id = ?
";

$stmt2 = $conn->prepare($studentQuery);
$stmt2->bind_param("i", $courseId);
$stmt2->execute();

$students = $stmt2->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Details</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>
<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p>Manage your course details, enrollment policies, and student list.</p>
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
                    <label for="title">Course Title</label>
                    <input
                    id="title"
                    type="text"
                    name="title"
                    value="<?php echo htmlspecialchars($course['title']); ?>"
                    required
                    >
                </div>

                <div class="form-field">
                    <label for="subject_id">Subject</label>
                    <select id="subject_id" name="subject_id" required>
                        <?php while($subject = $subjects->fetch_assoc()) { ?>
                            <option
                            value="<?php echo $subject['id']; ?>"
                            <?php if ($course['subject_id'] == $subject['id']) { echo "selected"; } ?>
                            >
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea
                    id="description"
                    name="description"
                    rows="5"
                    required
                    ><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="enrollment_type">Enrollment Type</label>
                    <select id="enrollment_type" name="enrollment_type" required>
                        <option value="open" <?php if ($course['enrollment_type'] == 'open') { echo "selected"; } ?>>Open Enrollment</option>
                        <option value="approval" <?php if ($course['enrollment_type'] == 'approval') { echo "selected"; } ?>>Approval Required</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="max_students">Maximum Students</label>
                    <input
                    id="max_students"
                    type="number"
                    min="1"
                    name="max_students"
                    value="<?php echo (int) $course['max_students']; ?>"
                    required
                    >
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="draft" <?php if ($course['status'] == 'draft') { echo "selected"; } ?>>Draft</option>
                        <option value="active" <?php if ($course['status'] == 'active') { echo "selected"; } ?>>Active</option>
                        <option value="archived" <?php if ($course['status'] == 'archived') { echo "selected"; } ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" name="update_course">Save Changes</button>
                <button class="btn secondary" type="submit" name="archive_course">Archive Course</button>
                <a class="btn" href="dashboard.php">Back to Dashboard</a>
            </div>

        </form>

    </div>

    <div class="card">
        <h2>Course Management</h2>

        <div class="action-row">
            <a class="btn" href="manage_quizzes.php?course_id=<?php echo $courseId; ?>">Manage Quizzes</a>
            <a class="btn" href="question_bank.php?course_id=<?php echo $courseId; ?>">Question Bank</a>
            <a class="btn" href="announcements.php?course_id=<?php echo $courseId; ?>">Announcements</a>
            <a class="btn" href="materials.php?course_id=<?php echo $courseId; ?>">Materials</a>
            <a class="btn" href="qa_board.php?course_id=<?php echo $courseId; ?>">Q&A Board</a>
            <a class="btn secondary" href="assign_ta.php?course_id=<?php echo $courseId; ?>">Assign TA</a>
            <a class="btn secondary" href="enrollment_requests.php?course_id=<?php echo $courseId; ?>">Enrollment Requests</a>
            <a class="btn secondary" href="course_performance.php?course_id=<?php echo $courseId; ?>">Performance Report</a>
        </div>
    </div>

    <div class="card">
        <h2>Enrolled Students</h2>

        <?php if ($students->num_rows == 0) { ?>
            <div class="empty-state">
                No enrolled students yet.
            </div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>

                <?php while($student = $students->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($student['name']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($student['email']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($student['status']); ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>
</html>