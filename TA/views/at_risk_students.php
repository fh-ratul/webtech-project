<!DOCTYPE html>
<html>

<head>
    <title>At Risk Students</title>
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
            <h1>At-Risk Students</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Flag students below performance threshold.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <form method="POST" class="inline-form">
            <div class="form-grid">
                <div class="form-field">
                    <label for="threshold">Threshold (%)</label>
                    <input id="threshold" type="number" min="0" max="100" step="0.01" name="threshold" value="<?php echo htmlspecialchars((string) $threshold); ?>" required>
                </div>
            </div>
            <button type="submit">Apply Threshold</button>
        </form>
    </div>

    <div class="card">
        <h2>Performance Overview</h2>

        <?php if ($reportRows->num_rows == 0) { ?>
            <div class="empty-state">No enrolled students yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Attempts</th>
                    <th>Average Score (%)</th>
                    <th>Flag</th>
                </tr>

                <?php while ($row = $reportRows->fetch_assoc()) { ?>
                    <?php
                        $avgPercent = $row['avg_percent'] !== null ? (float) $row['avg_percent'] : 0.0;
                        $isAtRisk = $avgPercent < $threshold;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo (int) $row['attempt_count']; ?></td>
                        <td><?php echo number_format($avgPercent, 2); ?>%</td>
                        <td>
                            <?php if ($isAtRisk) { ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="student_id" value="<?php echo (int) $row['student_id']; ?>">
                                    <input type="hidden" name="flag_student" value="1">
                                    <input type="hidden" name="threshold" value="<?php echo htmlspecialchars((string) $threshold); ?>">
                                    <div class="form-field">
                                        <input type="text" name="reason" placeholder="Reason (optional)">
                                    </div>
                                    <button type="submit">Flag</button>
                                </form>
                            <?php } else { ?>
                                <span class="pill">On Track</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>

    <div class="card">
        <h2>Flag History</h2>

        <?php if ($flags->num_rows == 0) { ?>
            <div class="empty-state">No flags recorded yet.</div>
        <?php } else { ?>
            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Threshold</th>
                    <th>Reason</th>
                    <th>Flagged At</th>
                </tr>
                <?php while ($flag = $flags->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo (int) $flag['student_id']; ?></td>
                        <td><?php echo number_format((float) $flag['threshold_percent'], 2); ?>%</td>
                        <td><?php echo htmlspecialchars($flag['reason'] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($flag['created_at']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
</div>

</body>

</html>
