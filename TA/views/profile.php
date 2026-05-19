<!DOCTYPE html>
<html>

<head>
    <title>TA Profile</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/instructor.css">
</head>

<body class="ta-page">

<nav class="navbar">
    <a href="<?php echo $baseUrl; ?>/ta/dashboard">Dashboard</a>
    <a href="<?php echo $baseUrl; ?>/ta/assigned-courses">Assigned Courses</a>
    <a href="<?php echo $baseUrl; ?>/ta/doubt-sessions">Doubt Sessions</a>
    <a href="<?php echo $baseUrl; ?>/ta/bookings">Bookings</a>
    <a href="<?php echo $baseUrl; ?>/ta/profile">Profile</a>
    <a href="<?php echo $baseUrl; ?>/auth/logout">Logout</a>
</nav>

<div class="container">
    <div class="page-header">
        <div class="title-block">
            <h1>TA Profile</h1>
            <p>Keep your profile updated for students.</p>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <div class="profile-card">
            <div class="profile-preview">
                <?php if (!empty($profilePictureUrl)) { ?>
                    <img class="profile-image" src="<?php echo htmlspecialchars($profilePictureUrl); ?>" alt="Profile">
                <?php } else { ?>
                    <div class="empty-state">No profile photo</div>
                <?php } ?>
                <p>Upload a professional photo for course pages.</p>
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
                    <a class="btn secondary" href="<?php echo $baseUrl; ?>/ta/dashboard">Back to Dashboard</a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>

</html>
