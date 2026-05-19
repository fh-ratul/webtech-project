<?php

class Student
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getTotalCourses(int $studentId): array
    {
        $query = "
        SELECT COUNT(*) AS total_courses
        FROM enrollments
        WHERE student_id = ?
        AND status = 'active'
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getDashboardMetrics(int $studentId): array
    {
        $query = "
        SELECT
            (
                SELECT COUNT(*)
                FROM attempts
                WHERE student_id = ?
            ) AS total_attempts,
            (
                SELECT COALESCE(AVG(score), 0)
                FROM attempts
                WHERE student_id = ?
            ) AS average_score,
            (
                SELECT COUNT(*)
                FROM attempts
                JOIN quizzes
                ON attempts.quiz_id = quizzes.id
                WHERE attempts.student_id = ?
                AND attempts.score >= quizzes.pass_mark
            ) AS passed_attempts,
            (
                SELECT COUNT(*)
                FROM doubt_session_bookings
                WHERE student_id = ?
            ) AS booked_sessions
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $studentId, $studentId, $studentId, $studentId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getUpcomingQuiz(int $studentId): ?array
    {
        $query = "
        SELECT
        quizzes.id,
        quizzes.title,
        quizzes.available_until,
        quizzes.total_marks,
        quizzes.time_limit_minutes,
        courses.title AS course_title
        FROM quizzes

        JOIN enrollments
        ON quizzes.course_id = enrollments.course_id

        JOIN courses
        ON quizzes.course_id = courses.id

        WHERE enrollments.student_id = ?
        AND enrollments.status = 'active'
        AND quizzes.status = 'published'
        AND (
            quizzes.available_until IS NULL
            OR quizzes.available_until >= NOW()
        )

        ORDER BY quizzes.available_until IS NULL ASC, quizzes.available_until ASC
        LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function getRecentAttempt(int $studentId): ?array
    {
        $query = "
        SELECT
        attempts.id,
        attempts.score,
        attempts.completed_at,
        quizzes.title,
        quizzes.total_marks,
        quizzes.pass_mark,
        courses.title AS course_title

        FROM attempts

        JOIN quizzes
        ON attempts.quiz_id = quizzes.id

        JOIN courses
        ON quizzes.course_id = courses.id

        WHERE attempts.student_id = ?

        ORDER BY attempts.completed_at DESC
        LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function getNextDoubtSession(int $studentId): ?array
    {
        $query = "
        SELECT
        doubt_sessions.title,
        doubt_sessions.scheduled_at,
        doubt_sessions.duration_minutes,
        doubt_sessions.location_or_link,
        doubt_sessions.status,
        courses.title AS course_title,
        users.name AS ta_name

        FROM doubt_session_bookings

        JOIN doubt_sessions
        ON doubt_session_bookings.doubt_session_id = doubt_sessions.id

        JOIN courses
        ON doubt_sessions.course_id = courses.id

        JOIN users
        ON doubt_sessions.ta_id = users.id

        WHERE doubt_session_bookings.student_id = ?
        AND doubt_sessions.scheduled_at >= NOW()
        AND doubt_sessions.status = 'scheduled'

        ORDER BY doubt_sessions.scheduled_at ASC
        LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function getRecentAnnouncements(int $studentId): mysqli_result
    {
        $query = "
        SELECT
        announcements.title,
        announcements.body,
        announcements.posted_role,
        announcements.created_at,
        courses.title AS course_title

        FROM announcements

        JOIN courses
        ON announcements.course_id = courses.id

        JOIN enrollments
        ON enrollments.course_id = courses.id

        WHERE enrollments.student_id = ?
        AND enrollments.status = 'active'

        ORDER BY announcements.created_at DESC

        LIMIT 5
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getCourses(int $studentId): mysqli_result
    {
        $query = "
        SELECT
        courses.id,
        courses.title,
        courses.description,
        enrollments.status

        FROM enrollments

        JOIN courses
        ON enrollments.course_id = courses.id

        WHERE enrollments.student_id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getQuizInfo(int $quizId): array
    {
        $query = "SELECT title, total_marks FROM quizzes WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $quizId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getLeaderboard(int $quizId): mysqli_result
    {
        $query = "
        SELECT
        users.name,
        MAX(attempts.score) AS highest_score

        FROM attempts

        JOIN users
        ON attempts.student_id = users.id

        WHERE attempts.quiz_id = ?

        GROUP BY attempts.student_id

        ORDER BY highest_score DESC

        LIMIT 10
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $quizId);
        $stmt->execute();

        return $stmt->get_result();
    }
}
