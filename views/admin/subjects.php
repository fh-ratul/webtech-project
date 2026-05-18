<?php include 'views/layouts/header.php'; ?>

<h2>Subject Taxonomy Management </h2>

<!-- DYNAMIC FORM: Changes between Add and Edit mode -->
<div class="card">
    <?php if (isset($editSubject)): ?>
        <h3>Rename/Edit Subject: <?php echo htmlspecialchars($editSubject['name']); ?></h3>
        <form method="POST" action="admin_index.php?action=subjects">
            <input type="hidden" name="id" value="<?php echo $editSubject['id']; ?>">
            <div style="margin-bottom: 10px;">
                <label>New Name:</label><br>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editSubject['name']); ?>" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Description:</label><br>
                <textarea name="description" rows="3" style="width: 100%; padding: 8px;"><?php echo htmlspecialchars($editSubject['description']); ?></textarea>
            </div>
            <button type="submit" name="update_subject" class="btn btn-primary">Update Subject</button>
            <a href="admin_index.php?action=subjects" class="btn" style="background:#ccc; text-decoration:none; color:black; padding:8px 12px; border-radius:4px;">Cancel</a>
        </form>
    <?php else: ?>
        <h3>Add New Subject</h3>
        <form method="POST" action="admin_index.php?action=subjects">
            <div style="margin-bottom: 10px;">
                <label>Subject Name:</label><br>
                <input type="text" name="name" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Description:</label><br>
                <textarea name="description" rows="3" style="width: 100%; padding: 8px;"></textarea>
            </div>
            <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Existing Subjects</h3>
    <table border="1" width="100%" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th>ID</th><th>Subject Name</th><th>Description</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($subjects && $subjects->num_rows > 0): 
                while($row = $subjects->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <!-- Rename Link -->
                        <a href="admin_index.php?action=subjects&edit=<?php echo $row['id']; ?>" style="color: blue;">Rename</a> | 
                        <!-- Delete Link with Confirmation from JS Guide -->
                        <a href="admin_index.php?action=subjects&delete=<?php echo $row['id']; ?>" 
                           style="color: red;" 
                           onclick="return confirm('Are you sure you want to delete this subject?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4" style="text-align: center;">No subjects found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>