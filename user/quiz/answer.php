<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$quiz_result_id = $_POST['quiz_result_id'];
$answers = $_POST['answers']; // Array jawaban dari form

// Validasi quiz_result milik user ini
$query = "SELECT qr.* FROM quiz_results qr 
          JOIN quiz q ON qr.quiz_id = q.id 
          WHERE qr.id = ? AND qr.user_id = ? AND q.is_active = 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $quiz_result_id, $user_id);
mysqli_stmt_execute($stmt);
if (mysqli_stmt_get_result($stmt)->num_rows === 0) {
    echo json_encode(['success' => false]);
    exit;
}

$success = true;

// Loop untuk setiap jawaban
foreach ($answers as $answer) {
    $question_id = $answer['question_id'];
    $user_answer = $answer['answer'];

    // Cek jawaban benar/salah
    $query = "SELECT answer FROM questions WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $question_id);
    mysqli_stmt_execute($stmt);
    $correct_answer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['answer'];
    $is_correct = $user_answer === $correct_answer;

    // Simpan jawaban
    $query = "INSERT INTO user_answers (quiz_result_id, question_id, user_answer, is_correct) 
              VALUES (?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE user_answer = ?, is_correct = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iissss", $quiz_result_id, $question_id, $user_answer, $is_correct, $user_answer, $is_correct);
    
    if (!mysqli_stmt_execute($stmt)) {
        $success = false;
        break;
    }
}

echo json_encode(['success' => $success]); 