<?php

include("../config/config.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $student_id = trim($_POST['student_id']);
    $program = trim($_POST['program']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (
        empty($name) ||
        empty($email) ||
        empty($student_id) ||
        empty($program) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "All fields are required.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Invalid email format.";
    }

    elseif ($password != $confirm_password) {

        $error = "Passwords do not match.";
    }

    else {

        // Check Existing Email
        $checkQuery = "SELECT id FROM users WHERE email = ?";

        $stmt = $conn->prepare($checkQuery);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {

            $error = "Email already exists.";
        }

        else {

            // Password Hashing
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert User
            $insertQuery = "
            INSERT INTO users
            (
                name,
                email,
                password_hash,
                role,
                student_id,
                program
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'student',
                ?,
                ?
            )
            ";

            $stmt = $conn->prepare($insertQuery);

            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $hashedPassword,
                $student_id,
                $program
            );

            if ($stmt->execute()) {

                $success = "Registration Successful!";
            }

            else {

                $error = "Registration Failed.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Student Registration</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/auth.css">

</head>

<body class="auth-page">

<div class="container">

    <h2>Student Registration</h2>

    <p class="error">
        <?php echo $error; ?>
    </p>

    <p class="success">
        <?php echo $success; ?>
    </p>

    <form method="POST">

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

    <a href="login.php">
        Already Have Account?
    </a>

</div>

</body>

</html>