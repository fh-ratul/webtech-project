<!DOCTYPE html>
<html>

<head>

    <title>My Doubt Sessions</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <div class="card">

        <h1>My Booked Doubt Sessions</h1>

        <table>

            <tr>

                <th>#</th>

                <th>Course</th>

                <th>Session Title</th>

                <th>Teaching Assistant</th>

                <th>Scheduled Time</th>

                <th>Duration</th>

                <th>Location / Link</th>

                <th>Status</th>

                <th>Notice</th>

                <th>Booked At</th>

            </tr>

            <?php
            $serial = 1;

            while ($session = $sessions->fetch_assoc()) {
            ?>

                <tr>

                    <td>
                        <?php echo $serial; ?>
                    </td>

                    <td>
                        <?php echo $session['course_title']; ?>
                    </td>

                    <td>
                        <?php echo $session['title']; ?>
                    </td>

                    <td>
                        <?php echo $session['ta_name']; ?>
                    </td>

                    <td>
                        <?php echo $session['scheduled_at']; ?>
                    </td>

                    <td>
                        <?php echo $session['duration_minutes']; ?>
                        Minutes
                    </td>

                    <td>
                        <?php echo $session['location_or_link']; ?>
                    </td>

                    <td>
                        <?php echo $session['status']; ?>
                    </td>

                    <td>
                        <?php echo $session['notice']; ?>
                    </td>

                    <td>
                        <?php echo $session['booked_at']; ?>
                    </td>

                </tr>

            <?php
                $serial++;
            } ?>

        </table>

    </div>

</div>

</body>

</html>
