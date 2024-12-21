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
    <title>Register - Quizku</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light-custom">
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 animate__animated animate__fadeIn">
                    <div class="row g-0">
                        <!-- Kolom Form -->
                        <div class="col-md-6">
                            <div class="card-body p-4">
                                <div class="text-center mb-3 d-md-none">
                                    <i class="bi bi-mortarboard-fill h3 text-primary"></i>
                                    <h5 class="mt-2">Quizku</h5>
                                </div>
                                <h5 class="mb-3 text-center">Daftar Akun</h5>

                                <?php if (isset($error)) { ?>
                                    <div class="alert alert-danger py-2 animate__animated animate__shakeX">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        <?php echo $error; ?>
                                    </div>
                                <?php } ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label small">
                                            <i class="bi bi-person me-1"></i>Username
                                        </label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">
                                            <i class="bi bi-lock me-1"></i>Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" required>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword(this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">
                                            <i class="bi bi-lock-fill me-1"></i>Konfirmasi Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password" class="form-control" required>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword(this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" name="register" class="btn btn-primary w-100 mb-3">
                                        <i class="bi bi-person-plus me-1"></i>Daftar
                                    </button>
                                </form>

                                <div class="text-center">
                                    <small>Sudah punya akun?
                                        <a href="login.php" class="text-primary-custom text-decoration-none">Login</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- Kolom Ilustrasi -->
                        <div class="col-md-6 bg-primary-dark d-none d-md-block">
                            <div class="d-flex flex-column justify-content-center align-items-center h-100 p-4">
                                <div class="text-center mb-3">
                                    <i class="bi bi-people-fill display-2 text-light"></i>
                                </div>
                                <h4 class="text-light text-center mb-2">Quizku</h4>
                                <p class="text-light text-center small">Platform quiz interaktif</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>

</html>