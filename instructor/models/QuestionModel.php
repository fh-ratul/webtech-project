<?php

class QuestionModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addQuestion($quizId, $questionText, $marks, $orderIndex) {
        $stmt = $this->conn->prepare("
            INSERT INTO questions (quiz_id, question_text, marks, order_index)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isdi", $quizId, $questionText, $marks, $orderIndex);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function getQuestionsByQuiz($quizId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM questions
            WHERE quiz_id = ?
            ORDER BY order_index ASC
        ");
        $stmt->bind_param("i", $quizId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteQuestion($questionId, $instructorId) {
        $stmt = $this->conn->prepare("
            DELETE q FROM questions q
            JOIN quizzes qz ON q.quiz_id = qz.id
            JOIN courses c ON qz.course_id = c.id
            WHERE q.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $questionId, $instructorId);
        return $stmt->execute();
    }

    public function updateQuestion($questionId, $questionText, $marks, $instructorId) {
        $stmt = $this->conn->prepare("
            UPDATE questions q
            JOIN quizzes qz ON q.quiz_id = qz.id
            JOIN courses c ON qz.course_id = c.id
            SET q.question_text = ?, q.marks = ?
            WHERE q.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("sdii", $questionText, $marks, $questionId, $instructorId);
        return $stmt->execute();
    }
}

?>