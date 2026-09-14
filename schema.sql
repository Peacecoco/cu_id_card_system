-- ============================================================
-- ID Card Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS idcard_system CHARACTER SET utf8mb4;
USE idcard_system;

-- ------------------------------------------------------------
-- Colleges: one row per college, drives which template/theme
-- is used to render that college's cards.
-- ------------------------------------------------------------
CREATE TABLE colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,          -- e.g. ENG, LAW, SCI
    template_key VARCHAR(50) NOT NULL,          -- only matters if a college needs a layout override; otherwise shared_front.php is used for all
    logo_path VARCHAR(255) NOT NULL,            -- college or university crest
    primary_color VARCHAR(7) NOT NULL DEFAULT '#1a3fa0',  -- drives header + footer theme
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Students
-- ------------------------------------------------------------
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matric_no VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    department VARCHAR(150) NOT NULL,
    programme VARCHAR(150) NOT NULL,
    college_id INT NOT NULL,
    level SMALLINT UNSIGNED NOT NULL,
    photo_path VARCHAR(255) NOT NULL,           -- raw uploaded photo
    photo_processed_path VARCHAR(255) NULL,     -- optimized photo, filled by preprocessing step
    validity_start YEAR NOT NULL,
    validity_end YEAR NOT NULL,
    status ENUM('active','graduated','suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Batches: audit trail of every generated print run
-- ------------------------------------------------------------
CREATE TABLE id_card_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    college_id INT NOT NULL,
    generated_by VARCHAR(100) NULL,
    student_count INT NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (college_id) REFERENCES colleges(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Per-student generation log inside a batch.
-- Lets one bad record fail without losing the whole batch.
-- ------------------------------------------------------------
CREATE TABLE id_card_batch_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('success','skipped','failed') NOT NULL,
    error_message VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES id_card_batches(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Sample seed data
-- ------------------------------------------------------------
INSERT INTO colleges (name, code, template_key, logo_path, primary_color) VALUES
('College of Engineering', 'ENG', 'engineering', '/assets/images/coe.jpg', '#1a3fa0'),  -- blue
('College of Law', 'LAW', 'law', '/assets/images/clds.jpg', '#c9a300'),                -- yellow
('College of Science', 'SCI', 'science', '/assets/images/cst.jpg', '#1f6b2e');         -- green
