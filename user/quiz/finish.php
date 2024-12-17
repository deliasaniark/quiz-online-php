<?php
require_once '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    redirect('/user/quiz.php');
}

$user_id = $_SESSION['user_id'];
$quiz_result_id = $_GET['id'];

// Validasi quiz_result milik user ini
$query = "SELECT qr.*, q.id as quiz_id 
          FROM quiz_results qr 
          JOIN quiz q ON qr.quiz_id = q.id 
          WHERE qr.id = ? AND qr.user_id = ? AND q.is_active = 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $quiz_result_id, $user_id);
mysqli_stmt_execute($stmt);
$quiz_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$quiz_result) {
    redirect('/user/quiz.php');
}

// Hitung skor
$query = "SELECT 
            (SELECT COUNT(*) FROM questions WHERE quiz_id = ? AND deleted_at IS NULL) as total_questions,
            (SELECT COUNT(*) FROM user_answers WHERE quiz_result_id = ? AND is_correct = 1) as correct_answers";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $quiz_result['quiz_id'], $quiz_result_id);
mysqli_stmt_execute($stmt);
$result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Hitung skor dengan rentang 0-100
$total_questions = $result['total_questions'];
$correct_answers = $result['correct_answers'];
$score = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100) : 0;

// Update quiz_result
$query = "UPDATE quiz_results SET score = ?, is_completed = 1 WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $score, $quiz_result_id);
mysqli_stmt_execute($stmt);

$_SESSION['success'] = "Quiz selesai! Skor Anda: " . $score;
redirect('/user/quiz.php'); 