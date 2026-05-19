<!DOCTYPE html>
<html>

<head>

    <title>Student Registration</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/auth.css">

</head>

<body class="auth-page">

<div class="container">
    <div class="card">

        <h2>Student Registration</h2>

        <p class="error">
            <?php echo $error ?? ""; ?>
        </p>

        <p class="success">
            <?php echo $success ?? ""; ?>
        </p>

        <form method="POST" action="<?php echo $baseUrl; ?>/auth/register">

        <input
        type="text"
        name="name"
        placeholder="Full Name"
        >

        <input
        type="email"
        name="email"
        placeholder="Email"
        >

        <input
        type="text"
        name="student_id"
        placeholder="Student ID"
        >

        <input
        type="text"
        name="program"
        placeholder="Program"
        >

        <input
        type="password"
        name="password"
        placeholder="Password"
        >

        <input
        type="password"
        name="confirm_password"
        placeholder="Confirm Password"
        >

            <button type="submit">
                Register
            </button>

        </form>

        <br>

        <a href="<?php echo $baseUrl; ?>/auth/login">
            Already Have Account?
        </a>
    </div>
</div>

</body>

</html>
