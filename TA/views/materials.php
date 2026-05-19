<!DOCTYPE html>
<html>

<head>
    <title>Materials</title>
    <?php $baseUrl = rtrim(APP_BASE_URL, "/"); ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/instructor.css">
</head>

<body class="ta-page">

<nav class="navbar">
    <a href="<?php echo $baseUrl; ?>/ta/dashboard">Dashboard</a>
    <a href="<?php echo $baseUrl; ?>/ta/assigned-courses">Assigned Courses</a>
    <a href="<?php echo $baseUrl; ?>/ta/doubt-sessions">Doubt Sessions</a>
    <a href="<?php echo $baseUrl; ?>/ta/bookings">Bookings</a>
    <a href="<?php echo $baseUrl; ?>/ta/profile">Profile</a>
    <a href="<?php echo $baseUrl; ?>/auth/logout">Logout</a>
</nav>

<div class="container">
    <div class="page-header">
        <div class="title-block">
            <h1>Course Materials</h1>
            <p><?php echo htmlspecialchars($course['title']); ?> • Upload files or share resources.</p>
        </div>
        <div class="action-row">
            <a class="btn" href="<?php echo $baseUrl; ?>/ta/course-details?id=<?php echo $courseId; ?>">Back to Course</a>
        </div>
    </div>

    <div class="card">
        <p class="success"><?php echo $success; ?></p>
        <p class="error"><?php echo $error; ?></p>

        <form method="POST" enctype="multipart/form-data" class="inline-form">
            <input type="hidden" name="create_material" value="1">

            <div class="form-grid">
                <div class="form-field">
                    <label for="title">Material Title</label>
                    <input id="title" type="text" name="title" required>
                </div>
                <div class="form-field">
                    <label for="material_type">Material Type</label>
                    <select id="material_type" name="material_type" required>
                        <option value="file">File Upload</option>
                        <option value="link">External Link</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="material_file">Upload File</label>
                    <input id="material_file" type="file" name="material_file">
                </div>
                <div class="form-field">
                    <label for="link">External Link</label>
                    <input id="link" type="url" name="link" placeholder="https://...">
                </div>
            </div>

            <button type="submit">Add Material</button>
        </form>
    </div>

    <div class="card">
        <h2>Uploaded Materials</h2>

        <?php if ($materials->num_rows == 0) { ?>
            <div class="empty-state">No materials uploaded yet.</div>
        <?php } else { ?>
            <div class="list-stack">
                <?php while ($material = $materials->fetch_assoc()) { ?>
                    <?php
                        $isOwn = ((int) $material['uploaded_by'] === (int) $_SESSION['user_id'])
                            && ($material['uploaded_role'] === 'ta');
                    ?>
                    <div class="list-item">
                        <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                        <p class="muted">Type: <?php echo htmlspecialchars($material['material_type']); ?></p>
                        <?php if ($material['material_type'] == 'file') { ?>
                            <a class="btn" href="<?php echo $baseUrl; ?>/<?php echo htmlspecialchars($material['file_path']); ?>" target="_blank">Open File</a>
                        <?php } else { ?>
                            <a class="btn" href="<?php echo htmlspecialchars($material['file_path']); ?>" target="_blank">Open Link</a>
                        <?php } ?>

                        <?php if ($isOwn) { ?>
                            <form method="POST" enctype="multipart/form-data" class="inline-form">
                                <input type="hidden" name="update_material" value="1">
                                <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                <input type="hidden" name="existing_path" value="<?php echo htmlspecialchars($material['file_path']); ?>">

                                <div class="form-grid">
                                    <div class="form-field">
                                        <label>Title</label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($material['title']); ?>" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Type</label>
                                        <select name="material_type" required>
                                            <option value="file" <?php if ($material['material_type'] == 'file') { echo "selected"; } ?>>File</option>
                                            <option value="link" <?php if ($material['material_type'] == 'link') { echo "selected"; } ?>>Link</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-field">
                                        <label>Replace File</label>
                                        <input type="file" name="material_file">
                                    </div>
                                    <div class="form-field">
                                        <label>External Link</label>
                                        <input type="url" name="link" value="<?php echo htmlspecialchars($material['material_type'] == 'link' ? $material['file_path'] : ""); ?>" placeholder="https://...">
                                    </div>
                                </div>

                                <div class="form-footer">
                                    <button type="submit">Save Changes</button>
                                    <a class="btn secondary" href="<?php echo $baseUrl; ?>/ta/materials?course_id=<?php echo $courseId; ?>&delete=<?php echo $material['id']; ?>" onclick="return confirm('Delete this material?');">Delete</a>
                                </div>
                            </form>
                        <?php } else { ?>
                            <p class="muted">Not editable.</p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

</body>

</html>
