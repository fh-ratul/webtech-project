<!DOCTYPE html>
<html>

<head>
    <title>Doubt Sessions</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/instructor.css">
</head>

<body class="ta-page">

<nav class="navbar">
    <a href="<?php echo $baseUrl; ?>/ta/dashboard">Dashboard</a>
    <a href="<?php echo $baseUrl; ?>/ta/assigned-courses">Assigned Courses</a>
    <a href="<?php echo $baseUrl; ?>/ta/profile">Profile</a>
    <a href="<?php echo $baseUrl; ?>/auth/logout">Logout</a>
</nav>

<div class="container">
    <div class="page-header">
        <div class="title-block">
            <h1>Doubt Sessions</h1>
            <p>Schedule and manage upcoming sessions.</p>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <a class="btn" href="<?php echo $baseUrl; ?>/ta/assigned-courses">Choose Course to Schedule</a>
    </div>

    <div class="card">
        <h2>Upcoming & Past Sessions</h2>

        <?php if ($sessions->num_rows == 0) { ?>
            <div class="empty-state">No sessions created yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while ($session = $sessions->fetch_assoc()) { ?>
                    <div class="list-item">
                        <h3><?php echo htmlspecialchars($session['title']); ?></h3>
                        <p class="muted">Course: <?php echo htmlspecialchars($session['course_title']); ?></p>
                        <p><strong>Scheduled:</strong> <?php echo htmlspecialchars($session['scheduled_at']); ?></p>
                        <p><strong>Duration:</strong> <?php echo (int) $session['duration_minutes']; ?> min</p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($session['location_or_link']); ?></p>
                        <p><strong>Max Attendees:</strong> <?php echo (int) $session['max_attendees']; ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($session['status']); ?></p>
                        <?php if (!empty($session['notice'])) { ?>
                            <p class="pending">Notice: <?php echo htmlspecialchars($session['notice']); ?></p>
                        <?php } ?>

                        <form method="POST" class="inline-form" onsubmit="return confirm('Cancel this session?');">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="session_id" value="<?php echo (int) $session['id']; ?>">
                            <div class="form-field">
                                <label>Cancellation Notice</label>
                                <input type="text" name="notice" placeholder="Optional notice">
                            </div>
                            <button type="submit" class="btn secondary">Cancel Session</button>
                        </form>

                        <form method="POST" class="inline-form">
                            <input type="hidden" name="action" value="reschedule">
                            <input type="hidden" name="session_id" value="<?php echo (int) $session['id']; ?>">
                            <div class="form-grid">
                                <div class="form-field">
                                    <label>New Date/Time</label>
                                    <input type="datetime-local" name="scheduled_at" required>
                                </div>
                                <div class="form-field">
                                    <label>Notice</label>
                                    <input type="text" name="notice" placeholder="Reason or notice">
                                </div>
                            </div>
                            <button type="submit">Reschedule</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

</body>

</html>
