<?php

class QuizModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Get quiz by ID with instructor authorization check
     */
    public function getQuizByIdForInstructor($quizId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                q.*,
                c.title AS course_title,
                c.id AS course_id
            FROM quizzes q
            JOIN courses c ON q.course_id = c.id
            WHERE q.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $quizId, $instructorId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get all quizzes for a course
     */
    public function getQuizzesByCourse($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                q.id,
                q.title,
                q.description,
                q.status,
                q.quiz_type,
                q.total_marks,
                q.pass_mark,
                q.time_limit_minutes,
                q.available_from,
                q.available_until,
                q.created_at,
                COUNT(DISTINCT a.id) AS attempt_count,
                AVG(CASE WHEN a.status = 'submitted' THEN a.score ELSE NULL END) AS avg_score
            FROM quizzes q
            JOIN courses c ON q.course_id = c.id
            LEFT JOIN attempts a ON q.id = a.quiz_id
            WHERE q.course_id = ? AND c.instructor_id = ?
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Create new quiz
     */
    public function createQuiz($courseId, $title, $description, $timeLimitMinutes, $totalMarks, $passMark, $quizType, $availableFrom, $availableUntil, $status) {
        $stmt = $this->conn->prepare("
            INSERT INTO quizzes
            (course_id, title, description, time_limit_minutes, total_marks, pass_mark, quiz_type, available_from, available_until, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issiddssss", $courseId, $title, $description, $timeLimitMinutes, $totalMarks, $passMark, $quizType, $availableFrom, $availableUntil, $status);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Update quiz status (publish/unpublish)
     */
    public function updateQuizStatus($quizId, $instructorId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE quizzes q
            JOIN courses c ON q.course_id = c.id
            SET q.status = ?
            WHERE q.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("sii", $status, $quizId, $instructorId);
        return $stmt->execute();
    }

    /**
     * Get quiz statistics
     */
    public function getQuizStats($quizId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                COUNT(*) AS total_attempts,
                AVG(score) AS avg_score,
                MAX(score) AS max_score,
                MIN(score) AS min_score,
                SUM(CASE WHEN score >= (SELECT pass_mark FROM quizzes WHERE id = ?) THEN 1 ELSE 0 END) AS pass_count
            FROM attempts
            WHERE quiz_id = ?
            AND status = 'submitted'
            AND EXISTS (SELECT 1 FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND c.instructor_id = ?)
        ");
        $stmt->bind_param("iiii", $quizId, $quizId, $quizId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get score distribution for AJAX endpoint
     */
    public function getScoreDistribution($quizId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                CASE
                    WHEN score >= 0 AND score <= 20 THEN '0-20'
                    WHEN score > 20 AND score <= 40 THEN '21-40'
                    WHEN score > 40 AND score <= 60 THEN '41-60'
                    WHEN score > 60 AND score <= 80 THEN '61-80'
                    WHEN score > 80 AND score <= 100 THEN '81-100'
                END AS score_range,
                COUNT(*) AS count
            FROM attempts
            WHERE quiz_id = ?
            AND status = 'submitted'
            AND EXISTS (SELECT 1 FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND c.instructor_id = ?)
            GROUP BY score_range
            ORDER BY score_range
        ");
        $stmt->bind_param("iii", $quizId, $quizId, $instructorId);
        $stmt->execute();
        $result = $stmt->get_result();

        $distribution = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['score_range'] !== null) {
                $distribution[] = [
                    'range' => $row['score_range'],
                    'count' => (int) $row['count']
                ];
            }
        }
        return $distribution;
    }

    /**
     * Get all attempts for a quiz
     */
    public function getQuizAttempts($quizId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                a.id,
                a.score,
                a.total_marks,
                a.duration_minutes,
                a.status,
                a.created_at,
                u.name AS student_name,
                u.email AS student_email,
                q.pass_mark
            FROM attempts a
            JOIN users u ON a.student_id = u.id
            JOIN quizzes q ON a.quiz_id = q.id
            JOIN courses c ON q.course_id = c.id
            WHERE a.quiz_id = ? AND c.instructor_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->bind_param("ii", $quizId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>
