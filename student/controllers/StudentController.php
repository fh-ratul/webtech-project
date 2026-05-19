<?php

require_once __DIR__ . "/../models/Student.php";

class StudentController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function redirectTo(string $path): void
    {
        $baseUrl = defined("APP_BASE_URL") ? APP_BASE_URL : "";
        header("Location: " . $baseUrl . $path);
        exit();
    }

    public function dashboard(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];
        $studentModel = new Student($this->conn);

        $totalCourses = $studentModel->getTotalCourses($studentId);
        $dashboardMetrics = $studentModel->getDashboardMetrics($studentId);
        $upcomingQuiz = $studentModel->getUpcomingQuiz($studentId);
        $recentAttempt = $studentModel->getRecentAttempt($studentId);
        $nextDoubtSession = $studentModel->getNextDoubtSession($studentId);
        $announcements = $studentModel->getRecentAnnouncements($studentId);
        $courses = $studentModel->getCourses($studentId);

        view("student/views/dashboard.php", [
            "totalCourses" => $totalCourses,
            "dashboardMetrics" => $dashboardMetrics,
            "upcomingQuiz" => $upcomingQuiz,
            "recentAttempt" => $recentAttempt,
            "nextDoubtSession" => $nextDoubtSession,
            "announcements" => $announcements,
            "courses" => $courses
        ]);
    }

    public function courses(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $search = trim($_GET['search'] ?? "");

        $query = "
        SELECT
        courses.id,
        courses.title,
        courses.description,
        courses.enrollment_type,
        subjects.name AS subject_name,
        users.name AS instructor_name,

        (
            SELECT COUNT(*)
            FROM enrollments
            WHERE enrollments.course_id = courses.id
            AND enrollments.status = 'active'
        ) AS enrolled_students

        FROM courses

        JOIN subjects
        ON courses.subject_id = subjects.id

        JOIN users
        ON courses.instructor_id = users.id

        WHERE courses.status = 'active'
        ";

        if ($search !== "") {
            $query .= "
            AND (
                courses.title LIKE ?
                OR subjects.name LIKE ?
            )
            ";
        }

        $stmt = $this->conn->prepare($query);
        if ($search !== "") {
            $searchValue = "%$search%";
            $stmt->bind_param("ss", $searchValue, $searchValue);
        }

        $stmt->execute();
        $courses = $stmt->get_result();

        view("student/views/courses.php", [
            "search" => $search,
            "courses" => $courses
        ]);
    }

    public function courseDetails(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];
        $courseId = (int) ($_GET['id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $enrollmentQuery = "
        SELECT id
        FROM enrollments
        WHERE student_id = ?
        AND course_id = ?
        AND status = 'active'
        ";

        $stmt = $this->conn->prepare($enrollmentQuery);
        $stmt->bind_param("ii", $studentId, $courseId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            die("You are not enrolled in this course.");
        }

        $courseQuery = "
        SELECT
        courses.title,
        courses.description,
        subjects.name AS subject_name,
        users.name AS instructor_name

        FROM courses

        JOIN subjects
        ON courses.subject_id = subjects.id

        JOIN users
        ON courses.instructor_id = users.id

        WHERE courses.id = ?
        ";

        $stmt2 = $this->conn->prepare($courseQuery);
        $stmt2->bind_param("i", $courseId);
        $stmt2->execute();
        $course = $stmt2->get_result()->fetch_assoc();

        $taQuery = "
        SELECT users.name

        FROM course_tas

        JOIN users
        ON course_tas.ta_id = users.id

        WHERE course_tas.course_id = ?
        ";

        $stmt3 = $this->conn->prepare($taQuery);
        $stmt3->bind_param("i", $courseId);
        $stmt3->execute();
        $tas = $stmt3->get_result();

        $announcementQuery = "
        SELECT
        title,
        body,
        posted_role,
        created_at

        FROM announcements

        WHERE course_id = ?

        ORDER BY created_at DESC
        ";

        $stmt4 = $this->conn->prepare($announcementQuery);
        $stmt4->bind_param("i", $courseId);
        $stmt4->execute();
        $announcements = $stmt4->get_result();

        $materialQuery = "
        SELECT
        id,
        title,
        file_path,
        material_type

        FROM course_materials

        WHERE course_id = ?
        ";

        $stmt5 = $this->conn->prepare($materialQuery);
        $stmt5->bind_param("i", $courseId);
        $stmt5->execute();
        $materials = $stmt5->get_result();

        $quizQuery = "
        SELECT
        id,
        title,
        quiz_type,
        total_marks,
        available_until

        FROM quizzes

        WHERE course_id = ?
        AND status = 'published'
        ";

        $stmt6 = $this->conn->prepare($quizQuery);
        $stmt6->bind_param("i", $courseId);
        $stmt6->execute();
        $quizzes = $stmt6->get_result();

        view("student/views/course_details.php", [
            "course" => $course,
            "tas" => $tas,
            "announcements" => $announcements,
            "materials" => $materials,
            "quizzes" => $quizzes,
            "courseId" => $courseId
        ]);
    }

    public function enrollCourse(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];
        $courseId = (int) ($_GET['id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $courseQuery = "
        SELECT
        id,
        title,
        enrollment_type,
        max_students
        FROM courses
        WHERE id = ?
        AND status = 'active'
        ";

        $stmt = $this->conn->prepare($courseQuery);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $courseResult = $stmt->get_result();

        if ($courseResult->num_rows == 0) {
            die("Course Not Found");
        }

        $course = $courseResult->fetch_assoc();

        $checkEnrollmentQuery = "
        SELECT id
        FROM enrollments
        WHERE student_id = ?
        AND course_id = ?
        ";

        $stmt2 = $this->conn->prepare($checkEnrollmentQuery);
        $stmt2->bind_param("ii", $studentId, $courseId);
        $stmt2->execute();
        $existingEnrollment = $stmt2->get_result();

        if ($existingEnrollment->num_rows > 0) {
            die("You are already enrolled in this course.");
        }

        $countQuery = "
        SELECT COUNT(*) AS total_students
        FROM enrollments
        WHERE course_id = ?
        AND status = 'active'
        ";

        $stmt3 = $this->conn->prepare($countQuery);
        $stmt3->bind_param("i", $courseId);
        $stmt3->execute();
        $countResult = $stmt3->get_result()->fetch_assoc();

        if ($countResult['total_students'] >= $course['max_students']) {
            die("Course is full.");
        }

        $status = $course['enrollment_type'] == "open" ? "active" : "pending";

        $insertQuery = "
        INSERT INTO enrollments
        (
            student_id,
            course_id,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
        ";

        $stmt4 = $this->conn->prepare($insertQuery);
        $stmt4->bind_param("iis", $studentId, $courseId, $status);

        if ($stmt4->execute()) {
            $this->redirectTo("/student/dashboard");
        }

        die("Enrollment Failed.");
    }

    public function dropCourse(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $enrollmentQuery = "
        SELECT id
        FROM enrollments
        WHERE student_id = ?
        AND course_id = ?
        AND status = 'active'
        ";

        $stmt = $this->conn->prepare($enrollmentQuery);
        $stmt->bind_param("ii", $studentId, $courseId);
        $stmt->execute();
        $enrollmentResult = $stmt->get_result();

        if ($enrollmentResult->num_rows == 0) {
            die("You are not enrolled in this course.");
        }

        $quizCheckQuery = "
        SELECT attempts.id

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        WHERE attempts.student_id = ?
        AND quizzes.course_id = ?
        AND quizzes.quiz_type = 'graded'
        ";

        $stmt2 = $this->conn->prepare($quizCheckQuery);
        $stmt2->bind_param("ii", $studentId, $courseId);
        $stmt2->execute();

        if ($stmt2->get_result()->num_rows > 0) {
            die("Cannot drop course after completing graded quiz.");
        }

        $dropQuery = "
        UPDATE enrollments
        SET status = 'dropped'
        WHERE student_id = ?
        AND course_id = ?
        ";

        $stmt3 = $this->conn->prepare($dropQuery);
        $stmt3->bind_param("ii", $studentId, $courseId);

        if ($stmt3->execute()) {
            $this->redirectTo("/student/dashboard");
        }

        die("Failed To Drop Course.");
    }

    public function attemptHistory(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];

        $query = "
        SELECT
        attempts.id,
        attempts.score,
        attempts.completed_at,

        quizzes.title,
        quizzes.total_marks,
        quizzes.pass_mark,
        quizzes.quiz_type,

        courses.title AS course_title

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        JOIN courses
        ON quizzes.course_id = courses.id

        WHERE attempts.student_id = ?

        ORDER BY attempts.completed_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $attempts = $stmt->get_result();

        view("student/views/attempt_history.php", ["attempts" => $attempts]);
    }

    public function quizResult(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $attemptId = (int) ($_GET['attempt_id'] ?? 0);
        if ($attemptId <= 0) {
            die("Invalid Attempt ID");
        }

        $studentId = (int) $_SESSION['user_id'];

        $resultQuery = "
        SELECT
        attempts.score,
        attempts.completed_at,

        quizzes.title,
        quizzes.total_marks,
        quizzes.pass_mark

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        WHERE attempts.id = ?
        AND attempts.student_id = ?
        ";

        $stmt = $this->conn->prepare($resultQuery);
        $stmt->bind_param("ii", $attemptId, $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            die("Result Not Found");
        }

        $data = $result->fetch_assoc();
        $passed = $data['score'] >= $data['pass_mark'];

        $breakdownQuery = "
        SELECT
        questions.question_text,
        questions.marks,
        options.option_text,
        options.is_correct

        FROM answers

        JOIN questions
        ON answers.question_id = questions.id

        JOIN options
        ON answers.selected_option_id = options.id

        WHERE answers.attempt_id = ?
        ";

        $stmt2 = $this->conn->prepare($breakdownQuery);
        $stmt2->bind_param("i", $attemptId);
        $stmt2->execute();
        $breakdowns = $stmt2->get_result();

        view("student/views/quiz_result.php", [
            "data" => $data,
            "passed" => $passed,
            "breakdowns" => $breakdowns
        ]);
    }

    public function takeQuiz(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];
        $quizId = (int) ($_GET['id'] ?? 0);
        if ($quizId <= 0) {
            die("Invalid Quiz ID");
        }

        $quizQuery = "
        SELECT
        id,
        title,
        description,
        time_limit_minutes,
        total_marks,
        pass_mark,
        quiz_type

        FROM quizzes

        WHERE id = ?
        AND status = 'published'
        ";

        $stmt = $this->conn->prepare($quizQuery);
        $stmt->bind_param("i", $quizId);
        $stmt->execute();
        $quizResult = $stmt->get_result();

        if ($quizResult->num_rows == 0) {
            die("Quiz Not Found");
        }

        $quiz = $quizResult->fetch_assoc();

        $questionQuery = "
        SELECT
        id,
        question_text,
        marks

        FROM questions

        WHERE quiz_id = ?

        ORDER BY order_index ASC
        ";

        $stmt2 = $this->conn->prepare($questionQuery);
        $stmt2->bind_param("i", $quizId);
        $stmt2->execute();
        $questionsResult = $stmt2->get_result();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $score = 0;

            $attemptQuery = "
            INSERT INTO attempts
            (
                quiz_id,
                student_id,
                score,
                started_at,
                completed_at,
                is_graded
            )
            VALUES
            (
                ?,
                ?,
                0,
                NOW(),
                NOW(),
                1
            )
            ";

            $stmt3 = $this->conn->prepare($attemptQuery);
            $stmt3->bind_param("ii", $quizId, $studentId);
            $stmt3->execute();
            $attemptId = $this->conn->insert_id;

            $stmt2->execute();
            $questionResult2 = $stmt2->get_result();

            while ($question = $questionResult2->fetch_assoc()) {
                $questionId = $question['id'];

                if (isset($_POST['answer'][$questionId])) {
                    $selectedOption = $_POST['answer'][$questionId];

                    $answerQuery = "
                    INSERT INTO answers
                    (
                        attempt_id,
                        question_id,
                        selected_option_id
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?
                    )
                    ";

                    $stmt4 = $this->conn->prepare($answerQuery);
                    $stmt4->bind_param("iii", $attemptId, $questionId, $selectedOption);
                    $stmt4->execute();

                    $correctQuery = "
                    SELECT is_correct
                    FROM options
                    WHERE id = ?
                    ";

                    $stmt5 = $this->conn->prepare($correctQuery);
                    $stmt5->bind_param("i", $selectedOption);
                    $stmt5->execute();
                    $correctResult = $stmt5->get_result()->fetch_assoc();

                    if ($correctResult['is_correct'] == 1) {
                        $score += $question['marks'];
                    }
                }
            }

            $updateScoreQuery = "
            UPDATE attempts
            SET score = ?
            WHERE id = ?
            ";

            $stmt6 = $this->conn->prepare($updateScoreQuery);
            $stmt6->bind_param("di", $score, $attemptId);
            $stmt6->execute();

            $this->redirectTo("/student/quiz-result?attempt_id=$attemptId");
        }

        $questions = [];
        while ($question = $questionsResult->fetch_assoc()) {
            $optionQuery = "
            SELECT
            id,
            option_text

            FROM options

            WHERE question_id = ?
            ";

            $stmt7 = $this->conn->prepare($optionQuery);
            $stmt7->bind_param("i", $question['id']);
            $stmt7->execute();
            $options = $stmt7->get_result()->fetch_all(MYSQLI_ASSOC);

            $question['options'] = $options;
            $questions[] = $question;
        }

        view("student/views/take_quiz.php", [
            "quiz" => $quiz,
            "questions" => $questions
        ]);
    }

    public function leaderboard(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $quizId = (int) ($_GET['quiz_id'] ?? 0);
        if ($quizId <= 0) {
            die("Invalid Quiz ID");
        }

        $studentModel = new Student($this->conn);
        $quiz = $studentModel->getQuizInfo($quizId);
        $leaders = $studentModel->getLeaderboard($quizId);

        view("student/views/leaderboard.php", [
            "quiz" => $quiz,
            "leaders" => $leaders
        ]);
    }

    public function performance(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];

        $averageQuery = "
        SELECT AVG(score) AS average_score
        FROM attempts
        WHERE student_id = ?
        ";

        $stmt = $this->conn->prepare($averageQuery);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $averageResult = $stmt->get_result()->fetch_assoc();

        $passRateQuery = "
        SELECT
        COUNT(*) AS total_attempts,

        SUM(
            CASE
                WHEN attempts.score >= quizzes.pass_mark
                THEN 1
                ELSE 0
            END
        ) AS passed_attempts

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        WHERE attempts.student_id = ?
        ";

        $stmt2 = $this->conn->prepare($passRateQuery);
        $stmt2->bind_param("i", $studentId);
        $stmt2->execute();
        $passData = $stmt2->get_result()->fetch_assoc();

        $passRate = 0;
        if ($passData['total_attempts'] > 0) {
            $passRate = ($passData['passed_attempts'] / $passData['total_attempts']) * 100;
        }

        $subjectQuery = "
        SELECT
        subjects.name AS subject_name,
        AVG(attempts.score) AS average_score

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        JOIN courses
        ON quizzes.course_id = courses.id

        JOIN subjects
        ON courses.subject_id = subjects.id

        WHERE attempts.student_id = ?

        GROUP BY subjects.id
        ";

        $stmt3 = $this->conn->prepare($subjectQuery);
        $stmt3->bind_param("i", $studentId);
        $stmt3->execute();
        $subjects = $stmt3->get_result();

        $classAverageQuery = "
        SELECT
        quizzes.title,

        AVG(attempts.score) AS class_average,

        (
            SELECT score
            FROM attempts a2
            WHERE a2.quiz_id = quizzes.id
            AND a2.student_id = ?
            ORDER BY a2.completed_at DESC
            LIMIT 1
        ) AS student_score

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        GROUP BY quizzes.id
        ";

        $stmt4 = $this->conn->prepare($classAverageQuery);
        $stmt4->bind_param("i", $studentId);
        $stmt4->execute();
        $classComparisons = $stmt4->get_result();

        view("student/views/performance_dashboard.php", [
            "averageResult" => $averageResult,
            "passRate" => $passRate,
            "subjects" => $subjects,
            "classComparisons" => $classComparisons
        ]);
    }

    public function qaBoard(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $studentId = (int) $_SESSION['user_id'];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = trim($_POST['title'] ?? "");
            $body = trim($_POST['body'] ?? "");

            if ($title !== "" && $body !== "") {
                $insertQuery = "
                INSERT INTO qa_questions
                (
                    course_id,
                    student_id,
                    title,
                    body,
                    is_resolved
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    0
                )
                ";

                $stmt = $this->conn->prepare($insertQuery);
                $stmt->bind_param("iiss", $courseId, $studentId, $title, $body);
                $stmt->execute();
            }
        }

        $questionQuery = "
        SELECT
        qa_questions.id,
        qa_questions.title,
        qa_questions.body,
        qa_questions.is_resolved,
        qa_questions.created_at,
        users.name

        FROM qa_questions

        JOIN users
        ON qa_questions.student_id = users.id

        WHERE qa_questions.course_id = ?

        ORDER BY qa_questions.created_at DESC
        ";

        $stmt2 = $this->conn->prepare($questionQuery);
        $stmt2->bind_param("i", $courseId);
        $stmt2->execute();
        $questions = $stmt2->get_result();

        view("student/views/qa_board.php", [
            "questions" => $questions,
            "courseId" => $courseId
        ]);
    }

    public function viewAnswers(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $questionId = (int) ($_GET['question_id'] ?? 0);
        if ($questionId <= 0) {
            die("Invalid Question ID");
        }

        $studentId = (int) $_SESSION['user_id'];

        $questionQuery = "
        SELECT
        qa_questions.id,
        qa_questions.title,
        qa_questions.body,
        qa_questions.student_id,
        qa_questions.is_resolved,
        users.name

        FROM qa_questions

        JOIN users
        ON qa_questions.student_id = users.id

        WHERE qa_questions.id = ?
        ";

        $stmt = $this->conn->prepare($questionQuery);
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $questionResult = $stmt->get_result();

        if ($questionResult->num_rows == 0) {
            die("Question Not Found");
        }

        $question = $questionResult->fetch_assoc();

        if (isset($_GET['resolve']) && $question['student_id'] == $studentId) {
            $updateQuery = "
            UPDATE qa_questions
            SET is_resolved = 1
            WHERE id = ?
            ";

            $stmt2 = $this->conn->prepare($updateQuery);
            $stmt2->bind_param("i", $questionId);
            $stmt2->execute();

            $this->redirectTo("/student/view-answers?question_id=$questionId");
        }

        $answerQuery = "
        SELECT
        qa_answers.body,
        qa_answers.is_endorsed,
        qa_answers.created_at,
        users.name,
        users.role

        FROM qa_answers

        JOIN users
        ON qa_answers.author_id = users.id

        WHERE qa_answers.qa_question_id = ?

        ORDER BY qa_answers.created_at ASC
        ";

        $stmt3 = $this->conn->prepare($answerQuery);
        $stmt3->bind_param("i", $questionId);
        $stmt3->execute();
        $answers = $stmt3->get_result();

        view("student/views/view_answers.php", [
            "question" => $question,
            "answers" => $answers,
            "questionId" => $questionId,
            "studentId" => $studentId
        ]);
    }

    public function myDoubtSessions(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];

        $query = "
        SELECT
        doubt_sessions.id,
        doubt_sessions.title,
        doubt_sessions.scheduled_at,
        doubt_sessions.duration_minutes,
        doubt_sessions.location_or_link,
        doubt_sessions.status,
        doubt_sessions.notice,

        courses.title AS course_title,

        users.name AS ta_name,

        doubt_session_bookings.booked_at

        FROM doubt_session_bookings

        JOIN doubt_sessions
        ON doubt_session_bookings.doubt_session_id =
        doubt_sessions.id

        JOIN courses
        ON doubt_sessions.course_id = courses.id

        JOIN users
        ON doubt_sessions.ta_id = users.id

        WHERE doubt_session_bookings.student_id = ?

        ORDER BY doubt_sessions.scheduled_at ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $sessions = $stmt->get_result();

        view("student/views/my_doubt_sessions.php", ["sessions" => $sessions]);
    }

    public function bookDoubtSession(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];
        $sessionId = (int) ($_GET['session_id'] ?? 0);
        if ($sessionId <= 0) {
            die("Invalid Session ID");
        }

        $success = "";
        $error = "";

        $query = "
        SELECT
        doubt_sessions.id,
        doubt_sessions.title,
        doubt_sessions.scheduled_at,
        doubt_sessions.duration_minutes,
        doubt_sessions.location_or_link,
        doubt_sessions.max_attendees,
        doubt_sessions.status,
        doubt_sessions.notice,
        courses.title AS course_title,
        users.name AS ta_name

        FROM doubt_sessions

        JOIN courses
        ON doubt_sessions.course_id = courses.id

        JOIN users
        ON doubt_sessions.ta_id = users.id

        WHERE doubt_sessions.id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            die("Session Not Found");
        }

        $session = $result->fetch_assoc();

        $checkBookingQuery = "
        SELECT id
        FROM doubt_session_bookings
        WHERE doubt_session_id = ?
        AND student_id = ?
        ";

        $stmt2 = $this->conn->prepare($checkBookingQuery);
        $stmt2->bind_param("ii", $sessionId, $studentId);
        $stmt2->execute();
        $bookingResult = $stmt2->get_result();

        if ($bookingResult->num_rows > 0) {
            $error = "You already booked this session.";
        }

        $countQuery = "
        SELECT COUNT(*) AS total_bookings
        FROM doubt_session_bookings
        WHERE doubt_session_id = ?
        ";

        $stmt3 = $this->conn->prepare($countQuery);
        $stmt3->bind_param("i", $sessionId);
        $stmt3->execute();
        $countResult = $stmt3->get_result()->fetch_assoc();
        $currentBookings = $countResult['total_bookings'];

        if (isset($_POST['book']) && $error === "") {
            if ($currentBookings >= $session['max_attendees']) {
                $error = "Session is full.";
            } else {
                $insertQuery = "
                INSERT INTO doubt_session_bookings
                (
                    doubt_session_id,
                    student_id
                )
                VALUES
                (
                    ?,
                    ?
                )
                ";

                $stmt4 = $this->conn->prepare($insertQuery);
                $stmt4->bind_param("ii", $sessionId, $studentId);

                if ($stmt4->execute()) {
                    $success = "Session Booked Successfully.";
                } else {
                    $error = "Booking Failed.";
                }
            }
        }

        view("student/views/book_doubt_session.php", [
            "session" => $session,
            "success" => $success,
            "error" => $error,
            "currentBookings" => $currentBookings
        ]);
    }

    public function profile(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $studentId = (int) $_SESSION['user_id'];

        $success = "";
        $error = "";

        $uploadDirectory = APP_ROOT . "/uploads/";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST['name'] ?? "");
            $phone = trim($_POST['phone'] ?? "");
            $program = trim($_POST['program'] ?? "");

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
                $targetFile = $uploadDirectory . $fileName;

                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0775, true);
                }

                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile);
                $profilePicture = "uploads/" . $fileName;

                $query = "
                UPDATE users
                SET
                name = ?,
                phone = ?,
                program = ?,
                profile_pic = ?
                WHERE id = ?
                ";

                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ssssi", $name, $phone, $program, $profilePicture, $studentId);
            } else {
                $query = "
                UPDATE users
                SET
                name = ?,
                phone = ?,
                program = ?
                WHERE id = ?
                ";

                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("sssi", $name, $phone, $program, $studentId);
            }

            if ($stmt->execute()) {
                $_SESSION['name'] = $name;
                $success = "Profile Updated Successfully";
            } else {
                $error = "Update Failed";
            }
        }

        $userQuery = "
        SELECT
        name,
        email,
        phone,
        student_id,
        program,
        profile_pic

        FROM users

        WHERE id = ?
        ";

        $stmt2 = $this->conn->prepare($userQuery);
        $stmt2->bind_param("i", $studentId);
        $stmt2->execute();
        $user = $stmt2->get_result()->fetch_assoc();

        view("student/views/profile.php", [
            "user" => $user,
            "success" => $success,
            "error" => $error
        ]);
    }

    public function materials(): void
    {
        Session::requireAuth();
        Session::requireRole('student');

        $materialId = (int) ($_GET['material_id'] ?? 0);
        if ($materialId <= 0) {
            die("Invalid Material ID");
        }

        $materialQuery = "
        SELECT
        file_path
        FROM course_materials
        WHERE id = ?
        ";

        $stmt = $this->conn->prepare($materialQuery);
        $stmt->bind_param("i", $materialId);
        $stmt->execute();
        $materialResult = $stmt->get_result();

        if ($materialResult->num_rows == 0) {
            die("Material Not Found");
        }

        $material = $materialResult->fetch_assoc();

        if (!empty($material['file_path']) && preg_match('/^https?:\/\//i', $material['file_path'])) {
            header("Location: " . $material['file_path']);
            exit();
        }

        $baseDir = realpath(APP_ROOT);
        $filePath = realpath($baseDir . "/" . $material['file_path']);

        if ($filePath === false || strpos($filePath, $baseDir) !== 0 || !file_exists($filePath)) {
            header("HTTP/1.1 404 Not Found");
            echo "File Not Found";
            exit();
        }

        $mimeType = mime_content_type($filePath);
        $filename = basename($filePath);

        header("Content-Type: " . $mimeType);
        header("Content-Disposition: inline; filename=\"" . $filename . "\"");
        header("Content-Length: " . filesize($filePath));

        readfile($filePath);
        exit();
    }

    public function myCourses(): void
    {
        Session::requireAuth();
        Session::requireRole('student');
        view("student/views/my_courses.php");
    }

    public function announcements(): void
    {
        Session::requireAuth();
        Session::requireRole('student');
        view("student/views/announcements.php");
    }

    public function askQuestion(): void
    {
        Session::requireAuth();
        Session::requireRole('student');
        view("student/views/ask_question.php");
    }

    public function bookedSessions(): void
    {
        Session::requireAuth();
        Session::requireRole('student');
        view("student/views/booked_sessions.php");
    }

    public function doubtSessions(): void
    {
        $this->myDoubtSessions();
    }
}
