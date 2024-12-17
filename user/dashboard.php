<?php
require_once '../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}

// Ambil data user dengan total score
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*,
          COALESCE((SELECT SUM(score) FROM quiz_results 
                    WHERE user_id = u.id AND is_completed = 1), 0) as total_score
          FROM users u 
          WHERE u.id = $user_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $query));

// Ambil statistik user
$total_quiz = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(DISTINCT quiz_id) as total FROM quiz_results WHERE user_id = $user_id"))['total'];

$completed_quiz = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM quiz_results WHERE user_id = $user_id AND is_completed = 1"))['total'];

// Ambil ranking user berdasarkan total score
$rank_query = "SELECT user_rank FROM (
                SELECT u.id,
                       @rank := @rank + 1 as user_rank,
                       COALESCE((SELECT SUM(score) FROM quiz_results 
                                WHERE user_id = u.id AND is_completed = 1), 0) as user_score
                FROM users u,
                     (SELECT @rank := 0) r
                WHERE u.role = 'user'
                ORDER BY user_score DESC
              ) ranked
              WHERE id = $user_id";

// Initialize rank variable
$rank = 1; // default value jika query gagal

// Execute query
$rank_result = mysqli_query($conn, $rank_query);
if ($rank_result && $row = mysqli_fetch_assoc($rank_result)) {
    $rank = $row['user_rank'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <main class="content mt-5">
    <div class="container-fluid px-4 mt-4">
        <!-- Welcome Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-light">
                    <div class="card-body d-flex align-items-center py-4">
                        <div class="display-6 bi bi-stars me-3"></div>
                        <div>
                            <h2 class="mb-1">Selamat datang, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>!</h2>
                            <p class="mb-0">Siap untuk tantangan quiz hari ini?</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <h3 class="mb-1">#<?php echo $rank; ?></h3>
                    <p class="text-muted mb-0">Peringkat Global</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h3 class="mb-1"><?php echo number_format($user['total_score']); ?></h3>
                    <p class="text-muted mb-0">Total Skor</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $completed_quiz; ?></h3>
                    <p class="text-muted mb-0">Quiz Selesai</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="mb-1"><?php 
                        echo $completed_quiz > 0 ? 
                            number_format(($user['total_score'] / $completed_quiz), 1) : 0; 
                    ?></h3>
                    <p class="text-muted mb-0">Rata-rata Skor</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Available Quiz -->
        <div class="row">
            <!-- Recent Activity -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-light">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktivitas Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_activities = mysqli_query($conn, 
                            "SELECT qr.*, q.title 
                             FROM quiz_results qr 
                             JOIN quiz q ON qr.quiz_id = q.id 
                             WHERE qr.user_id = $user_id 
                             ORDER BY qr.created_at DESC LIMIT 5");
                        
                        if (mysqli_num_rows($recent_activities) > 0):
                            while ($activity = mysqli_fetch_assoc($recent_activities)):
                        ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="activity-icon me-3">
                                <?php if ($activity['is_completed']): ?>
                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                <?php else: ?>
                                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                <small class="text-muted">
                                    <?php 
                                    echo $activity['is_completed'] ? 
                                        "Selesai dengan skor " . $activity['score'] : 
                                        "Sedang dikerjakan"; 
                                    ?>
                                </small>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <p class="text-muted text-center py-3">Belum ada aktivitas</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Achievement Section -->
                <div class="card">
                    <div class="card-header bg-primary text-light">
                        <h5 class="mb-0"><i class="bi bi-award me-2"></i>Pencapaian</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Ambil statistik tambahan
                        $perfect_scores = mysqli_fetch_assoc(mysqli_query($conn, 
                            "SELECT COUNT(*) as total FROM quiz_results 
                             WHERE user_id = $user_id AND score = 100 AND is_completed = 1"))['total'];
                        
                        $achievements = [
                            [
                                'icon' => 'bi-mortarboard-fill text-primary',
                                'title' => 'Quiz Warrior',
                                'description' => "Selesaikan quiz ($completed_quiz quiz)",
                                'progress' => $completed_quiz
                            ],
                            [
                                'icon' => 'bi-trophy-fill text-warning',
                                'title' => 'Point Collector',
                                'description' => "Total skor: " . number_format($user['total_score']) . " poin",
                                'progress' => $user['total_score']
                            ],
                            [
                                'icon' => 'bi-stars text-success',
                                'title' => 'Perfect Score',
                                'description' => "Nilai sempurna: $perfect_scores kali",
                                'progress' => $perfect_scores
                            ]
                        ];
                        
                        foreach ($achievements as $achievement):
                        ?>
                        <div class="achievement-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="achievement-icon me-3">
                                    <i class="bi <?php echo $achievement['icon']; ?> fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $achievement['title']; ?></h6>
                                    <small class="text-muted"><?php echo $achievement['description']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Available Quiz -->
            <div class="col-md-8">
                <h4 class="mb-4"><i class="bi bi-journal-text me-2"></i>Quiz Tersedia</h4>
                <?php
                $available_quiz = mysqli_query($conn, 
                    "SELECT q.*, 
                            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as total_questions,
                            (SELECT score FROM quiz_results WHERE quiz_id = q.id AND user_id = $user_id ORDER BY created_at DESC LIMIT 1) as last_score,
                            (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id AND is_completed = 1) as total_attempts
                     FROM quiz q 
                     WHERE q.is_active = 1 
                     AND q.deleted_at IS NULL
                     ORDER BY q.created_at DESC");

                while ($quiz = mysqli_fetch_assoc($available_quiz)):
                ?>
                <div class="quiz-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                            <span class="badge bg-primary"><?php echo $quiz['total_questions']; ?> Soal</span>
                        </div>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($quiz['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-people-fill me-1"></i>
                                    <?php echo $quiz['total_attempts']; ?> peserta telah mencoba
                                </small>
                                <?php if (isset($quiz['last_score'])): ?>
                                    <br>
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Skor: <?php echo $quiz['last_score']; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($quiz['last_score'])): ?>
                                <a href="quiz/review.php?id=<?php echo $quiz['id']; ?>" 
                                   class="btn btn-secondary">
                                    <i class="bi bi-journal-text me-1"></i>Lihat Pembahasan
                                </a>
                            <?php else: ?>
                                <a href="quiz/start.php?id=<?php echo $quiz['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-play-fill me-1"></i>Mulai Quiz
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    </main>

    <?php include 'components/footer.php'; ?>
    <script>
    // Tambahkan class scrolled saat scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-custom');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    </script>
</body>
</html> 