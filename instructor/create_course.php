<?php

include("../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'instructor') {
    die("Access Denied");
}

$instructorId = $_SESSION['user_id'];
$success = "";
$error = "";

$subjectsQuery = "SELECT id, name FROM subjects ORDER BY name";
$subjects = $conn->query($subjectsQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = trim($_POST['title']);
    $subjectId = (int) $_POST['subject_id'];
    $description = trim($_POST['description']);
    $enrollmentType = $_POST['enrollment_type'];
    $maxStudents = (int) $_POST['max_students'];
    $statusInput = $_POST['status'];

    if (
        empty($title) ||
        empty($description) ||
        empty($enrollmentType) ||
        $subjectId <= 0 ||
        $maxStudents <= 0
    ) {

        $error = "All fields are required.";
    }

    else {

        $status = ($statusInput == 'draft') ? 'draft' : 'active';

        $insertQuery = "
        INSERT INTO courses
        (
            title,
            subject_id,
            description,
            enrollment_type,
            max_students,
            status,
            instructor_id
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
        ";

        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param(
            "sissisi",
            $title,
            $subjectId,
            $description,
            $enrollmentType,
            $maxStudents,
            $status,
            $instructorId
        );

        if ($stmt->execute()) {
            $success = "Course created successfully.";
        }

        else {
            $error = "Failed to create course.";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Create Course</title>

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
            <h1>Create New Course</h1>
            <p>Design the next learning experience with clear enrollment controls.</p>
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
                    required
                    >
                </div>

                <div class="form-field">
                    <label for="subject">Subject</label>
                    <select id="subject" name="subject_id" required>
                        <option value="">Select a subject</option>
                        <?php while($subject = $subjects->fetch_assoc()) { ?>
                            <option value="<?php echo $subject['id']; ?>">
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
                    ></textarea>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="enrollment">Enrollment Type</label>
                    <select id="enrollment" name="enrollment_type" required>
                        <option value="open">Open Enrollment</option>
                        <option value="approval">Approval Required</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="max_students">Maximum Students</label>
                    <input
                    id="max_students"
                    type="number"
                    min="1"
                    name="max_students"
                    required
                    >
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="status">Save Mode</label>
                    <select id="status" name="status" required>
                        <option value="draft">Save as Draft</option>
                        <option value="published">Publish Now</option>
                    </select>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit">Create Course</button>
                <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
            </div>

        </form>

    </div>

</div>

</body>

</html>
