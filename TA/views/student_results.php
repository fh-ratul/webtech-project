<!DOCTYPE html>
<html>

<head>

    <title>Student Results</title>

    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>

    <link
    rel="stylesheet"
    href="<?php echo $baseUrl; ?>/assets/css/base.css"
    >

    <link
    rel="stylesheet"
    href="<?php echo $baseUrl; ?>/assets/css/instructor.css"
    >

</head>

<body class="ta-page">

<nav class="navbar">

    <a href="<?php echo $baseUrl; ?>/ta/dashboard">
        Dashboard
    </a>

    <a href="<?php echo $baseUrl; ?>/ta/assigned-courses">
        Assigned Courses
    </a>

    <a href="<?php echo $baseUrl; ?>/ta/doubt-sessions">
        Doubt Sessions
    </a>

    <a href="<?php echo $baseUrl; ?>/ta/bookings">
        Bookings
    </a>

    <a href="<?php echo $baseUrl; ?>/ta/profile">
        Profile
    </a>

    <a href="<?php echo $baseUrl; ?>/auth/logout">
        Logout
    </a>

</nav>

<div class="container">

    <div class="page-header">

        <div class="title-block">

            <h1>
                Student Results
            </h1>

            <p>

                <?php echo htmlspecialchars($course['title']); ?>

                • Quiz attempt history.

            </p>

        </div>

        <div class="action-row">

            <a
            class="btn"
            href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>"
            >

                Back to Course

            </a>

        </div>

    </div>

    <div class="card">

        <!-- Empty State -->

        <?php if (empty($attempts)) { ?>

            <div class="empty-state">

                No student attempts yet.

            </div>

        <?php } else { ?>

            <!-- Result Table -->

            <table>

                <tr>

                    <th>
                        Student
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Quiz
                    </th>

                    <th>
                        Score
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Duration (min)
                    </th>

                    <th>
                        Completed At
                    </th>

                </tr>

                <?php foreach ($attempts as $attempt) { ?>

                    <?php

                    $passed =
                    (
                        $attempt['score']
                        >=
                        $attempt['pass_mark']
                    );

                    ?>

                    <tr>

                        <!-- Student Name -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $attempt['student_name']
                            );
                            ?>

                        </td>

                        <!-- Email -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $attempt['email']
                            );
                            ?>

                        </td>

                        <!-- Quiz -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $attempt['quiz_title']
                            );
                            ?>

                        </td>

                        <!-- Score -->

                        <td>

                            <?php
                            echo $attempt['score'];
                            ?>

                            /

                            <?php
                            echo $attempt['total_marks'];
                            ?>

                        </td>

                        <!-- Pass Fail -->

                        <td>

                            <?php if ($passed) { ?>

                                <span class="pass">

                                    PASS

                                </span>

                            <?php } else { ?>

                                <span class="fail">

                                    FAIL

                                </span>

                            <?php } ?>

                        </td>

                        <!-- Duration -->

                        <td>

                            <?php
                            echo (int)
                            $attempt['duration_minutes'];
                            ?>

                        </td>

                        <!-- Completed -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $attempt['completed_at']
                            );
                            ?>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        <?php } ?>

    </div>

</div>

</body>

</html>