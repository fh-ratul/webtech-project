<?php

class MaterialModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getMaterialsByCourse($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                m.id,
                m.title,
                m.material_type,
                m.file_path,
                m.external_link,
                m.uploaded_at
            FROM course_materials m
            JOIN courses c ON m.course_id = c.id
            WHERE m.course_id = ? AND c.instructor_id = ?
            ORDER BY m.uploaded_at DESC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addMaterial($courseId, $title, $materialType, $filePath, $externalLink, $userId) {
        $stmt = $this->conn->prepare("
            INSERT INTO course_materials
            (course_id, title, material_type, file_path, external_link, uploaded_by, uploaded_role)
            VALUES (?, ?, ?, ?, ?, ?, 'instructor')
        ");
        $stmt->bind_param("issssi", $courseId, $title, $materialType, $filePath, $externalLink, $userId);
        return $stmt->execute();
    }

    public function deleteMaterial($materialId, $instructorId) {
        $stmt = $this->conn->prepare("
            DELETE m FROM course_materials m
            JOIN courses c ON m.course_id = c.id
            WHERE m.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $materialId, $instructorId);
        return $stmt->execute();
    }
}

?>