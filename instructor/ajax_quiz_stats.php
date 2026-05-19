<?php

include("../config/config.php");

// Check Login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Role Check
if ($_SESSION['role'] != 'instructor') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access Denied']);
    exit();
}

// Validate quiz_id parameter
if (!isset($_GET['quiz_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing quiz_id parameter']);
    exit();
}

$quizId = (int) $_GET['quiz_id'];
$instructorId = $_SESSION['user_id'];

// Verify the quiz belongs to the logged-in instructor
$verifyQuery = "
SELECT quizzes.id
FROM quizzes
JOIN courses ON quizzes.course_id = courses.id
WHERE quizzes.id = ?
AND courses.instructor_id = ?
";

$stmtVerify = $conn->prepare($verifyQuery);
$stmtVerify->bind_param("ii", $quizId, $instructorId);
$stmtVerify->execute();
$verifyResult = $stmtVerify->get_result();

if ($verifyResult->num_rows == 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Quiz not found or access denied']);
    exit();
}

// Fetch quiz statistics
$statsQuery = "
SELECT
    COUNT(*) AS total_attempts,
    AVG(score) AS average_score,
    MAX(score) AS highest_score,
    MIN(score) AS lowest_score,
    SUM(CASE WHEN score >= (SELECT pass_mark FROM quizzes WHERE id = ?) THEN 1 ELSE 0 END) AS passed_count
FROM attempts
WHERE quiz_id = ?
AND status = 'submitted'
";

$stmtStats = $conn->prepare($statsQuery);
$stmtStats->bind_param("ii", $quizId, $quizId);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

// Calculate pass rate
$passRate = 0;
if ($stats['total_attempts'] > 0) {
    $passRate = ($stats['passed_count'] / $stats['total_attempts']) * 100;
}

// Fetch score distribution (buckets: 0-20, 21-40, 41-60, 61-80, 81-100)
$distributionQuery = "
SELECT
    CASE
        WHEN score >= 0 AND score <= 20 THEN '0-20'
        WHEN score > 20 AND score <= 40 THEN '21-40'
        WHEN score > 40 AND score <= 60 THEN '41-60'
        WHEN score > 60 AND score <= 80 THEN '61-80'
        WHEN score > 80 AND score <= 100 THEN '81-100'
    END AS score_range,
    COUNT(*) AS count
FROM attempts
WHERE quiz_id = ?
AND status = 'submitted'
GROUP BY score_range
ORDER BY score_range
";

$stmtDist = $conn->prepare($distributionQuery);
$stmtDist->bind_param("i", $quizId);
$stmtDist->execute();
$distResult = $stmtDist->get_result();

$distribution = [];
while ($row = $distResult->fetch_assoc()) {
    if ($row['score_range'] !== null) {
        $distribution[] = [
            'range' => $row['score_range'],
            'count' => (int) $row['count']
        ];
    }
}

// Build response
$response = [
    'success' => true,
    'total_attempts' => (int) $stats['total_attempts'],
    'average' => round((float) $stats['average_score'], 2),
    'highest' => round((float) $stats['highest_score'], 2),
    'lowest' => round((float) $stats['lowest_score'], 2),
    'pass_rate' => round($passRate, 2),
    'distribution' => $distribution
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();

?>
