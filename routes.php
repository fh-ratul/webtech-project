<?php

$router->get("/", function () {
    header("Location: /auth/login");
    exit();
});

require APP_ROOT . "/auth/controllers/AuthController.php";
require APP_ROOT . "/student/controllers/StudentController.php";
require APP_ROOT . "/TA/controllers/TAController.php";

$authController = new AuthController($conn);
$studentController = new StudentController($conn);
$taController = new TAController($conn);

$router->get("/auth/login", [$authController, "showLogin"]);
$router->post("/auth/login", [$authController, "login"]);
$router->get("/auth/register", [$authController, "showRegister"]);
$router->post("/auth/register", [$authController, "register"]);
$router->get("/auth/forgot-password", [$authController, "showForgotPassword"]);
$router->post("/auth/forgot-password", [$authController, "showForgotPassword"]);
$router->get("/auth/change-password", [$authController, "showChangePassword"]);
$router->post("/auth/change-password", [$authController, "changePassword"]);
$router->get("/auth/logout", [$authController, "logout"]);

$router->get("/student/dashboard", [$studentController, "dashboard"]);
$router->get("/student/courses", [$studentController, "courses"]);
$router->get("/student/course-details", [$studentController, "courseDetails"]);
$router->get("/student/enroll-course", [$studentController, "enrollCourse"]);
$router->get("/student/drop-course", [$studentController, "dropCourse"]);
$router->get("/student/attempt-history", [$studentController, "attemptHistory"]);
$router->get("/student/quiz-result", [$studentController, "quizResult"]);
$router->get("/student/take-quiz", [$studentController, "takeQuiz"]);
$router->post("/student/take-quiz", [$studentController, "takeQuiz"]);
$router->get("/student/leaderboard", [$studentController, "leaderboard"]);
$router->get("/student/performance", [$studentController, "performance"]);
$router->get("/student/qa-board", [$studentController, "qaBoard"]);
$router->post("/student/qa-board", [$studentController, "qaBoard"]);
$router->get("/student/view-answers", [$studentController, "viewAnswers"]);
$router->get("/student/doubt-sessions", [$studentController, "doubtSessions"]);
$router->get("/student/book-doubt-session", [$studentController, "bookDoubtSession"]);
$router->post("/student/book-doubt-session", [$studentController, "bookDoubtSession"]);
$router->get("/student/profile", [$studentController, "profile"]);
$router->post("/student/profile", [$studentController, "profile"]);
$router->get("/student/materials", [$studentController, "materials"]);
$router->get("/student/my-courses", [$studentController, "myCourses"]);
$router->get("/student/announcements", [$studentController, "announcements"]);
$router->get("/student/ask-question", [$studentController, "askQuestion"]);
$router->get("/student/booked-sessions", [$studentController, "bookedSessions"]);

$router->get("/ta/dashboard", [$taController, "dashboard"]);
$router->get("/ta/assigned-courses", [$taController, "assignedCourses"]);
$router->get("/ta/course-details", [$taController, "courseDetails"]);
$router->get("/ta/create-practice-quiz", [$taController, "createPracticeQuiz"]);
$router->post("/ta/create-practice-quiz", [$taController, "createPracticeQuiz"]);
$router->get("/ta/question-bank", [$taController, "questionBank"]);
$router->post("/ta/question-bank", [$taController, "questionBank"]);
$router->get("/ta/student-results", [$taController, "studentResults"]);
$router->get("/ta/at-risk-students", [$taController, "atRiskStudents"]);
$router->post("/ta/at-risk-students", [$taController, "atRiskStudents"]);
$router->get("/ta/announcements", [$taController, "announcements"]);
$router->post("/ta/announcements", [$taController, "announcements"]);
$router->get("/ta/materials", [$taController, "materials"]);
$router->post("/ta/materials", [$taController, "materials"]);
$router->get("/ta/qa-board", [$taController, "qaBoard"]);
$router->post("/ta/qa-board", [$taController, "qaBoard"]);
$router->post("/ta/qa-answer", [$taController, "qaAnswerAjax"]);
$router->get("/ta/doubt-sessions", [$taController, "doubtSessions"]);
$router->get("/ta/create-doubt-session", [$taController, "createDoubtSession"]);
$router->post("/ta/create-doubt-session", [$taController, "createDoubtSession"]);
$router->get("/ta/bookings", [$taController, "bookings"]);
$router->get("/ta/profile", [$taController, "profile"]);
$router->post("/ta/profile", [$taController, "profile"]);
$router->get("/ta/reports", [$taController, "reports"]);
