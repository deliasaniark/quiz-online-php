<?php
// Mulai session
session_start();

// Koneksi database
$conn = mysqli_connect('localhost', 'root', '', 'db_quiz_online');

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal");
}

// Fungsi redirect sederhana
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fungsi base URL
function base_url($path = '') {
    $base_url = 'http://localhost:8000';
    return rtrim($base_url . '/' . ltrim($path, '/'), '/');
}

// Fungsi cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi cek role admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Tambahkan fungsi ini
function getProfilePhotoUrl($user) {
    // Gunakan ROOT_PATH untuk mendapatkan path absolut
    $photo_path = __DIR__ . '/../assets/img/profile/' . $user['profile_photo'];
    
    if ($user['profile_photo'] && file_exists($photo_path)) {
        return '/assets/img/profile/' . $user['profile_photo'];
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random';
}
  