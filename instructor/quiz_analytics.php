<?php

include("../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'instructor') {
    die("Access Denied");
}

if (!isset($_GET['quiz_id'])) {
    die("Invalid Quiz ID");
}

$quizId = $_GET['quiz_id'];
$instructorId = $_SESSION['user_id'];

$quizQuery = "
SELECT
quizzes.title,
quizzes.total_marks,
quizzes.pass_mark,
quizzes.course_id,
courses.title AS course_title

FROM quizzes

JOIN courses
ON quizzes.course_id = courses.id

WHERE quizzes.id = ?
AND courses.instructor_id = ?
";

$stmtQuiz = $conn->prepare($quizQuery);
$stmtQuiz->bind_param("ii", $quizId, $instructorId);
$stmtQuiz->execute();
$quizResult = $stmtQuiz->get_result();

if ($quizResult->num_rows == 0) {
    die("Quiz Not Found");
}

$quiz = $quizResult->fetch_assoc();

$statsQuery = "
SELECT
COUNT(*) AS total_attempts,
AVG(score) AS avg_score,
MAX(score) AS max_score,
MIN(score) AS min_score,
SUM(CASE WHEN score >= ? THEN 1 ELSE 0 END) AS pass_count
FROM attempts
WHERE quiz_id = ?
";

$stmtStats = $conn->prepare($statsQuery);
$stmtStats->bind_param("di", $quiz['pass_mark'], $quizId);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

$totalAttempts = (int) $stats['total_attempts'];
$passRate = $totalAttempts > 0 ? ($stats['pass_count'] / $totalAttempts) * 100 : 0;

?>

<!DOCTYPE html>
<html>

<head>
    <title>Quiz Analytics</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/instructor.css">
</head>

<body class="instructor-page">

<?php include("../includes/instructor_navbar.php"); ?>

<div class="container">

    <div class="page-header">
        <div class="title-block">
            <h1>Quiz Analytics</h1>
            <p><?php echo htmlspecialchars($quiz['course_title']); ?> • <?php echo htmlspecialchars($quiz['title']); ?></p>
        </div>
        <div class="action-row">
            <a class="btn" href="manage_quizzes.php?course_id=<?php echo $quiz['course_id']; ?>">Back to Quizzes</a>
        </div>
    </div>

    <div class="card split thirds">
        <div class="stat-card">
            <h3>Class Average</h3>
            <div class="stat-value">
                <?php echo $stats['avg_score'] !== null ? number_format($stats['avg_score'], 2) : "0.00"; ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>Highest Score</h3>
            <div class="stat-value">
                <?php echo $stats['max_score'] !== null ? $stats['max_score'] : 0; ?>
            </div>
        </div>
        <div class="stat-card">
            <h3>Lowest Score</h3>
            <div class="stat-value">
                <?php echo $stats['min_score'] !== null ? $stats['min_score'] : 0; ?>
            </div>
        </div>
    </div>

    <div class="card split">
        <div>
            <h2>Pass Rate</h2>
            <p class="pill"><?php echo number_format($passRate, 2); ?>%</p>
            <p class="muted">Total Attempts: <?php echo $totalAttempts; ?></p>
        </div>
        <div>
            <h2>Score Distribution</h2>
            <div id="distribution-loading">Loading distribution data...</div>
            <div id="distribution-content" style="display: none;">
                <table id="distribution-table">
                    <tr>
                        <th>Range</th>
                        <th>Count</th>
                    </tr>
                </table>
            </div>
            <div id="distribution-error" style="display: none; color: red;"></div>
        </div>
    </div>

</div>

<script>
// Load score distribution via AJAX (XMLHttpRequest)
(function() {
    var quizId = <?php echo (int) $quizId; ?>;
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            var loadingDiv = document.getElementById('distribution-loading');
            var contentDiv = document.getElementById('distribution-content');
            var errorDiv = document.getElementById('distribution-error');

            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);

                    if (response.success && response.distribution) {
                        // Hide loading, show content
                        loadingDiv.style.display = 'none';
                        contentDiv.style.display = 'block';

                        // Populate table
                        var table = document.getElementById('distribution-table');
                        var tbody = document.createElement('tbody');

                        if (response.distribution.length === 0) {
                            var row = tbody.insertRow();
                            var cell = row.insertCell(0);
                            cell.colSpan = 2;
                            cell.textContent = 'No attempts yet';
                            cell.style.textAlign = 'center';
                        } else {
                            response.distribution.forEach(function(item) {
                                var row = tbody.insertRow();
                                row.insertCell(0).textContent = item.range;
                                row.insertCell(1).textContent = item.count;
                            });
                        }

                        // Replace existing tbody if any
                        var oldTbody = table.querySelector('tbody');
                        if (oldTbody) {
                            table.removeChild(oldTbody);
                        }
                        table.appendChild(tbody);

                    } else {
                        throw new Error(response.error || 'Invalid response format');
                    }
                } catch (e) {
                    loadingDiv.style.display = 'none';
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = 'Error parsing data: ' + e.message;
                }
            } else {
                loadingDiv.style.display = 'none';
                errorDiv.style.display = 'block';
                errorDiv.textContent = 'Failed to load distribution data (HTTP ' + xhr.status + ')';
            }
        }
    };

    xhr.open('GET', 'ajax_quiz_stats.php?quiz_id=' + quizId, true);
    xhr.send();
})();
</script>

</body>

</html>
