-- TA feature schema updates
-- Run these statements in your database before using TA features.

ALTER TABLE announcements
    ADD COLUMN posted_by INT NULL,
    ADD COLUMN posted_role VARCHAR(20) NULL;

ALTER TABLE course_materials
    ADD COLUMN uploaded_by INT NULL,
    ADD COLUMN uploaded_role VARCHAR(20) NULL;

ALTER TABLE doubt_sessions
    ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'scheduled',
    ADD COLUMN notice TEXT NULL;

CREATE TABLE IF NOT EXISTS at_risk_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    flagged_by INT NOT NULL,
    threshold_percent DECIMAL(5,2) NOT NULL,
    reason VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS course_tas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    ta_id INT NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_course_ta (course_id, ta_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (ta_id) REFERENCES users(id) ON DELETE CASCADE
);
