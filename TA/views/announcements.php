<!DOCTYPE html>
<html>

<head>
    <title>Announcements</title>
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
            <h1>Announcements</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Share updates with students.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <form method="POST" class="inline-form">
            <input type="hidden" name="create_announcement" value="1">

            <div class="form-grid">
                <div class="form-field">
                    <label for="title">Title</label>
                    <input id="title" type="text" name="title" required>
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label for="body">Announcement</label>
                    <textarea id="body" name="body" rows="4" required></textarea>
                </div>
            </div>

            <button type="submit">Post Announcement</button>
        </form>
    </div>

    <div class="card">
        <h2>Recent Announcements</h2>

        <?php if ($announcements->num_rows == 0) { ?>
            <div class="empty-state">No announcements posted yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while ($announcement = $announcements->fetch_assoc()) { ?>
                    <div class="list-item">
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <p><?php echo htmlspecialchars($announcement['body']); ?></p>
                        <p class="muted">Role: <?php echo htmlspecialchars($announcement['posted_role'] ?? 'ta'); ?> • <?php echo htmlspecialchars($announcement['created_at']); ?></p>
                        <?php if (($announcement['posted_role'] ?? 'ta') === 'ta') { ?>
                            <a class="btn secondary" href="<?php echo $baseUrl; ?>/ta/announcements?course_id=<?php echo $courseId; ?>&delete=<?php echo $announcement['id']; ?>" onclick="return confirm('Delete this announcement?');">Delete</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

</body>

</html>
