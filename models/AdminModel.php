<?php
require_once __DIR__ . '/../config/Database.php';

class AdminModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Instr 1: Dashboard Logic
    public function getDashboardStats() {
        $stats = [];
        $res = $this->db->query("SELECT COUNT(*) FROM courses WHERE status='active'");
        $stats['active_courses'] = $res->fetch_row()[0];

        $res = $this->db->query("SELECT COUNT(*) FROM attempts WHERE DATE(started_at) = CURDATE()");
        $stats['attempts_today'] = $res->fetch_row()[0];

        $res = $this->db->query("SELECT COUNT(*) FROM users WHERE role='instructor' AND is_active=0");
        $stats['pending_instructors'] = $res->fetch_row()[0];
        
        return $stats;
    }

    // Instr 2 & 10: Search Users (AJAX API)
    public function searchUsers($query) {
        $q = "%" . $query . "%";
        $stmt = $this->db->prepare("SELECT id, name, email, role, is_active FROM users WHERE name LIKE ? OR email LIKE ? OR student_id LIKE ?");
        $stmt->bind_param("sss", $q, $q, $q);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Instr 2: Get Single User Details (FIXES YOUR ERROR)
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Instr 2: Update Role and Status
    public function updateUser($id, $role, $status) {
        $stmt = $this->db->prepare("UPDATE users SET role = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sii", $role, $status, $id);
        return $stmt->execute();
    }
// Instr 3: Create User/TA directly
  // Instr 3: Updated to include phone, pic, student_id, and program
    public function createUser($name, $email, $password, $role, $phone, $pic, $sid, $prog) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password_hash, role, phone, profile_pic, student_id, program, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $this->db->prepare($sql);
        // "ssssssss" means 8 strings
        $stmt->bind_param("ssssssss", $name, $email, $hash, $role, $phone, $pic, $sid, $prog);
        return $stmt->execute();
    }

    // User Management: Delete User
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
   // Instr 3: Fetch only pending instructor requests
    public function getPendingInstructors() {
        $sql = "SELECT id, name, email, phone, created_at FROM users 
                WHERE role = 'instructor' AND is_active = 0 
                ORDER BY created_at ASC";
        return $this->db->query($sql);
    }
    // Instr 4: View all courses with filters
    public function getFilteredCourses($subject_id = null, $instructor_id = null) {
        $sql = "SELECT c.*, u.name as instructor_name, s.name as subject_name 
                FROM courses c
                JOIN users u ON c.instructor_id = u.id
                JOIN subjects s ON c.subject_id = s.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if ($subject_id) {
            $sql .= " AND c.subject_id = ?";
            $params[] = $subject_id;
            $types .= "i";
        }
        if ($instructor_id) {
            $sql .= " AND c.instructor_id = ?";
            $params[] = $instructor_id;
            $types .= "i";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
   
    // Get all instructors for the filter dropdown
    public function getAllInstructors() {
        return $this->db->query("SELECT id, name FROM users WHERE role = 'instructor'");
    }
    // Instr 5: Subject Taxonomy Logic
    public function getSubjects() {
        return $this->db->query("SELECT * FROM subjects ORDER BY id ASC");
    }

    public function addSubject($name, $desc) {
        $stmt = $this->db->prepare("INSERT INTO subjects (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $desc);
        return $stmt->execute();
    }

    public function deleteSubject($id) {
        $stmt = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getSubjectById($id) {
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateSubject($id, $name, $desc) {
        $stmt = $this->db->prepare("UPDATE subjects SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        return $stmt->execute();
    }
    // Instr 6: View all quizzes with attempt counts and filters
    public function getFilteredQuizzes($course_id = null, $status = null, $type = null) {
        // Query joins quizzes with courses and uses a subquery to count attempts
        $sql = "SELECT q.*, c.title as course_title, 
                (SELECT COUNT(*) FROM attempts WHERE quiz_id = q.id) as attempt_count 
                FROM quizzes q
                JOIN courses c ON q.course_id = c.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if ($course_id) {
            $sql .= " AND q.course_id = ?";
            $params[] = $course_id;
            $types .= "i";
        }
        if ($status) {
            $sql .= " AND q.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        if ($type) {
            $sql .= " AND q.quiz_type = ?";
            $params[] = $type;
            $types .= "s";
        }

        $sql .= " ORDER BY q.id DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    // Helper for filter dropdown
    public function getAllCourses() {
        return $this->db->query("SELECT id, title FROM courses ORDER BY title ASC");
    }
    // Instr 8: Analytics - Enrollments per Subject
    public function getSubjectEnrollments() {
        $sql = "SELECT s.name as subject_name, COUNT(e.id) as total_enrolled 
                FROM subjects s 
                LEFT JOIN courses c ON s.id = c.subject_id 
                LEFT JOIN enrollments e ON c.id = e.course_id 
                GROUP BY s.id ORDER BY total_enrolled DESC";
        return $this->db->query($sql);
    }

    // Instr 8: Analytics - Quiz Pass Rates
    public function getQuizPassRates() {
        $sql = "SELECT q.title, 
                COUNT(a.id) as total_attempts,
                SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) as pass_count
                FROM quizzes q
                JOIN attempts a ON q.id = a.quiz_id
                GROUP BY q.id";
        return $this->db->query($sql);
    }

    // Instr 8: Analytics - Most Active Instructors (by course count)
    public function getActiveInstructors() {
        $sql = "SELECT u.name, COUNT(c.id) as course_count 
                FROM users u 
                JOIN courses c ON u.id = c.instructor_id 
                WHERE u.role = 'instructor' 
                GROUP BY u.id ORDER BY course_count DESC LIMIT 5";
        return $this->db->query($sql);
    }

    // Instr 8: Analytics - Peak Usage Times (Attempts by Hour)
    public function getUsageTrends() {
        $sql = "SELECT HOUR(started_at) as hour, COUNT(*) as attempt_count 
                FROM attempts 
                GROUP BY hour ORDER BY hour ASC";
        return $this->db->query($sql);
    }

   // Instr 11: Institutional Report by Subject and Date Range
    public function getInstitutionalReportData($fromDate, $toDate) {
        // Global count for total users (since users aren't tied to subjects)
        $globalUsers = $this->db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

        // Per-subject statistics
        $sql = "SELECT 
                    s.name AS subject_name,
                    COUNT(DISTINCT CASE WHEN c.status = 'active' THEN c.id END) AS active_courses,
                    COUNT(DISTINCT q.id) AS total_quizzes,
                    COUNT(a.id) AS total_attempts,
                    SUM(CASE WHEN a.score >= q.pass_mark THEN 1 ELSE 0 END) AS pass_count
                FROM subjects s
                LEFT JOIN courses c ON s.id = c.subject_id
                LEFT JOIN quizzes q ON q.course_id = c.id
                LEFT JOIN attempts a ON a.quiz_id = q.id 
                    AND a.started_at >= ? 
                    AND a.started_at <= ?
                GROUP BY s.id";

        $stmt = $this->db->prepare($sql);
        // Append time to dates to cover the full day
        $start = $fromDate . " 00:00:00";
        $end = $toDate . " 23:59:59";
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $data = [];
        while($row = $res->fetch_assoc()) {
            // Calculate pass rate percentage
            $row['pass_rate'] = ($row['total_attempts'] > 0) 
                ? round(($row['pass_count'] / $row['total_attempts']) * 100, 2) 
                : 0;
            $data[] = $row;
        }

        return [
            'total_users' => $globalUsers,
            'subject_stats' => $data
        ];
    }
    // Instr 9: Get all platform announcements
    public function getAnnouncements() {
        // Platform-wide announcements have course_id as NULL
        $sql = "SELECT a.*, u.name as author_name FROM announcements a 
                JOIN users u ON a.author_id = u.id 
                WHERE a.course_id IS NULL 
                ORDER BY a.created_at DESC";
        return $this->db->query($sql);
    }

    // Instr 9: Create new platform announcement
    public function createAnnouncement($author_id, $title, $body) {
        $sql = "INSERT INTO announcements (author_id, title, body, course_id) VALUES (?, ?, ?, NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iss", $author_id, $title, $body);
        
        if ($stmt->execute()) {
            $this->logAction("Posted platform announcement: $title");
            return true;
        }
        return false;
    }

    // Instr 9: Delete an announcement
    public function deleteAnnouncement($id) {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $this->logAction("Deleted announcement ID: $id");
            return true;
        }
        return false;
    }
   // Instr 10: Search for students only
    public function searchStudentsOnly($query) {
        $q = "%" . $query . "%";
        $stmt = $this->db->prepare("SELECT id, name, email, student_id, program FROM users WHERE role='student' AND (name LIKE ? OR student_id LIKE ?)");
        $stmt->bind_param("ss", $q, $q);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Instr 10: Per-student summary: All courses, attempt counts, and average scores
    public function getSpecificStudentReport($student_id) {
        $sql = "SELECT 
                    c.title as course_name,
                    COUNT(a.id) as attempt_count,
                    AVG(a.score) as avg_score
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN quizzes q ON q.course_id = c.id
                LEFT JOIN attempts a ON a.quiz_id = q.id AND a.student_id = e.student_id
                WHERE e.student_id = ?
                GROUP BY c.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    // Instr 12: Get Policies from JSON file (File Handling Guide)
    public function getSystemPolicies() {
        $filePath = "system_policies.json";
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            return json_decode($jsonContent, true);
        }
        // Default values if file doesn't exist yet
        return [
            "max_quiz_duration" => 120,
            "default_max_students" => 50,
            "allow_late_submission" => "no",
            "platform_name" => "QuizPlatform Pro"
        ];
    }

    // Instr 12: Save Policies (File Handling Guide - Write mode "w")
    public function saveSystemPolicies($data) {
        $filePath = "system_policies.json";
        $jsonString = json_encode($data, JSON_PRETTY_PRINT);
        
        // Using file_put_contents as shown in your File Handling Guide
        if (file_put_contents($filePath, $jsonString)) {
            $this->logAction("Updated platform-wide policies in system_policies.json");
            return true;
        }
        return false;
    }
    // Instr 13: Audit Log Function
    public function logAction($action) {
        $log = "[" . date("Y-m-d H:i:s") . "] " . $action . PHP_EOL;
        file_put_contents("admin_audit.log", $log, FILE_APPEND);
    }
}

?>