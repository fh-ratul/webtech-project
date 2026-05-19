<!DOCTYPE html>
<html>

<head>

    <title>My Profile</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h1>My Profile</h1>

        <p class="success">
            <?php echo $success; ?>
        </p>

        <p class="error">
            <?php echo $error; ?>
        </p>

        <?php if (!empty($user['profile_pic'])) { ?>

            <img
            class="profile-image"
            src="<?php echo APP_BASE_URL; ?>/<?php echo $user['profile_pic']; ?>"
            >

        <?php } ?>

        <form
        method="POST"
        enctype="multipart/form-data"
        >

            <label>Full Name</label>

            <input
            type="text"
            name="name"
            value="<?php echo $user['name']; ?>"
            required
            >

            <label>Email</label>

            <input
            type="email"
            value="<?php echo $user['email']; ?>"
            disabled
            >

            <label>Phone</label>

            <input
            type="text"
            name="phone"
            value="<?php echo $user['phone']; ?>"
            >

            <label>Student ID</label>

            <input
            type="text"
            value="<?php echo $user['student_id']; ?>"
            disabled
            >

            <label>Program</label>

            <input
            type="text"
            name="program"
            value="<?php echo $user['program']; ?>"
            >

            <label>Profile Picture</label>

            <input
            type="file"
            name="profile_pic"
            >

            <button type="submit">
                Update Profile
            </button>

        </form>

        <br>

        <a
        class="btn"
        href="<?php echo APP_BASE_URL; ?>/auth/change-password"
        >
            Change Password
        </a>

    </div>

</div>

</body>

</html>
