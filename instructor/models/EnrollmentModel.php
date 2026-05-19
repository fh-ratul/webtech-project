<?php

class EnrollmentModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getPendingEnrollmentsByCourse($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                e.id,
                e.student_id,
                e.enrolled_at,
                u.name AS student_name,
                u.email AS student_email
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            WHERE e.course_id = ? AND c.instructor_id = ? AND e.status = 'pending'
            ORDER BY e.enrolled_at ASC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPendingCountByInstructor($instructorId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS pending_requests
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE c.instructor_id = ? AND e.status = 'pending'
        ");
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int) $result['pending_requests'];
    }

    public function approveEnrollment($enrollmentId, $instructorId) {
        $stmt = $this->conn->prepare("
            UPDATE enrollments e
            JOIN courses c ON e.course_id = c.id
            SET e.status = 'active'
            WHERE e.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $enrollmentId, $instructorId);
        return $stmt->execute();
    }

    public function rejectEnrollment($enrollmentId, $instructorId) {
        $stmt = $this->conn->prepare("
            UPDATE enrollments e
            JOIN courses c ON e.course_id = c.id
            SET e.status = 'dropped'
            WHERE e.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $enrollmentId, $instructorId);
        return $stmt->execute();
    }
}

?>