<?php
require_once '../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Ambil riwayat quiz
$query = "SELECT qr.*, q.title, q.description,
          (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as total_questions,
          (SELECT COUNT(*) FROM user_answers ua WHERE ua.quiz_result_id = qr.id AND ua.is_correct = 1) as correct_answers
          FROM quiz_results qr
          JOIN quiz q ON qr.quiz_id = q.id
          WHERE qr.user_id = $user_id AND qr.is_completed = 1
          ORDER BY qr.created_at DESC";
$quiz_results = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Quiz - QuizKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <main class="content mt-5">
        <div class="container-fluid px-4 mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-light">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Quiz</h5>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($quiz_results) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Quiz</th>
                                                <th>Tanggal</th>
                                                <th>Skor</th>
                                                <th>Jawaban Benar</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($result = mysqli_fetch_assoc($quiz_results)): ?>
                                                <tr>
                                                    <td>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($result['title']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($result['description']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo date('d M Y H:i', strtotime($result['created_at'])); ?>
                                                    </td>
                                                    <td>
                                                        <h5 class="mb-0 badge bg-primary">
                                                            <?php echo $result['score']; ?>
                                                        </h5>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['correct_answers']; ?> / <?php echo $result['total_questions']; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($result['score'] >= 70): ?>
                                                            <span class="badge bg-success">Lulus</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Tidak Lulus</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="quiz/review.php?id=<?php echo $result['quiz_id']; ?>&result_id=<?php echo $result['id']; ?>" 
                                                           class="btn btn-sm btn-secondary">
                                                            <i class="bi bi-eye me-1"></i>Lihat Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <img src="/assets/images/empty-state.svg" alt="No Data" class="mb-3" style="width: 200px;">
                                    <h5 class="text-muted">Belum ada riwayat quiz</h5>
                                    <p class="text-muted mb-3">Anda belum mengerjakan quiz apapun</p>
                                    <a href="quiz.php" class="btn btn-primary">
                                        <i class="bi bi-play-fill me-1"></i>Mulai Quiz
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <?php if (mysqli_num_rows($quiz_results) > 0): ?>
            <div class="row mt-4">
                <?php
                // Hitung statistik
                $total_quiz = mysqli_num_rows($quiz_results);
                $avg_score = mysqli_fetch_assoc(mysqli_query($conn, 
                    "SELECT AVG(score) as avg FROM quiz_results WHERE user_id = $user_id AND is_completed = 1"))['avg'];
                $passed_quiz = mysqli_fetch_assoc(mysqli_query($conn, 
                    "SELECT COUNT(*) as total FROM quiz_results WHERE user_id = $user_id AND score >= 70 AND is_completed = 1"))['total'];
                ?>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h3 class="mb-1"><?php echo $total_quiz; ?></h3>
                        <p class="text-muted mb-0">Total Quiz</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3 class="mb-1"><?php echo number_format($avg_score, 1); ?></h3>
                        <p class="text-muted mb-0">Rata-rata Skor</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <h3 class="mb-1"><?php echo $passed_quiz; ?></h3>
                        <p class="text-muted mb-0">Quiz Lulus</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
</body>
</html> 