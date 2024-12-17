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

// Ambil semua hasil quiz dengan detail lengkap
$query = "SELECT 
    qr.*, 
    u.username, 
    u.full_name,
          q.title as quiz_title,
          q.id as quiz_id,
          (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id AND deleted_at IS NULL) as total_questions,
    (SELECT COUNT(*) FROM user_answers ua WHERE ua.quiz_result_id = qr.id AND ua.is_correct = 1) as correct_answers,
    qr.updated_at as completion_time
          FROM quiz_results qr
          JOIN users u ON qr.user_id = u.id
          JOIN quiz q ON qr.quiz_id = q.id
          WHERE qr.is_completed = 1
          ORDER BY q.title ASC, qr.updated_at DESC";
$results = mysqli_query($conn, $query);

// Ambil semua data user (kecuali admin)
$query_users = "SELECT id, username, full_name FROM users WHERE role != 'admin'";
$all_users = mysqli_query($conn, $query_users);
$users_list = [];
while ($user_row = mysqli_fetch_assoc($all_users)) {
    $users_list[$user_row['id']] = $user_row;
}

// Kelompokkan hasil berdasarkan quiz
$grouped_results = [];
while ($row = mysqli_fetch_assoc($results)) {
    $quiz_id = $row['quiz_id'];
    if (!isset($grouped_results[$quiz_id])) {
        $grouped_results[$quiz_id] = [
            'title' => $row['quiz_title'],
            'results' => [],
            'completed_users' => []
        ];
    }
    $grouped_results[$quiz_id]['results'][] = $row;
    $grouped_results[$quiz_id]['completed_users'][] = $row['user_id'];
}

