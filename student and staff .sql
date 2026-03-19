CREATE DATABASE IF NOT EXISTS fmrkit;
USE fmrkit;

-- =========================
-- STUDENTS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    student_email VARCHAR(255) NOT NULL UNIQUE,
    student_password VARCHAR(255) NOT NULL,
    student_batch VARCHAR(50),
    student_dept VARCHAR(50),
    student_semester VARCHAR(50),
    student_regno VARCHAR(50),
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================
--staff table
-- =========================
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_name VARCHAR(100) NOT NULL,
    staff_email VARCHAR(100) UNIQUE NOT NULL,
    staff_password VARCHAR(255) NOT NULL,
    staff_department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    subject_code VARCHAR(50) NOT NULL,
    department VARCHAR(50) NOT NULL,
    regulations VARCHAR(50) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    pdf_file VARCHAR(255) NOT NULL,
    image_base64 TEXT,
    youtube_link VARCHAR(255),
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (staff_id) REFERENCES staff(id) 
    ON DELETE CASCADE
);