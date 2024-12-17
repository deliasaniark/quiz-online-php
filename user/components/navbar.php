<?php
if (!isset($user)) {
    die('Invalid access');
}
?>

<nav class="navbar navbar-custom navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="/user/dashboard.php">QuizKu</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list text-light"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="/user/dashboard.php">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'quiz.php' ? 'active' : ''; ?>" href="/user/quiz.php">
                        Quiz
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>" href="/user/history.php">
                        Riwayat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : ''; ?>" href="/user/leaderboard.php">
                        Peringkat
                    </a>
                </li>
            </ul>
            <div class="profile-nav">
                <div class="dropdown">
                    <button class="nav-link dropdown-toggle d-flex align-items-center border-0 bg-transparent" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="profile-circle">
                                <?php if ($user['profile_photo']): ?>
                                <img src="<?php echo getProfilePhotoUrl($user); ?>" class="profile-image-sm" alt="Profile">
                                <?php else: ?>
                                    <div class="profile-initial">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <span class="profile-name d-none d-md-inline">
                            <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 d-flex align-items-center">
                            <div class="profile-circle me-2">
                                <?php if ($user['profile_photo']): ?>
                                <img src="<?php echo getProfilePhotoUrl($user); ?>" class="profile-image-sm" alt="Profile">
                                <?php else: ?>
                                    <div class="profile-initial">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></span>
                                <small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/user/profile.php">
                                <i class="bi bi-person"></i> Profil Saya
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav> 