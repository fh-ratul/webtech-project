<!DOCTYPE html>
<html>

<head>
    <title>Create Doubt Session</title>
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
            <h1>Create Doubt Session</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Schedule support sessions for students.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <form method="POST">
            <div class="form-grid">
                <div class="form-field">
                    <label for="title">Session Title</label>
                    <input id="title" type="text" name="title" required>
                </div>
                <div class="form-field">
                    <label for="scheduled_at">Scheduled At</label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="duration_minutes">Duration (minutes)</label>
                    <input id="duration_minutes" type="number" min="1" name="duration_minutes" required>
                </div>
                <div class="form-field">
                    <label for="max_attendees">Max Attendees</label>
                    <input id="max_attendees" type="number" min="1" name="max_attendees" required>
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label for="location_or_link">Location / Meeting Link</label>
                    <input id="location_or_link" type="text" name="location_or_link" required>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit">Create Session</button>
                <a class="btn secondary" href="<?php echo $baseUrl; ?>/ta/doubt-sessions">View Sessions</a>
            </div>
        </form>
    </div>
</div>

</body>

</html>
