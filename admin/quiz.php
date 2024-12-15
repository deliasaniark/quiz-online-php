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

// Proses hapus quiz
if (isset($_GET['delete'])) {
    $quiz_id = $_GET['delete'];
    $query = "DELETE FROM quiz WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $quiz_id);

    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Quiz berhasil dihapus!'
        ];
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus quiz!'
        ];
    }
}

// Ambil semua data quiz
$query = "SELECT q.*, 
          (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as total_questions 
          FROM quiz q 
          WHERE q.deleted_at IS NULL 
          ORDER BY q.created_at DESC";
$result = mysqli_query($conn, $query);

// Debug query jika tidak ada hasil
if (!$result) {
    die("Error in query: " . mysqli_error($conn));
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
    <title>Kelola Quiz - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/sidebar.css" rel="stylesheet">
    <link href="/assets/css/datatables-custom.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary-dark">Kelola Quiz</h3>
                <div>
                    <button class="btn d-md-none me-2" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                        <i class="bi bi-plus-lg"></i> Tambah Quiz
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="quizTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Quiz</th>
                                    <th>Total Soal</th>
                                    <th>Status</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td><?php echo $row['total_questions']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $row['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $row['is_active'] ? 'Aktif' : 'Draft'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="questions.php?quiz_id=<?php echo $row['id']; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="bi bi-list-check"></i> Soal
                                                </a>
                                                <a href="#" class="btn btn-warning btn-sm edit-btn"
                                                   data-id="<?php echo $row['id']; ?>"
                                                   data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                   data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                                   data-active="<?php echo $row['is_active']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="?delete=<?php echo $row['id']; ?>"
                                                    class="btn btn-danger btn-sm delete-btn"
                                                    data-title="<?php echo htmlspecialchars($row['title']); ?>">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">Belum ada quiz</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
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
            // Inisialisasi DataTables
            initializeDataTable('quizTable', {
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 5 }
                ]
            });

            // Konfirmasi hapus
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                const title = $(this).data('title');
                if(confirm(`Yakin ingin menghapus quiz "${title}"?`)) {
                    window.location.href = this.href;
                }
            });

            // Handler untuk tombol edit
            $('.edit-btn').on('click', function() {
                const quizId = $(this).data('id');
                const title = $(this).data('title');
                const description = $(this).data('description');
                const isActive = $(this).data('active');

                $('#edit_quiz_id').val(quizId);
                $('#edit_title').val(title);
                $('#edit_description').val(description);
                $('#edit_is_active').prop('checked', isActive == 1);

                $('#editQuizModal').modal('show');
            });
        });

        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Notifikasi
        <?php if (isset($_SESSION['notification'])): ?>
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

    <!-- Tambahkan modal di bagian bawah sebelum tag penutup body -->
    <div class="modal fade" id="addQuizModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Quiz Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="quiz_process.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Quiz <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive">
                                <label class="form-check-label" for="isActive">Aktifkan Quiz</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_quiz" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tambahkan modal edit setelah modal add -->
    <div class="modal fade" id="editQuizModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="quiz_process.php" method="POST">
                    <input type="hidden" name="quiz_id" id="edit_quiz_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Quiz <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active">
                                <label class="form-check-label" for="edit_is_active">Aktifkan Quiz</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_quiz" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>