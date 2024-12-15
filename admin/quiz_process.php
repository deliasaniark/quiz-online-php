<?php
require_once '../config/config.php';

// Cek apakah sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('/auth/login.php');
}

// Proses tambah quiz baru
if (isset($_POST['add_quiz'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $query = "INSERT INTO quiz (title, description, is_active) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $title, $description, $is_active);

    if (mysqli_stmt_execute($stmt)) {
        $quiz_id = mysqli_insert_id($conn);
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Quiz berhasil ditambahkan! Silakan tambahkan soal.'
        ];
        header("Location: questions.php?quiz_id=" . $quiz_id);
        exit;
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal menambahkan quiz!'
        ];
        header("Location: quiz.php");
        exit;
    }
}

// Proses edit quiz
if (isset($_POST['edit_quiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $query = "UPDATE quiz SET title = ?, description = ?, is_active = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $title, $description, $is_active, $quiz_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Quiz berhasil diupdate!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal mengupdate quiz!'
        ];
    }
    header("Location: quiz.php");
    exit;
}

// Proses hapus quiz
if (isset($_GET['delete'])) {
    $quiz_id = $_GET['delete'];
    
    // Hapus soal terkait terlebih dahulu
    $query = "DELETE FROM questions WHERE quiz_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $quiz_id);
    mysqli_stmt_execute($stmt);
    
    // Kemudian hapus quiz
    $query = "DELETE FROM quiz WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $quiz_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Quiz berhasil dihapus!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus quiz!'
        ];
    }
    header("Location: quiz.php");
    exit;
}

// Redirect jika tidak ada aksi yang sesuai
header("Location: quiz.php");
exit; 