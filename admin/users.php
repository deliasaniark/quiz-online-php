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

// Proses hapus user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $now = date('Y-m-d H:i:s');
    $query = "UPDATE users SET deleted_at = '$now' WHERE id = '$id' AND role != 'admin'";
    if(mysqli_query($conn, $query)) {
        header('Location: users.php');
        exit;
    }
}

// Ambil semua data users kecuali admin
$query = "SELECT * FROM users WHERE role != 'admin' AND deleted_at IS NULL ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Debug
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Fungsi untuk mendapatkan URL foto profil (diperlukan untuk sidebar)
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
    <title>Kelola Users - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/sidebar.css" rel="stylesheet">
    <link href="/assets/css/datatables-custom.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary-dark">Kelola Users</h3>
                <button class="btn d-md-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) { 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm delete-btn" 
                                           data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/js/datatables-config.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables dengan konfigurasi khusus untuk tabel users
            initializeDataTable('usersTable', {
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 4 } // Kolom aksi tidak bisa diurutkan
                ]
            });

            // Inisialisasi konfirmasi hapus
            confirmDelete('.delete-btn');

        // Toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            });
        });
    </script>
</body>
</html> 