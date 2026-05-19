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
$success = "";

if (isset($_GET['approve'])) {

    $enrollmentId = $_GET['approve'];

    $query = "
    UPDATE enrollments
    JOIN courses
    ON enrollments.course_id = courses.id
    SET enrollments.status = 'active'
    WHERE enrollments.id = ?
    AND courses.id = ?
    AND courses.instructor_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $enrollmentId, $courseId, $instructorId);
    if ($stmt->execute()) {
        $success = "Enrollment approved.";
    }
}

if (isset($_GET['reject'])) {

    $enrollmentId = $_GET['reject'];

    $query = "
    UPDATE enrollments
    JOIN courses
    ON enrollments.course_id = courses.id
    SET enrollments.status = 'dropped'
    WHERE enrollments.id = ?
    AND courses.id = ?
    AND courses.instructor_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $enrollmentId, $courseId, $instructorId);
    if ($stmt->execute()) {
        $success = "Enrollment rejected.";
    }
}

$requestQuery = "
SELECT
enrollments.id,
users.name,
users.email

FROM enrollments

JOIN users
ON enrollments.student_id = users.id

WHERE enrollments.course_id = ?
AND enrollments.status = 'pending'
";

$stmt2 = $conn->prepare($requestQuery);
$stmt2->bind_param("i", $courseId);
$stmt2->execute();

$requests = $stmt2->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Requests</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>
<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Enrollment Requests</h1>
            <p>Approve or reject pending enrollments for this course.</p>
        </div>
    </div>

    <div class="card">

        <p class="success">
            <?php echo $success; ?>
        </p>

        <?php if ($requests->num_rows == 0) { ?>
            <div class="empty-state">
                No pending requests right now.
            </div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>

                <?php while($request = $requests->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($request['name']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($request['email']); ?>
                        </td>
                        <td>
                            <a class="btn" href="?course_id=<?php echo $courseId; ?>&approve=<?php echo $request['id']; ?>">
                                Approve
                            </a>
                            <a class="btn secondary" href="?course_id=<?php echo $courseId; ?>&reject=<?php echo $request['id']; ?>">
                                Reject
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>
</html>