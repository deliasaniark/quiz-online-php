<?php
require_once '../../config/config.php';

// Cek login
if (!is_logged_in()) {
    redirect('auth/login.php');
}

$result_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data hasil kuis
$result = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT qr.*, q.title 
     FROM quiz_results qr 
     JOIN quiz q ON q.id = qr.quiz_id 
     WHERE qr.id = $result_id AND qr.user_id = {$_SESSION['user_id']}"));

if (!$result) {
    $_SESSION['error'] = "Data hasil kuis tidak ditemukan!";
    redirect('user/index.php');
}

// Ambil detail jawaban
$answers = mysqli_query($conn, 
    "SELECT ua.*, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.answer as correct_answer, q.explanation
     FROM user_answers ua 
     JOIN questions q ON q.id = ua.question_id 
     WHERE ua.quiz_result_id = $result_id 
     ORDER BY q.id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Kuis - <?php echo htmlspecialchars($result['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Hasil Kuis: <?php echo htmlspecialchars($result['title']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h1 class="display-4"><?php echo number_format($result['score'], 1); ?></h1>
                            <p class="lead">Nilai Anda</p>
                        </div>

                        <h5>Detail Jawaban:</h5>
                        <?php 
                        $no = 1;
                        while ($answer = mysqli_fetch_assoc($answers)): 
                        ?>
                            <div class="card mb-3 <?php echo $answer['is_correct'] ? 'border-success' : 'border-danger'; ?>">
                                <div class="card-body">
                                    <h6>Soal <?php echo $no++; ?></h6>
                                    <p><?php echo htmlspecialchars($answer['question']); ?></p>
                                    
                                    <div class="mb-2">
                                        <strong>Jawaban Anda:</strong>
                                        <?php 
                                        $option_key = 'option_' . strtolower($answer['user_answer']);
                                        echo $answer['user_answer'] . '. ' . htmlspecialchars($answer[$option_key]);
                                        ?>
                                    </div>
                                    
                                    <?php if (!$answer['is_correct']): ?>
                                        <div class="mb-2">
                                            <strong>Jawaban Benar:</strong>
                                            <?php 
                                            $option_key = 'option_' . strtolower($answer['correct_answer']);
                                            echo $answer['correct_answer'] . '. ' . htmlspecialchars($answer[$option_key]);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($answer['explanation']): ?>
                                        <div class="mt-2">
                                            <strong>Penjelasan:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($answer['explanation'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <div class="text-center mt-4">
                            <a href="../index.php" class="btn btn-secondary me-2">Kembali ke Dashboard</a>
                            <a href="start.php?id=<?php echo $result['quiz_id']; ?>" class="btn btn-primary">Kerjakan Lagi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 