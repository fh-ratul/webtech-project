<?php include 'views/layouts/header.php'; ?>

<h2>Student Academic Report </h2>

<div style="display: flex; gap: 20px;">
    
    <!-- Search Section -->
    <div style="flex: 1;">
        <div class="card">
            <h3>Find Student</h3>
            <input type="text" id="stuQuery" placeholder="Enter Name or Student ID..." onkeyup="doStuSearch()" style="width:100%; padding:10px; border:1px solid #1877f2; border-radius:4px;">
            <div id="stuResults" style="margin-top:10px; border:1px solid #ddd; display:none; background:white;"></div>
        </div>
    </div>

    <!-- Report Section -->
    <div style="flex: 2;">
        <?php if ($student): ?>
            <div class="card">
                <div style="border-bottom: 2px solid #1877f2; margin-bottom:15px; padding-bottom:10px;">
                    <h3 style="margin:0;"><?php echo htmlspecialchars($student['name']); ?></h3>
                    <p style="margin:5px 0; color:#666;">ID: <?php echo $student['student_id']; ?> | Program: <?php echo $student['program']; ?></p>
                </div>

                <h4>Performance Summary</h4>
                <table border="1" width="100%" style="border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th style="padding:10px;">Course Title</th>
                            <th>Total Attempts</th>
                            <th>Avg. Quiz Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($performance as $row): ?>
                        <tr>
                            <td style="padding:10px;"><strong><?php echo htmlspecialchars($row['course_name']); ?></strong></td>
                            <td style="text-align:center;"><?php echo $row['attempt_count']; ?></td>
                            <td>
                                <?php echo ($row['avg_score'] !== null) ? number_format($row['avg_score'], 2) . "%" : "No attempts yet"; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card" style="text-align:center; padding:40px; color:#999;">
                <p>Search and select a student on the left to view their detailed academic summary.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function doStuSearch() {
    var q = document.getElementById('stuQuery').value;
    var resDiv = document.getElementById('stuResults');
    
    if (q.length < 2) { resDiv.style.display = "none"; return; }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/student_search.php?q=' + encodeURIComponent(q), true);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var students = JSON.parse(this.responseText);
            var html = '';
            students.forEach(function(s) {
                html += '<div style="padding:10px; border-bottom:1px solid #eee; cursor:pointer;" onclick="window.location=\'admin_index.php?action=academic_report&student_id='+s.id+'\'">' +
                        '<strong>'+s.name+'</strong><br><small>ID: '+s.student_id+'</small></div>';
            });
            resDiv.innerHTML = html;
            resDiv.style.display = "block";
        }
    };
    xhr.send();
}
</script>

<?php include 'views/layouts/footer.php'; ?>