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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $bio = trim($_POST['bio']);

    $profilePicture = "";

    if (
        isset($_FILES['profile_pic']) &&
        $_FILES['profile_pic']['error'] == 0
    ) {

        $fileName =
        time() . "_" .
        basename($_FILES['profile_pic']['name']);

        move_uploaded_file(
            $_FILES['profile_pic']['tmp_name'],
            "../uploads/" . $fileName
        );

        $profilePicture = "uploads/" . $fileName;

        $query = "
        UPDATE users
        SET
        name = ?,
        department = ?,
        bio = ?,
        profile_pic = ?
        WHERE id = ?
        ";

        $stmt = $conn->prepare($query);

        $stmt->bind_param(
            "ssssi",
            $name,
            $department,
            $bio,
            $profilePicture,
            $instructorId
        );
    }

    else {

        $query = "
        UPDATE users
        SET
        name = ?,
        department = ?,
        bio = ?
        WHERE id = ?
        ";

        $stmt = $conn->prepare($query);

        $stmt->bind_param(
            "sssi",
            $name,
            $department,
            $bio,
            $instructorId
        );
    }

    if ($stmt->execute()) {

        $_SESSION['name'] = $name;

        $success = "Profile Updated";
    }

    else {

        $error = "Update Failed";
    }
}

$userQuery = "SELECT * FROM users WHERE id = ?";

$stmt2 = $conn->prepare($userQuery);
$stmt2->bind_param("i", $instructorId);
$stmt2->execute();

$user = $stmt2
->get_result()
->fetch_assoc();

$profilePictureUrl = "";
if (!empty($user['profile_pic'])) {
    $profilePictureUrl = "../" . $user['profile_pic'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Profile</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>
<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Instructor Profile</h1>
            <p>Keep your academic profile updated for students.</p>
        </div>
    </div>

    <div class="card">

        <p class="success">
            <?php echo $success; ?>
        </p>

        <p class="error">
            <?php echo $error; ?>
        </p>

        <div class="profile-card">
            <div class="profile-preview">
                <?php if (!empty($profilePictureUrl)) { ?>
                    <img class="profile-image" src="<?php echo htmlspecialchars($profilePictureUrl); ?>" alt="Profile">
                <?php } else { ?>
                    <div class="empty-state">No profile photo</div>
                <?php } ?>
                <p>Upload a professional photo for your course pages.</p>
            </div>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-grid">
                    <div class="form-field">
                        <label for="name">Name</label>
                        <input
                        id="name"
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($user['name']); ?>"
                        required
                        >
                    </div>

                    <div class="form-field">
                        <label for="department">Department</label>
                        <input
                        id="department"
                        type="text"
                        name="department"
                        value="<?php echo htmlspecialchars($user['department']); ?>"
                        placeholder="Department"
                        >
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-field">
                        <label for="bio">Bio</label>
                        <textarea
                        id="bio"
                        name="bio"
                        rows="5"
                        ><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="profile_pic">Profile Picture</label>
                        <input
                        id="profile_pic"
                        type="file"
                        name="profile_pic"
                        accept="image/*"
                        >
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit">Update Profile</button>
                    <a class="btn secondary" href="dashboard.php">Back to Dashboard</a>
                </div>

            </form>
        </div>

    </div>

</div>

</body>
</html>