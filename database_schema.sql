-- Create the database with full UTF-8 support for Kurdish/Arabic characters
CREATE DATABASE IF NOT EXISTS edu_nova_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE edu_nova_db;

-- ==========================================
-- 1. USERS TABLE (Handles all 7 Roles)
-- ==========================================
CREATE TABLE users (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_identifier ENUM(
        'student', 
        'teacher', 
        'assistant_principal', 
        'principal', 
        'accounting', 
        'hr', 
        'alumni'
    ) NOT NULL,
    department VARCHAR(100) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 2. STUDENT FINANCES (Enforcing 5 Installments)
-- ==========================================
CREATE TABLE student_finances (
    student_id VARCHAR(50) PRIMARY KEY,
    total_fees DECIMAL(12, 2) NOT NULL,
    paid_amount DECIMAL(12, 2) DEFAULT 0.00,
    installments_paid INT DEFAULT 0 CHECK (installments_paid >= 0 AND installments_paid <= 5),
    is_blocked BOOLEAN DEFAULT FALSE,
    currency VARCHAR(10) DEFAULT 'IQD',
    last_payment_date TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 3. COURSES & SCHEDULES
-- ==========================================
CREATE TABLE courses (
    id VARCHAR(50) PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    teacher_id VARCHAR(50),
    schedule_time VARCHAR(100) NOT NULL,
    location VARCHAR(100) DEFAULT 'Main Campus - Erbil',
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ==========================================
-- 4. ALUMNI EVENTS
-- ==========================================
CREATE TABLE alumni_events (
    id VARCHAR(50) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Admin User for Testing (Password: password123)
INSERT INTO users (id, name, email, password_hash, role_identifier, department) 
VALUES (
    'U_999', 
    'ZAS TECH Admin', 
    'admin@zas-tech.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'principal', 
    'Administration'
);