// Fungsi untuk mendapatkan URL foto profil (diperlukan untuk sidebar)
function getProfilePhotoUrl($user)
{
    if ($user['profile_photo'] && file_exists('../assets/img/profile/' . $user['profile_photo'])) {
        return '/assets/img/profile/' . $user['profile_photo'];
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random';
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Hasil Quiz - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/sidebar.css" rel="stylesheet">
    <link href="/assets/css/datatables-custom.css" rel="stylesheet">
    <style>
        .quiz-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .quiz-icon {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .quiz-card.active .quiz-icon {
            transform: rotate(180deg);
        }

        .quiz-results {
            margin-bottom: 2rem;
        }

        .quiz-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .quiz-card small.text-muted {
            font-size: 0.8rem;
        }
    </style>
</head>

<body class="bg-light">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary-dark">Hasil Quiz</h3>
                <button class="btn d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($grouped_results as $quiz_id => $quiz): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm quiz-card"
                                    role="button" data-quiz-id="quiz-<?php echo $quiz_id; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="mb-0"><?php echo count($quiz['results']); ?> / <?php echo count($users_list); ?></h4>
                                                <small class="text-muted">peserta</small>
                                            </div>
                                            <i class="bi bi-chevron-down quiz-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ($grouped_results as $quiz_id => $quiz): ?>
                        <div class="quiz-results" id="quiz-<?php echo $quiz_id; ?>" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                <button class="btn btn-sm btn-secondary hide-results">
                                    <i class="bi bi-x-lg"></i> Tutup
                                </button>
                            </div>
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#completed-<?php echo $quiz_id; ?>">
                                        Sudah Mengerjakan
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#incomplete-<?php echo $quiz_id; ?>">
                                        Belum Mengerjakan
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="completed-<?php echo $quiz_id; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover results-table">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Peserta</th>
                                                    <th>Skor</th>
                                                    <th>Jawaban Benar</th>
                                                    <th>Waktu Selesai</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($quiz['results'])) {
                                                    $no = 1;
                                                    foreach ($quiz['results'] as $row) {
                                                        // Hitung skor (benar semua = 100)
                                                        $score = ($row['total_questions'] > 0)
                                                            ? round(($row['correct_answers'] / $row['total_questions']) * 100)
                                                            : 0;
                                                ?>
                                                        <tr>
                                                            <td><?php echo $no++; ?></td>
                                                            <td><?php echo htmlspecialchars($row['full_name'] ?? $row['username']); ?></td>
                                                            <td>
                                                                <span class="fw-bold <?php echo ($score >= 70) ? 'text-success' : 'text-danger'; ?>">
                                                                    <?php echo $score; ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo $row['correct_answers']; ?> / <?php echo $row['total_questions']; ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($row['completion_time'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-primary btn-sm view-details" 
                                                                    type="button"
                                                                    data-id="<?php echo $row['id']; ?>"
                                                                    data-user="<?php echo htmlspecialchars($row['full_name'] ?? $row['username']); ?>"
                                                                    data-quiz="<?php echo htmlspecialchars($row['quiz_title']); ?>"
                                                                    data-score="<?php echo $score; ?>"
                                                                    data-correct="<?php echo $row['correct_answers']; ?>"
                                                                    data-total="<?php echo $row['total_questions']; ?>"
                                                                    data-time="<?php echo date('d/m/Y H:i', strtotime($row['completion_time'])); ?>">
                                                                    <i class="bi bi-eye"></i> Detail
                                                                </button>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo '<tr><td colspan="6" class="text-center">Belum ada hasil quiz</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="incomplete-<?php echo $quiz_id; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover incomplete-table">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Username</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                foreach ($users_list as $user_id => $user_data) {
                                                    if (!in_array($user_id, $quiz['completed_users'])) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo $no++; ?></td>
                                                            <td><?php echo htmlspecialchars($user_data['full_name'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($user_data['username']); ?></td>
                                                        </tr>
                                                <?php
                                                    }
                                                }
                                                if ($no === 1) {
                                                    echo '<tr><td colspan="3" class="text-center">Semua siswa sudah mengerjakan quiz ini</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Hasil Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold d-block mb-2">Nama Peserta:</label>
                        <div id="modalUser"></div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold d-block mb-2">Quiz:</label>
                        <div id="modalQuiz"></div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold d-block mb-2">Skor:</label>
                        <div id="modalScore"></div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold d-block mb-2">Jawaban Benar:</label>
                        <div id="modalCorrect"></div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold d-block mb-2">Waktu Selesai:</label>
                        <div id="modalTime"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/js/datatables-config.js"></script>

    <script>
        $(document).ready(function() {
            // Handler untuk quiz card
            $('.quiz-card').click(function() {
                const quizId = $(this).data('quiz-id');
                if ($(this).hasClass('active')) {
                    // Jika card sudah active, tutup
                    $(this).removeClass('active');
                    $(`#${quizId}`).slideUp();
                } else {
                    // Jika card belum active, tutup yang lain dan buka ini
                    $('.quiz-results').slideUp();
                    $('.quiz-card').removeClass('active');
                    $(this).addClass('active');
                    $(`#${quizId}`).slideDown();
                }
            });

            // Handler untuk tombol tutup
            $('.hide-results').click(function(e) {
                e.stopPropagation();
                $(this).closest('.quiz-results').slideUp();
                $('.quiz-card').removeClass('active');
            });

            // Inisialisasi DataTables
            $('.results-table').each(function() {
                $(this).DataTable({
                    order: [[0, 'asc']], // Urutkan berdasarkan nomor
                    columnDefs: [{
                        orderable: false,
                        targets: 5 // Kolom aksi tidak bisa diurutkan
                    }],
                    drawCallback: function() {
                        $('.view-details').off('click').on('click', function() {
                            const user = $(this).data('user');
                            const quiz = $(this).data('quiz');
                            const score = $(this).data('score');
                            const correct = $(this).data('correct');
                            const total = $(this).data('total');
                            const time = $(this).data('time');
                            
                            $('#modalUser').text(user || '-');
                            $('#modalQuiz').text(quiz || '-');
                            $('#modalScore').html(`<span class="${score >= 70 ? 'text-success' : 'text-danger'}">${score}</span>`);
                            $('#modalCorrect').text(`${correct} dari ${total} soal`);
                            $('#modalTime').text(time || '-');
                            
                            $('#detailModal').modal('show');
                        });
                    }
                });
            });

            // Inisialisasi DataTables untuk tabel siswa yang belum mengerjakan
            $('.incomplete-table').each(function() {
                $(this).DataTable({
                    order: [
                        [1, 'asc']
                    ], // Urutkan berdasarkan nama
                    pageLength: 5, // Tampilkan 5 data per halaman
                    language: {
                        url: '/assets/js/datatables-id.json'
                    }
                });
            });

            // Toggle sidebar di mobile
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        });
    </script>
</body>

</html>