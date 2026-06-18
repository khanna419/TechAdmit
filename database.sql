-- ============================================================
--  Engineering Admission Portal  |  Database Schema
--  Run this in phpMyAdmin or MySQL CLI before starting XAMPP
-- ============================================================

CREATE DATABASE IF NOT EXISTS engineering_admission;
USE engineering_admission;

-- ---------- users (login accounts) ----------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,       -- stored as SHA2-256 hash
    role        ENUM('student','admin') DEFAULT 'student',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------- courses ----------
CREATE TABLE IF NOT EXISTS courses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(20)  NOT NULL UNIQUE,
    name        VARCHAR(150) NOT NULL,
    seats       INT          NOT NULL DEFAULT 60,
    fee_per_yr  DECIMAL(10,2) NOT NULL,
    duration    VARCHAR(30)  DEFAULT '4 Years (B.Tech)',
    description TEXT
);

-- ---------- applications ----------
CREATE TABLE IF NOT EXISTS applications (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    app_number      VARCHAR(20) NOT NULL UNIQUE,
    user_id         INT NOT NULL,
    course_id       INT NOT NULL,
    -- personal
    first_name      VARCHAR(60)  NOT NULL,
    last_name       VARCHAR(60)  NOT NULL,
    dob             DATE         NOT NULL,
    gender          ENUM('Male','Female','Non-binary','Prefer not to say') NOT NULL,
    mobile          VARCHAR(15)  NOT NULL,
    category        ENUM('General','OBC','SC','ST','EWS') NOT NULL,
    address         TEXT         NOT NULL,
    state           VARCHAR(60)  NOT NULL,
    pincode         VARCHAR(10)  NOT NULL,
    -- academics
    score_10        DECIMAL(5,2),
    score_12        DECIMAL(5,2),
    jee_percentile  DECIMAL(5,2),
    jee_rank        INT,
    cet_percentile  DECIMAL(5,2),
    board           VARCHAR(30),
    passing_year    YEAR,
    -- status
    status          ENUM('Pending','Under Review','Accepted','Rejected') DEFAULT 'Pending',
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ---------- seed courses ----------
INSERT IGNORE INTO courses (code, name, seats, fee_per_yr, description) VALUES
('CSE',  'Computer Science & Engineering',   280, 180000, 'Algorithms, OS, Networks, AI/ML'),
('ECE',  'Electronics & Communication',       180, 160000, 'VLSI, Signals, Embedded Systems'),
('ME',   'Mechanical Engineering',            200, 150000, 'Thermodynamics, CAD, Manufacturing'),
('CE',   'Civil Engineering',                 160, 140000, 'Structures, Geotechnics, Transportation'),
('EE',   'Electrical Engineering',            180, 150000, 'Power Systems, Control, Machines'),
('AIML', 'Artificial Intelligence & ML',      120, 200000, 'Deep Learning, NLP, Computer Vision'),
('IT',   'Information Technology',            160, 170000, 'Web, Cloud, Cybersecurity'),
('CHE',  'Chemical Engineering',              120, 150000, 'Process Design, Thermodynamics');

-- ---------- seed admin user (password: admin123) ----------
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Admin', 'admin@techadmit.in', SHA2('admin123', 256), 'admin');
