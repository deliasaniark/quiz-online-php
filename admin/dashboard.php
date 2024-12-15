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

// Hitung jumlah user dan quiz
$query_users = "SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'";
$result_users = mysqli_query($conn, $query_users);
$total_users = mysqli_fetch_assoc($result_users)['total_users'];

$query_quiz = "SELECT COUNT(*) as total_quiz FROM quiz WHERE deleted_at IS NULL";
$result_quiz = mysqli_query($conn, $query_quiz);
$total_quiz = mysqli_fetch_assoc($result_quiz)['total_quiz'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Admin - Quiz Online</title>
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
                <h3 class="text-primary-dark">Dashboard Admin</h3>
                <button class="btn d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="card welcome-card mb-4">
                <div class="card-body">
                    <h4>Selamat Datang, <?php echo $user['full_name'] ?? $user['username']; ?>!</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0"><?php echo $total_users; ?></h4>
                                <small class="text-secondary">Total User</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-question-circle fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0"><?php echo $total_quiz; ?></h4>
                                <small class="text-secondary">Total Quiz</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Section -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm">
                        <h6 class="mb-0 mt-3">Top 5 Peserta Quiz</h6>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Peringkat</th>
                                            <th>Nama</th>
                                            <th>Quiz Selesai</th>
                                            <th>Total Skor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT u.username, u.full_name,
                                                 COUNT(DISTINCT qr.quiz_id) as completed_quiz,
                                                 u.total_score
                                                 FROM users u
                                                 JOIN quiz_results qr ON u.id = qr.user_id
                                                 WHERE u.role != 'admin' AND qr.is_completed = 1
                                                 GROUP BY u.id
                                                 ORDER BY u.total_score DESC, completed_quiz DESC
                                                 LIMIT 5";
                                        $top_users = mysqli_query($conn, $query);
                                        $rank = 1;
                                        while ($row = mysqli_fetch_assoc($top_users)) {
                                            echo "<tr>";
                                            // Badge untuk 3 peringkat teratas
                                            echo "<td>";
                                            switch ($rank) {
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
                                                    echo "<span class='badge bg-light text-dark'>" . $rank . "</span>";
                                            }
                                            echo "</td>";
                                            echo "<td>" . htmlspecialchars($row['full_name'] ?? $row['username']) . "</td>";
                                            echo "<td>" . $row['completed_quiz'] . " Quiz</td>";
                                            echo "<td><span class='fw-bold text-primary-dark'>" .
                                                number_format($row['total_score']) . "</span></td>";
                                            $rank++;
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>

    <?php
    function getProfilePhotoUrl($user)
    {
        if ($user['profile_photo'] && file_exists('../assets/img/profile/' . $user['profile_photo'])) {
            return '/assets/img/profile/' . $user['profile_photo'];
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random';
    }
    ?>
</body>

</html>