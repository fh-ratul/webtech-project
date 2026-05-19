<?php
$baseUrl = defined("APP_BASE_URL") ? APP_BASE_URL : "";
$studentUrl = static fn (string $path): string => $baseUrl . $path;
$assetUrl = static fn (string $path): string => $baseUrl . $path;
$e = static fn ($value): string => htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
$formatDate = static function ($value) use ($e): string {
    if (empty($value)) {
        return "No deadline";
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date("M d, Y, h:i A", $timestamp) : $e($value);
};

$studentName = $_SESSION["name"] ?? "Student";
$totalCourseCount = (int) ($totalCourses["total_courses"] ?? 0);
$totalAttempts = (int) ($dashboardMetrics["total_attempts"] ?? 0);
$averageScore = (float) ($dashboardMetrics["average_score"] ?? 0);
$passedAttempts = (int) ($dashboardMetrics["passed_attempts"] ?? 0);
$bookedSessions = (int) ($dashboardMetrics["booked_sessions"] ?? 0);
$passRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100) : 0;
$announcementCount = $announcements instanceof mysqli_result ? $announcements->num_rows : 0;
$courseCount = $courses instanceof mysqli_result ? $courses->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="<?php echo $assetUrl("/assets/css/base.css"); ?>">
    <link rel="stylesheet" href="<?php echo $assetUrl("/assets/css/student.css"); ?>">
</head>

