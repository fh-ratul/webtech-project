<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../models/CourseModel.php';
require_once __DIR__ . '/../models/EnrollmentModel.php';

Session::requireRole('instructor');

$instructorId = Session::userId();
$instructorName = Session::name();

// Initialize models
$courseModel = new CourseModel($conn);
$enrollmentModel = new EnrollmentModel($conn);

// Fetch data
$stats = $courseModel->getCourseStats($instructorId);
$pendingRequests = $enrollmentModel->getPendingCountByInstructor($instructorId);
$courses = $courseModel->getCoursesByInstructor($instructorId);

// Include view
require __DIR__ . '/../views/dashboard.php';

?>
