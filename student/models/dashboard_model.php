<?php

function get_student_total_courses(mysqli $conn, int $studentId): array
{
    $totalCourseQuery = "
    SELECT COUNT(*) AS total_courses
    FROM enrollments
    WHERE student_id = ?
    AND status = 'active'
    ";

    $stmt = $conn->prepare($totalCourseQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

function get_student_upcoming_quiz(mysqli $conn, int $studentId): ?array
{
    $quizQuery = "
    SELECT
    quizzes.title,
    quizzes.available_until,
    courses.title AS course_title
    FROM quizzes

    JOIN enrollments
    ON quizzes.course_id = enrollments.course_id

    JOIN courses
    ON quizzes.course_id = courses.id

    WHERE enrollments.student_id = ?
    AND quizzes.status = 'published'

    ORDER BY quizzes.available_until ASC
    LIMIT 1
    ";

    $stmt = $conn->prepare($quizQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function get_student_recent_announcements(mysqli $conn, int $studentId): mysqli_result
{
    $announcementQuery = "
    SELECT
    announcements.title,
    announcements.body,
    announcements.posted_role,
    courses.title AS course_title

    FROM announcements

    JOIN courses
    ON announcements.course_id = courses.id

    JOIN enrollments
    ON enrollments.course_id = courses.id

    WHERE enrollments.student_id = ?

    ORDER BY announcements.created_at DESC

    LIMIT 5
    ";

    $stmt = $conn->prepare($announcementQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();

    return $stmt->get_result();
}

function get_student_courses(mysqli $conn, int $studentId): mysqli_result
{
    $courseQuery = "
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

    $stmt = $conn->prepare($courseQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();

    return $stmt->get_result();
}
