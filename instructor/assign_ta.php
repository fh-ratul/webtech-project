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
$error = "";

$courseCheck = "
SELECT id, title
FROM courses
WHERE id = ?
AND instructor_id = ?
";

$stmtCourse = $conn->prepare($courseCheck);
$stmtCourse->bind_param("ii", $courseId, $instructorId);
$stmtCourse->execute();
$courseResult = $stmtCourse->get_result();

if ($courseResult->num_rows == 0) {
    die("Course Not Found");
}

$course = $courseResult->fetch_assoc();

$taQuery = "
SELECT
id,
name
FROM users
WHERE role = 'ta'
ORDER BY name
";

$tas = $conn->query($taQuery);

$assignedQuery = "
SELECT course_tas.id, users.name
FROM course_tas
JOIN users
ON course_tas.ta_id = users.id
WHERE course_tas.course_id = ?
";

$stmtAssigned = $conn->prepare($assignedQuery);
$stmtAssigned->bind_param("i", $courseId);
$stmtAssigned->execute();
$assignedTas = $stmtAssigned->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $taId = (int) $_POST['ta_id'];

    $checkQuery = "
    SELECT id
    FROM course_tas
    WHERE course_id = ?
    AND ta_id = ?
    ";

    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->bind_param("ii", $courseId, $taId);
    $stmtCheck->execute();
    $checkResult = $stmtCheck->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "TA already assigned to this course.";
    }

    else {

        $query = "
        INSERT INTO course_tas
        (
            course_id,
            ta_id
        )
        VALUES
        (
            ?,
            ?
        )
        ";

        $stmt = $conn->prepare($query);

        $stmt->bind_param(
            "ii",
            $courseId,
            $taId
        );

        if ($stmt->execute()) {

            $success = "TA Assigned Successfully";
            $stmtAssigned->execute();
            $assignedTas = $stmtAssigned->get_result();
        }

        else {
            $error = "Failed to assign TA.";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign TA</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>
<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Assign Teaching Assistant</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Choose a TA for course support.</p>
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
                    <label for="ta_id">Select TA</label>
                    <select id="ta_id" name="ta_id" required>
                        <option value="">Choose a TA</option>
                        <?php while($ta = $tas->fetch_assoc()) { ?>
                            <option value="<?php echo $ta['id']; ?>">
                                <?php echo htmlspecialchars($ta['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit">Assign TA</button>
                <a class="btn secondary" href="course_details.php?id=<?php echo $courseId; ?>">Back to Course</a>
            </div>

        </form>

    </div>

    <div class="card">
        <h2>Assigned TAs</h2>

        <?php if ($assignedTas->num_rows == 0) { ?>
            <div class="empty-state">No TA assigned yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Name</th>
                </tr>
                <?php while($assigned = $assignedTas->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assigned['name']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>
</html>