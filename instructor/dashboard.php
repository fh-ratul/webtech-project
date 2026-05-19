<?php

include("../config/config.php");

// Check Login

if (!isset($_SESSION['user_id'])) {

    header("Location: ../auth/login.php");

    exit();
}

// Role Check

if ($_SESSION['role'] != 'instructor') {

    die("Access Denied");
}

$instructorId = $_SESSION['user_id'];

// Fetch Stats

$statsQuery = "
SELECT
COUNT(*) AS total_courses,
SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_courses,
SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_courses

FROM courses
WHERE instructor_id = ?
";

$stmtStats = $conn->prepare($statsQuery);
$stmtStats->bind_param("i", $instructorId);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

$pendingQuery = "
SELECT COUNT(*) AS pending_requests
FROM enrollments
JOIN courses
ON enrollments.course_id = courses.id
WHERE courses.instructor_id = ?
AND enrollments.status = 'pending'
";

$stmtPending = $conn->prepare($pendingQuery);
$stmtPending->bind_param("i", $instructorId);
$stmtPending->execute();
$pending = $stmtPending->get_result()->fetch_assoc();

// Fetch Courses

$query = "
SELECT
courses.id,
courses.title,
courses.description,
courses.status,
courses.enrollment_type,
courses.max_students,
subjects.name AS subject_name,
courses.created_at

FROM courses

JOIN subjects
ON courses.subject_id = subjects.id

WHERE instructor_id = ?

ORDER BY created_at DESC
";

$stmt = $conn->prepare($query);

$stmt->bind_param("i", $instructorId);

$stmt->execute();

$courses = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Instructor Dashboard</title>

    <link
    rel="stylesheet"
    href="../assets/css/base.css"
    >

    <link
    rel="stylesheet"
    href="../assets/css/instructor.css"
    >

</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>
                Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
            </h1>
            <p>Manage courses, approvals, and teaching operations.</p>
        </div>

        <a class="btn" href="create_course.php">Create New Course</a>
    </div>

    <div class="card hero">
        <div>
            <h2>Instructor Control Panel</h2>
            <p>Track active offerings, drafts, and student approvals in one place.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Courses</h3>
                <div class="stat-value">
                    <?php echo (int) $stats['total_courses']; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Active Courses</h3>
                <div class="stat-value">
                    <?php echo (int) $stats['active_courses']; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Drafts</h3>
                <div class="stat-value">
                    <?php echo (int) $stats['draft_courses']; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Pending Requests</h3>
                <div class="stat-value">
                    <?php echo (int) $pending['pending_requests']; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>My Courses</h2>

        <?php if ($courses->num_rows == 0) { ?>
            <div class="empty-state">
                No courses yet. Create your first course to get started.
            </div>
        <?php } else { ?>
            <div class="course-grid">
                <?php while($course = $courses->fetch_assoc()) { ?>

                    <?php
                        $statusClass = "";
                        if ($course['status'] == 'draft') {
                            $statusClass = "draft";
                        } elseif ($course['status'] == 'archived') {
                            $statusClass = "archived";
                        }
                    ?>

                    <div class="course-card">

                        <div class="badge <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($course['status']); ?>
                        </div>

                        <h3>
                            <?php echo htmlspecialchars($course['title']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($course['description']); ?>
                        </p>

                        <div class="course-meta">
                            <span>Subject: <?php echo htmlspecialchars($course['subject_name']); ?></span>
                            <span>Enrollment: <?php echo htmlspecialchars($course['enrollment_type']); ?></span>
                            <span>Max: <?php echo (int) $course['max_students']; ?></span>
                        </div>

                        <div class="action-row">
                            <a class="btn" href="course_details.php?id=<?php echo $course['id']; ?>">
                                Manage Course
                            </a>
                            <a class="btn secondary" href="create_quiz.php?course_id=<?php echo $course['id']; ?>">
                                Create Quiz
                            </a>
                            <a class="btn" href="assign_ta.php?course_id=<?php echo $course['id']; ?>">
                                Assign TA
                            </a>
                            <a class="btn" href="enrollment_requests.php?course_id=<?php echo $course['id']; ?>">
                                Enrollment Requests
                            </a>
                        </div>

                    </div>

                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

</body>

</html>