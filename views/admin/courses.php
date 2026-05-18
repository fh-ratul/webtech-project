<?php include 'views/layouts/header.php'; ?>

<style>
    .status-active { color: green; font-weight: bold; }
    .status-draft { color: #666; font-weight: bold; }
    .status-archived { color: #e67e22; font-weight: bold; }
    .filter-bar { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px; }
    .filter-group { flex: 1; }
    select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
</style>

<h2>Course Catalog Oversight </h2>

<!-- Filter Form -->
<div class="card">
    <form method="GET" action="admin_index.php">
        <input type="hidden" name="action" value="courses">
        <div class="filter-bar">
            <div class="filter-group">
                <label><small>Filter by Subject:</small></label>
                <select name="subject_id">
                    <option value="">All Subjects</option>
                    <?php while($s = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($selected_subj == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label><small>Filter by Instructor:</small></label>
                <select name="instructor_id">
                    <option value="">All Instructors</option>
                    <?php while($i = $instructors->fetch_assoc()): ?>
                        <option value="<?php echo $i['id']; ?>" <?php echo ($selected_inst == $i['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($i['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="admin_index.php?action=courses" class="btn" style="background:#eee; text-decoration:none; color:black;">Reset</a>
        </div>
    </form>
</div>

<!-- Course Table -->
<div class="card">
    <table border="1" width="100%" style="border-collapse: collapse;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="padding:10px;">Course Title</th>
                <th>Subject</th>
                <th>Instructor</th>
                <th>Max Students</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($courses->num_rows > 0): ?>
                <?php while($row = $courses->fetch_assoc()): ?>
                <tr>
                    <td style="padding:10px;">
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <small style="color:#888;">Created: <?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['instructor_name']); ?></td>
                    <td style="text-align:center;"><?php echo $row['max_students']; ?></td>
                    <td>
                        <?php 
                            $statusClass = 'status-' . strtolower($row['status']);
                            echo "<span class='$statusClass'>" . strtoupper($row['status']) . "</span>";
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:20px;">No courses found matching filters.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>