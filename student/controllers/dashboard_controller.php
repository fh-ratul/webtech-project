<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../models/dashboard_model.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'student') {
    die("Access Denied");
}

$studentId = (int) $_SESSION['user_id'];

$totalCourses = get_student_total_courses($conn, $studentId);
$upcomingQuiz = get_student_upcoming_quiz($conn, $studentId);
$announcements = get_student_recent_announcements($conn, $studentId);
$courses = get_student_courses($conn, $studentId);

require __DIR__ . "/../views/dashboard_view.php";
