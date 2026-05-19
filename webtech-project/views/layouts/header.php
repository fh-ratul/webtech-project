<!DOCTYPE html>
<html>
<head>
    <title>Admin - Online Quiz Platform</title>
    <style>
        body { font-family: sans-serif; margin:0; background:#f0f2f5; }
        /* The Blue Navigation Bar from your guide style */
        .nav { background:#1877f2; color:white; padding:15px; display:flex; gap:20px; align-items: center; }
        .nav a { color:white; text-decoration:none; font-size: 14px; font-weight: bold; }
        .nav a:hover { text-decoration: underline; }
        .content { padding:20px; }
        .card { background:white; padding:15px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #00367c; color: white; }
    </style>
</head>
<body>
<div class="nav">
    <span style="font-size: 18px; margin-right: 10px;">Admin Panel</span>
    <a href="admin_index.php?action=dashboard">Dashboard</a>
     <a href="admin_index.php?action=approvals">Approvals</a> <!-- Instr 3: Instructor Approvals -->
    <a href="admin_index.php?action=users">Users</a> <!-- Instr 2: Search/View Users -->
    <a href="admin_index.php?action=courses">Course Catalog</a> <!-- Instr 4: View Courses -->
    <a href="admin_index.php?action=subjects">Subjects</a> <!-- Instr 5: Taxonomy -->
    <a href="admin_index.php?action=quizzes">Quizzes</a> <!-- Instr 6: Quiz Management -->
    <a href="admin_index.php?action=reports">Reports</a> <!-- Instr 11: Reports -->
    <a href="admin_index.php?action=analytics">Analytics</a> <!-- Instr 8: Analytics -->
    <a href="admin_index.php?action=announcements">Announcements</a> <!-- Instr 9: Announcements -->
    <a href="admin_index.php?action=academic_report">Academic Reports</a>
    <a href="admin_index.php?action=policies">Policies</a> <!-- Instr 12: Config -->
    <a href="admin_index.php?action=logout" style="margin-left: auto; color: #ffcccc;">Logout</a>
</div>
<div class="content">