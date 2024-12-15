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