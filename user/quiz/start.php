<?php
require_once '../../config/config.php';

// Cek login
if (!is_logged_in()) {
    redirect('auth/login.php');
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cek apakah kuis valid dan aktif
$quiz = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM quiz WHERE id = $quiz_id AND is_active = 1 AND deleted_at IS NULL"));

if (!$quiz) {
    $_SESSION['error'] = "Kuis tidak ditemukan atau tidak aktif!";
    redirect('user/index.php');
}

// Buat quiz_result baru
$user_id = $_SESSION['user_id'];
$sql = "INSERT INTO quiz_results (user_id, quiz_id, score, is_completed) VALUES ($user_id, $quiz_id, 0, 0)";
if (mysqli_query($conn, $sql)) {
    $quiz_result_id = mysqli_insert_id($conn);
    redirect("quiz/question.php?result_id=" . $quiz_result_id);
} else {
    $_SESSION['error'] = "Terjadi kesalahan saat memulai kuis!";
    redirect('user/index.php');
}
?> 