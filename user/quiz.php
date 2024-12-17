<?php
require_once '../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Ambil semua quiz yang aktif beserta status pengerjaannya untuk user ini
$query = "SELECT 
            q.*,
            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id AND deleted_at IS NULL) as total_questions,
            qr.score as last_score,
            qr.is_completed,
            (SELECT COUNT(DISTINCT user_id) FROM quiz_results WHERE quiz_id = q.id AND is_completed = 1) as total_attempts
          FROM quiz q
          LEFT JOIN (
              SELECT * FROM quiz_results 
              WHERE user_id = $user_id AND is_completed = 1
          ) qr ON q.id = qr.quiz_id
          WHERE q.deleted_at IS NULL
          ORDER BY q.created_at DESC";

$quiz_list = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - QuizKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <main class="content mt-5">
        <div class="container-fluid px-4 mt-4">
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="all">
                                    Semua Quiz
                                </button>
                                <button type="button" class="btn btn-outline-success filter-btn" data-filter="completed">
                                    Sudah Dikerjakan
                                </button>
                                <button type="button" class="btn btn-outline-warning filter-btn" data-filter="uncompleted">
                                    Belum Dikerjakan
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchQuiz" 
                                       placeholder="Cari quiz...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz List -->
            <div class="row" id="quizContainer">
                <?php while ($quiz = mysqli_fetch_assoc($quiz_list)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 position-relative quiz-card">
                            <?php if (!$quiz['is_active']): ?>
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                                     style="background: rgba(0,0,0,0.05); backdrop-filter: blur(2px); z-index: 1; border-radius: 0.375rem;">
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title text-primary-dark mb-0 me-2">
                                        <?php echo htmlspecialchars($quiz['title']); ?>
                                    </h5>
                                    <span class="badge bg-primary flex-shrink-0">
                                        <?php echo $quiz['total_questions']; ?> Soal
                                    </span>
                                </div>
                                
                                <!-- Description -->
                                <p class="text-muted flex-grow-1 mb-3">
                                    <?php echo htmlspecialchars($quiz['description']); ?>
                                </p>
                                
                                <!-- Footer -->
                                <div class="mt-auto">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                        <!-- Info -->
                                        <div class="small">
                                            <div class="text-muted mb-1">
                                                <i class="bi bi-people-fill me-1"></i>
                                                <?php echo $quiz['total_attempts']; ?> peserta
                                            </div>
                                            <?php if ($quiz['is_completed']): ?>
                                                <div class="<?php echo $quiz['last_score'] >= 70 ? 'text-success' : 'text-danger'; ?>">
                                                    <i class="bi <?php echo $quiz['last_score'] >= 70 ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> me-1"></i>
                                                    Skor: <?php echo $quiz['last_score']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Action Button -->
                                        <?php if ($quiz['is_active']): ?>
                                            <?php if ($quiz['is_completed']): ?>
                                                <a href="quiz/review.php?id=<?php echo $quiz['id']; ?>" 
                                                   class="btn btn-secondary btn-sm">
                                                    <i class="bi bi-journal-text me-1"></i>Lihat Pembahasan
                                                </a>
                                            <?php else: ?>
                                                <a href="quiz/start.php?id=<?php echo $quiz['id']; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="bi bi-play-fill me-1"></i>Mulai Quiz
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="bi bi-lock-fill me-1"></i>Tidak Tersedia
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <?php if ($quiz['is_completed']): ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <?php if ($quiz['last_score'] >= 70): ?>
                                        <span class="badge bg-success" title="Quiz Lulus">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger" title="Quiz Belum Lulus">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($quiz_list) == 0): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <img src="/assets/images/empty-state.svg" alt="No Quiz" class="mb-3" style="width: 200px;">
                            <h5 class="text-muted">Belum ada quiz tersedia</h5>
                            <p class="text-muted">Silakan cek kembali nanti</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
    $(document).ready(function() {
        // Filter quiz
        $('.filter-btn').click(function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            const filter = $(this).data('filter');
            const searchText = $('#searchQuiz').val().toLowerCase();
            
            filterQuiz(filter, searchText);
        });

        // Search quiz
        $('#searchQuiz').on('keyup', function() {
            const filter = $('.filter-btn.active').data('filter');
            const searchText = $(this).val().toLowerCase();
            
            filterQuiz(filter, searchText);
        });

        function filterQuiz(filter, searchText) {
            $('.col-md-6').each(function() {
                const $card = $(this);
                const title = $card.find('.card-title').text().toLowerCase();
                const description = $card.find('.text-muted').first().text().toLowerCase();
                const isCompleted = $card.find('.badge[title="Quiz Lulus"], .badge[title="Quiz Belum Lulus"]').length > 0;
                
                let showCard = true;

                // Filter berdasarkan status
                if (filter === 'completed' && !isCompleted) {
                    showCard = false;
                } else if (filter === 'uncompleted' && isCompleted) {
                    showCard = false;
                }

                // Filter berdasarkan pencarian
                if (searchText && !title.includes(searchText) && !description.includes(searchText)) {
                    showCard = false;
                }

                $card.toggle(showCard);
            });

            // Tampilkan pesan jika tidak ada hasil
            const visibleCards = $('.col-md-6:visible').length;
            if (visibleCards === 0) {
                if (!$('#noResults').length) {
                    $('#quizContainer').append(`
                        <div class="col-12" id="noResults">
                            <div class="text-center py-5">
                                <img src="/assets/images/empty-state.svg" alt="No Results" class="mb-3" style="width: 150px;">
                                <h5 class="text-muted">Tidak ada quiz yang sesuai</h5>
                                <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                            </div>
                        </div>
                    `);
                }
            } else {
                $('#noResults').remove();
            }
        }
    });
    </script>
</body>
</html> 