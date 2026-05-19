`<?php

require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../middleware/auth_middleware.php";

class AuthController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function showLogin(): void
    {
        $error = "";
        view("auth/views/login.php", ["error" => $error]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? "");
        $password = trim($_POST['password'] ?? "");
        $error = "";

        if ($email === "" || $password === "") {
            $error = "All fields are required.";
            view("auth/views/login.php", ["error" => $error]);
            return;
        }

        $userModel = new User($this->conn);
        $user = $userModel->findByEmail($email);

        if (!$user) {
            $error = "Email Not Found.";
            view("auth/views/login.php", ["error" => $error]);
            return;
        }

        if ((int) $user['is_active'] !== 1) {
            $error = "Account is inactive.";
            view("auth/views/login.php", ["error" => $error]);
            return;
        }

        if (!password_verify($password, $user['password_hash'])) {
            $error = "Wrong Password.";
            view("auth/views/login.php", ["error" => $error]);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        $baseUrl = rtrim(APP_BASE_URL, "/");

        if ($user['role'] === 'student') {
            header("Location: {$baseUrl}/student/dashboard");
        } elseif ($user['role'] === 'instructor') {
            header("Location: {$baseUrl}/instructor/dashboard");
        } elseif ($user['role'] === 'ta') {
            header("Location: {$baseUrl}/ta/dashboard");
        } else {
            $error = "Invalid role.";
            view("auth/views/login.php", ["error" => $error]);
            return;
        }

        exit();
    }

    public function showRegister(): void
    {
        $error = "";
        $success = "";
        view("auth/views/register.php", ["error" => $error, "success" => $success]);
    }

    public function register(): void
    {
        $name = trim($_POST['name'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $studentId = trim($_POST['student_id'] ?? "");
        $program = trim($_POST['program'] ?? "");
        $password = trim($_POST['password'] ?? "");
        $confirmPassword = trim($_POST['confirm_password'] ?? "");

        $error = "";
        $success = "";

        if ($name === "" || $email === "" || $studentId === "" || $program === "" || $password === "" || $confirmPassword === "") {
            $error = "All fields are required.";
            view("auth/views/register.php", ["error" => $error, "success" => $success]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
            view("auth/views/register.php", ["error" => $error, "success" => $success]);
            return;
        }

        if ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
            view("auth/views/register.php", ["error" => $error, "success" => $success]);
            return;
        }

        $userModel = new User($this->conn);
        if ($userModel->emailExists($email)) {
            $error = "Email already exists.";
            view("auth/views/register.php", ["error" => $error, "success" => $success]);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $created = $userModel->createStudent($name, $email, $hash, $studentId, $program);

        if ($created) {
            $success = "Registration Successful!";
        } else {
            $error = "Registration Failed.";
        }

        view("auth/views/register.php", ["error" => $error, "success" => $success]);
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        $baseUrl = rtrim(APP_BASE_URL, "/");
        header("Location: {$baseUrl}/auth/login");
        exit();
    }

    public function showForgotPassword(): void
    {
        $error = "";
        $success = "";
        view("auth/views/forgot_password.php", ["error" => $error, "success" => $success]);
    }

    public function showChangePassword(): void
    {
        require_auth();
        $error = "";
        $success = "";
        view("auth/views/change_password.php", ["error" => $error, "success" => $success]);
    }

    public function changePassword(): void
    {
        require_auth();
        $error = "";
        $success = "";

        $currentPassword = trim($_POST['current_password'] ?? "");
        $newPassword = trim($_POST['new_password'] ?? "");
        $confirmPassword = trim($_POST['confirm_password'] ?? "");

        if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
            $error = "All fields are required.";
            view("auth/views/change_password.php", ["error" => $error, "success" => $success]);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
            view("auth/views/change_password.php", ["error" => $error, "success" => $success]);
            return;
        }

        $userModel = new User($this->conn);
        $user = $userModel->findById((int) $_SESSION['user_id']);

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $error = "Current Password Incorrect.";
            view("auth/views/change_password.php", ["error" => $error, "success" => $success]);
            return;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($userModel->updatePassword((int) $_SESSION['user_id'], $newHash)) {
            $success = "Password Changed Successfully.";
        } else {
            $error = "Password Update Failed.";
        }

        view("auth/views/change_password.php", ["error" => $error, "success" => $success]);
    }
}
