<?php

include("../config/config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    $password = trim($_POST['password']);

    // Validation

    if (
        empty($email) ||
        empty($password)
    ) {

        $error = "All fields are required.";
    }

    else {

        // Fetch User

        $query = "
        SELECT
        id,
        name,
        password_hash,
        role,
        is_active

        FROM users

        WHERE email = ?
        ";

        $stmt = $conn->prepare($query);

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        // User Found

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            // Check Account Active

            if ($user['is_active'] != 1) {

                $error =
                "Account is inactive.";
            }

            else {

                // Verify hashed password

                if (
                    password_verify(
                        $password,
                        $user['password_hash']
                    )
                ) {

                    // Store Session

                    $_SESSION['user_id'] =
                    $user['id'];

                    $_SESSION['name'] =
                    $user['name'];

                    $_SESSION['role'] =
                    $user['role'];

                    // Redirect By Role

                    if (
                        $user['role'] ==
                        'student'
                    ) {

                        header(
                            "Location: ../student/dashboard.php"
                        );
                    }

                    elseif (
                        $user['role'] ==
                        'instructor'
                    ) {

                        header(
                            "Location: ../instructor/dashboard.php"
                        );
                    }

                    elseif (
                        $user['role'] ==
                        'ta'
                    ) {

                        header(
                            "Location: ../ta/dashboard.php"
                        );
                    }

                    elseif (
                        $user['role'] ==
                        'admin'
                    ) {

                        header(
                            "Location: ../admin/dashboard.php"
                        );
                    }

                    else {

                        $error =
                        "Invalid role.";
                    }

                    exit();
                }

                else {

                    $error =
                    "Wrong Password.";
                }
            }
        }

        else {

            $error =
            "Email Not Found.";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Login</title>

    <link
    rel="stylesheet"
    href="../assets/css/base.css"
    >

    <link
    rel="stylesheet"
    href="../assets/css/auth.css"
    >

</head>

<body class="auth-page">

<div class="container">

    <h2>Login</h2>

    <p class="error">

        <?php echo $error; ?>

    </p>

    <form method="POST">

        <!-- Email -->

        <input
        type="email"
        name="email"
        placeholder="Email"
        required
        >

        <!-- Password -->

        <input
        type="password"
        name="password"
        placeholder="Password"
        required
        >

        <!-- Submit -->

        <button type="submit">

            Login

        </button>

    </form>

    <br>

    <a href="register.php">

        Create Account

    </a>

</div>

</body>

</html>