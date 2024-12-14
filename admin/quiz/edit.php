<?php
require_once '../../config/config.php';

// Cek apakah user sudah login dan role-nya admin
if (!is_logged_in() || !is_admin()) {
    redirect('auth/login.php');
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data kuis
$quiz = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM quiz WHERE id = $quiz_id AND deleted_at IS NULL"));

if (!$quiz) {
    $_SESSION['error'] = "Kuis tidak ditemukan!";
    redirect('admin/quiz/index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $sql = "UPDATE quiz 
            SET title = '$title', 
                description = '$description', 
                is_active = $is_active 
            WHERE id = $quiz_id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Kuis berhasil diperbarui!";
        redirect('admin/quiz/index.php');
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kuis - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Kuis</h5>
                        <a href="../questions/index.php?quiz_id=<?php echo $quiz_id; ?>" 
                           class="btn btn-primary btn-sm">
                            Kelola Soal
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Judul Kuis</label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="4" required><?php 
                                    echo htmlspecialchars($quiz['description']); 
                                ?></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" 
                                           id="is_active" <?php echo $quiz['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Aktifkan kuis ini
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 