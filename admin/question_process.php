<?php
require_once '../config/config.php';

// Cek apakah sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('/auth/login.php');
}

// Proses tambah soal
if (isset($_POST['add_question'])) {
    $quiz_id = $_POST['quiz_id'];
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $option_a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $option_b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $option_c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $option_d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $answer = $_POST['answer'];
    $explanation = !empty($_POST['explanation']) ? mysqli_real_escape_string($conn, $_POST['explanation']) : null;

    $query = "INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, answer, explanation) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isssssss", $quiz_id, $question, $option_a, $option_b, $option_c, $option_d, $answer, $explanation);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Soal berhasil ditambahkan!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal menambahkan soal!'
        ];
    }
    header("Location: questions.php?quiz_id=" . $quiz_id);
    exit;
}

// Proses edit soal
if (isset($_POST['edit_question'])) {
    $quiz_id = $_POST['quiz_id'];
    $question_id = $_POST['question_id'];
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $option_a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $option_b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $option_c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $option_d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $answer = $_POST['answer'];
    $explanation = !empty($_POST['explanation']) ? mysqli_real_escape_string($conn, $_POST['explanation']) : null;

    $query = "UPDATE questions SET 
              question = ?, 
              option_a = ?, 
              option_b = ?, 
              option_c = ?, 
              option_d = ?, 
              answer = ?, 
              explanation = ? 
              WHERE id = ? AND quiz_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssii", $question, $option_a, $option_b, $option_c, $option_d, $answer, $explanation, $question_id, $quiz_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Soal berhasil diupdate!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal mengupdate soal!'
        ];
    }
    header("Location: questions.php?quiz_id=" . $quiz_id);
    exit;
}

// Proses hapus soal
if (isset($_GET['delete']) && isset($_GET['quiz_id'])) {
    $question_id = $_GET['delete'];
    $quiz_id = $_GET['quiz_id'];

    $now = date('Y-m-d H:i:s');
    $query = "UPDATE questions SET deleted_at = ? WHERE id = ? AND quiz_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sii", $now, $question_id, $quiz_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Soal berhasil dihapus!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus soal!'
        ];
    }
    header("Location: questions.php?quiz_id=" . $quiz_id);
    exit;
}

// Redirect jika tidak ada aksi yang sesuai
header("Location: quiz.php");
exit; 