<?php
require_once '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    redirect('/user/quiz.php');
}

$user_id = $_SESSION['user_id'];
$quiz_id = $_GET['id'];

// Ambil data quiz dan hasil
$query = "SELECT q.*, qr.id as result_id, qr.score, qr.created_at as attempt_date
          FROM quiz q 
          JOIN quiz_results qr ON q.id = qr.quiz_id
          WHERE q.id = ? AND qr.user_id = ? AND qr.is_completed = 1
          ORDER BY qr.created_at DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $quiz_id, $user_id);
mysqli_stmt_execute($stmt);
$quiz = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$quiz) {
    redirect('/user/quiz.php');
}

// Ambil soal dan jawaban user
$query = "SELECT q.*, ua.user_answer, ua.is_correct
          FROM questions q
          LEFT JOIN user_answers ua ON q.id = ua.question_id AND ua.quiz_result_id = ?
          WHERE q.quiz_id = ? AND q.deleted_at IS NULL
          ORDER BY q.id ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $quiz['result_id'], $quiz_id);
mysqli_stmt_execute($stmt);
$questions = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembahasan <?php echo htmlspecialchars($quiz['title']); ?> - QuizKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <main class="content mt-5">
        <div class="container-fluid px-4 mt-4">
            <!-- Quiz Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                <span class="badge bg-primary">Skor: <?php echo $quiz['score']; ?></span>
                            </div>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($quiz['description']); ?></p>
                            <small class="text-muted">
                                Dikerjakan pada: <?php echo date('d M Y H:i', strtotime($quiz['attempt_date'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tambahkan setelah Quiz Header dan sebelum Questions Review -->
            <div class="row mb-4">
                <div class="col-md-8 mx-auto">
                    <div class="answer-legend">
                        <div class="legend-item">
                            <div class="legend-icon">
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <span>Jawaban Anda Benar</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon">
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            </div>
                            <span>Jawaban Anda Salah</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon">
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            <span>Jawaban yang Benar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Review -->
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <?php 
                    $number = 1;
                    while ($question = mysqli_fetch_assoc($questions)): 
                    ?>
                    <div class="card mb-4">
                        <div class="card-header <?php echo $question['is_correct'] ? 'bg-success' : 'bg-danger'; ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Soal <?php echo $number++; ?></h6>
                                <?php if ($question['is_correct']): ?>
                                    <i class="bi bi-check-circle-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title mb-4"><?php echo htmlspecialchars($question['question']); ?></h6>
                            
                            <!-- Pilihan Jawaban -->
                            <div class="options mb-4">
                                <?php
                                $options = [
                                    'A' => $question['option_a'],
                                    'B' => $question['option_b'],
                                    'C' => $question['option_c'],
                                    'D' => $question['option_d']
                                ];
                                
                                foreach ($options as $key => $value):
                                    $isUserAnswer = $question['user_answer'] === $key;
                                    $isCorrectAnswer = $question['answer'] === $key;
                                ?>
                                <div class="option-item mb-2 <?php 
                                    echo $isUserAnswer ? ($isCorrectAnswer ? 'correct' : 'incorrect') : 
                                         ($isCorrectAnswer ? 'correct' : ''); 
                                ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="option-label me-2">
                                            <?php echo $key; ?>
                                        </div>
                                        <div class="option-text flex-grow-1">
                                            <?php echo htmlspecialchars($value); ?>
                                        </div>
                                        <?php if ($isUserAnswer): ?>
                                            <i class="bi bi-check-circle-fill ms-2"></i>
                                        <?php endif; ?>
                                        <?php if ($isCorrectAnswer && !$isUserAnswer): ?>
                                            <i class="bi bi-star-fill ms-2 text-warning"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Pembahasan -->
                            <?php if ($question['explanation']): ?>
                            <div class="explanation mt-3">
                                <h6 class="text-primary">Pembahasan:</h6>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($question['explanation'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <div class="text-center mb-4">
                        <a href="/user/quiz.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Quiz
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
</body>
</html> 