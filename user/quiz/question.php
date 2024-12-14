<?php
require_once '../../config/config.php';

// Cek login
if (!is_logged_in()) {
    redirect('auth/login.php');
}

$result_id = isset($_GET['result_id']) ? (int)$_GET['result_id'] : 0;

// Ambil data quiz_result
$quiz_result = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT qr.*, q.title 
     FROM quiz_results qr 
     JOIN quiz q ON q.id = qr.quiz_id 
     WHERE qr.id = $result_id AND qr.user_id = {$_SESSION['user_id']}"));

if (!$quiz_result) {
    $_SESSION['error'] = "Data kuis tidak ditemukan!";
    redirect('user/index.php');
}

// Jika sudah selesai, redirect ke halaman hasil
if ($quiz_result['is_completed']) {
    redirect("quiz/result.php?id=" . $result_id);
}

// Ambil semua soal untuk kuis ini
$questions = mysqli_query($conn, 
    "SELECT q.*, ua.user_answer 
     FROM questions q 
     LEFT JOIN user_answers ua ON ua.question_id = q.id AND ua.quiz_result_id = $result_id
     WHERE q.quiz_id = {$quiz_result['quiz_id']} 
     AND q.deleted_at IS NULL 
     ORDER BY q.id");

// Proses jawaban jika ada
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = (int)$_POST['question_id'];
    $answer = $_POST['answer'];
    
    // Cek jawaban benar/salah
    $question = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT answer FROM questions WHERE id = $question_id"));
    $is_correct = ($answer === $question['answer']) ? 1 : 0;
    
    // Simpan atau update jawaban
    $sql = "INSERT INTO user_answers (quiz_result_id, question_id, user_answer, is_correct) 
            VALUES ($result_id, $question_id, '$answer', $is_correct)
            ON DUPLICATE KEY UPDATE user_answer = '$answer', is_correct = $is_correct";
    mysqli_query($conn, $sql);
    
    // Redirect untuk menghindari resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?result_id=" . $result_id);
    exit;
}

// Untuk submit final
if (isset($_POST['finish'])) {
    // Hitung total skor
    $total_questions = mysqli_num_rows($questions);
    $correct_answers = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as total FROM user_answers 
         WHERE quiz_result_id = $result_id AND is_correct = 1"))['total'];
    
    $score = ($correct_answers / $total_questions) * 100;
    
    // Update quiz_result
    mysqli_query($conn, 
        "UPDATE quiz_results 
         SET score = $score, is_completed = 1 
         WHERE id = $result_id");
    
    // Update total_score user
    mysqli_query($conn, 
        "UPDATE users 
         SET total_score = total_score + $score 
         WHERE id = {$_SESSION['user_id']}");
    
    redirect("quiz/result.php?id=" . $result_id);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengerjaan Kuis - <?php echo htmlspecialchars($quiz_result['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($quiz_result['title']); ?></h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#finishModal">
                            Selesai
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Navigasi Soal -->
                        <div class="mb-4">
                            <?php 
                            mysqli_data_seek($questions, 0);
                            $question_num = 1;
                            while ($q = mysqli_fetch_assoc($questions)): 
                            ?>
                                <button class="btn btn-sm me-1 mb-1 <?php 
                                    echo isset($q['user_answer']) ? 'btn-success' : 'btn-outline-secondary'; 
                                ?>" onclick="showQuestion(<?php echo $question_num; ?>)">
                                    <?php echo $question_num++; ?>
                                </button>
                            <?php endwhile; ?>
                        </div>

                        <!-- Daftar Soal -->
                        <?php 
                        mysqli_data_seek($questions, 0);
                        $question_num = 1;
                        while ($question = mysqli_fetch_assoc($questions)): 
                        ?>
                            <div class="question-item" id="question-<?php echo $question_num; ?>" style="display: none;">
                                <h5>Soal <?php echo $question_num; ?></h5>
                                <p><?php echo htmlspecialchars($question['question']); ?></p>
                                
                                <form method="POST">
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                    
                                    <div class="mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answer" value="A" 
                                                <?php echo ($question['user_answer'] === 'A') ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                A. <?php echo htmlspecialchars($question['option_a']); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answer" value="B"
                                                <?php echo ($question['user_answer'] === 'B') ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                B. <?php echo htmlspecialchars($question['option_b']); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answer" value="C"
                                                <?php echo ($question['user_answer'] === 'C') ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                C. <?php echo htmlspecialchars($question['option_c']); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answer" value="D"
                                                <?php echo ($question['user_answer'] === 'D') ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                D. <?php echo htmlspecialchars($question['option_d']); ?>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Simpan Jawaban</button>
                                </form>
                            </div>
                        <?php 
                        $question_num++;
                        endwhile; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Selesai -->
    <div class="modal fade" id="finishModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyelesaikan kuis ini?</p>
                    <p>Jawaban yang belum disimpan tidak akan dihitung.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="finish" class="btn btn-primary">Ya, Selesaikan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tampilkan soal pertama saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            showQuestion(1);
        });

        function showQuestion(num) {
            // Sembunyikan semua soal
            document.querySelectorAll('.question-item').forEach(function(item) {
                item.style.display = 'none';
            });
            
            // Tampilkan soal yang dipilih
            document.getElementById('question-' + num).style.display = 'block';
        }
    </script>
</body>
</html> 