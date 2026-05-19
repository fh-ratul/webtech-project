<!DOCTYPE html>
<html>

<head>

    <title>Browse Courses</title>
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo APP_BASE_URL; ?>/assets/css/student.css">

</head>

<body class="student-page">

<?php include APP_ROOT . "/includes/student_navbar.php"; ?>

<div class="container">

    <h1>Browse Courses</h1>

    <form class="search-form" method="GET" action="<?php echo APP_BASE_URL; ?>/student/courses">

        <input
        class="search-input"
        type="text"
        name="search"
        placeholder="Search by Course or Subject"
        value="<?php echo $search; ?>"
        >

        <button class="search-button" type="submit">
            Search
        </button>

    </form>

    <br>

    <?php while ($row = $courses->fetch_assoc()) { ?>

        <div class="course-box">

            <h2>
                <?php echo $row['title']; ?>
            </h2>

            <p>
                <?php echo $row['description']; ?>
            </p>

            <p>
                <strong>Subject:</strong>
                <?php echo $row['subject_name']; ?>
            </p>

            <p>
                <strong>Instructor:</strong>
                <?php echo $row['instructor_name']; ?>
            </p>

            <p>
                <strong>Enrollment Type:</strong>
                <?php echo $row['enrollment_type']; ?>
            </p>

            <p>
                <strong>Enrolled Students:</strong>
                <?php echo $row['enrolled_students']; ?>
            </p>

            <a
            class="btn"
            href="<?php echo APP_BASE_URL; ?>/student/enroll-course?id=<?php echo $row['id']; ?>"
            >
                Enroll
            </a>

        </div>

    <?php } ?>

</div>

</body>

</html>
