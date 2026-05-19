<?php include __DIR__ . '/partials/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="../../public/css/base.css">
    <link rel="stylesheet" href="../../public/css/instructor.css">
</head>

<body class="instructor-page">

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Welcome, <?= htmlspecialchars($instructorName) ?></h1>
            <p>Manage courses, approvals, and teaching operations.</p>
        </div>
        <a class="btn" href="../controllers/CourseController.php?action=create">Create New Course</a>
    </div>

    <div class="card hero">
        <div>
            <h2>Instructor Control Panel</h2>
            <p>Track active offerings, drafts, and student approvals in one place.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Courses</h3>
                <div class="stat-value"><?= (int) $stats['total_courses'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Courses</h3>
                <div class="stat-value"><?= (int) $stats['active_courses'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Drafts</h3>
                <div class="stat-value"><?= (int) $stats['draft_courses'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Requests</h3>
                <div class="stat-value"><?= $pendingRequests ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>My Courses</h2>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                No courses yet. Create your first course to get started.
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <?php
                        $statusClass = "";
                        if ($course['status'] == 'draft') {
                            $statusClass = "draft";
                        } elseif ($course['status'] == 'archived') {
                            $statusClass = "archived";
                        }
                    ?>

                    <div class="course-card">
                        <div class="badge <?= $statusClass ?>">
                            <?= htmlspecialchars($course['status']) ?>
                        </div>

                        <h3><?= htmlspecialchars($course['title']) ?></h3>
                        <p><?= htmlspecialchars($course['description']) ?></p>

                        <div class="course-meta">
                            <span>Subject: <?= htmlspecialchars($course['subject_name']) ?></span>
                            <span>Enrollment: <?= htmlspecialchars($course['enrollment_type']) ?></span>
                            <span>Max: <?= (int) $course['max_students'] ?></span>
                        </div>

                        <div class="action-row">
                            <a class="btn" href="../controllers/CourseController.php?action=details&id=<?= $course['id'] ?>">
                                Manage Course
                            </a>
                            <a class="btn secondary" href="../controllers/QuizController.php?action=create&course_id=<?= $course['id'] ?>">
                                Create Quiz
                            </a>
                            <a class="btn" href="../controllers/TAController.php?course_id=<?= $course['id'] ?>">
                                Assign TA
                            </a>
                            <a class="btn" href="../controllers/EnrollmentController.php?course_id=<?= $course['id'] ?>">
                                Enrollment Requests
                            </a>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>

</html>
