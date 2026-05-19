<?php
session_start();
require_once 'models/AdminModel.php';

// Exact Sanitization Function from PDF 2, Page 3
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

class AdminController {
    private $model;

    public function __construct() {
        // Basic check for session role
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            // Uncomment for production: header("Location: login.php"); exit();
        }
        $this->model = new AdminModel();
    }

    public function index() {
        $stats = $this->model->getDashboardStats();
        $this->loadView('admin/dashboard', ['stats' => $stats]);
    }
public function manageUsers() {
        // Handle DELETE
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            $this->model->deleteUser($id);
            header("Location: admin_index.php?action=users&msg=deleted");
            exit();
        }

        // Handle ADD USER with File Validation (Guide Page 6)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $role = sanitizeInput($_POST['role']);
            $phone = sanitizeInput($_POST['phone']);
            $student_id = sanitizeInput($_POST['student_id']);
            $program = sanitizeInput($_POST['program']);

            $profile_pic = "default.png";
            if (!empty($_FILES["profile_pic"]["name"])) {
                $file = $_FILES["profile_pic"];
                $fileSize = $file["size"];
                $fileExt = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                // Guide Validation: 4MB limit and specific formats
                if ($fileSize > 4000000) {
                    die("Error: File is too large. Max 4MB allowed.");
                } elseif (!in_array($fileExt, $allowed)) {
                    die("Error: Only JPG, JPEG, & PNG files are allowed.");
                } else {
                    $target_dir = "uploads/";
                    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
                    $profile_pic = time() . "_" . basename($file["name"]);
                    move_uploaded_file($file["tmp_name"], $target_dir . $profile_pic);
                }
            }
            
            $this->model->createUser($name, $email, $password, $role, $phone, $profile_pic, $student_id, $program);
            header("Location: admin_index.php?action=users&msg=added");
            exit();
        }

        // Handle UPDATE (Role/Status)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
            $id = (int)$_POST['user_id'];
            $role = sanitizeInput($_POST['role']);
            $status = (int)$_POST['is_active'];
            $this->model->updateUser($id, $role, $status);
            header("Location: admin_index.php?action=users&id=$id&msg=updated");
            exit();
        }

        $viewUser = isset($_GET['id']) ? $this->model->getUserById((int)$_GET['id']) : null;
        $this->loadView('admin/users', ['viewUser' => $viewUser]);
    }
    public function manageSubjects() {
        // Handle DELETE action
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            if ($this->model->deleteSubject($id)) {
                $this->model->logAction("Deleted subject ID: $id");
                header("Location: admin_index.php?action=subjects");
                exit();
            }
        }

        // Handle UPDATE (Rename) submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subject'])) {
            $id = (int)$_POST['id'];
            $name = sanitizeInput($_POST['name']);
            $desc = sanitizeInput($_POST['description']);
            
            if ($this->model->updateSubject($id, $name, $desc)) {
                $this->model->logAction("Renamed/Updated subject: $name");
                header("Location: admin_index.php?action=subjects");
                exit();
            }
        }

        // Handle ADD submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
            $name = sanitizeInput($_POST['name']);
            $desc = sanitizeInput($_POST['description']);
            if ($this->model->addSubject($name, $desc)) {
                $this->model->logAction("Added new subject: $name");
                header("Location: admin_index.php?action=subjects");
                exit();
            }
        }

        // Check if we are in "Edit Mode"
        $editSubject = null;
        if (isset($_GET['edit'])) {
            $editSubject = $this->model->getSubjectById((int)$_GET['edit']);
        }

        $subjects = $this->model->getSubjects();
        // Pass the editSubject data to the view
        $this->loadView('admin/subjects', ['subjects' => $subjects, 'editSubject' => $editSubject]);
    }
    public function viewCourses() {
        // Capture filter inputs
        $subj_id = isset($_GET['subject_id']) && $_GET['subject_id'] !== "" ? (int)$_GET['subject_id'] : null;
        $inst_id = isset($_GET['instructor_id']) && $_GET['instructor_id'] !== "" ? (int)$_GET['instructor_id'] : null;

        // Fetch data
        $courses = $this->model->getFilteredCourses($subj_id, $inst_id);
        $subjects = $this->model->getSubjects(); // Reusing Instruction 5 method
        $instructors = $this->model->getAllInstructors();

        $this->loadView('admin/courses', [
            'courses' => $courses,
            'subjects' => $subjects,
            'instructors' => $instructors,
            'selected_subj' => $subj_id,
            'selected_inst' => $inst_id
        ]);
    }
    public function viewQuizzes() {
        // Capture filters from URL
        $course_id = isset($_GET['course_id']) && $_GET['course_id'] !== "" ? (int)$_GET['course_id'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== "" ? sanitizeInput($_GET['status']) : null;
        $type = isset($_GET['quiz_type']) && $_GET['quiz_type'] !== "" ? sanitizeInput($_GET['quiz_type']) : null;

        // Fetch data from Model
        $quizzes = $this->model->getFilteredQuizzes($course_id, $status, $type);
        $courses = $this->model->getAllCourses();

        $this->loadView('admin/quizzes', [
            'quizzes' => $quizzes,
            'courses' => $courses,
            'selected_course' => $course_id,
            'selected_status' => $status,
            'selected_type' => $type
        ]);
    }
    public function viewAnalytics() {
        $subjectStats = $this->model->getSubjectEnrollments();
        $passRates = $this->model->getQuizPassRates();
        $topInstructors = $this->model->getActiveInstructors();
        $usageTrends = $this->model->getUsageTrends();

        $this->loadView('admin/analytics', [
            'subjectStats' => $subjectStats,
            'passRates' => $passRates,
            'topInstructors' => $topInstructors,
            'usageTrends' => $usageTrends
        ]);
    }

  public function viewReports() {
        // Default range: last 6 months if not specified
        $from = isset($_GET['from_date']) ? sanitizeInput($_GET['from_date']) : date('Y-m-d', strtotime('-6 months'));
        $to = isset($_GET['to_date']) ? sanitizeInput($_GET['to_date']) : date('Y-m-d');

        // Fetch data from Model
        $reportData = $this->model->getInstitutionalReportData($from, $to);

        $this->loadView('admin/reports', [
            'report' => $reportData,
            'fromDate' => $from,
            'toDate' => $to
        ]);
    }
    public function manageApprovals() {
        // Handle Approval (Approve = Activate)
        if (isset($_GET['approve_id'])) {
            $id = (int)$_GET['approve_id'];
            if ($this->model->updateUserStatus($id, 1)) { // Set is_active to 1
                $this->model->logAction("Approved Instructor registration: ID $id");
                header("Location: admin_index.php?action=approvals&msg=approved");
                exit();
            }
        }

        // Handle Rejection (Reject = Delete the record)
        if (isset($_GET['reject_id'])) {
            $id = (int)$_GET['reject_id'];
            if ($this->model->deleteUser($id)) {
                $this->model->logAction("Rejected and Deleted Instructor request: ID $id");
                header("Location: admin_index.php?action=approvals&msg=rejected");
                exit();
            }
        }

        $pending = $this->model->getPendingInstructors();
        $this->loadView('admin/approvals', ['pending' => $pending]);
    }
    public function manageAnnouncements() {
        // Handle Delete Request
        if (isset($_GET['delete_id'])) {
            $id = (int)$_GET['delete_id'];
            $this->model->deleteAnnouncement($id);
            header("Location: admin_index.php?action=announcements&msg=deleted");
            exit();
        }

        // Handle Post Request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_announcement'])) {
            $title = sanitizeInput($_POST['title']);
            $body = sanitizeInput($_POST['body']);
            $admin_id = $_SESSION['user_id'] ?? 4; // Default to Admin ID 4 from your SQL

            if ($this->model->createAnnouncement($admin_id, $title, $body)) {
                header("Location: admin_index.php?action=announcements&msg=posted");
                exit();
            }
        }

        $news = $this->model->getAnnouncements();
        $this->loadView('admin/announcements', ['news' => $news]);
    }
    public function academicReport() {
        $studentData = null;
        $performance = [];

        // If a student is selected from the search results
        if (isset($_GET['student_id'])) {
            $id = (int)$_GET['student_id'];
            $studentData = $this->model->getUserById($id);
            $performance = $this->model->getSpecificStudentReport($id);
        }

        $this->loadView('admin/academic_report', [
            'student' => $studentData,
            'performance' => $performance
        ]);
    }
   public function managePolicies() {
        $message = "";
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
            // Sanitize all inputs using your guide's function
            $data = [
                "max_quiz_duration" => (int)sanitizeInput($_POST['max_duration']),
                "default_max_students" => (int)sanitizeInput($_POST['max_students']),
                "allow_late_submission" => sanitizeInput($_POST['late_sub']),
                "platform_name" => sanitizeInput($_POST['p_name'])
            ];

            if ($this->model->saveSystemPolicies($data)) {
                $message = "Policies updated successfully!";
            }
        }

        $currentSettings = $this->model->getSystemPolicies();

        $this->loadView('admin/policies', [
            'settings' => $currentSettings,
            'success_msg' => $message
        ]);
    }
    

    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>