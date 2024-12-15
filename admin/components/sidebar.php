<?php
// Pastikan $user dan session tersedia
if (!isset($user) || !isset($_SESSION)) {
    die('Invalid access');
}

// Tentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="<?php echo getProfilePhotoUrl($user); ?>"
            class="profile-image-sm rounded-circle mb-2" alt="Profile">
        <h6 class="text-white mb-0"><?php echo $_SESSION['username']; ?></h6>
        <small class="text-white-50">Administrator</small>
    </div>

    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a href="/admin/dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/users.php" class="nav-link <?php echo ($current_page == 'users') ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> Kelola User
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/quiz.php" class="nav-link <?php echo (in_array($current_page, ['quiz', 'questions'])) ? 'active' : ''; ?>">
                <i class="bi bi-question-circle"></i> Kelola Quiz
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/results.php" class="nav-link <?php echo ($current_page == 'results') ? 'active' : ''; ?>">
                <i class="bi bi-file-text"></i> Lihat Hasil Quiz
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/profile.php" class="nav-link <?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                <i class="bi bi-person"></i> Edit Profil
            </a>
        </li>
        <li class="nav-item mt-2">
            <a href="/auth/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</div>