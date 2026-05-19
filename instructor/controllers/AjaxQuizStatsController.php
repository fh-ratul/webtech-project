<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../models/QuizModel.php';

Session::requireRole('instructor');
header('Content-Type: application/json');

$quizId = (int)($_GET['quiz_id'] ?? 0);
if ($quizId === 0) {
    echo json_encode(['error' => 'Invalid quiz ID']);
    exit();
}

$instructorId = Session::userId();
$model = new QuizModel($conn);

// Verify quiz belongs to this instructor
$quiz = $model->getQuizByIdForInstructor($quizId, $instructorId);
if (!$quiz) {
    echo json_encode(['error' => 'Quiz not found or access denied']);
    exit();
}

// Get statistics
$stats = $model->getQuizStats($quizId, $instructorId);
$distribution = $model->getScoreDistribution($quizId, $instructorId);

// Calculate pass rate
$passRate = 0;
if ($stats['total_attempts'] > 0) {
    $passRate = ($stats['pass_count'] / $stats['total_attempts']) * 100;
}

// Build response
$response = [
    'success' => true,
    'total_attempts' => (int) $stats['total_attempts'],
    'average' => round((float) $stats['avg_score'], 2),
    'highest' => round((float) $stats['max_score'], 2),
    'lowest' => round((float) $stats['min_score'], 2),
    'pass_rate' => round($passRate, 2),
    'distribution' => $distribution
];

echo json_encode($response);
exit();

?>
