<?php
require_once '../config/config.php';

// Cek apakah user sudah login dan role-nya admin
if (!is_logged_in() || !is_admin()) {
    redirect('auth/login.php');
}

// Ambil statistik untuk dashboard
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'"))['total'];
$total_quiz = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM quiz WHERE deleted_at IS NULL"))['total'];
$total_questions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM questions WHERE deleted_at IS NULL"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Quiz Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Quiz Online - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quiz/">Kelola Kuis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="questions/">Kelola Soal</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Dashboard Admin</h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Peserta</h5>
                        <h2><?php echo $total_users; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Kuis</h5>
                        <h2><?php echo $total_quiz; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Soal</h5>
                        <h2><?php echo $total_questions; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambahkan bagian lain sesuai kebutuhan -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 