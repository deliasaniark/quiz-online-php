<?php
require_once '../../config/config.php';

// Cek apakah user sudah login dan role-nya admin
if (!is_logged_in() || !is_admin()) {
    redirect('auth/login.php');
}

// Proses hapus kuis (soft delete)
if (isset($_POST['delete'])) {
    $quiz_id = (int)$_POST['quiz_id'];
    mysqli_query($conn, "UPDATE quiz SET deleted_at = CURRENT_TIMESTAMP WHERE id = $quiz_id");
    $_SESSION['success'] = "Kuis berhasil dihapus!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Proses aktivasi/deaktivasi kuis
if (isset($_POST['toggle_status'])) {
    $quiz_id = (int)$_POST['quiz_id'];
    mysqli_query($conn, "UPDATE quiz SET is_active = NOT is_active WHERE id = $quiz_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil daftar kuis
$quizzes = mysqli_query($conn, 
    "SELECT q.*, 
            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id AND deleted_at IS NULL) as total_questions
     FROM quiz q 
     WHERE q.deleted_at IS NULL 
     ORDER BY q.created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Quiz Online - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Kelola Kuis</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Kuis</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Kuis
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Total Soal</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($quiz = mysqli_fetch_assoc($quizzes)): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($quiz['description'], 0, 100)) . '...'; ?></td>
                            <td>
                                <?php echo $quiz['total_questions']; ?>
                                <a href="../questions/index.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary ms-2">
                                    Kelola Soal
                                </a>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm <?php 
                                        echo $quiz['is_active'] ? 'btn-success' : 'btn-secondary';
                                    ?>">
                                        <?php echo $quiz['is_active'] ? 'Aktif' : 'Non-aktif'; ?>
                                    </button>
                                </form>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($quiz['created_at'])); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $quiz['id']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Yakin ingin menghapus kuis ini?')">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($quizzes) == 0): ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada kuis</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 