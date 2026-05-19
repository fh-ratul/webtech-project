<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../core/Session.php';

Session::start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validation
    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {

        // Fetch User
        $query = "
            SELECT id, name, password_hash, role, is_active
            FROM users
            WHERE email = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // User Found
        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            // Check Account Active
            if ($user['is_active'] != 1) {
                $error = "Account is inactive.";
            } else {

                // Verify hashed password
                if (password_verify($password, $user['password_hash'])) {

                    // Store Session
                    Session::login($user['id'], $user['name'], $user['role']);

                    // Redirect By Role
                    if ($user['role'] == 'student') {
                        header("Location: ../../student/views/dashboard.php");
                        exit();
                    } elseif ($user['role'] == 'instructor') {
                        header("Location: ../../instructor/controllers/DashboardController.php");
                        exit();
                    } elseif ($user['role'] == 'ta') {
                        header("Location: ../../ta/views/dashboard.php");
                        exit();
                    } elseif ($user['role'] == 'admin') {
                        header("Location: ../../admin/controllers/DashboardController.php");
                        exit();
                    } else {
                        $error = "Invalid role.";
                    }

                } else {
                    $error = "Wrong Password.";
                }
            }
        } else {
            $error = "Email Not Found.";
        }
    }
}

// Include view
require __DIR__ . '/../views/login.php';

?>
