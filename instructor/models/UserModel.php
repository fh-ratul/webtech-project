<?php

class UserModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserById($userId) {
        $stmt = $this->conn->prepare("
            SELECT id, name, email, role, department, bio, profile_pic, is_active
            FROM users
            WHERE id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function updateProfile($userId, $name, $department, $bio) {
        $stmt = $this->conn->prepare("
            UPDATE users
            SET name = ?, department = ?, bio = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $name, $department, $bio, $userId);
        return $stmt->execute();
    }

    public function updateProfileWithPicture($userId, $name, $department, $bio, $profilePic) {
        $stmt = $this->conn->prepare("
            UPDATE users
            SET name = ?, department = ?, bio = ?, profile_pic = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $name, $department, $bio, $profilePic, $userId);
        return $stmt->execute();
    }

    public function getTAUsers() {
        $stmt = $this->conn->prepare("
            SELECT id, name, email
            FROM users
            WHERE role = 'ta' AND is_active = 1
            ORDER BY name
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function assignTAToCourse($taId, $courseId, $instructorId) {
        // First verify course belongs to instructor
        $verifyStmt = $this->conn->prepare("
            SELECT id FROM courses WHERE id = ? AND instructor_id = ?
        ");
        $verifyStmt->bind_param("ii", $courseId, $instructorId);
        $verifyStmt->execute();

        if ($verifyStmt->get_result()->num_rows == 0) {
            return false;
        }

        // Check if already assigned
        $checkStmt = $this->conn->prepare("
            SELECT id FROM course_tas WHERE course_id = ? AND ta_id = ?
        ");
        $checkStmt->bind_param("ii", $courseId, $taId);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            return false; // Already assigned
        }

        // Insert assignment
        $insertStmt = $this->conn->prepare("
            INSERT INTO course_tas (course_id, ta_id) VALUES (?, ?)
        ");
        $insertStmt->bind_param("ii", $courseId, $taId);
        return $insertStmt->execute();
    }

    public function getAssignedTAs($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                u.id,
                u.name,
                u.email,
                ct.assigned_at
            FROM course_tas ct
            JOIN users u ON ct.ta_id = u.id
            JOIN courses c ON ct.course_id = c.id
            WHERE ct.course_id = ? AND c.instructor_id = ?
            ORDER BY ct.assigned_at DESC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>