<!DOCTYPE html>
<html>

<head>
    <title>TA Dashboard</title>
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
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
            <p>Manage your assigned courses and student support tasks.</p>
        </div>
    </div>

    <div class="card split thirds">
        <div class="stat-card">
            <h3>Assigned Courses</h3>
            <div class="stat-value"><?php echo (int) ($stats['total_courses'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Active Students</h3>
            <div class="stat-value"><?php echo (int) ($studentStats['total_students'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Upcoming Sessions</h3>
            <div class="stat-value"><?php echo (int) ($sessionStats['upcoming_sessions'] ?? 0); ?></div>
        </div>
    </div>

    <div class="card">
        <h2>Assigned Courses</h2>

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