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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_material'])) {

    $title = trim($_POST['title']);
    $materialType = $_POST['material_type'];
    $link = trim($_POST['link']);

    if (empty($title)) {
        $error = "Material title is required.";
    } else {

        $filePath = "";

        if ($materialType == 'file') {
            if (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] != 0) {
                $error = "Please upload a file.";
            } else {
                $fileName = time() . "_" . basename($_FILES['material_file']['name']);
                $targetPath = "../uploads/" . $fileName;

                if (move_uploaded_file($_FILES['material_file']['tmp_name'], $targetPath)) {
                    $filePath = "uploads/" . $fileName;
                } else {
                    $error = "File upload failed.";
                }
            }
        }

        if ($materialType == 'link') {
            if (empty($link)) {
                $error = "Please provide a link.";
            } else {
                $filePath = $link;
            }
        }

        if (empty($error)) {
            $insertQuery = "
            INSERT INTO course_materials
            (course_id, title, file_path, material_type)
            VALUES
            (?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("isss", $courseId, $title, $filePath, $materialType);

            if ($stmt->execute()) {
                $success = "Material uploaded.";
            } else {
                $error = "Failed to upload material.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_material'])) {

    $materialId = (int) $_POST['material_id'];
    $title = trim($_POST['title']);
    $materialType = $_POST['material_type'];
    $link = trim($_POST['link']);

    $filePath = $_POST['existing_path'];

    if ($materialType == 'file' && isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $fileName = time() . "_" . basename($_FILES['material_file']['name']);
        $targetPath = "../uploads/" . $fileName;

        if (move_uploaded_file($_FILES['material_file']['tmp_name'], $targetPath)) {
            $filePath = "uploads/" . $fileName;
        }
    }

    if ($materialType == 'link' && !empty($link)) {
        $filePath = $link;
    }

    $updateQuery = "
    UPDATE course_materials
    SET title = ?, file_path = ?, material_type = ?
    WHERE id = ?
    AND course_id = ?
    ";

    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("sssii", $title, $filePath, $materialType, $materialId, $courseId);

    if ($stmtUpdate->execute()) {
        $success = "Material updated.";
    } else {
        $error = "Failed to update material.";
    }
}

if (isset($_GET['delete'])) {

    $materialId = (int) $_GET['delete'];

    $fileQuery = "SELECT file_path, material_type FROM course_materials WHERE id = ? AND course_id = ?";
    $stmtFile = $conn->prepare($fileQuery);
    $stmtFile->bind_param("ii", $materialId, $courseId);
    $stmtFile->execute();
    $fileResult = $stmtFile->get_result();

    if ($fileResult->num_rows > 0) {
        $fileRow = $fileResult->fetch_assoc();

        if ($fileRow['material_type'] == 'file' && !empty($fileRow['file_path'])) {
            $baseDir = realpath(__DIR__ . "/..");
            $fullPath = realpath($baseDir . "/" . $fileRow['file_path']);

            if ($fullPath !== false && strpos($fullPath, $baseDir) === 0 && file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    $deleteQuery = "DELETE FROM course_materials WHERE id = ? AND course_id = ?";
    $stmtDelete = $conn->prepare($deleteQuery);
    $stmtDelete->bind_param("ii", $materialId, $courseId);

    if ($stmtDelete->execute()) {
        $success = "Material deleted.";
    } else {
        $error = "Failed to delete material.";
    }
}

$listQuery = "
SELECT id, title, file_path, material_type
FROM course_materials
WHERE course_id = ?
ORDER BY id DESC
";

$stmtList = $conn->prepare($listQuery);
$stmtList->bind_param("i", $courseId);
$stmtList->execute();
$materials = $stmtList->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Course Materials</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Course Materials</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Upload files or share resources.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="course_details.php?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <form method="POST" enctype="multipart/form-data" class="inline-form">
            <input type="hidden" name="create_material" value="1">

            <div class="form-grid">
                <div class="form-field">
                    <label for="title">Material Title</label>
                    <input id="title" type="text" name="title" required>
                </div>
                <div class="form-field">
                    <label for="material_type">Material Type</label>
                    <select id="material_type" name="material_type" required>
                        <option value="file">File Upload</option>
                        <option value="link">External Link</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="material_file">Upload File</label>
                    <input id="material_file" type="file" name="material_file">
                </div>
                <div class="form-field">
                    <label for="link">External Link</label>
                    <input id="link" type="url" name="link" placeholder="https://...">
                </div>
            </div>

            <button type="submit">Add Material</button>
        </form>
    </div>

    <div class="card">
        <h2>Uploaded Materials</h2>

        <?php if ($materials->num_rows == 0) { ?>
            <div class="empty-state">No materials uploaded yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while($material = $materials->fetch_assoc()) { ?>
                    <div class="list-item">
                        <form method="POST" enctype="multipart/form-data" class="inline-form">
                            <input type="hidden" name="update_material" value="1">
                            <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                            <input type="hidden" name="existing_path" value="<?php echo htmlspecialchars($material['file_path']); ?>">

                            <div class="form-grid">
                                <div class="form-field">
                                    <label>Title</label>
                                    <input type="text" name="title" value="<?php echo htmlspecialchars($material['title']); ?>" required>
                                </div>
                                <div class="form-field">
                                    <label>Type</label>
                                    <select name="material_type" required>
                                        <option value="file" <?php if ($material['material_type'] == 'file') { echo "selected"; } ?>>File</option>
                                        <option value="link" <?php if ($material['material_type'] == 'link') { echo "selected"; } ?>>Link</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-field">
                                    <label>Replace File</label>
                                    <input type="file" name="material_file">
                                </div>
                                <div class="form-field">
                                    <label>External Link</label>
                                    <input type="url" name="link" value="<?php echo htmlspecialchars($material['material_type'] == 'link' ? $material['file_path'] : ""); ?>" placeholder="https://...">
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="submit">Save Changes</button>
                                <a class="btn secondary" href="materials.php?course_id=<?php echo $courseId; ?>&delete=<?php echo $material['id']; ?>" onclick="return confirm('Delete this material?');">Delete</a>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

</body>

</html>
