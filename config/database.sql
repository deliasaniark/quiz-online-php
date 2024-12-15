-- Create database
CREATE DATABASE IF NOT EXISTS db_quiz_online;
USE db_quiz_online;

-- Users table with additional profile columns
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    gender CHAR(1) DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Quiz table
CREATE TABLE IF NOT EXISTS quiz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Questions table
CREATE TABLE IF NOT EXISTS questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    answer ENUM('A','B','C','D') NOT NULL,
    explanation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
);

-- Quiz results table
CREATE TABLE IF NOT EXISTS quiz_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    quiz_id INT,
    score INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
);

-- User answers table
CREATE TABLE IF NOT EXISTS user_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_result_id INT,
    question_id INT,
    user_answer ENUM('A','B','C','D'),
    is_correct BOOLEAN DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_result_id) REFERENCES quiz_results(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

-- Seed data for users table
-- Password:
-- admin = admin
-- user = 12345
INSERT INTO users (username, password, full_name, gender, birth_date, phone, role) VALUES
('admin', '$2a$12$bYZtl.nAgc9mvuA6CwYlIur4s2OLi6RSimVd7iKQOksewtIvH8qbu', 'Administrator', 'L', '1990-01-01', '08123456789', 'admin'),
('johndoe', '$2a$12$JA1xxAi.VC9wMim8b52ZheSVreL2jWumFsRQUN.kN5FA61tPBIjii', 'John Doe', 'L', '1995-05-15', '08234567890', 'user'),
('janedoe', '$2a$12$JA1xxAi.VC9wMim8b52ZheSVreL2jWumFsRQUN.kN5FA61tPBIjii', 'Jane Doe', 'P', '1997-08-20', '08345678901', 'user');

-- Seed data for quiz table
INSERT INTO quiz (title, description, is_active) VALUES
('Matematika Dasar', 'Quiz tentang operasi matematika dasar', TRUE),
('Bahasa Inggris', 'Test kemampuan bahasa Inggris dasar', TRUE),
('Pengetahuan Umum', 'Quiz seputar pengetahuan umum', TRUE);

-- Seed data for questions table
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, answer, explanation) VALUES
-- Matematika Dasar (Quiz ID: 1)
(1, 'Berapakah hasil dari 8 x 7?', '54', '56', '58', '60', 'B', 'Hasil perkalian 8 x 7 = 56'),
(1, 'Berapakah hasil dari 15 + 27?', '42', '41', '43', '40', 'A', 'Hasil penjumlahan 15 + 27 = 42'),
(1, 'Hasil dari 125 ÷ 5 adalah?', '20', '25', '30', '35', 'B', 'Hasil pembagian 125 ÷ 5 = 25'),

-- Bahasa Inggris (Quiz ID: 2) 
(2, 'What is the past tense of "eat"?', 'ate', 'eaten', 'eating', 'eated', 'A', 'Past tense dari eat adalah ate'),
(2, 'She ___ to school every day', 'go', 'goes', 'going', 'went', 'B', 'Menggunakan goes karena subjek orang ketiga tunggal'),
(2, 'The opposite of "happy" is...', 'sad', 'glad', 'mad', 'bad', 'A', 'Lawan kata dari happy adalah sad'),

-- Pengetahuan Umum (Quiz ID: 3)
(3, 'Ibukota Indonesia adalah?', 'Bandung', 'Surabaya', 'Jakarta', 'Medan', 'C', 'Jakarta adalah ibukota Indonesia'),
(3, 'Siapakah presiden pertama Indonesia?', 'Soekarno', 'Soeharto', 'Habibie', 'Gusdur', 'A', 'Ir. Soekarno adalah presiden pertama Indonesia'),
(3, 'Planet terbesar di tata surya adalah?', 'Mars', 'Jupiter', 'Saturnus', 'Uranus', 'B', 'Jupiter adalah planet terbesar di tata surya');

-- Seed data for quiz_results table
INSERT INTO quiz_results (user_id, quiz_id, score, is_completed) VALUES
(2, 1, 100, TRUE), -- User 2 menjawab semua benar di Quiz 1
(2, 2, 67, TRUE),  -- User 2 menjawab 2 benar dari 3 soal di Quiz 2
(3, 1, 100, TRUE), -- User 3 menjawab semua benar di Quiz 1
(3, 3, 67, TRUE);  -- User 3 menjawab 2 benar dari 3 soal di Quiz 3

-- Seed data for user_answers table
INSERT INTO user_answers (quiz_result_id, question_id, user_answer, is_correct) VALUES
-- Quiz 1 (Matematika) - User 2 (100%)
(1, 1, 'B', TRUE),
(1, 2, 'A', TRUE),
(1, 3, 'B', TRUE),

-- Quiz 2 (B. Inggris) - User 2 (67%)
(2, 4, 'A', TRUE),
(2, 5, 'B', TRUE),
(2, 6, 'B', FALSE),

-- Quiz 1 (Matematika) - User 3 (100%)
(3, 1, 'B', TRUE),
(3, 2, 'A', TRUE),
(3, 3, 'B', TRUE),

-- Quiz 3 (Pengetahuan Umum) - User 3 (67%)
(4, 7, 'C', TRUE),
(4, 8, 'A', TRUE),
(4, 9, 'C', FALSE);
