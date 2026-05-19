<!DOCTYPE html>
<html>

<head>
    <title>Course Details</title>
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
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p><?php echo htmlspecialchars($course['subject_name']); ?></p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/assigned-courses">Back to Courses</a>
        </div>
    </div>

    <div class="card split thirds">
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/announcements?course_id=<?php echo $courseId; ?>">
            <h3>Announcements</h3>
            <div class="stat-value">Manage</div>
        </a>
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/materials?course_id=<?php echo $courseId; ?>">
            <h3>Materials</h3>
            <div class="stat-value">Upload</div>
        </a>
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/qa-board?course_id=<?php echo $courseId; ?>">
            <h3>Q&A Board</h3>
            <div class="stat-value">Answer</div>
        </a>
    </div>

    <div class="card split thirds">
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/create-practice-quiz?course_id=<?php echo $courseId; ?>">
            <h3>Practice Quiz</h3>
            <div class="stat-value">Create</div>
        </a>
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/question-bank?course_id=<?php echo $courseId; ?>">
            <h3>Question Bank</h3>
            <div class="stat-value">Manage</div>
        </a>
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/student-results?course_id=<?php echo $courseId; ?>">
            <h3>Student Results</h3>
            <div class="stat-value">View</div>
        </a>
    </div>

    <div class="card split thirds">
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/at-risk-students?course_id=<?php echo $courseId; ?>">
            <h3>At-Risk Students</h3>
            <div class="stat-value">Flag</div>
        </a>
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/create-doubt-session?course_id=<?php echo $courseId; ?>">
            <h3>Doubt Session</h3>
            <div class="stat-value">Schedule</div>
        </a>
        <a class="stat-card" href="<?php echo $baseUrl; ?>/ta/reports?course_id=<?php echo $courseId; ?>">
            <h3>Summary Report</h3>
            <div class="stat-value">View</div>
        </a>
    </div>

    <div class="card">
        <h2>Enrolled Students</h2>

        <?php if ($students->num_rows == 0) { ?>
            <div class="empty-state">No enrolled students yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
                <?php while ($student = $students->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['status']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

    <div class="card">
        <h2>Quizzes</h2>

        <?php if ($quizzes->num_rows == 0) { ?>
            <div class="empty-state">No quizzes created yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Total Marks</th>
                    <th>Available Until</th>
                </tr>
                <?php while ($quiz = $quizzes->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['status']); ?></td>
                        <td><?php echo (int) $quiz['total_marks']; ?></td>
                        <td><?php echo htmlspecialchars($quiz['available_until']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

</div>

</body>

</html>
