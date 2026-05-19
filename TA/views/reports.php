<!DOCTYPE html>
<html>

<head>
    <title>Course Summary</title>
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
            <h1>Course Summary</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Engagement and results snapshot.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card split thirds">
        <div class="stat-card">
            <h3>Enrolled Students</h3>
            <div class="stat-value"><?php echo $activeCount; ?></div>
        </div>
        <div class="stat-card">
            <h3>Dropouts</h3>
            <div class="stat-value"><?php echo $droppedCount; ?></div>
        </div>
        <div class="stat-card">
            <h3>Drop-out Rate</h3>
            <div class="stat-value"><?php echo number_format($dropRate, 2); ?>%</div>
        </div>
    </div>

    <div class="card">
        <h2>Quiz Completion & Averages</h2>

        <?php if ($quizReports->num_rows == 0) { ?>
            <div class="empty-state">No quizzes created yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Quiz</th>
                    <th>Students Attempted</th>
                    <th>Completion Rate</th>
                    <th>Average Score</th>
                </tr>

                <?php while ($quiz = $quizReports->fetch_assoc()) { ?>
                    <?php
                        $attempted = (int) $quiz['students_attempted'];
                        $completionRate = $activeCount > 0 ? ($attempted / $activeCount) * 100 : 0;
                        $avgScore = $quiz['avg_score'] !== null ? number_format($quiz['avg_score'], 2) : "0.00";
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo $attempted; ?></td>
                        <td><?php echo number_format($completionRate, 2); ?>%</td>
                        <td><?php echo $avgScore; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>

</html>
