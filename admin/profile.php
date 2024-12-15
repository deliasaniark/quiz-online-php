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

// Proses update profil
if (isset($_POST['update'])) {
    $full_name = !empty($_POST['full_name']) ? $_POST['full_name'] : null;
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
    
    // Proses upload foto
    $profile_photo = $user['profile_photo']; // Default ke foto yang sudah ada
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = uniqid() . '.' . $ext;
            $uploadPath = '../assets/img/profile/' . $newName;
            
            // Hapus foto lama jika ada
            if ($profile_photo && file_exists('../assets/img/profile/' . $profile_photo)) {
                unlink('../assets/img/profile/' . $profile_photo);
            }
            
            // Upload foto baru
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                $profile_photo = $newName;
            }
        }
    }
    
    // Update data
    if ($password) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET 
                  full_name = NULLIF(?, ''), 
                  username = ?, 
                  password = ?,
                  gender = NULLIF(?, ''),
                  birth_date = NULLIF(?, ''),
                  phone = NULLIF(?, ''),
                  profile_photo = NULLIF(?, '')
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssi", $full_name, $username, $password, $gender, $birth_date, $phone, $profile_photo, $id);
    } else {
        $query = "UPDATE users SET 
                  full_name = NULLIF(?, ''), 
                  username = ?,
                  gender = NULLIF(?, ''),
                  birth_date = NULLIF(?, ''),
                  phone = NULLIF(?, ''),
                  profile_photo = NULLIF(?, '')
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssi", $full_name, $username, $gender, $birth_date, $phone, $profile_photo, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['username'] = $username;
        $notification = [
            'message' => 'Profil berhasil diupdate!',
            'type' => 'success'
        ];
        // Refresh data user
        $query = "SELECT * FROM users WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
    } else {
        $notification = [
            'message' => 'Gagal mengupdate profil!',
            'type' => 'error'
        ];
    }
}

// Fungsi untuk mendapatkan URL foto profil sudah ada di sidebar
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
    <title>Edit Profil - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/sidebar.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary-dark">Edit Profil</h3>
                <button class="btn d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <img src="<?php echo getProfilePhotoUrl($user); ?>" 
                                         class="profile-image mb-3" alt="Profile" id="preview-photo">
                                    <label for="photo-input" class="upload-photo-btn">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                </div>
                                <h4 class="text-primary-dark"><?php echo $user['full_name'] ?? $user['username']; ?></h4>
                                <p class="text-secondary">Administrator</p>
                            </div>

                            <form method="POST" enctype="multipart/form-data">
                                <input type="file" name="photo" id="photo-input" class="d-none" accept="image/jpeg,image/png">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="full_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?php echo ($user['gender'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($user['gender'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" name="birth_date" class="form-control" 
                                               value="<?php echo $user['birth_date']; ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="update" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Preview foto sebelum upload
        document.getElementById('photo-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-photo').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        <?php if (isset($notification)): ?>
        // Tampilkan notifikasi
        $(document).ready(function() {
            const notification = $('<div>')
                .addClass('notification ' + '<?php echo $notification['type']; ?>')
                .text('<?php echo $notification['message']; ?>')
                .appendTo('body');

            setTimeout(function() {
                notification.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        });
        <?php endif; ?>
    </script>
</body>
</html> 