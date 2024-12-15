<?php
require_once '../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard User - Quiz Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">Quiz Online</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person-circle"></i>
                    <span><?php echo $_SESSION['username']; ?></span>
                </a>
                <a class="nav-link" href="/auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary-dark">
                        <h3 class="text-light mb-0">Dashboard User</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary-dark">Selamat datang di Quiz Online</h5>
                        <p class="text-secondary">Silahkan pilih menu yang tersedia untuk memulai quiz.</p>
                        
                        <div class="list-group mt-4">
                            <a href="quiz/start.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-play-circle text-primary-custom"></i> Mulai Quiz Baru
                            </a>
                            <a href="quiz/history.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-clock-history text-primary-custom"></i> Riwayat Quiz
                            </a>
                            <a href="profile.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person text-primary-custom"></i> Edit Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 