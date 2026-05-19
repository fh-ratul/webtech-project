<!DOCTYPE html>
<html>

<head>
    <title>Create Practice Quiz</title>
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
            <h1>Create Practice Quiz</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Create practice quizzes for students.</p>
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
                    <label for="title">Quiz Title</label>
                    <input id="title" type="text" name="title" required>
                </div>
                <div class="form-field">
                    <label>Quiz Type</label>
                    <input type="text" value="Practice" disabled>
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" required></textarea>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="time_limit">Time Limit (minutes)</label>
                    <input id="time_limit" type="number" min="1" name="time_limit" required>
                </div>
                <div class="form-field">
                    <label for="total_marks">Total Marks</label>
                    <input id="total_marks" type="number" min="1" name="total_marks" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="pass_mark">Pass Mark</label>
                    <input id="pass_mark" type="number" min="0" name="pass_mark" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="available_from">Available From</label>
                    <input id="available_from" type="datetime-local" name="available_from">
                </div>
                <div class="form-field">
                    <label for="available_until">Available Until</label>
                    <input id="available_until" type="datetime-local" name="available_until">
                </div>
            </div>

            <div class="form-footer">
                <button type="submit">Create Practice Quiz</button>
                <a class="btn secondary" href="<?php echo $baseUrl; ?>/ta/question-bank?course_id=<?php echo $courseId; ?>">Question Bank</a>
            </div>
        </form>
    </div>
</div>

</body>

</html>
