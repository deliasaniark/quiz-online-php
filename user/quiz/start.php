<?php
require_once '../../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Cek quiz_id
if (!isset($_GET['id'])) {
    redirect('/user/quiz.php');
}

$quiz_id = $_GET['id'];

// Ambil data quiz
$query = "SELECT * FROM quiz WHERE id = ? AND is_active = 1 AND deleted_at IS NULL";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_id);
mysqli_stmt_execute($stmt);
$quiz = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$quiz) {
    redirect('/user/quiz.php');
}

// Cek apakah sudah pernah mengerjakan
$query = "SELECT * FROM quiz_results WHERE user_id = ? AND quiz_id = ? AND is_completed = 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $quiz_id);
mysqli_stmt_execute($stmt);
if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
    redirect('/user/quiz.php');
}

// Ambil semua soal
$query = "SELECT * FROM questions WHERE quiz_id = ? AND deleted_at IS NULL ORDER BY id ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_id);
mysqli_stmt_execute($stmt);
$questions = mysqli_stmt_get_result($stmt);
$total_questions = mysqli_num_rows($questions);

// Buat atau ambil quiz_result yang belum selesai
$query = "SELECT * FROM quiz_results WHERE user_id = ? AND quiz_id = ? AND is_completed = 0";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $quiz_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result->num_rows === 0) {
    // Buat quiz_result baru
    $query = "INSERT INTO quiz_results (user_id, quiz_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $quiz_id);
    mysqli_stmt_execute($stmt);
    $quiz_result_id = mysqli_insert_id($conn);
} else {
    $quiz_result = mysqli_fetch_assoc($result);
    $quiz_result_id = $quiz_result['id'];
}

// Ambil jawaban yang sudah dijawab
$query = "SELECT question_id, user_answer FROM user_answers WHERE quiz_result_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_result_id);
mysqli_stmt_execute($stmt);
$answered = mysqli_stmt_get_result($stmt);
$answered_questions = [];
while ($row = mysqli_fetch_assoc($answered)) {
    $answered_questions[$row['question_id']] = $row['user_answer'];
}

$answered_count = count($answered_questions);
$progress = ($answered_count / $total_questions) * 100;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - QuizKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <main class="content mt-5">
        <div class="container-fluid px-4 mt-4">
            <!-- Progress Bar -->
            <div class="progress-container fixed-bottom bg-white border-top py-2">
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h6>
                        <span class="badge bg-primary"><?php echo $answered_count; ?>/<?php echo $total_questions; ?> Soal</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo $progress; ?>%"
                             aria-valuenow="<?php echo $progress; ?>" 
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <form id="quizForm">
                        <?php 
                        mysqli_data_seek($questions, 0);
                        while ($question = mysqli_fetch_assoc($questions)): 
                            $question_id = $question['id'];
                            $is_answered = isset($answered_questions[$question_id]);
                        ?>
                        <div class="card mb-4 question-card">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><?php echo htmlspecialchars($question['question']); ?></h5>
                                
                                <div class="options">
                                    <?php
                                    $options = [
                                        'A' => $question['option_a'],
                                        'B' => $question['option_b'],
                                        'C' => $question['option_c'],
                                        'D' => $question['option_d']
                                    ];
                                    
                                    foreach ($options as $key => $value):
                                        $is_selected = isset($answered_questions[$question_id]) && 
                                                    $answered_questions[$question_id] === $key;
                                    ?>
                                    <label class="quiz-option <?php echo $is_selected ? 'selected' : ''; ?>" style="display: block;">
                                        <div class="d-flex align-items-center">
                                            <div class="option-circle"><?php echo $key; ?></div>
                                            <input type="radio" 
                                                name="question_<?php echo $question_id; ?>" 
                                                value="<?php echo $key; ?>"
                                                class="d-none"
                                                <?php echo $is_selected ? 'checked' : ''; ?>>
                                            <div class="option-text"><?php echo htmlspecialchars($value); ?></div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>

                        <div class="text-center mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Submit Jawaban
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
    $(document).ready(function() {
        // Fungsi untuk update progress
        function updateProgress() {
            const total = $('.question-card').length;
            const answered = $('input[type="radio"]:checked').length;
            const progress = (answered / total) * 100;
            
            // Update progress bar
            $('.progress-bar').css('width', progress + '%');
            // Update counter soal
            $('.badge.bg-primary').text(answered + '/' + total + ' Soal');
            
            // Update warna card yang sudah dijawab
            $('.question-card').each(function() {
                const isAnswered = $(this).find('input[type="radio"]:checked').length > 0;
                $(this).toggleClass('border-success', isAnswered);
            });
        }

        // Klik pada quiz-option untuk memilih jawaban
        $('.quiz-option').click(function() {
            const radioInput = $(this).find('input[type="radio"]');
            if (!radioInput.prop('disabled')) {
                radioInput.prop('checked', true);
                $(this).addClass('selected').siblings().removeClass('selected');
                
                // Update progress setiap kali ada jawaban yang dipilih
                updateProgress();
            }
        });

        // Submit quiz
        $('#quizForm').on('submit', function(e) {
            e.preventDefault();
            
            const answers = [];
            let allAnswered = true;

            // Cek semua jawaban
            $('.question-card').each(function() {
                const questionId = $(this).find('input[type="radio"]').first().attr('name').replace('question_', '');
                const answer = $(this).find('input[type="radio"]:checked').val();
                
                if (!answer) {
                    allAnswered = false;
                    $(this).addClass('border-danger');
                } else {
                    $(this).removeClass('border-danger');
                    answers.push({
                        question_id: questionId,
                        answer: answer
                    });
                }
            });

            if (!allAnswered) {
                showNotification('Harap jawab semua pertanyaan!', 'error');
                return;
            }

            // Konfirmasi sebelum submit
            if (confirm('Yakin ingin menyelesaikan quiz?')) {
                // Disable form selama proses submit
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Memproses...');

                // Submit semua jawaban
                $.ajax({
                    url: '/user/quiz/answer.php',
                    type: 'POST',
                    data: {
                        quiz_result_id: '<?php echo $quiz_result_id; ?>',
                        answers: answers
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = `/user/quiz/finish.php?id=<?php echo $quiz_result_id; ?>`;
                        } else {
                            showNotification('Gagal menyimpan jawaban!', 'error');
                            $submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Submit Jawaban');
                        }
                    },
                    error: function() {
                        showNotification('Terjadi kesalahan! Silakan coba lagi.', 'error');
                        $submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Submit Jawaban');
                    }
                });
            }
        });

        // Panggil updateProgress saat halaman pertama dimuat
        updateProgress();
    });
    </script>

    <style>
    .content {
        margin-bottom: 100px; /* Sesuaikan dengan tinggi progress bar */
    }

    .progress-container {
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }

    /* Animasi progress bar */
    .progress-bar {
        transition: width 0.5s ease-in-out;
    }

    /* Responsive padding */
    @media (max-width: 768px) {
        .progress-container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    </style>
</body>
</html> 