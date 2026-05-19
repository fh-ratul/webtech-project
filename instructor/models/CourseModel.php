<?php

class CourseModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Get all courses for an instructor
     */
    public function getCoursesByInstructor($instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                c.id,
                c.title,
                c.description,
                c.status,
                c.enrollment_type,
                c.max_students,
                c.created_at,
                s.name AS subject_name
            FROM courses c
            JOIN subjects s ON c.subject_id = s.id
            WHERE c.instructor_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get single course by ID for instructor
     */
    public function getCourseByIdForInstructor($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                c.*,
                s.name AS subject_name
            FROM courses c
            JOIN subjects s ON c.subject_id = s.id
            WHERE c.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Create new course
     */
    public function createCourse($instructorId, $title, $subjectId, $description, $enrollmentType, $maxStudents, $status) {
        $stmt = $this->conn->prepare("
            INSERT INTO courses
            (instructor_id, title, subject_id, description, enrollment_type, max_students, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isissis", $instructorId, $title, $subjectId, $description, $enrollmentType, $maxStudents, $status);
        return $stmt->execute();
    }

    /**
     * Update course
     */
    public function updateCourse($courseId, $instructorId, $title, $subjectId, $description, $enrollmentType, $maxStudents, $status) {
        $stmt = $this->conn->prepare("
            UPDATE courses
            SET title = ?, subject_id = ?, description = ?, enrollment_type = ?, max_students = ?, status = ?
            WHERE id = ? AND instructor_id = ?
        ");
        $stmt->bind_param("sissisii", $title, $subjectId, $description, $enrollmentType, $maxStudents, $status, $courseId, $instructorId);
        return $stmt->execute();
    }

    /**
     * Archive course
     */
    public function archiveCourse($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            UPDATE courses
            SET status = 'archived'
            WHERE id = ? AND instructor_id = ?
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        return $stmt->execute();
    }

    /**
     * Get course statistics for instructor
     */
    public function getCourseStats($instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                COUNT(*) AS total_courses,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_courses,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_courses
            FROM courses
            WHERE instructor_id = ?
        ");
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all subjects for dropdown
     */
    public function getAllSubjects() {
        $result = $this->conn->query("SELECT id, name FROM subjects ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get enrolled students for a course
     */
    public function getEnrolledStudents($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                u.id,
                u.name,
                u.email,
                e.status,
                e.enrolled_at
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            WHERE e.course_id = ? AND c.instructor_id = ? AND e.status = 'active'
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>
