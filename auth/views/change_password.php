<!DOCTYPE html>
<html>

<head>

    <title>Change Password</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/auth.css">

</head>

<body class="auth-page">

<div class="container">

    <h2>Change Password</h2>

    <p class="success">
        <?php echo $success ?? ""; ?>
    </p>

    <p class="error">
        <?php echo $error ?? ""; ?>
    </p>

    <form method="POST" action="/auth/change-password">

        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">
            Change Password
        </button>

    </form>

</div>

</body>

</html>
