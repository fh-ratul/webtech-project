<?php include 'views/layouts/header.php'; ?>

<h2>Platform-Wide Announcements </h2>

<?php if(isset($_GET['msg'])): ?>
    <div class="success card">Action successful!</div>
<?php endif; ?>

<div style="display: flex; gap: 20px; align-items: flex-start;">
    
    <!-- Left Column: Post Form -->
    <div style="flex: 1;">
        <div class="card">
            <h3>Post New Message</h3>
            <form method="POST" action="admin_index.php?action=announcements">
                <label><small>Announcement Title:</small></label><br>
                <input type="text" name="title" required style="width:100%; padding:8px; margin-bottom:10px; border:1px solid #ccc;">
                
                <label><small>Message Body:</small></label><br>
                <textarea name="body" rows="6" required style="width:100%; padding:8px; margin-bottom:10px; border:1px solid #ccc;"></textarea>
                
                <button type="submit" name="post_announcement" class="btn btn-primary" style="width:100%;">Broadcast to All Users</button>
            </form>
        </div>
    </div>

    <!-- Right Column: List of Announcements -->
    <div style="flex: 1.5;">
        <div class="card">
            <h3>Previous Announcements</h3>
            <?php if ($news->num_rows > 0): ?>
                <?php while($row = $news->fetch_assoc()): ?>
                    <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="color: #1877f2;"><?php echo htmlspecialchars($row['title']); ?></strong>
                            <a href="admin_index.php?action=announcements&delete_id=<?php echo $row['id']; ?>" 
                               style="color: red; font-size: 11px; text-decoration: none;" 
                               onclick="return confirm('Delete this announcement?')">[Delete]</a>
                        </div>
                        <p style="font-size: 14px; margin: 10px 0;"><?php echo nl2br(htmlspecialchars($row['body'])); ?></p>
                        <small style="color: #888;">Posted on: <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">No platform-wide announcements yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>