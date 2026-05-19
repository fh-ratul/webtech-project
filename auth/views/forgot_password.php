<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>

<body class="auth-page">

<div class="container">
    <h2>Forgot Password</h2>

    <p class="error">
        <?php echo $error ?? ""; ?>
    </p>

    <p class="success">
        <?php echo $success ?? ""; ?>
    </p>

    <form method="POST" action="/auth/forgot-password">
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <br>
    <a href="/auth/login">Back to Login</a>
</div>

</body>

</html>
