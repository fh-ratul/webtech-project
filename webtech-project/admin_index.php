<?php
require_once 'controllers/AdminController.php';

$controller = new AdminController();
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

switch ($action) {
    case 'dashboard': $controller->index(); break;
    case 'users': $controller->manageUsers(); break;
    case 'approvals': $controller->manageApprovals(); break;
    case 'subjects': $controller->manageSubjects(); break;
    case 'courses': $controller->viewCourses(); break;
    case 'quizzes': $controller->viewQuizzes(); break;
    case 'reports': $controller->viewReports(); break;
    case 'policies': $controller->managePolicies(); break;
    case 'announcements': $controller->manageAnnouncements(); break;
    case 'analytics': $controller->viewAnalytics(); break;
    case 'academic_report':  $controller->academicReport(); break;
    default: $controller->index(); break;
}
?>