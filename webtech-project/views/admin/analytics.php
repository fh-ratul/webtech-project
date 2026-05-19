<?php include 'views/layouts/header.php'; ?>

<style>
    .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .bar-container { background: #eee; width: 100%; height: 20px; border-radius: 10px; margin-top: 5px; }
    .bar-fill { background: #1877f2; height: 100%; border-radius: 10px; }
    .hour-box { padding: 5px; background: #e8f4fd; border: 1px solid #1877f2; border-radius: 4px; text-align: center; }
    td {
      text-align: center; /* Horizontally centers content */
    }

</style>

<h2>Platform-Wide Analytics </h2>

<div class="analytics-grid">

    <!-- 1. Enrollments per Subject -->
    <div class="card">
        <h3>Enrollments per Subject</h3>
        <table border="1" width="100%" style="border-collapse: collapse;">
            <thead><tr style="background:#f8f9fa;"><th>Subject</th><th>Students</th></tr></thead>
            <tbody>
                <?php while($row = $subjectStats->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><strong><?php echo $row['total_enrolled']; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- 2. Most Active Instructors -->
    <div class="card">
        <h3>Most Active Instructors</h3>
        <table border="1" width="100%" style="border-collapse: collapse;">
            <thead><tr style="background:#f8f9fa;"><th>Instructor</th><th>Courses</th></tr></thead>
            <tbody>
                <?php while($row = $topInstructors->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><strong><?php echo $row['course_count']; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- 3. Quiz Pass Rates -->
    <div class="card" style="grid-column: span 2;">
        <h3>Quiz Performance (Pass Rates)</h3>
        <?php while($row = $passRates->fetch_assoc()): 
            $rate = ($row['total_attempts'] > 0) ? round(($row['pass_count'] / $row['total_attempts']) * 100) : 0;
        ?>
            <div style="margin-bottom: 15px;">
                <small><?php echo htmlspecialchars($row['title']); ?> (<?php echo $rate; ?>%)</small>
                <div class="bar-container">
                    <div class="bar-fill" style="width: <?php echo $rate; ?>%;"></div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- 4. Peak Usage Times -->
    <div class="card" style="grid-column: span 2;">
        <h3>Peak Usage Times (Hourly Attempt Volume)</h3>
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            <?php 
            $trendData = [];
            while($t = $usageTrends->fetch_assoc()) { $trendData[$t['hour']] = $t['attempt_count']; }
            for($h=0; $h<24; $h++): 
                $count = $trendData[$h] ?? 0;
                $opacity = ($count > 0) ? min(1, $count/10) : 0.1;
            ?>
                <div class="hour-box" style="opacity: <?php echo $opacity + 0.2; ?>; flex: 1;">
                    <small><?php echo $h; ?>:00</small><br>
                    <strong><?php echo $count; ?></strong>
                </div>
            <?php endfor; ?>
        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>