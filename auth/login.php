<?php
// Hubungkan ke config.php
require_once '../config/config.php';

// Proses login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username di database
    $query = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password menggunakan password_verify
        if (password_verify($password, $user['password'])) {
            // Simpan data user ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Arahkan ke halaman yang sesuai
            if ($user['role'] == 'admin') {
                redirect('/admin/dashboard.php');
            } else {
                redirect('/user/dashboard.php');
            }
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Quizku</title>
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
                        <!-- Kolom Ilustrasi -->
                        <div class="col-md-6 bg-primary-dark d-none d-md-block">
                            <div class="d-flex flex-column justify-content-center align-items-center h-100 p-4">
                                <div class="text-center mb-3">
                                    <i class="bi bi-mortarboard-fill display-2 text-light"></i>
                                </div>
                                <h4 class="text-light text-center mb-2">Quizku</h4>
                                <p class="text-light text-center small">Platform quiz interaktif</p>
                            </div>
                        </div>
                        <!-- Kolom Form -->
                        <div class="col-md-6">
                            <div class="card-body p-4">
                                <div class="text-center mb-3 d-md-none">
                                    <i class="bi bi-mortarboard-fill h3 text-primary"></i>
                                    <h5 class="mt-2">Quizku</h5>
                                </div>
                                <h5 class="mb-3 text-center">Masuk</h5>

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
                                    <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                                    </button>
                                </form>

                                <div class="text-center">
                                    <small>Belum punya akun?
                                        <a href="register.php" class="text-primary-custom text-decoration-none">Daftar</a>
                                    </small>
                                </div>
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