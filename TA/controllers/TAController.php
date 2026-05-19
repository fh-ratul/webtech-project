<?php

class TAController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function requireTa(): int
    {
        Session::requireAuth();
        Session::requireRole('ta');
        return (int) $_SESSION['user_id'];
    }

    private function getAssignedCourse(int $courseId, int $taId): array
    {
        $courseQuery = "
        SELECT
        courses.id,
        courses.title,
        courses.description,
        subjects.name AS subject_name

        FROM course_tas

        JOIN courses
        ON course_tas.course_id = courses.id

        JOIN subjects
        ON courses.subject_id = subjects.id

        WHERE course_tas.ta_id = ?
        AND courses.id = ?
        ";

        $stmt = $this->conn->prepare($courseQuery);
        $stmt->bind_param("ii", $taId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            die("Course Not Found");
        }

        return $result->fetch_assoc();
    }

    public function dashboard(): void
    {
        $taId = $this->requireTa();

        $statsQuery = "
        SELECT COUNT(*) AS total_courses
        FROM course_tas
        WHERE ta_id = ?
        ";

        $stmtStats = $this->conn->prepare($statsQuery);
        $stmtStats->bind_param("i", $taId);
        $stmtStats->execute();
        $stats = $stmtStats->get_result()->fetch_assoc();

        $studentCountQuery = "
        SELECT COUNT(DISTINCT enrollments.student_id) AS total_students
        FROM enrollments
        JOIN course_tas
        ON enrollments.course_id = course_tas.course_id
        WHERE course_tas.ta_id = ?
        AND enrollments.status = 'active'
        ";

        $stmtStudents = $this->conn->prepare($studentCountQuery);
        $stmtStudents->bind_param("i", $taId);
        $stmtStudents->execute();
        $studentStats = $stmtStudents->get_result()->fetch_assoc();

        $sessionCountQuery = "
        SELECT COUNT(*) AS upcoming_sessions
        FROM doubt_sessions
        WHERE ta_id = ?
        AND status = 'scheduled'
        AND scheduled_at >= NOW()
        ";

        $stmtSessions = $this->conn->prepare($sessionCountQuery);
        $stmtSessions->bind_param("i", $taId);
        $stmtSessions->execute();
        $sessionStats = $stmtSessions->get_result()->fetch_assoc();

        $courseQuery = "
        SELECT
        courses.id,
        courses.title,
        courses.description,
        subjects.name AS subject_name

        FROM course_tas

        JOIN courses
        ON course_tas.course_id = courses.id

        JOIN subjects
        ON courses.subject_id = subjects.id

        WHERE course_tas.ta_id = ?

        ORDER BY courses.created_at DESC
        ";

        $stmtCourses = $this->conn->prepare($courseQuery);
        $stmtCourses->bind_param("i", $taId);
        $stmtCourses->execute();
        $courses = $stmtCourses->get_result();

        view("TA/views/dashboard.php", [
            "stats" => $stats,
            "studentStats" => $studentStats,
            "sessionStats" => $sessionStats,
            "courses" => $courses
        ]);
    }

    public function assignedCourses(): void
    {
        $taId = $this->requireTa();

        $courseQuery = "
        SELECT
        courses.id,
        courses.title,
        courses.description,
        subjects.name AS subject_name

        FROM course_tas

        JOIN courses
        ON course_tas.course_id = courses.id

        JOIN subjects
        ON courses.subject_id = subjects.id

        WHERE course_tas.ta_id = ?

        ORDER BY courses.title
        ";

        $stmtCourses = $this->conn->prepare($courseQuery);
        $stmtCourses->bind_param("i", $taId);
        $stmtCourses->execute();
        $courses = $stmtCourses->get_result();

        view("TA/views/assigned_courses.php", [
            "courses" => $courses
        ]);
    }

    public function courseDetails(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);

        $studentQuery = "
        SELECT
        users.name,
        users.email,
        enrollments.status

        FROM enrollments

        JOIN users
        ON enrollments.student_id = users.id

        WHERE enrollments.course_id = ?

        ORDER BY users.name
        ";

        $stmtStudents = $this->conn->prepare($studentQuery);
        $stmtStudents->bind_param("i", $courseId);
        $stmtStudents->execute();
        $students = $stmtStudents->get_result();

        $quizQuery = "
        SELECT
        id,
        title,
        quiz_type,
        status,
        total_marks,
        available_until

        FROM quizzes

        WHERE course_id = ?

        ORDER BY id DESC
        ";

        $stmtQuizzes = $this->conn->prepare($quizQuery);
        $stmtQuizzes->bind_param("i", $courseId);
        $stmtQuizzes->execute();
        $quizzes = $stmtQuizzes->get_result();

        view("TA/views/course_details.php", [
            "course" => $course,
            "students" => $students,
            "quizzes" => $quizzes,
            "courseId" => $courseId
        ]);
    }

    public function createPracticeQuiz(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);

        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = trim($_POST['title'] ?? "");
            $description = trim($_POST['description'] ?? "");
            $timeLimit = (int) ($_POST['time_limit'] ?? 0);
            $totalMarks = (int) ($_POST['total_marks'] ?? 0);
            $passMark = (int) ($_POST['pass_mark'] ?? 0);
            $from = $_POST['available_from'] ?? null;
            $until = $_POST['available_until'] ?? null;

            if ($title === "" || $description === "" || $timeLimit <= 0 || $totalMarks <= 0 || $passMark < 0) {
                $error = "All fields are required.";
            } else {
                $query = "
                INSERT INTO quizzes
                (
                    course_id,
                    created_by,
                    title,
                    description,
                    time_limit_minutes,
                    total_marks,
                    pass_mark,
                    quiz_type,
                    status,
                    available_from,
                    available_until
                )
                VALUES
                (
                    ?,?,?,?,?,?,?,?,?,?,?
                )
                ";

                $status = "draft";
                $quizType = "practice";

                $stmt = $this->conn->prepare($query);
                $stmt->bind_param(
                    "iissiisssss",
                    $courseId,
                    $taId,
                    $title,
                    $description,
                    $timeLimit,
                    $totalMarks,
                    $passMark,
                    $quizType,
                    $status,
                    $from,
                    $until
                );

                if ($stmt->execute()) {
                    $success = "Practice quiz created successfully.";
                } else {
                    $error = "Failed to create quiz.";
                }
            }
        }

        view("TA/views/create_practice_quiz.php", [
            "course" => $course,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function questionBank(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);
        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? "";

            if ($action == 'update_question') {
                $questionId = (int) $_POST['question_id'];
                $questionText = trim($_POST['question_text']);
                $marks = (int) $_POST['marks'];
                $correctOptionId = (int) $_POST['correct_option_id'];

                $updateQuestion = "
                UPDATE questions
                JOIN quizzes
                ON questions.quiz_id = quizzes.id
                SET questions.question_text = ?, questions.marks = ?
                WHERE questions.id = ?
                AND quizzes.course_id = ?
                AND quizzes.quiz_type = 'practice'
                ";

                $stmtUpdate = $this->conn->prepare($updateQuestion);
                $stmtUpdate->bind_param("siii", $questionText, $marks, $questionId, $courseId);

                if ($stmtUpdate->execute()) {
                    if (isset($_POST['option_id']) && isset($_POST['option_text'])) {
                        foreach ($_POST['option_id'] as $index => $optionId) {
                            $optionText = trim($_POST['option_text'][$index]);
                            $isCorrect = ($optionId == $correctOptionId) ? 1 : 0;

                            $updateOption = "
                            UPDATE options
                            SET option_text = ?, is_correct = ?
                            WHERE id = ?
                            AND question_id = ?
                            ";

                            $stmtOption = $this->conn->prepare($updateOption);
                            $stmtOption->bind_param("siii", $optionText, $isCorrect, $optionId, $questionId);
                            $stmtOption->execute();
                        }
                    }

                    $success = "Question updated.";
                } else {
                    $error = "Failed to update question.";
                }
            }

            if ($action == 'delete_question') {
                $questionId = (int) $_POST['question_id'];

                $deleteOptions = "DELETE FROM options WHERE question_id = ?";
                $stmtDelOpt = $this->conn->prepare($deleteOptions);
                $stmtDelOpt->bind_param("i", $questionId);
                $stmtDelOpt->execute();

                $deleteQuestion = "
                DELETE questions
                FROM questions
                JOIN quizzes
                ON questions.quiz_id = quizzes.id
                WHERE questions.id = ?
                AND quizzes.course_id = ?
                AND quizzes.quiz_type = 'practice'
                ";

                $stmtDelQ = $this->conn->prepare($deleteQuestion);
                $stmtDelQ->bind_param("ii", $questionId, $courseId);

                if ($stmtDelQ->execute()) {
                    $success = "Question deleted.";
                } else {
                    $error = "Failed to delete question.";
                }
            }

            if ($action == 'reuse_question') {
                $sourceQuestionId = (int) $_POST['source_question_id'];
                $targetQuizId = (int) $_POST['target_quiz_id'];

                $quizCheck = "
                SELECT id
                FROM quizzes
                WHERE id = ?
                AND course_id = ?
                AND quiz_type = 'practice'
                ";

                $stmtQuizCheck = $this->conn->prepare($quizCheck);
                $stmtQuizCheck->bind_param("ii", $targetQuizId, $courseId);
                $stmtQuizCheck->execute();

                if ($stmtQuizCheck->get_result()->num_rows == 0) {
                    $error = "Target quiz not found.";
                } else {
                    $sourceQuery = "
                    SELECT questions.question_text, questions.marks
                    FROM questions
                    JOIN quizzes
                    ON questions.quiz_id = quizzes.id
                    WHERE questions.id = ?
                    AND quizzes.course_id = ?
                    AND quizzes.quiz_type = 'practice'
                    ";

                    $stmtSource = $this->conn->prepare($sourceQuery);
                    $stmtSource->bind_param("ii", $sourceQuestionId, $courseId);
                    $stmtSource->execute();
                    $sourceResult = $stmtSource->get_result();

                    if ($sourceResult->num_rows > 0) {
                        $source = $sourceResult->fetch_assoc();

                        $orderQuery = "SELECT COALESCE(MAX(order_index), 0) AS max_order FROM questions WHERE quiz_id = ?";
                        $stmtOrder = $this->conn->prepare($orderQuery);
                        $stmtOrder->bind_param("i", $targetQuizId);
                        $stmtOrder->execute();
                        $orderResult = $stmtOrder->get_result()->fetch_assoc();
                        $orderIndex = (int) $orderResult['max_order'] + 1;

                        $insertQuestion = "
                        INSERT INTO questions
                        (quiz_id, question_text, marks, order_index)
                        VALUES
                        (?, ?, ?, ?)
                        ";

                        $stmtInsert = $this->conn->prepare($insertQuestion);
                        $stmtInsert->bind_param("isii", $targetQuizId, $source['question_text'], $source['marks'], $orderIndex);

                        if ($stmtInsert->execute()) {
                            $newQuestionId = $this->conn->insert_id;

                            $optionsQuery = "SELECT option_text, is_correct FROM options WHERE question_id = ?";
                            $stmtOptions = $this->conn->prepare($optionsQuery);
                            $stmtOptions->bind_param("i", $sourceQuestionId);
                            $stmtOptions->execute();
                            $optionsResult = $stmtOptions->get_result();

                            while ($option = $optionsResult->fetch_assoc()) {
                                $insertOption = "
                                INSERT INTO options
                                (question_id, option_text, is_correct)
                                VALUES
                                (?, ?, ?)
                                ";

                                $stmtOption = $this->conn->prepare($insertOption);
                                $stmtOption->bind_param("isi", $newQuestionId, $option['option_text'], $option['is_correct']);
                                $stmtOption->execute();
                            }

                            $success = "Question reused in target quiz.";
                        } else {
                            $error = "Failed to reuse question.";
                        }
                    }
                }
            }
        }

        $quizListQuery = "
        SELECT id, title
        FROM quizzes
        WHERE course_id = ?
        AND quiz_type = 'practice'
        ORDER BY title
        ";

        $stmtQuizList = $this->conn->prepare($quizListQuery);
        $stmtQuizList->bind_param("i", $courseId);
        $stmtQuizList->execute();
        $quizList = $stmtQuizList->get_result();

        $reuseQuery = "
        SELECT questions.id, questions.question_text, quizzes.title AS quiz_title
        FROM questions
        JOIN quizzes
        ON questions.quiz_id = quizzes.id
        WHERE quizzes.course_id = ?
        AND quizzes.quiz_type = 'practice'
        ORDER BY quizzes.title, questions.id DESC
        ";

        $stmtReuse = $this->conn->prepare($reuseQuery);
        $stmtReuse->bind_param("i", $courseId);
        $stmtReuse->execute();
        $reuseQuestions = $stmtReuse->get_result();

        $questionQuery = "
        SELECT questions.id, questions.question_text, questions.marks, quizzes.title AS quiz_title
        FROM questions
        JOIN quizzes
        ON questions.quiz_id = quizzes.id
        WHERE quizzes.course_id = ?
        AND quizzes.quiz_type = 'practice'
        ORDER BY quizzes.title, questions.id DESC
        ";

        $stmtQuestions = $this->conn->prepare($questionQuery);
        $stmtQuestions->bind_param("i", $courseId);
        $stmtQuestions->execute();
        $questions = $stmtQuestions->get_result();

        view("TA/views/question_bank.php", [
            "course" => $course,
            "quizList" => $quizList,
            "reuseQuestions" => $reuseQuestions,
            "questions" => $questions,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function studentResults(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);

        $attemptQuery = "
        SELECT
        users.name AS student_name,
        users.email,
        quizzes.title AS quiz_title,
        quizzes.total_marks,
        quizzes.pass_mark,
        attempts.score,
        attempts.started_at,
        attempts.completed_at,
        TIMESTAMPDIFF(MINUTE, attempts.started_at, attempts.completed_at) AS duration_minutes,
        attempts.completed_at

        FROM attempts

        JOIN users
        ON attempts.student_id = users.id

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        WHERE quizzes.course_id = ?

        ORDER BY attempts.completed_at DESC
        ";

        $stmtAttempts = $this->conn->prepare($attemptQuery);
        $stmtAttempts->bind_param("i", $courseId);
        $stmtAttempts->execute();
        $attemptsResult = $stmtAttempts->get_result();
        $attempts = [];
        $totalAttempts = 0;
        $passedAttempts = 0;
        $scoreSum = 0;
        $durationSum = 0;

        while ($attempt = $attemptsResult->fetch_assoc()) {
            $attempts[] = $attempt;
            $totalAttempts++;
            $scoreSum += (float) $attempt['score'];
            $durationSum += max(0, (int) $attempt['duration_minutes']);

            if ((float) $attempt['score'] >= (float) $attempt['pass_mark']) {
                $passedAttempts++;
            }
        }

        $averageScore = $totalAttempts > 0 ? $scoreSum / $totalAttempts : 0;
        $passRate = $totalAttempts > 0 ? ($passedAttempts / $totalAttempts) * 100 : 0;
        $averageDuration = $totalAttempts > 0 ? $durationSum / $totalAttempts : 0;

        view("TA/views/student_results.php", [
            "course" => $course,
            "attempts" => $attempts,
            "courseId" => $courseId,
            "totalAttempts" => $totalAttempts,
            "passedAttempts" => $passedAttempts,
            "averageScore" => $averageScore,
            "passRate" => $passRate,
            "averageDuration" => $averageDuration
        ]);
    }

    public function atRiskStudents(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);
        $success = "";
        $error = "";

        $threshold = (float) ($_GET['threshold'] ?? 60);
        if (isset($_POST['threshold'])) {
            $threshold = (float) $_POST['threshold'];
        }

        if (isset($_POST['flag_student'])) {
            $studentId = (int) $_POST['student_id'];
            $reason = trim($_POST['reason'] ?? "");

            $insertQuery = "
            INSERT INTO at_risk_flags
            (course_id, student_id, flagged_by, threshold_percent, reason)
            VALUES
            (?, ?, ?, ?, ?)
            ";

            $stmtInsert = $this->conn->prepare($insertQuery);
            $stmtInsert->bind_param("iiids", $courseId, $studentId, $taId, $threshold, $reason);

            if ($stmtInsert->execute()) {
                $success = "Student flagged successfully.";
            } else {
                $error = "Failed to flag student.";
            }
        }

        $reportQuery = "
        SELECT
        users.id AS student_id,
        users.name,
        users.email,
        COUNT(attempts.id) AS attempt_count,
        AVG((attempts.score / NULLIF(quizzes.total_marks, 0)) * 100) AS avg_percent

        FROM enrollments

        JOIN users
        ON enrollments.student_id = users.id

        LEFT JOIN attempts
        ON attempts.student_id = users.id

        LEFT JOIN quizzes
        ON attempts.quiz_id = quizzes.id
        AND quizzes.course_id = enrollments.course_id

        WHERE enrollments.course_id = ?
        AND enrollments.status = 'active'

        GROUP BY users.id
        ORDER BY users.name
        ";

        $stmtReport = $this->conn->prepare($reportQuery);
        $stmtReport->bind_param("i", $courseId);
        $stmtReport->execute();
        $reportRows = $stmtReport->get_result();

        $flagQuery = "
        SELECT student_id, threshold_percent, reason, created_at
        FROM at_risk_flags
        WHERE course_id = ?
        ORDER BY created_at DESC
        ";

        $stmtFlags = $this->conn->prepare($flagQuery);
        $stmtFlags->bind_param("i", $courseId);
        $stmtFlags->execute();
        $flags = $stmtFlags->get_result();

        view("TA/views/at_risk_students.php", [
            "course" => $course,
            "reportRows" => $reportRows,
            "flags" => $flags,
            "threshold" => $threshold,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function announcements(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);

        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_announcement'])) {
            $title = trim($_POST['title'] ?? "");
            $body = trim($_POST['body'] ?? "");

            if ($title === "" || $body === "") {
                $error = "Title and body are required.";
            } else {
                $insertQuery = "
                INSERT INTO announcements
                (course_id, title, body, posted_by, posted_role, created_at)
                VALUES
                (?, ?, ?, ?, 'ta', NOW())
                ";

                $stmt = $this->conn->prepare($insertQuery);
                $stmt->bind_param("issi", $courseId, $title, $body, $taId);

                if ($stmt->execute()) {
                    $success = "Announcement posted.";
                } else {
                    $error = "Failed to post announcement.";
                }
            }
        }

        if (isset($_GET['delete'])) {
            $announcementId = (int) $_GET['delete'];

            $deleteQuery = "
            DELETE announcements
            FROM announcements
            JOIN courses
            ON announcements.course_id = courses.id
            JOIN course_tas
            ON courses.id = course_tas.course_id
            WHERE announcements.id = ?
            AND courses.id = ?
            AND course_tas.ta_id = ?
            AND announcements.posted_by = ?
            AND announcements.posted_role = 'ta'
            ";

            $stmtDelete = $this->conn->prepare($deleteQuery);
            $stmtDelete->bind_param("iiii", $announcementId, $courseId, $taId, $taId);

            if ($stmtDelete->execute()) {
                $success = "Announcement deleted.";
            } else {
                $error = "Failed to delete announcement.";
            }
        }

        $listQuery = "
        SELECT id, title, body, posted_role, created_at
        FROM announcements
        WHERE course_id = ?
        ORDER BY created_at DESC
        ";

        $stmtList = $this->conn->prepare($listQuery);
        $stmtList->bind_param("i", $courseId);
        $stmtList->execute();
        $announcements = $stmtList->get_result();

        view("TA/views/announcements.php", [
            "course" => $course,
            "announcements" => $announcements,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function materials(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);
        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_material'])) {
            $title = trim($_POST['title'] ?? "");
            $materialType = $_POST['material_type'] ?? "";
            $link = trim($_POST['link'] ?? "");

            if ($title === "") {
                $error = "Material title is required.";
            } else {
                $filePath = "";

                if ($materialType == 'file') {
                    if (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] != 0) {
                        $error = "Please upload a file.";
                    } else {
                        $fileName = time() . "_" . basename($_FILES['material_file']['name']);
                        $targetPath = APP_ROOT . "/uploads/" . $fileName;

                        if (!is_dir(APP_ROOT . "/uploads")) {
                            mkdir(APP_ROOT . "/uploads", 0775, true);
                        }

                        if (move_uploaded_file($_FILES['material_file']['tmp_name'], $targetPath)) {
                            $filePath = "uploads/" . $fileName;
                        } else {
                            $error = "File upload failed.";
                        }
                    }
                }

                if ($materialType == 'link') {
                    if ($link === "") {
                        $error = "Please provide a link.";
                    } else {
                        $filePath = $link;
                    }
                }

                if ($error === "") {
                    $insertQuery = "
                    INSERT INTO course_materials
                    (course_id, title, file_path, material_type, uploaded_by, uploaded_role)
                    VALUES
                    (?, ?, ?, ?, ?, 'ta')
                    ";

                    $stmt = $this->conn->prepare($insertQuery);
                    $stmt->bind_param("isssi", $courseId, $title, $filePath, $materialType, $taId);

                    if ($stmt->execute()) {
                        $success = "Material uploaded.";
                    } else {
                        $error = "Failed to upload material.";
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_material'])) {
            $materialId = (int) $_POST['material_id'];
            $title = trim($_POST['title'] ?? "");
            $materialType = $_POST['material_type'] ?? "";
            $link = trim($_POST['link'] ?? "");
            $filePath = $_POST['existing_path'] ?? "";

            if ($materialType == 'file' && isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
                $fileName = time() . "_" . basename($_FILES['material_file']['name']);
                $targetPath = APP_ROOT . "/uploads/" . $fileName;

                if (move_uploaded_file($_FILES['material_file']['tmp_name'], $targetPath)) {
                    $filePath = "uploads/" . $fileName;
                }
            }

            if ($materialType == 'link' && $link !== "") {
                $filePath = $link;
            }

            $updateQuery = "
            UPDATE course_materials
            SET title = ?, file_path = ?, material_type = ?
            WHERE id = ?
            AND course_id = ?
            AND uploaded_by = ?
            AND uploaded_role = 'ta'
            ";

            $stmtUpdate = $this->conn->prepare($updateQuery);
            $stmtUpdate->bind_param("sssiii", $title, $filePath, $materialType, $materialId, $courseId, $taId);

            if ($stmtUpdate->execute()) {
                $success = "Material updated.";
            } else {
                $error = "Failed to update material.";
            }
        }

        if (isset($_GET['delete'])) {
            $materialId = (int) $_GET['delete'];

            $fileQuery = "SELECT file_path, material_type FROM course_materials WHERE id = ? AND course_id = ? AND uploaded_by = ? AND uploaded_role = 'ta'";
            $stmtFile = $this->conn->prepare($fileQuery);
            $stmtFile->bind_param("iii", $materialId, $courseId, $taId);
            $stmtFile->execute();
            $fileResult = $stmtFile->get_result();

            if ($fileResult->num_rows > 0) {
                $fileRow = $fileResult->fetch_assoc();

                if ($fileRow['material_type'] == 'file' && !empty($fileRow['file_path'])) {
                    $baseDir = realpath(APP_ROOT);
                    $fullPath = realpath($baseDir . "/" . $fileRow['file_path']);

                    if ($fullPath !== false && strpos($fullPath, $baseDir) === 0 && file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }

            $deleteQuery = "DELETE FROM course_materials WHERE id = ? AND course_id = ? AND uploaded_by = ? AND uploaded_role = 'ta'";
            $stmtDelete = $this->conn->prepare($deleteQuery);
            $stmtDelete->bind_param("iii", $materialId, $courseId, $taId);

            if ($stmtDelete->execute()) {
                $success = "Material deleted.";
            } else {
                $error = "Failed to delete material.";
            }
        }

        $listQuery = "
        SELECT id, title, file_path, material_type, uploaded_by, uploaded_role
        FROM course_materials
        WHERE course_id = ?
        ORDER BY id DESC
        ";

        $stmtList = $this->conn->prepare($listQuery);
        $stmtList->bind_param("i", $courseId);
        $stmtList->execute();
        $materials = $stmtList->get_result();

        view("TA/views/materials.php", [
            "course" => $course,
            "materials" => $materials,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function qaBoard(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);
        $success = "";
        $error = "";

        if (isset($_GET['endorse'])) {
            $answerId = (int) $_GET['endorse'];

            $endorseQuery = "
            UPDATE qa_answers
            JOIN qa_questions
            ON qa_answers.qa_question_id = qa_questions.id
            SET qa_answers.is_endorsed = 1
            WHERE qa_answers.id = ?
            AND qa_questions.course_id = ?
            ";

            $stmtEndorse = $this->conn->prepare($endorseQuery);
            $stmtEndorse->bind_param("ii", $answerId, $courseId);

            if ($stmtEndorse->execute()) {
                $success = "Answer endorsed.";
            } else {
                $error = "Failed to endorse answer.";
            }
        }

        if (isset($_GET['resolve'])) {
            $questionId = (int) $_GET['resolve'];

            $resolveQuery = "
            UPDATE qa_questions
            SET is_resolved = 1
            WHERE id = ?
            AND course_id = ?
            ";

            $stmtResolve = $this->conn->prepare($resolveQuery);
            $stmtResolve->bind_param("ii", $questionId, $courseId);

            if ($stmtResolve->execute()) {
                $success = "Question marked as resolved.";
            } else {
                $error = "Failed to resolve question.";
            }
        }

        $questionQuery = "
        SELECT
        qa_questions.id,
        qa_questions.title,
        qa_questions.body,
        qa_questions.is_resolved,
        qa_questions.created_at,
        users.name AS student_name

        FROM qa_questions

        JOIN users
        ON qa_questions.student_id = users.id

        WHERE qa_questions.course_id = ?

        ORDER BY qa_questions.created_at DESC
        ";

        $stmtQuestions = $this->conn->prepare($questionQuery);
        $stmtQuestions->bind_param("i", $courseId);
        $stmtQuestions->execute();
        $questions = $stmtQuestions->get_result();

        view("TA/views/qa_board.php", [
            "course" => $course,
            "questions" => $questions,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function qaAnswerAjax(): void
    {
        $taId = $this->requireTa();
        header("Content-Type: application/json");

        $questionId = (int) ($_POST['question_id'] ?? 0);
        $body = trim($_POST['body'] ?? "");

        if ($questionId <= 0 || $body === "") {
            echo json_encode(["ok" => false, "message" => "Question and answer are required."]);
            return;
        }

        $checkQuery = "
        SELECT qa_questions.id
        FROM qa_questions
        JOIN course_tas
        ON qa_questions.course_id = course_tas.course_id
        WHERE qa_questions.id = ?
        AND course_tas.ta_id = ?
        ";

        $stmtCheck = $this->conn->prepare($checkQuery);
        $stmtCheck->bind_param("ii", $questionId, $taId);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result();

        if ($checkResult->num_rows === 0) {
            echo json_encode(["ok" => false, "message" => "Unauthorized."]);
            return;
        }

        $insertQuery = "
        INSERT INTO qa_answers
        (qa_question_id, author_id, body, is_endorsed, created_at)
        VALUES
        (?, ?, ?, 0, NOW())
        ";

        $stmtInsert = $this->conn->prepare($insertQuery);
        $stmtInsert->bind_param("iis", $questionId, $taId, $body);

        if ($stmtInsert->execute()) {
            echo json_encode(["ok" => true]);
        } else {
            echo json_encode(["ok" => false, "message" => "Failed to post answer."]);
        }
    }

    public function doubtSessions(): void
    {
        $taId = $this->requireTa();

        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? "";
            $sessionId = (int) ($_POST['session_id'] ?? 0);
            $notice = trim($_POST['notice'] ?? "");

            if ($sessionId > 0 && ($action == 'cancel' || $action == 'reschedule')) {
                if ($action == 'cancel') {
                    $updateQuery = "
                    UPDATE doubt_sessions
                    SET status = 'cancelled', notice = ?
                    WHERE id = ?
                    AND ta_id = ?
                    ";

                    $stmtUpdate = $this->conn->prepare($updateQuery);
                    $stmtUpdate->bind_param("sii", $notice, $sessionId, $taId);
                } else {
                    $scheduledAt = $_POST['scheduled_at'] ?? "";
                    if ($scheduledAt === "") {
                        $error = "New schedule time is required.";
                    } else {
                        $updateQuery = "
                        UPDATE doubt_sessions
                        SET status = 'rescheduled', scheduled_at = ?, notice = ?
                        WHERE id = ?
                        AND ta_id = ?
                        ";

                        $stmtUpdate = $this->conn->prepare($updateQuery);
                        $stmtUpdate->bind_param("ssii", $scheduledAt, $notice, $sessionId, $taId);
                    }
                }

                if ($error === "" && isset($stmtUpdate)) {
                    if ($stmtUpdate->execute()) {
                        $success = $action == 'cancel' ? "Session cancelled." : "Session rescheduled.";
                    } else {
                        $error = "Failed to update session.";
                    }
                }
            }
        }

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
        courses.title AS course_title

        FROM doubt_sessions

        JOIN courses
        ON doubt_sessions.course_id = courses.id

        WHERE doubt_sessions.ta_id = ?

        ORDER BY doubt_sessions.scheduled_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $taId);
        $stmt->execute();
        $sessions = $stmt->get_result();

        view("TA/views/doubt_sessions.php", [
            "sessions" => $sessions,
            "success" => $success,
            "error" => $error
        ]);
    }

    public function createDoubtSession(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);
        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = trim($_POST['title'] ?? "");
            $scheduledAt = $_POST['scheduled_at'] ?? "";
            $duration = (int) ($_POST['duration_minutes'] ?? 0);
            $location = trim($_POST['location_or_link'] ?? "");
            $maxAttendees = (int) ($_POST['max_attendees'] ?? 0);

            if ($title === "" || $scheduledAt === "" || $duration <= 0 || $location === "" || $maxAttendees <= 0) {
                $error = "All fields are required.";
            } else {
                $insertQuery = "
                INSERT INTO doubt_sessions
                (course_id, ta_id, title, scheduled_at, duration_minutes, location_or_link, max_attendees)
                VALUES
                (?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = $this->conn->prepare($insertQuery);
                $stmt->bind_param("iissisi", $courseId, $taId, $title, $scheduledAt, $duration, $location, $maxAttendees);

                if ($stmt->execute()) {
                    $success = "Doubt session created.";
                } else {
                    $error = "Failed to create session.";
                }
            }
        }

        view("TA/views/create_doubt_session.php", [
            "course" => $course,
            "success" => $success,
            "error" => $error,
            "courseId" => $courseId
        ]);
    }

    public function bookings(): void
    {
        $taId = $this->requireTa();

        $query = "
        SELECT
        doubt_sessions.id AS session_id,
        doubt_sessions.title,
        doubt_sessions.scheduled_at,
        doubt_sessions.status,
        courses.title AS course_title,
        users.name AS student_name,
        doubt_session_bookings.booked_at

        FROM doubt_session_bookings

        JOIN doubt_sessions
        ON doubt_session_bookings.doubt_session_id = doubt_sessions.id

        JOIN courses
        ON doubt_sessions.course_id = courses.id

        JOIN users
        ON doubt_session_bookings.student_id = users.id

        WHERE doubt_sessions.ta_id = ?

        ORDER BY doubt_sessions.scheduled_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $taId);
        $stmt->execute();
        $bookings = $stmt->get_result();

        view("TA/views/bookings.php", ["bookings" => $bookings]);
    }

    public function profile(): void
    {
        $taId = $this->requireTa();

        $success = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? "");
            $department = trim($_POST['department'] ?? "");
            $bio = trim($_POST['bio'] ?? "");

            $profilePicture = "";

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
                $uploadDir = APP_ROOT . "/uploads/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $fileName);
                $profilePicture = "uploads/" . $fileName;
            }

            $updateQuery = "
            UPDATE users
            SET name = ?, department = ?, bio = ?
            " . ($profilePicture !== "" ? ", profile_pic = ?" : "") . "
            WHERE id = ?
            ";

            if ($profilePicture !== "") {
                $stmt = $this->conn->prepare($updateQuery);
                $stmt->bind_param("ssssi", $name, $department, $bio, $profilePicture, $taId);
            } else {
                $stmt = $this->conn->prepare($updateQuery);
                $stmt->bind_param("sssi", $name, $department, $bio, $taId);
            }

            if ($stmt->execute()) {
                $success = "Profile updated.";
            } else {
                $error = "Failed to update profile.";
            }
        }

        $userQuery = "SELECT id, name, email, department, bio, profile_pic FROM users WHERE id = ?";
        $stmtUser = $this->conn->prepare($userQuery);
        $stmtUser->bind_param("i", $taId);
        $stmtUser->execute();
        $user = $stmtUser->get_result()->fetch_assoc();

        $profilePictureUrl = !empty($user['profile_pic']) ? "/" . $user['profile_pic'] : "";

        view("TA/views/profile.php", [
            "user" => $user,
            "profilePictureUrl" => $profilePictureUrl,
            "success" => $success,
            "error" => $error
        ]);
    }

    public function reports(): void
    {
        $taId = $this->requireTa();
        $courseId = (int) ($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            die("Invalid Course ID");
        }

        $course = $this->getAssignedCourse($courseId, $taId);

        $enrollmentQuery = "
        SELECT
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) AS dropped_count
        FROM enrollments
        WHERE course_id = ?
        ";

        $stmtEnroll = $this->conn->prepare($enrollmentQuery);
        $stmtEnroll->bind_param("i", $courseId);
        $stmtEnroll->execute();
        $enrollmentStats = $stmtEnroll->get_result()->fetch_assoc();

        $quizReportQuery = "
        SELECT
        quizzes.id,
        quizzes.title,
        quizzes.total_marks,
        COUNT(DISTINCT attempts.student_id) AS students_attempted,
        AVG(attempts.score) AS avg_score

        FROM quizzes
        LEFT JOIN attempts
        ON attempts.quiz_id = quizzes.id

        WHERE quizzes.course_id = ?

        GROUP BY quizzes.id
        ORDER BY quizzes.id DESC
        ";

        $stmtQuizReport = $this->conn->prepare($quizReportQuery);
        $stmtQuizReport->bind_param("i", $courseId);
        $stmtQuizReport->execute();
        $quizReports = $stmtQuizReport->get_result();

        $activeCount = (int) $enrollmentStats['active_count'];
        $droppedCount = (int) $enrollmentStats['dropped_count'];
        $dropRate = ($activeCount + $droppedCount) > 0 ? ($droppedCount / ($activeCount + $droppedCount)) * 100 : 0;

        view("TA/views/reports.php", [
            "course" => $course,
            "activeCount" => $activeCount,
            "droppedCount" => $droppedCount,
            "dropRate" => $dropRate,
            "quizReports" => $quizReports,
            "courseId" => $courseId
        ]);
    }
}
