<?php
require_once '../config/config.php';

// Cek apakah sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Ambil data peringkat
$query = "SELECT 
            u.id,
            u.username,
            u.full_name,
            u.profile_photo,
            COUNT(DISTINCT qr.quiz_id) as total_quiz,
            COALESCE(AVG(qr.score), 0) as avg_score,
            COALESCE(SUM(qr.score), 0) as total_score
          FROM users u
          LEFT JOIN quiz_results qr ON u.id = qr.user_id AND qr.is_completed = 1
          WHERE u.role = 'user'
          GROUP BY u.id
          ORDER BY total_score DESC, avg_score DESC";

$leaderboard = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringkat - QuizKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <main class="content mt-5">
        <div class="container-fluid px-4 mt-4">
            <div class="card">
                <div class="card-header bg-primary text-light">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Papan Peringkat</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Peserta</th>
                                    <th class="text-center">Total Quiz</th>
                                    <th class="text-center">Rata-rata</th>
                                    <th class="text-center">Total Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $position = 1;
                                while($row = mysqli_fetch_assoc($leaderboard)): 
                                    $isCurrentUser = $row['id'] == $user_id;
                                ?>
                                <tr class="<?php echo $isCurrentUser ? 'table-primary' : ''; ?>">
                                    <td class="align-middle">
                                        <?php 
                                        switch ($position) {
                                            case 1:
                                                echo "<span class='badge' style='font-size: 1.2em; background-color: var(--gold);' title='Juara 1'><i class='bi bi-trophy-fill'></i></span>";
                                                break;
                                            case 2:
                                                echo "<span class='badge' style='font-size: 1.2em; background-color: var(--silver);' title='Juara 2'><i class='bi bi-award'></i></span>";
                                                break;
                                            case 3:
                                                echo "<span class='badge' style='font-size: 1.2em; background-color: var(--bronze);' title='Juara 3'><i class='bi bi-award'></i></span>";
                                                break;
                                            default:
                                                echo "<span class='badge bg-light text-dark'>#" . $position . "</span>";
                                        }
                                        ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <?php if ($row['profile_photo']): ?>
                                                <img src="<?php echo getProfilePhotoUrl($row); ?>" 
                                                     class="profile-image-sm me-2" 
                                                     alt="Profile">
                                            <?php else: ?>
                                                <div class="profile-circle me-2">
                                                    <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($row['full_name'] ?? $row['username']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle"><?php echo $row['total_quiz']; ?></td>
                                    <td class="text-center align-middle">
                                        <span class="fw-bold text-primary-dark">
                                        <?php echo number_format($row['avg_score'], 1); ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="fw-bold text-primary-dark">
                                        <?php echo number_format($row['total_score']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                $position++;
                                endwhile; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
</body>
</html>