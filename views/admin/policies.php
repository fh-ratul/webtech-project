<?php include 'views/layouts/header.php'; ?>

<h2>System Configuration & Policies </h2>

<?php if (!empty($success_msg)): ?>
    <div class="card" style="color: green; border-left: 5px solid green;">
        <strong>Success:</strong> <?php echo $success_msg; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="admin_index.php?action=policies">
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label><strong>Platform Display Name:</strong></label><br>
            <input type="text" name="p_name" value="<?php echo htmlspecialchars($settings['platform_name']); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label><strong>Maximum Quiz Duration (Minutes):</strong></label><br>
            <small style="color: #666;">Limits how long any quiz can last platform-wide.</small><br>
            <input type="number" name="max_duration" value="<?php echo $settings['max_quiz_duration']; ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label><strong>Default Max Students per Course:</strong></label><br>
            <small style="color: #666;">Suggested limit for newly created courses.</small><br>
            <input type="number" name="max_students" value="<?php echo $settings['default_max_students']; ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label><strong>Allow Late Submissions:</strong></label><br>
            <select name="late_sub" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <option value="yes" <?php echo ($settings['allow_late_submission'] == 'yes') ? 'selected' : ''; ?>>Yes (Enabled)</option>
                <option value="no" <?php echo ($settings['allow_late_submission'] == 'no') ? 'selected' : ''; ?>>No (Strict Deadline)</option>
            </select>
        </div>

        <hr>
        <button type="submit" name="save_settings" class="btn btn-primary" style="width: 100%;">Save Platform Policies</button>
    </form>
</div>

<!-- Info box for your project defense -->
<div class="card" style="background: #f8f9fa; font-size: 13px; color: #555;">
    <strong>Note for Admin:</strong> These settings are stored in <code>system_policies.json</code>. This allows the platform to maintain global configurations without unnecessary database queries on every page load.
</div>

<?php include 'views/layouts/footer.php'; ?>