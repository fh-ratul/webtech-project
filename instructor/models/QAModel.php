<?php

class QAModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getQuestionsByCourse($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                q.id,
                q.question_text,
                q.is_resolved,
                q.created_at,
                u.name AS student_name,
                COUNT(DISTINCT a.id) AS answer_count
            FROM qa_questions q
            JOIN users u ON q.student_id = u.id
            JOIN courses c ON q.course_id = c.id
            LEFT JOIN qa_answers a ON q.id = a.question_id
            WHERE q.course_id = ? AND c.instructor_id = ?
            GROUP BY q.id
            ORDER BY q.is_resolved ASC, q.created_at DESC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function postAnswer($questionId, $answerText, $userId) {
        $stmt = $this->conn->prepare("
            INSERT INTO qa_answers
            (question_id, user_id, user_role, answer_text)
            VALUES (?, ?, 'instructor', ?)
        ");
        $stmt->bind_param("iis", $questionId, $userId, $answerText);
        return $stmt->execute();
    }

    public function endorseAnswer($answerId, $instructorId) {
        $stmt = $this->conn->prepare("
            UPDATE qa_answers a
            JOIN qa_questions q ON a.question_id = q.id
            JOIN courses c ON q.course_id = c.id
            SET a.is_endorsed = 1
            WHERE a.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $answerId, $instructorId);
        return $stmt->execute();
    }

    public function markResolved($questionId, $instructorId) {
        $stmt = $this->conn->prepare("
            UPDATE qa_questions q
            JOIN courses c ON q.course_id = c.id
            SET q.is_resolved = 1
            WHERE q.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $questionId, $instructorId);
        return $stmt->execute();
    }
}

?>