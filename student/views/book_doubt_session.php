<!DOCTYPE html>
<html>

<head>

    <title>Book Doubt Session</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<div class="container">

    <div class="card">

        <h1>
            Book Doubt Session
        </h1>

        <p class="success">
            <?php echo $success; ?>
        </p>

        <p class="error">
            <?php echo $error; ?>
        </p>

        <h2>
            <?php echo $session['title']; ?>
        </h2>

        <p>
            <strong>Course:</strong>
            <?php echo $session['course_title']; ?>
        </p>

        <p>
            <strong>Teaching Assistant:</strong>
            <?php echo $session['ta_name']; ?>
        </p>

        <p>
            <strong>Scheduled Time:</strong>
            <?php echo $session['scheduled_at']; ?>
        </p>

        <p>
            <strong>Duration:</strong>
            <?php echo $session['duration_minutes']; ?>
            Minutes
        </p>

        <p>
            <strong>Location/Link:</strong>
            <?php echo $session['location_or_link']; ?>
        </p>

        <p>
            <strong>Status:</strong>
            <?php echo $session['status']; ?>
        </p>

        <?php if (!empty($session['notice'])) { ?>
            <p class="pending">
                Notice: <?php echo $session['notice']; ?>
            </p>
        <?php } ?>

        <p>
            <strong>Seats:</strong>
            <?php echo $currentBookings; ?>
            /
            <?php echo $session['max_attendees']; ?>
        </p>

        <?php if (empty($success) && $session['status'] == 'scheduled') { ?>
            <form method="POST">
                <button
                type="submit"
                name="book"
                >
                    Confirm Booking
                </button>
            </form>
        <?php } ?>

        <br>

        <a
        class="btn"
        href="<?php echo APP_BASE_URL; ?>/student/doubt-sessions"
        >
            My Booked Sessions
        </a>

    </div>

</div>

</body>

</html>
