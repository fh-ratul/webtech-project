<!DOCTYPE html>
<html>

<head>

    <title>Login</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/auth.css">

</head>

<body class="auth-page">

<div class="container">
    <div class="card">

        <h2>Login</h2>

        <p class="error">
            <?php echo $error ?? ""; ?>
        </p>

        <p class="success">
            <?php echo $success ?? ""; ?>
        </p>

        <form method="POST" action="<?php echo $baseUrl; ?>/auth/login">

        <input
        type="email"
        name="email"
        placeholder="Email"
        required
        >

        <input
        type="password"
        name="password"
        placeholder="Password"
        required
        >

            <button type="submit">
                Login
            </button>

        </form>

        <br>

        <a href="<?php echo $baseUrl; ?>/auth/register">
            Create Account
        </a>
    </div>
</div>

</body>

</html>
