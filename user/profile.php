<?php
require_once '../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Update password jika diisi
    $password_query = "";
    if (!empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'])) {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_query = ", password = '$hashed_password'";
        } else {
            $_SESSION['error'] = "Password saat ini tidak sesuai!";
            redirect('/user/profile.php');
        }
    }

    // Handle upload foto profil
    $profile_photo = $user['profile_photo']; // Default ke foto yang sudah ada
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = uniqid() . '.' . $ext;
            $uploadPath = '../assets/img/profile/' . $newName;
            
                // Hapus foto lama jika ada
            if ($profile_photo && file_exists('../assets/img/profile/' . $profile_photo)) {
                unlink('../assets/img/profile/' . $profile_photo);
            }
            
            // Upload foto baru
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
                $profile_photo = $newName;
                $photo_query = ", profile_photo = '$profile_photo'";
            }
        }
    }

    // Update data user
    if ($password_query) {
    $query = "UPDATE users SET 
                  full_name = NULLIF(?, ''), 
                  gender = NULLIF(?, ''),
                  birth_date = NULLIF(?, ''),
                  phone = NULLIF(?, ''),
                  profile_photo = NULLIF(?, '')
              $password_query
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $gender, $birth_date, $phone, $profile_photo, $user_id);
    } else {
        $query = "UPDATE users SET 
                  full_name = NULLIF(?, ''), 
                  gender = NULLIF(?, ''),
                  birth_date = NULLIF(?, ''),
                  phone = NULLIF(?, ''),
                  profile_photo = NULLIF(?, '')
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $gender, $birth_date, $phone, $profile_photo, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Profil berhasil diperbarui!";
        redirect('/user/profile.php');
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - QuizKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <main class="content mt-5">
        <div class="container-fluid px-4 mt-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-light">
                            <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Edit Profil</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <!-- Foto Profil -->
                                <div class="text-center mb-4 position-relative">
                                    <div class="profile-image-container mx-auto" style="width: 150px;">
                                        <?php if ($user['profile_photo']): ?>
                                            <img src="<?php echo getProfilePhotoUrl($user); ?>" 
                                                 class="profile-image" 
                                                 id="preview-photo"
                                                 alt="Profile Photo">
                                        <?php else: ?>
                                            <div class="profile-image d-flex align-items-center justify-content-center bg-light" id="profile-initial">
                                                <span class="display-4 text-muted">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </span>
                                            </div>
                                            <img src="" 
                                                 class="profile-image d-none" 
                                                 id="preview-photo"
                                                 alt="Profile Photo">
                                        <?php endif; ?>
                                        <label for="profile_photo" class="upload-photo-btn">
                                            <i class="bi bi-camera"></i>
                                        </label>
                                    </div>
                                    <input type="file" 
                                           id="profile_photo" 
                                           name="profile_photo" 
                                           class="d-none" 
                                           accept="image/jpeg,image/png">
                                </div>

                                <!-- Data Profil -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" 
                                               disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" 
                                               name="full_name" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?php echo $user['gender'] === 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo $user['gender'] === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" 
                                               name="birth_date" 
                                               class="form-control" 
                                               value="<?php echo $user['birth_date']; ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="tel" 
                                               name="phone" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                               required>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Ganti Password (Opsional) -->
                                <h6 class="mb-3">Ganti Password (Opsional)</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Password Saat Ini</label>
                                        <input type="password" 
                                               name="current_password" 
                                               class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" 
                                               name="new_password" 
                                               class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" 
                                               name="confirm_password" 
                                               class="form-control">
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Preview foto profil sebelum upload
    document.getElementById('profile_photo').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            const previewPhoto = document.getElementById('preview-photo');
            const profileInitial = document.getElementById('profile-initial');

            reader.onload = function(e) {
                previewPhoto.src = e.target.result;
                previewPhoto.classList.remove('d-none');
                if (profileInitial) {
                    profileInitial.classList.add('d-none');
                }
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Validasi form sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.querySelector('input[name="new_password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        const currentPassword = document.querySelector('input[name="current_password"]').value;

        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword) {
                e.preventDefault();
                alert('Masukkan password saat ini untuk mengubah password!');
                return;
            }
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return;
            }
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                return;
            }
        }
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-custom');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    </script>
</body>
</html> 