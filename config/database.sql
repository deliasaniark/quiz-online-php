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
    total_score INT DEFAULT 0,
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
    score INT,
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
INSERT INTO users (username, password, full_name, gender, birth_date, phone, role, total_score) VALUES
('admin', '$2a$12$bYZtl.nAgc9mvuA6CwYlIur4s2OLi6RSimVd7iKQOksewtIvH8qbu', 'Administrator', 'L', '1990-01-01', '08123456789', 'admin', NULL),
('johndoe', '$2a$12$JA1xxAi.VC9wMim8b52ZheSVreL2jWumFsRQUN.kN5FA61tPBIjii', 'John Doe', 'L', '1995-05-15', '08234567890', 'user', 850),
('janedoe', '$2a$12$JA1xxAi.VC9wMim8b52ZheSVreL2jWumFsRQUN.kN5FA61tPBIjii', 'Jane Doe', 'P', '1997-08-20', '08345678901', 'user', 920),
('bobsmith', '$2a$12$JA1xxAi.VC9wMim8b52ZheSVreL2jWumFsRQUN.kN5FA61tPBIjii', 'Bob Smith', 'L', '1993-03-10', '08456789012', 'user', 750),
('alicejones', '$2a$12$JA1xxAi.VC9wMim8b52ZheSVreL2jWumFsRQUN.kN5FA61tPBIjii', 'Alice Jones', 'P', '1996-12-25', '08567890123', 'user', 880);

-- Seed data for quiz table
INSERT INTO quiz (title, description, is_active) VALUES
('Matematika Dasar', 'Quiz tentang operasi matematika dasar', TRUE),
('Bahasa Inggris', 'Test kemampuan bahasa Inggris dasar', TRUE),
('Pengetahuan Umum', 'Quiz seputar pengetahuan umum', TRUE),
('Sejarah Indonesia', 'Quiz tentang sejarah Indonesia', FALSE),
('Sains Dasar', 'Quiz tentang ilmu pengetahuan alam', TRUE);

-- Seed data for questions table
INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, answer, explanation) VALUES
-- Matematika Dasar (Quiz ID: 1)
(1, 'Berapakah hasil dari 8 x 7?', '54', '56', '58', '60', 'B', 'Hasil perkalian 8 x 7 = 56'),
(1, 'Berapakah hasil dari 15 + 27?', '42', '41', '43', '40', 'A', 'Hasil penjumlahan 15 + 27 = 42'),
(1, 'Hasil dari 125 ÷ 5 adalah?', '20', '25', '30', '35', 'B', 'Hasil pembagian 125 ÷ 5 = 25'),
(1, 'Berapakah akar kuadrat dari 81?', '8', '9', '10', '11', 'B', 'Akar kuadrat dari 81 adalah 9'),
(1, 'Hasil dari 23 - 15 adalah?', '8', '7', '9', '6', 'A', 'Hasil pengurangan 23 - 15 = 8'),

-- Bahasa Inggris (Quiz ID: 2)
(2, 'What is the past tense of "eat"?', 'ate', 'eaten', 'eating', 'eated', 'A', 'Past tense dari eat adalah ate'),
(2, 'She ___ to school every day', 'go', 'goes', 'going', 'went', 'B', 'Menggunakan goes karena subjek orang ketiga tunggal'),
(2, 'The opposite of "happy" is...', 'sad', 'glad', 'mad', 'bad', 'A', 'Lawan kata dari happy adalah sad'),
(2, 'Which word is an adverb?', 'quick', 'quickly', 'quicker', 'quickest', 'B', 'Quickly adalah kata keterangan (adverb)'),
(2, 'Choose the correct spelling:', 'recieve', 'receive', 'receeve', 'receve', 'B', 'Receive adalah ejaan yang benar'),

