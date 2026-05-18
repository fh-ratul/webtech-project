<?php include 'views/layouts/header.php'; ?>

<h1>Dashboard Summary</h1>

<div style="display:flex; gap:20px;">
    <div class="card">
        <h3>Active Courses</h3>
        <p><?php echo $stats['active_courses']; ?></p>
    </div>
    <div class="card">
        <h3>Quiz Attempts Today</h3>
        <p><?php echo $stats['attempts_today']; ?></p>
    </div>
    <div class="card">
        <h3>Pending Instructor Approvals</h3>
        <p style="color:red;"><?php echo $stats['pending_instructors']; ?></p>
    </div>
</div>

<div class="card">
    <h3>System Audit (Latest Activity)</h3>
    <pre style="background: #eee; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;">
<?php 
    // Logic from your File Handling Guide: Check if file exists before reading
    if (file_exists("admin_audit.log")) {
        echo nl2br(file_get_contents("admin_audit.log")); 
    } else {
        echo "No activity recorded yet.";
    }
?>
    </pre>
</div>

<?php include 'views/layouts/footer.php'; ?>