# FMRKit - MySQL Database Queries Documentation

## Table of Contents
1. [Database Configuration](#database-configuration)
2. [Database Schema](#database-schema)
3. [Student Management Queries](#student-management-queries)
4. [Staff Management Queries](#staff-management-queries)
5. [Material/Book Management Queries](#materialbook-management-queries)
6. [Admin Panel Queries](#admin-panel-queries)
7. [Common Queries](#common-queries)
8. [Security Notes](#security-notes)

---

## Database Configuration

### Connection Details (db1.php)
```
php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "fmrkit";
```

---

## Database Schema

### students Table
```
sql
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
```

### staff Table
```
sql
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_name VARCHAR(100) NOT NULL,
    staff_email VARCHAR(100) UNIQUE NOT NULL,
    staff_password VARCHAR(255) NOT NULL,
    staff_department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### departments Table
```
sql
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);
```

### staff Table (Admin Version)
```
sql
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    department_id INT,
    designation VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

### students Table (Admin Version)
```
sql
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    department_id INT,
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

### materials Table
```
sql
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    type ENUM('book', 'labmanual', 'notes', 'questionbank') NOT NULL,
    department_id INT,
    description TEXT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

---

## Student Management Queries

### studentregister.php

#### Check if email exists
```
sql
SELECT id FROM students WHERE student_email = ?
```
**Parameters:** email (string)

#### Insert new student
```
sql
INSERT INTO students 
    (student_name, student_email, student_password, student_batch, student_dept, student_semester, student_regno)
VALUES (?, ?, ?, ?, ?, ?, ?)
```
**Parameters:** name, email, password (hashed), batch, department, semester, regno

---

### student.php

#### Get student by ID
```
sql
SELECT * FROM students WHERE id = ?
```
**Parameters:** user_id (integer)

#### Update student photo
```
sql
UPDATE students SET photo = ? WHERE id = ?
```
**Parameters:** targetFile, user_id

#### Fetch materials by department
```
sql
SELECT id, title, semester, content_type, pdf_file, image_base64, youtube_link,
       subject_name, subject_code, department, staff_id
FROM materials
WHERE department = ?
```
**Parameters:** department (string)

---

### simaple.php

#### Get student by ID
```
sql
SELECT * FROM students WHERE id = ?
```
**Parameters:** user_id (integer)

#### Update student photo
```
sql
UPDATE students SET photo = ? WHERE id = ?
```
**Parameters:** targetFile, user_id

---

### dep.php

#### Student login authentication
```
sql
SELECT id, student_name, student_password, student_dept
FROM students
WHERE student_email = ?
```
**Parameters:** email (string)

---

### eeestudent.php

#### Get student by ID
```
sql
SELECT * FROM students WHERE id = ?
```
**Parameters:** user_id (integer)

#### Update student photo
```
sql
UPDATE students SET photo = ? WHERE id = ?
```
**Parameters:** targetFile, user_id

#### Fetch materials by department
```
sql
SELECT id, title, semester, content_type, pdf_file, image_base64, youtube_link,
       subject_name, subject_code, department
FROM materials
WHERE department = ?
```
**Parameters:** department (string)

---

## Staff Management Queries

### staffregister.php

#### Check if email exists
```
sql
SELECT id FROM staff WHERE staff_email = ?
```
**Parameters:** email (string)

#### Get latest staff ID by department
```
sql
SELECT staff_id FROM staff WHERE department = ? ORDER BY id DESC LIMIT 1
```
**Parameters:** department (string)

#### Insert new staff
```
sql
INSERT INTO staff (staff_id, staff_name, staff_email, staff_password, department)
VALUES (?, ?, ?, ?, ?)
```
**Parameters:** staff_id, name, email, password (hashed), department

---

### staffdep.php

#### Staff login authentication
```
sql
SELECT staff_name, staff_password, department
FROM staff
WHERE staff_email = ?
```
**Parameters:** email (string)

---

### staff.php

#### Get staff by ID
```
sql
SELECT staff_email, department, photo FROM staff WHERE staff_id = ? LIMIT 1
```
**Parameters:** staff_id (string)

#### Update staff photo
```
sql
UPDATE staff SET photo = ? WHERE staff_id = ?
```
**Parameters:** path, staff_id

#### Get PDF file by material ID
```
sql
SELECT pdf_file FROM materials WHERE id = ? LIMIT 1
```
**Parameters:** mid (integer)

#### Delete material
```
sql
DELETE FROM materials WHERE id = ?
```
**Parameters:** mid (integer)

#### Get all staff
```
sql
SELECT staff_id, staff_name FROM staff
```

#### Fetch all materials
```
sql
SELECT id, title, semester, content_type, pdf_file, image_base64,
       youtube_link, subject_name, subject_code, department, staff_id 
FROM materials 
ORDER BY id DESC
```

#### Get distinct departments
```
sql
SELECT DISTINCT department FROM materials
```

---

### st2.php

#### Get staff by ID
```
sql
SELECT staff_email, department, photo FROM staff WHERE staff_id = ? LIMIT 1
```
**Parameters:** staff_id (string)

#### Update staff photo
```
sql
UPDATE staff SET photo = ? WHERE staff_id = ?
```
**Parameters:** path, staff_id

#### Get PDF by ID and department
```
sql
SELECT pdf_file FROM materials WHERE id = ? AND department = ?
```
**Parameters:** mid (integer), department (string)

#### Delete material
```
sql
DELETE FROM materials WHERE id = ? AND department = ?
```
**Parameters:** mid (integer), department (string)

#### Fetch materials
```
sql
SELECT id, title, semester, content_type, pdf_file, image_base64, youtube_link,
       subject_name, subject_code, department
FROM materials
WHERE department = ?
ORDER BY id DESC
```
**Parameters:** department (string)

---

## Material/Book Management Queries

### upload.php

#### Insert new material
```
sql
INSERT INTO materials
    (staff_id, title, subject_name, subject_code, department, regulations, semester, content_type, pdf_file, image_base64, youtube_link, upload_time)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
```

---

### handler.php

#### Insert book
```sql
INSERT INTO books (title, subject_code, youtube_link, uploaded_by, filename, thumbnail) 
VALUES (?, ?, ?, ?, ?, ?)
```

#### Fetch all books
```
sql
SELECT * FROM books ORDER BY semester, id DESC
```

---

## Admin Panel Queries

### admin.php

#### Create departments table
```
sql
CREATE TABLE IF NOT EXISTS departments (...)
```

#### Create staff table
```
sql
CREATE TABLE IF NOT EXISTS staff (...)
```

#### Create students table
```
sql
CREATE TABLE IF NOT EXISTS students (...)
```

#### Create materials table
```
sql
CREATE TABLE IF NOT EXISTS materials (...)
```

#### Check departments count
```
sql
SELECT COUNT(*) as count FROM departments
```

#### Insert default departments
```sql
INSERT INTO departments (name) VALUES ('$dept')
```

#### Add staff
```
sql
INSERT INTO staff (name, email, phone, department_id, designation) VALUES (?, ?, ?, ?, ?)
```

#### Update staff
```
sql
UPDATE staff SET name = ?, email = ?, phone = ?, department_id = ?, designation = ? WHERE id = ?
```

#### Delete staff
```
sql
DELETE FROM staff WHERE id = $id
```

#### Add student
```
sql
INSERT INTO students (name, email, phone, department_id, year) VALUES (?, ?, ?, ?, ?)
```

#### Update student
```
sql
UPDATE students SET name = ?, email = ?, phone = ?, department_id = ?, year = ? WHERE id = ?
```

#### Delete student
```
sql
DELETE FROM students WHERE id = $id
```

#### Add material
```
sql
INSERT INTO materials (title, type, department_id, description) VALUES (?, ?, ?, ?)
```

#### Update material
```
sql
UPDATE materials SET title = ?, type = ?, department_id = ?, description = ? WHERE id = ?
```

#### Delete material
```
sql
DELETE FROM materials WHERE id = $id
```

#### Fetch all departments
```
sql
SELECT * FROM departments ORDER BY name
```

#### Fetch staff with department join
```
sql
SELECT s.*, d.name as dept_name 
FROM staff s 
LEFT JOIN departments d ON s.department_id = d.id
WHERE s.department_id = $filter_dept
ORDER BY s.id DESC
```

#### Fetch students with department join
```
sql
SELECT st.*, d.name as dept_name 
FROM students st 
LEFT JOIN departments d ON st.department_id = d.id
WHERE st.department_id = $filter_dept
ORDER BY st.id DESC
```

#### Fetch materials with department join
```
sql
SELECT m.*, d.name as dept_name 
FROM materials m 
LEFT JOIN departments d ON m.department_id = d.id 
WHERE m.department_id = $filter_dept AND m.type = '$filter_type'
ORDER BY m.id DESC
```

#### Get department totals
```
sql
SELECT COUNT(*) as cnt FROM staff WHERE department_id = $dept_id
SELECT COUNT(*) as cnt FROM students WHERE department_id = $dept_id
SELECT COUNT(*) as cnt FROM materials WHERE department_id = $dept_id AND type = 'book'
SELECT COUNT(*) as cnt FROM materials WHERE department_id = $dept_id AND type = 'labmanual'
SELECT COUNT(*) as cnt FROM materials WHERE department_id = $dept_id AND type = 'notes'
SELECT COUNT(*) as cnt FROM materials WHERE department_id = $dept_id AND type = 'questionbank'
```

#### Dashboard count queries
```
sql
SELECT COUNT(*) FROM staff
SELECT COUNT(*) FROM students
SELECT COUNT(*) FROM materials WHERE type = 'book'
SELECT COUNT(*) FROM materials WHERE type = 'labmanual'
SELECT COUNT(*) FROM materials WHERE type = 'notes'
SELECT COUNT(*) FROM materials WHERE type = 'questionbank'
SELECT COUNT(*) FROM departments
SELECT COUNT(*) FROM materials
```

---

## Common Queries

### Authentication Patterns

#### Login with prepared statement
```
sql
SELECT * FROM table WHERE column = ?
```

#### Check existing record
```
sql
SELECT id FROM table WHERE column = ?
```

### Update Patterns

#### Update single field
```
sql
UPDATE table SET field = ? WHERE id = ?
```

#### Update multiple fields
```
sql
UPDATE table SET field1 = ?, field2 = ? WHERE id = ?
```

### Delete Patterns

#### Delete by ID (safe with prepared statement)
```
sql
DELETE FROM table WHERE id = ?
```

#### Delete by ID (unsafe - direct variable)
```
sql
DELETE FROM table WHERE id = $id
```

### Join Patterns

#### LEFT JOIN with filtering
```
sql
SELECT t.*, d.name as dept_name 
FROM table t 
LEFT JOIN departments d ON t.department_id = d.id
WHERE condition
ORDER BY t.id DESC
```

### Aggregation Patterns

#### COUNT with condition
```
sql
SELECT COUNT(*) as cnt FROM table WHERE condition
```

#### COUNT all
```
sql
SELECT COUNT(*) FROM table
```

---

## Security Notes

### ✅ Secure Practices Found

1. **Prepared Statements** - Most INSERT, UPDATE, SELECT queries use prepared statements with `bind_param()`
2. **Password Hashing** - Using `password_hash()` for password storage
3. **mysqli_set_charset** - Character set properly set to utf8mb4

### ⚠️ Security Issues Found

1. **Direct Variable Interpolation** in admin.php:
   
```php
   $conn->query("DELETE FROM staff WHERE id=$id");
   $conn->query("INSERT INTO departments (name) VALUES ('$dept')");
   
```
   These should use prepared statements to prevent SQL injection.

2. **String Interpolation in Queries**:
   
```
php
   $staff_query .= " WHERE s.department_id = $filter_dept";
   $material_query .= " AND m.department_id = $filter_dept";
   
```

### Recommendations

1. Replace all direct variable interpolations with prepared statements
2. Add input validation/sanitization
3. Use stored procedures for complex operations
4. Implement proper error handling without exposing sensitive information
5. Add rate limiting for login attempts

---

## Query Summary by File

| File | Query Types | Count |
|------|-------------|-------|
| studentregister.php | SELECT, INSERT | 2 |
| student.php | SELECT, UPDATE | 4 |
| staffregister.php | SELECT, INSERT | 3 |
| staff.php | SELECT, UPDATE, DELETE | 8 |
| admin.php | CREATE, SELECT, INSERT, UPDATE, DELETE, COUNT | 30+ |
| dep.php | SELECT | 1 |
| st2.php | SELECT, UPDATE, DELETE | 6 |
| upload.php | INSERT | 1 |
| handler.php | INSERT, SELECT | 2 |
| simaple.php | SELECT, UPDATE | 2 |
| eeestudent.php | SELECT, UPDATE | 3 |

---

*Document generated for FMRKit Project*
*Database: MySQL / MariaDB*
*PHP Interface: mysqli*
