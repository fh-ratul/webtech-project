<?php include 'views/layouts/header.php'; ?>

<style>
    .filter-bar { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-item { flex: 1; min-width: 150px; }
    select, input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .badge-graded { background: #e8f4fd; color: #1877f2; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .badge-practice { background: #eee; color: #666; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .status-published { color: green; font-weight: bold; }
    .status-draft { color: #f39c12; font-weight: bold; }
</style>

<h2>Platform Quiz Overview </h2>

<!-- Filter Form -->
<div class="card">
    <form method="GET" action="admin_index.php">
        <input type="hidden" name="action" value="quizzes">
        <div class="filter-bar">
            <div class="filter-item">
                <label><small>Course:</small></label>
                <select name="course_id">
                    <option value="">All Courses</option>
                    <?php while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($selected_course == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-item">
                <label><small>Status:</small></label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="published" <?php echo ($selected_status == 'published') ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo ($selected_status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>

            <div class="filter-item">
                <label><small>Type:</small></label>
                <select name="quiz_type">
                    <option value="">All Types</option>
                    <option value="graded" <?php echo ($selected_type == 'graded') ? 'selected' : ''; ?>>Graded</option>
                    <option value="practice" <?php echo ($selected_type == 'practice') ? 'selected' : ''; ?>>Practice</option>
                </select>
            </div>

            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin_index.php?action=quizzes" class="btn" style="background:#ddd; text-decoration:none; color:black; font-size: 13px;">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Results Table -->
<div class="card">
    <table border="1" width="100%" style="border-collapse: collapse;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="padding:12px;">Quiz Title</th>
                <th>Course</th>
                <th>Type</th>
                <th>Status</th>
                <th>Attempts</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($quizzes->num_rows > 0): ?>
                <?php while($row = $quizzes->fetch_assoc()): ?>
                <tr>
                    <td style="padding:12px;">
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <small style="color:#888;">Time Limit: <?php echo $row['time_limit_minutes']; ?> mins</small>
                    </td>
                    <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                    <td>
                        <span class="<?php echo ($row['quiz_type'] == 'graded') ? 'badge-graded' : 'badge-practice'; ?>">
                            <?php echo strtoupper($row['quiz_type']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <strong style="font-size: 16px; color: #1877f2;"><?php echo $row['attempt_count']; ?></strong>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:30px; color:#999;">No quizzes found matching these criteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>