<body class="student-page student-dashboard-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<main class="student-dashboard">

    <section class="dashboard-hero" aria-labelledby="dashboard-title">
        <div class="dashboard-hero__content">
            <span class="dashboard-kicker">Student Dashboard</span>
            <h1 id="dashboard-title">
                Welcome back, <?php echo $e($studentName); ?>
            </h1>
            <p>
                Keep an eye on quiz deadlines, course updates, performance, and sessions from one focused workspace.
            </p>
        </div>

        <div class="dashboard-hero__actions">
            <a class="btn" href="<?php echo $studentUrl("/student/courses"); ?>">
                Browse Courses
            </a>
            <a class="btn secondary" href="<?php echo $studentUrl("/student/performance"); ?>">
                View Performance
            </a>
        </div>
    </section>

    <section class="dashboard-stats" aria-label="Dashboard stats">
        <article class="dashboard-stat dashboard-stat--courses">
            <span class="dashboard-stat__label">Active Courses</span>
            <strong><?php echo $totalCourseCount; ?></strong>
            <span><?php echo $courseCount; ?> course<?php echo $courseCount === 1 ? "" : "s"; ?> listed below</span>
        </article>

        <article class="dashboard-stat dashboard-stat--score">
            <span class="dashboard-stat__label">Average Score</span>
            <strong><?php echo number_format($averageScore, 1); ?></strong>
            <span>Across <?php echo $totalAttempts; ?> attempt<?php echo $totalAttempts === 1 ? "" : "s"; ?></span>
        </article>

        <article class="dashboard-stat dashboard-stat--pass">
            <span class="dashboard-stat__label">Pass Rate</span>
            <strong><?php echo $passRate; ?>%</strong>
            <span><?php echo $passedAttempts; ?> passed attempt<?php echo $passedAttempts === 1 ? "" : "s"; ?></span>
        </article>

        <article class="dashboard-stat dashboard-stat--sessions">
            <span class="dashboard-stat__label">Booked Sessions</span>
            <strong><?php echo $bookedSessions; ?></strong>
            <span>Doubt session booking<?php echo $bookedSessions === 1 ? "" : "s"; ?></span>
        </article>
    </section>

    <section class="dashboard-overview" aria-label="Dashboard overview">
        <article class="dashboard-panel dashboard-panel--featured">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-kicker">Next Deadline</span>
                    <h2>Upcoming Quiz</h2>
                </div>
                <?php if ($upcomingQuiz) { ?>
                    <span class="status-pill status-pill--active">Published</span>
                <?php } ?>
            </div>

            <?php if ($upcomingQuiz) { ?>
                <div class="quiz-highlight">
                    <h3><?php echo $e($upcomingQuiz["title"]); ?></h3>
                    <p><?php echo $e($upcomingQuiz["course_title"]); ?></p>

                    <dl class="dashboard-meta-grid">
                        <div>
                            <dt>Deadline</dt>
                            <dd><?php echo $formatDate($upcomingQuiz["available_until"] ?? null); ?></dd>
                        </div>
                        <div>
                            <dt>Marks</dt>
                            <dd><?php echo (float) ($upcomingQuiz["total_marks"] ?? 0); ?></dd>
                        </div>
                        <div>
                            <dt>Time Limit</dt>
                            <?php $timeLimit = (int) ($upcomingQuiz["time_limit_minutes"] ?? 0); ?>
                            <dd><?php echo $timeLimit > 0 ? $timeLimit . " min" : "No limit"; ?></dd>
                        </div>
                    </dl>

                    <a class="btn" href="<?php echo $studentUrl("/student/take-quiz?id=" . (int) $upcomingQuiz["id"]); ?>">
                        Start Quiz
                    </a>
                </div>
            <?php } else { ?>
                <div class="dashboard-empty">
                    <h3>No upcoming quiz</h3>
                    <p>Your published quizzes with open deadlines will appear here.</p>
                    <a class="btn secondary" href="<?php echo $studentUrl("/student/courses"); ?>">
                        Review Courses
                    </a>
                </div>
            <?php } ?>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-kicker">Latest Result</span>
                    <h2>Recent Attempt</h2>
                </div>
            </div>

            <?php if ($recentAttempt) { ?>
                <?php $recentPassed = (float) $recentAttempt["score"] >= (float) $recentAttempt["pass_mark"]; ?>
                <div class="dashboard-summary">
                    <h3><?php echo $e($recentAttempt["title"]); ?></h3>
                    <p><?php echo $e($recentAttempt["course_title"]); ?></p>
                    <strong class="dashboard-score">
                        <?php echo (float) $recentAttempt["score"]; ?> / <?php echo (float) $recentAttempt["total_marks"]; ?>
                    </strong>
                    <span class="status-pill <?php echo $recentPassed ? "status-pill--success" : "status-pill--danger"; ?>">
                        <?php echo $recentPassed ? "Passed" : "Needs Review"; ?>
                    </span>
                    <a class="dashboard-link" href="<?php echo $studentUrl("/student/quiz-result?attempt_id=" . (int) $recentAttempt["id"]); ?>">
                        View result
                    </a>
                </div>
            <?php } else { ?>
                <div class="dashboard-empty dashboard-empty--compact">
                    <h3>No attempts yet</h3>
                    <p>Quiz results will show up after your first submission.</p>
                </div>
            <?php } ?>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-kicker">Support</span>
                    <h2>Next Session</h2>
                </div>
            </div>

            <?php if ($nextDoubtSession) { ?>
                <div class="dashboard-summary">
                    <h3><?php echo $e($nextDoubtSession["title"]); ?></h3>
                    <p><?php echo $e($nextDoubtSession["course_title"]); ?> with <?php echo $e($nextDoubtSession["ta_name"]); ?></p>
                    <dl class="dashboard-meta-list">
                        <div>
                            <dt>When</dt>
                            <dd><?php echo $formatDate($nextDoubtSession["scheduled_at"] ?? null); ?></dd>
                        </div>
                        <div>
                            <dt>Duration</dt>
                            <dd><?php echo (int) ($nextDoubtSession["duration_minutes"] ?? 0); ?> min</dd>
                        </div>
                    </dl>
                    <a class="dashboard-link" href="<?php echo $studentUrl("/student/doubt-sessions"); ?>">
                        View sessions
                    </a>
                </div>
            <?php } else { ?>
                <div class="dashboard-empty dashboard-empty--compact">
                    <h3>No session booked</h3>
                    <p>Booked doubt sessions will appear here.</p>
                    <a class="dashboard-link" href="<?php echo $studentUrl("/student/doubt-sessions"); ?>">
                        Check sessions
                    </a>
                </div>
            <?php } ?>
        </article>
    </section>

    <section class="dashboard-columns" aria-label="Courses and announcements">
        <article class="dashboard-panel dashboard-panel--wide">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-kicker"><?php echo $announcementCount; ?> Update<?php echo $announcementCount === 1 ? "" : "s"; ?></span>
                    <h2>Recent Announcements</h2>
                </div>
            </div>

            <?php if ($announcementCount > 0) { ?>
                <div class="dashboard-list">
                    <?php while ($announcement = $announcements->fetch_assoc()) { ?>
                        <article class="dashboard-list__item">
                            <div>
                                <div class="dashboard-list__title-row">
                                    <h3><?php echo $e($announcement["title"]); ?></h3>
                                    <?php if (($announcement["posted_role"] ?? "") === "ta") { ?>
                                        <span class="status-pill">From TA</span>
                                    <?php } ?>
                                </div>
                                <p><?php echo $e($announcement["body"]); ?></p>
                                <span><?php echo $e($announcement["course_title"]); ?> - <?php echo $formatDate($announcement["created_at"] ?? null); ?></span>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="dashboard-empty dashboard-empty--compact">
                    <h3>No announcements</h3>
                    <p>Course announcements will appear here when instructors or TAs post updates.</p>
                </div>
            <?php } ?>
        </article>

        <article class="dashboard-panel dashboard-panel--wide">
            <div class="dashboard-panel__header">
                <div>
                    <span class="dashboard-kicker"><?php echo $courseCount; ?> Enrollment<?php echo $courseCount === 1 ? "" : "s"; ?></span>
                    <h2>My Courses</h2>
                </div>
                <a class="dashboard-link" href="<?php echo $studentUrl("/student/courses"); ?>">Browse more</a>
            </div>

            <?php if ($courseCount > 0) { ?>
                <div class="course-grid">
                    <?php while ($course = $courses->fetch_assoc()) { ?>
                        <article class="course-tile">
                            <div>
                                <span class="status-pill <?php echo ($course["status"] ?? "") === "active" ? "status-pill--success" : ""; ?>">
                                    <?php echo $e(ucfirst((string) ($course["status"] ?? "unknown"))); ?>
                                </span>
                                <h3><?php echo $e($course["title"]); ?></h3>
                                <p><?php echo $e($course["description"]); ?></p>
                            </div>

                            <a
                            class="btn"
                            href="<?php echo $studentUrl("/student/course-details?id=" . (int) $course["id"]); ?>"
                            >
                                View Course
                            </a>
                        </article>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="dashboard-empty dashboard-empty--compact">
                    <h3>No courses yet</h3>
                    <p>Enroll in a course to start receiving quizzes, materials, and announcements.</p>
                    <a class="btn" href="<?php echo $studentUrl("/student/courses"); ?>">
                        Find Courses
                    </a>
                </div>
            <?php } ?>
        </article>
    </section>

</main>

</body>

</html>
