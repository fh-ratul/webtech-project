<?php include 'views/layouts/header.php'; ?>

<h2>Institutional Performance Report </h2>

<!-- Date Range Filter -->
<div class="card">
    <form method="GET" action="admin_index.php">
        <input type="hidden" name="action" value="reports">
        <div style="display: flex; gap: 15px; align-items: flex-end;">
            <div>
                <label><small>Semester Start Date:</small></label><br>
                <input type="date" name="from_date" value="<?php echo $fromDate; ?>" style="padding:8px; border:1px solid #ccc;">
            </div>
            <div>
                <label><small>Semester End Date:</small></label><br>
                <input type="date" name="to_date" value="<?php echo $toDate; ?>" style="padding:8px; border:1px solid #ccc;">
            </div>
            <button type="submit" class="btn btn-primary">Generate Report</button>
            <button type="button" class="btn" style="background:#eee;" onclick="window.print()">Print PDF</button>
        </div>
    </form>
</div>

<!-- Global Stats Row -->
<div class="card" style="background: #e8f4fd; border-left: 5px solid #1877f2;">
    <h3 style="margin-top:0;">Platform Summary</h3>
    <p>Total Platform Users: <strong><?php echo $report['total_users']; ?></strong></p>
    <p><small>Showing quiz metrics from <strong><?php echo $fromDate; ?></strong> to <strong><?php echo $toDate; ?></strong></small></p>
</div>

<!-- Detailed Table -->
<div class="card">
    <h3>Data Breakdown per Subject</h3>
    <table border="1" width="100%" style="border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="padding:12px;">Subject Name</th>
                <th>Active Courses</th>
                <th>Total Quizzes</th>
                <th>Attempts</th>
                <th>Avg. Pass Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($report['subject_stats'] as $row): ?>
            <tr>
                <td style="padding:12px;"><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
                <td><?php echo $row['active_courses']; ?></td>
                <td><?php echo $row['total_quizzes']; ?></td>
                <td><?php echo $row['total_attempts']; ?></td>
                <td>
                    <span style="font-weight:bold; color: <?php echo ($row['pass_rate'] >= 50) ? 'green' : 'red'; ?>;">
                        <?php echo $row['pass_rate']; ?>%
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>