-- Pengetahuan Umum (Quiz ID: 3)
(3, 'Ibukota Indonesia adalah?', 'Bandung', 'Surabaya', 'Jakarta', 'Medan', 'C', 'Jakarta adalah ibukota Indonesia'),
(3, 'Siapakah presiden pertama Indonesia?', 'Soekarno', 'Soeharto', 'Habibie', 'Gusdur', 'A', 'Ir. Soekarno adalah presiden pertama Indonesia'),
(3, 'Planet terbesar di tata surya adalah?', 'Mars', 'Jupiter', 'Saturnus', 'Uranus', 'B', 'Jupiter adalah planet terbesar di tata surya'),
(3, 'Benua terluas di dunia adalah?', 'Amerika', 'Afrika', 'Asia', 'Eropa', 'C', 'Asia adalah benua terluas di dunia'),
(3, 'Hewan tercepat di darat adalah?', 'Singa', 'Cheetah', 'Macan', 'Kuda', 'B', 'Cheetah adalah hewan tercepat di darat'),

-- Sejarah Indonesia (Quiz ID: 4)
(4, 'Kapan Indonesia merdeka?', '17 Agustus 1945', '17 Agustus 1944', '17 Agustus 1946', '17 Agustus 1947', 'A', 'Indonesia merdeka pada 17 Agustus 1945'),
(4, 'Dimana teks proklamasi ditandatangani?', 'Jl. Pegangsaan Timur 56', 'Jl. Pegangsaan Barat 56', 'Jl. Pegangsaan Timur 65', 'Jl. Pegangsaan Barat 65', 'A', 'Teks proklamasi ditandatangani di Jl. Pegangsaan Timur 56'),
(4, 'Siapa yang menjahit bendera merah putih pertama?', 'Fatmawati', 'Megawati', 'Cut Nyak Dien', 'R.A. Kartini', 'A', 'Fatmawati yang menjahit bendera merah putih pertama'),
(4, 'Perjanjian Linggarjati terjadi pada tahun?', '1945', '1946', '1947', '1948', 'B', 'Perjanjian Linggarjati terjadi pada tahun 1946'),
(4, 'Siapa pahlawan yang dijuluki Bapak Koperasi Indonesia?', 'Moh. Hatta', 'Soekarno', 'Tan Malaka', 'Soepomo', 'A', 'Moh. Hatta dijuluki sebagai Bapak Koperasi Indonesia'),

-- Sains Dasar (Quiz ID: 5)
(5, 'Apa simbol kimia untuk emas?', 'Ag', 'Au', 'Fe', 'Cu', 'B', 'Au (Aurum) adalah simbol kimia untuk emas'),
(5, 'Berapa jumlah planet di tata surya?', '7', '8', '9', '10', 'B', 'Ada 8 planet di tata surya'),
(5, 'Apa satuan dasar untuk mengukur suhu?', 'Kelvin', 'Celcius', 'Fahrenheit', 'Reamur', 'A', 'Kelvin adalah satuan dasar untuk mengukur suhu'),
(5, 'H2O adalah rumus kimia untuk?', 'Garam', 'Air', 'Gula', 'Asam', 'B', 'H2O adalah rumus kimia untuk air'),
(5, 'Organ apa yang menghasilkan insulin?', 'Hati', 'Ginjal', 'Pankreas', 'Usus', 'C', 'Pankreas adalah organ yang menghasilkan insulin');

-- Seed data for quiz_results table
INSERT INTO quiz_results (user_id, quiz_id, score, is_completed) VALUES
(2, 1, 90, TRUE),
(2, 2, 85, TRUE),
(3, 1, 95, TRUE),
(4, 3, 75, TRUE),
(5, 2, 88, TRUE);

-- Seed data for user_answers table
INSERT INTO user_answers (quiz_result_id, question_id, user_answer, is_correct) VALUES
(1, 1, 'B', TRUE),
(1, 2, 'A', TRUE),
(2, 3, 'A', TRUE),
(2, 4, 'B', TRUE),
(3, 1, 'B', TRUE),
(3, 2, 'A', TRUE),
(4, 5, 'C', TRUE),
(4, 6, 'A', TRUE),
(5, 3, 'A', TRUE),
(5, 4, 'B', TRUE);
