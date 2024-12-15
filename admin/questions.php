<?php
require_once '../config/config.php';

// Cek apakah sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('/auth/login.php');
}

// Cek quiz_id
if (!isset($_GET['quiz_id'])) {
    redirect('/admin/quiz.php');
}

$quiz_id = $_GET['quiz_id'];

// Ambil data quiz
$query = "SELECT * FROM quiz WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_id);
mysqli_stmt_execute($stmt);
$quiz = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$quiz) {
    redirect('/admin/quiz.php');
}

// Ambil data admin
$id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Ambil semua soal untuk quiz ini
$query = "SELECT * FROM questions WHERE quiz_id = ? AND deleted_at IS NULL ORDER BY id ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_id);
mysqli_stmt_execute($stmt);
$questions = mysqli_stmt_get_result($stmt);

// Fungsi untuk mendapatkan URL foto profil
function getProfilePhotoUrl($user) {
    if ($user['profile_photo'] && file_exists('../assets/img/profile/' . $user['profile_photo'])) {
        return '/assets/img/profile/' . $user['profile_photo'];
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Soal Quiz - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/sidebar.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <a href="quiz.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <h3 class="text-primary-dark mb-0">Kelola Soal Quiz</h3>
                    <p class="text-secondary mb-0"><?php echo htmlspecialchars($quiz['title']); ?></p>
                </div>
                <div>
                    <button class="btn d-md-none me-2" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="bi bi-plus-lg"></i> Tambah Soal
                    </button>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="questionsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Pertanyaan</th>
                                    <th>Jawaban Benar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($questions) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($questions)) { 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['question']); ?></td>
                                    <td><?php echo $row['answer']; ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-btn"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-question="<?php echo htmlspecialchars($row['question']); ?>"
                                                data-option-a="<?php echo htmlspecialchars($row['option_a']); ?>"
                                                data-option-b="<?php echo htmlspecialchars($row['option_b']); ?>"
                                                data-option-c="<?php echo htmlspecialchars($row['option_c']); ?>"
                                                data-option-d="<?php echo htmlspecialchars($row['option_d']); ?>"
                                                data-answer="<?php echo $row['answer']; ?>"
                                                data-explanation="<?php echo htmlspecialchars($row['explanation']); ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <a href="question_process.php?delete=<?php echo $row['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                                           class="btn btn-danger btn-sm delete-btn">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="text-center">Belum ada soal</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Soal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Soal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="question_process.php" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <textarea name="question" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Opsi A <span class="text-danger">*</span></label>
                                <input type="text" name="option_a" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Opsi B <span class="text-danger">*</span></label>
                                <input type="text" name="option_b" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Opsi C <span class="text-danger">*</span></label>
                                <input type="text" name="option_c" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Opsi D <span class="text-danger">*</span></label>
                                <input type="text" name="option_d" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="answer" class="form-select" required>
                                <option value="">Pilih Jawaban Benar</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Penjelasan</label>
                            <textarea name="explanation" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_question" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Soal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="question_process.php" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="question_id" id="edit_question_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <textarea name="question" id="edit_question" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Opsi A <span class="text-danger">*</span></label>
                                <input type="text" name="option_a" id="edit_option_a" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Opsi B <span class="text-danger">*</span></label>
                                <input type="text" name="option_b" id="edit_option_b" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Opsi C <span class="text-danger">*</span></label>
                                <input type="text" name="option_c" id="edit_option_c" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Opsi D <span class="text-danger">*</span></label>
                                <input type="text" name="option_d" id="edit_option_d" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="answer" id="edit_answer" class="form-select" required>
                                <option value="">Pilih Jawaban Benar</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Penjelasan</label>
                            <textarea name="explanation" id="edit_explanation" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_question" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#questionsTable').DataTable({
                language: {
                    url: '/assets/js/datatables-id.json'
                },
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 3 }
                ]
            });

            // Handler untuk tombol edit
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const question = $(this).data('question');
                const optionA = $(this).data('option-a');
                const optionB = $(this).data('option-b');
                const optionC = $(this).data('option-c');
                const optionD = $(this).data('option-d');
                const answer = $(this).data('answer');
                const explanation = $(this).data('explanation');

                $('#edit_question_id').val(id);
                $('#edit_question').val(question);
                $('#edit_option_a').val(optionA);
                $('#edit_option_b').val(optionB);
                $('#edit_option_c').val(optionC);
                $('#edit_option_d').val(optionD);
                $('#edit_answer').val(answer);
                $('#edit_explanation').val(explanation);

                $('#editQuestionModal').modal('show');
            });

            // Konfirmasi hapus
            $('.delete-btn').click(function(e) {
                if (!confirm('Yakin ingin menghapus soal ini?')) {
                    e.preventDefault();
                }
            });
        });

        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        <?php if (isset($_SESSION['notification'])): ?>
        // Tampilkan notifikasi
        $(document).ready(function() {
            const notification = $('<div>')
                .addClass('notification ' + '<?php echo $_SESSION['notification']['type']; ?>')
                .text('<?php echo $_SESSION['notification']['message']; ?>')
                .appendTo('body');

            setTimeout(function() {
                notification.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
            
            <?php unset($_SESSION['notification']); ?>
        });
        <?php endif; ?>
    </script>
</body>
</html> 