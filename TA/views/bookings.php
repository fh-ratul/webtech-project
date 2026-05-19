<!DOCTYPE html>
<html>

<head>
    <title>Doubt Session Bookings</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/instructor.css">
</head>

<body class="ta-page">

<nav class="navbar">
    <a href="<?php echo $baseUrl; ?>/ta/dashboard">Dashboard</a>
    <a href="<?php echo $baseUrl; ?>/ta/assigned-courses">Assigned Courses</a>
    <a href="<?php echo $baseUrl; ?>/ta/doubt-sessions">Doubt Sessions</a>
    <a href="<?php echo $baseUrl; ?>/ta/profile">Profile</a>
    <a href="<?php echo $baseUrl; ?>/auth/logout">Logout</a>
</nav>

<div class="container">
    <div class="card">
        <h1>Doubt Session Bookings</h1>

        <?php if ($bookings->num_rows == 0) { ?>
            <div class="empty-state">No bookings yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Course</th>
                    <th>Session</th>
                    <th>Scheduled At</th>
                    <th>Status</th>
                    <th>Student</th>
                    <th>Booked At</th>
                </tr>

                <?php while ($booking = $bookings->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['course_title']); ?></td>
                        <td><?php echo htmlspecialchars($booking['title']); ?></td>
                        <td><?php echo htmlspecialchars($booking['scheduled_at']); ?></td>
                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
                        <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booked_at']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
</div>

</body>

</html>
