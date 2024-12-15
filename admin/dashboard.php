<?php
require_once '../config/config.php';

// Cek apakah sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('/auth/login.php');
}

// Ambil data admin
$id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Hitung jumlah user dan quiz
$query_users = "SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'";
$result_users = mysqli_query($conn, $query_users);
$total_users = mysqli_fetch_assoc($result_users)['total_users'];

$query_quiz = "SELECT COUNT(*) as total_quiz FROM questions";
$result_quiz = mysqli_query($conn, $query_quiz);
$total_quiz = mysqli_fetch_assoc($result_quiz)['total_quiz'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin - Quiz Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/sidebar.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary-dark">Dashboard Admin</h3>
                <button class="btn d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="card welcome-card mb-4">
                <div class="card-body">
                    <h4>Selamat Datang, <?php echo $user['full_name'] ?? $user['username']; ?>!</h4>
                    <p class="mb-0">Kelola quiz online dengan mudah melalui dashboard admin.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0"><?php echo $total_users; ?></h4>
                                <small class="text-secondary">Total User</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-question-circle fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0"><?php echo $total_quiz; ?></h4>
                                <small class="text-secondary">Total Soal Quiz</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>

    <?php
    function getProfilePhotoUrl($user) {
        if ($user['profile_photo'] && file_exists('../assets/img/profile/' . $user['profile_photo'])) {
            return '/assets/img/profile/' . $user['profile_photo'];
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random';
    }
    ?>
</body>
</html> 