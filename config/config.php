<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_quiz_online');

// Buat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Start session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi base URL
function base_url($path = '') {
    $base_url = 'http://localhost/quiz-online';
    return $base_url . '/' . $path;
}

// Fungsi redirect
function redirect($path) {
    header("Location: " . base_url($path));
    exit();
}

// Fungsi cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi cek role admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
} 