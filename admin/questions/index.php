<?php
require_once '../../config/config.php';

// Cek apakah user sudah login dan role-nya admin
if (!is_logged_in() || !is_admin()) {
    redirect('auth/login.php');
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Ambil data kuis
$quiz = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM quiz WHERE id = $quiz_id AND deleted_at IS NULL"));

if (!$quiz) {
    $_SESSION['error'] = "Kuis tidak ditemukan!";
    redirect('admin/quiz/index.php');
}

// Proses hapus soal (soft delete)
if (isset($_POST['delete'])) {
    $question_id = (int)$_POST['question_id'];
    mysqli_query($conn, "UPDATE questions SET deleted_at = CURRENT_TIMESTAMP WHERE id = $question_id");
    $_SESSION['success'] = "Soal berhasil dihapus!";
    header("Location: " . $_SERVER['PHP_SELF'] . "?quiz_id=" . $quiz_id);
    exit;
}

// Ambil daftar soal
$questions = mysqli_query($conn, 
    "SELECT * FROM questions 
     WHERE quiz_id = $quiz_id AND deleted_at IS NULL 
     ORDER BY id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Soal - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Kelola Soal: <?php echo htmlspecialchars($quiz['title']); ?>
                            </h5>
                            <a href="create.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Tambah Soal
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="40%">Pertanyaan</th>
                                        <th width="40%">Pilihan Jawaban</th>
                                        <th width="5%">Kunci</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($question = mysqli_fetch_assoc($questions)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($question['question']); ?>
                                                <?php if ($question['explanation']): ?>
                                                    <div class="text-muted small mt-1">
                                                        <strong>Penjelasan:</strong><br>
                                                        <?php echo htmlspecialchars($question['explanation']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>A. <?php echo htmlspecialchars($question['option_a']); ?></div>
                                                <div>B. <?php echo htmlspecialchars($question['option_b']); ?></div>
                                                <div>C. <?php echo htmlspecialchars($question['option_c']); ?></div>
                                                <div>D. <?php echo htmlspecialchars($question['option_d']); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <?php echo $question['answer']; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $question['id']; ?>" 
                                                   class="btn btn-sm btn-warning mb-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Yakin ingin menghapus soal ini?')">
                                                    <input type="hidden" name="question_id" 
                                                           value="<?php echo $question['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>

                                    <?php if (mysqli_num_rows($questions) == 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada soal</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="../quiz/index.php" class="btn btn-secondary">
                                Kembali ke Daftar Kuis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 