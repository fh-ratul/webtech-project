<?php include 'views/layouts/header.php'; ?>

<h2>Instructor Registration Requests </h2>

<?php if(isset($_GET['msg'])): ?>
    <div class="success card">Instructor request successfully <?php echo $_GET['msg']; ?>.</div>
<?php endif; ?>

<div class="card">
    <table border="1" width="100%" style="border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="padding:10px;">Instructor Name</th>
                <th>Contact Info</th>
                <th>Request Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pending->num_rows > 0): ?>
                <?php while($row = $pending->fetch_assoc()): ?>
                <tr>
                    <td style="padding:10px;"><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo $row['email']; ?><br><small><?php echo $row['phone']; ?></small></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <!-- APPROVE BUTTON -->
                        <a href="admin_index.php?action=approvals&approve_id=<?php echo $row['id']; ?>" 
                           class="btn" style="background: green; color:white; font-size:12px;">Approve</a>
                        
                        <!-- REJECT BUTTON -->
                        <a href="admin_index.php?action=approvals&reject_id=<?php echo $row['id']; ?>" 
                           class="btn" style="background: red; color:white; font-size:12px;" 
                           onclick="return confirm('Are you sure you want to REJECT this instructor?')">Reject</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center; padding:30px; color:#999;">No pending instructor registrations.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>