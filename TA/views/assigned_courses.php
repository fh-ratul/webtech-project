<!DOCTYPE html>
<html>

<head>
    <title>Assigned Courses</title>
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
    <div class="card">
        <h1>Assigned Courses</h1>
        <?php if ($courses->num_rows == 0) { ?>
            <div class="empty-state">No assigned courses yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while ($course = $courses->fetch_assoc()) { ?>
                    <div class="list-item">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <p class="muted">Subject: <?php echo htmlspecialchars($course['subject_name']); ?></p>
                        <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $course['id']; ?>">Open Course</a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

</body>

</html>
