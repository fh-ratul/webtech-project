<?php

class AnnouncementModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAnnouncementsByCourse($courseId, $instructorId) {
        $stmt = $this->conn->prepare("
            SELECT
                a.id,
                a.title,
                a.body,
                a.created_at,
                u.name AS posted_by_name
            FROM announcements a
            LEFT JOIN users u ON a.posted_by = u.id
            JOIN courses c ON a.course_id = c.id
            WHERE a.course_id = ? AND c.instructor_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->bind_param("ii", $courseId, $instructorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addAnnouncement($courseId, $title, $body, $userId) {
        $stmt = $this->conn->prepare("
            INSERT INTO announcements
            (course_id, title, body, posted_by, posted_role)
            VALUES (?, ?, ?, ?, 'instructor')
        ");
        $stmt->bind_param("issi", $courseId, $title, $body, $userId);
        return $stmt->execute();
    }

    public function deleteAnnouncement($announcementId, $instructorId) {
        $stmt = $this->conn->prepare("
            DELETE a FROM announcements a
            JOIN courses c ON a.course_id = c.id
            WHERE a.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $announcementId, $instructorId);
        return $stmt->execute();
    }
}

?>