<?php include 'views/layouts/header.php'; ?>

<style>
    .admin-grid { display: flex; gap: 15px; align-items: flex-start; }
    .col-form { flex: 1.2; min-width: 280px; }
    .col-list { flex: 2; min-width: 400px; }
    .col-view { flex: 1.5; min-width: 320px; }
    
    /* Big Profile Styling */
    .profile-img-big { width: 100%; max-width: 250px; height: auto; border: 3px solid #1877f2; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    
    /* Input Styling */
    .input-field { width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
    .student-only { display: block; background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px dashed #ccc; }

    /* STATUS COLORS - As Requested */
    .status-active { color: green; font-weight: bold; }
    .status-inactive { color: red; font-weight: bold; }
    
    tr:hover { background-color: #f1f1f1; }
</style>

<h2>User Administration Center</h2>

<div class="admin-grid">
    
    <!-- COLUMN 1: CREATE USER -->
    <div class="col-form">
        <div class="card">
            <h3>Add New Account</h3>
            <form method="POST" enctype="multipart/form-data" action="admin_index.php?action=users">
                <input type="text" name="name" placeholder="Full Name" required class="input-field">
                <input type="email" name="email" placeholder="Email Address" required class="input-field">
                <input type="password" name="password" placeholder="Password" required class="input-field">
                <input type="text" name="phone" placeholder="Phone Number" class="input-field">
                
                <label><small>Select User Role:</small></label>
                <select name="role" id="roleSelect" class="input-field" onchange="toggleStudentFields()">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="ta">TA</option>
                    <option value="admin">Admin</option>
                </select>

                <div id="studentSection" class="student-only">
                    <input type="text" name="student_id" placeholder="Student ID" class="input-field">
                    <input type="text" name="program" placeholder="Program (e.g. CSE)" class="input-field">
                </div>

                <label><small>Profile Pic (Max 4MB):</small></label>
                <input type="file" name="profile_pic" class="input-field">
                
                <button type="submit" name="add_user" class="btn btn-primary" style="width:100%;">Create Account</button>
            </form>
        </div>
    </div>

    <!-- COLUMN 2: SEARCH & LIST (AJAX) -->
    <div class="col-list">
        <div class="card">
            <input type="text" id="userSearch" placeholder="Search by name or email..." onkeyup="searchUsers()" style="width:100%; padding:10px; box-sizing:border-box; border: 1px solid #1877f2; border-radius:4px;">
        </div>
        <table border="1" width="100%" style="background:white; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#eee;">
                    <th style="padding:10px;">User Info</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="results">
                <!-- AJAX rows here -->
            </tbody>
        </table>
    </div>

    <!-- COLUMN 3: BIG VIEW & EDIT -->
    <div class="col-view">
        <div class="card" style="text-align:center;">
            <h3>User Control Panel</h3>
            <?php if (isset($viewUser)): ?>
                <img src="uploads/<?php echo $viewUser['profile_pic']; ?>" class="profile-img-big" alt="Profile">
                
                <form method="POST" action="admin_index.php?action=users">
                    <input type="hidden" name="user_id" value="<?php echo $viewUser['id']; ?>">
                    <p style="font-size:18px; margin:5px;"><strong><?php echo htmlspecialchars($viewUser['name']); ?></strong></p>
                    
                    <!-- Status Display in View -->
                    <p>Current Status: 
                        <span class="<?php echo ($viewUser['is_active'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ($viewUser['is_active'] == 1) ? 'ACTIVE' : 'INACTIVE'; ?>
                        </span>
                    </p>

                    <div style="text-align:left; font-size:13px; margin: 15px 0; border-top:1px solid #eee; padding-top:10px;">
                        <strong>Email:</strong> <?php echo $viewUser['email']; ?><br>
                        <strong>Phone:</strong> <?php echo $viewUser['phone'] ?><br>
                        <?php if($viewUser['role'] == 'student'): ?>
                            <strong>ID:</strong> <?php echo $viewUser['student_id']; ?><br>
                            <strong>Program:</strong> <?php echo $viewUser['program']; ?>
                        <?php endif; ?>
                    </div>

                    <label><strong>Role Management:</strong></label>
                    <select name="role" class="input-field">
                        <option value="student" <?php if($viewUser['role']=='student') echo 'selected'; ?>>Student</option>
                        <option value="instructor" <?php if($viewUser['role']=='instructor') echo 'selected'; ?>>Instructor</option>
                        <option value="ta" <?php if($viewUser['role']=='ta') echo 'selected'; ?>>TA</option>
                        <option value="admin" <?php if($viewUser['role']=='admin') echo 'selected'; ?>>Admin</option>
                    </select>

                    <label><strong>Account Status:</strong></label>
                    <select name="is_active" class="input-field">
                        <option value="1" <?php if($viewUser['is_active']==1) echo 'selected'; ?>>Active</option>
                        <option value="0" <?php if($viewUser['is_active']==0) echo 'selected'; ?>>Inactive</option>
                    </select>

                    <button type="submit" name="update_user" class="btn btn-primary" style="width:100%;">Save Changes</button>
                    <a href="admin_index.php?action=users&delete=<?php echo $viewUser['id']; ?>" style="color:red; display:block; margin-top:15px; font-size:12px;" onclick="return confirm('Delete this account permanently?')">Delete User</a>
                </form>
            <?php else: ?>
                <p style="color:#999; padding:50px 0;">Select a user from the list to view profile and manage roles.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleStudentFields() {
    var role = document.getElementById("roleSelect").value;
    document.getElementById("studentSection").style.display = (role === "student") ? "block" : "none";
}

function searchUsers() {
    var query = document.getElementById('userSearch').value;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/search_users.php?q=' + encodeURIComponent(query), true);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var users = JSON.parse(this.responseText);
            var html = '';
            users.forEach(function(u) {
                // Determine color class for AJAX table
                var statusClass = u.is_active == 1 ? 'status-active' : 'status-inactive';
                var statusText = u.is_active == 1 ? 'Active' : 'Inactive';

                html += '<tr style="cursor:pointer" onclick="window.location=\'admin_index.php?action=users&id='+u.id+'\'">' +
                    '<td style="padding:10px;"><b>' + u.name + '</b><br><small>' + u.email + '</small></td>' +
                    '<td>' + u.role.toUpperCase() + '</td>' +
                    '<td class="' + statusClass + '">' + statusText + '</td>' +
                '</tr>';
            });
            document.getElementById('results').innerHTML = html;
        }
    };
    xhr.send();
}
window.onload = function() { searchUsers(); toggleStudentFields(); };
</script>

<?php include 'views/layouts/footer.php'; ?>