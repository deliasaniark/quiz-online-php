<?php
require_once '../config/config.php';

// Proses registrasi
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi password
    if ($password !== $confirm_password) {
        $error = "Password tidak sama!";
    } else {
        // Cek apakah username sudah ada
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan user baru dengan password yang sudah di-hash
            $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'user')";
            if (mysqli_query($conn, $query)) {
                // Redirect ke login
                redirect('/auth/login.php');
            } else {
                $error = "Gagal mendaftar! " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Quiz Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary-dark text-center">
                        <h3 class="text-light mb-0">Daftar Akun Baru</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php } ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100">Daftar</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            Sudah punya akun? <a href="/auth/login.php" class="text-primary-custom">Login disini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 