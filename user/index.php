<?php
require_once '../config/config.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect('auth/login.php');
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Ambil statistik user
$total_quiz_taken = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(DISTINCT quiz_id) as total FROM quiz_results WHERE user_id = $user_id"))['total'];

$completed_quiz = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM quiz_results WHERE user_id = $user_id AND is_completed = 1"))['total'];

// Ambil daftar kuis yang tersedia
$available_quiz = mysqli_query($conn, 
    "SELECT q.*, 
            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as total_questions,
            (SELECT score FROM quiz_results WHERE quiz_id = q.id AND user_id = $user_id ORDER BY created_at DESC LIMIT 1) as last_score
     FROM quiz q 
     WHERE q.is_active = 1 
     AND q.deleted_at IS NULL
     ORDER BY q.created_at DESC");

// Ambil ranking user
$ranking_query = "SELECT u.id, u.username, u.full_name, u.total_score,
                        RANK() OVER (ORDER BY u.total_score DESC) as rank
                 FROM users u 
                 WHERE u.role = 'user'
                 ORDER BY u.total_score DESC
                 LIMIT 10";
$rankings = mysqli_query($conn, $ranking_query);

// Ambil rank user yang login
$my_rank = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) + 1 as rank 
     FROM users 
     WHERE role = 'user' AND total_score > {$user['total_score']}"))['rank'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Quiz Online</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">Riwayat Kuis</a>
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
        <div class="row">
            <!-- Profil dan Statistik -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="<?php echo base_url($user['profile_photo']); ?>" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <div class="d-flex justify-content-around">
                            <div>
                                <h6>Total Skor</h6>
                                <h4><?php echo $user['total_score']; ?></h4>
                            </div>
                            <div>
                                <h6>Peringkat</h6>
                                <h4>#<?php echo $my_rank; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ranking -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top 10 Ranking</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php while ($rank = mysqli_fetch_assoc($rankings)): ?>
                                <div class="list-group-item <?php echo ($rank['id'] == $user_id) ? 'bg-light' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-primary me-2">#<?php echo $rank['rank']; ?></span>
                                            <?php echo htmlspecialchars($rank['full_name']); ?>
                                        </div>
                                        <span class="badge bg-secondary"><?php echo $rank['total_score']; ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Kuis -->
            <div class="col-md-8">
                <h4>Kuis Tersedia</h4>
                <div class="row">
                    <?php while ($quiz = mysqli_fetch_assoc($available_quiz)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?php echo $quiz['total_questions']; ?> Soal</small>
                                        <?php if (isset($quiz['last_score'])): ?>
                                            <small class="text-muted">Skor Terakhir: <?php echo $quiz['last_score']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="quiz/start.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary w-100">
                                        <?php echo isset($quiz['last_score']) ? 'Kerjakan Lagi' : 'Mulai Kuis'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 