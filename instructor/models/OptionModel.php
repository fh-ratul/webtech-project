<?php

class OptionModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addOption($questionId, $optionText, $isCorrect) {
        $stmt = $this->conn->prepare("
            INSERT INTO options (question_id, option_text, is_correct)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("isi", $questionId, $optionText, $isCorrect);
        return $stmt->execute();
    }

    public function getOptionsByQuestion($questionId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM options WHERE question_id = ? ORDER BY id
        ");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteOptionsByQuestion($questionId) {
        $stmt = $this->conn->prepare("DELETE FROM options WHERE question_id = ?");
        $stmt->bind_param("i", $questionId);
        return $stmt->execute();
    }
}

?>