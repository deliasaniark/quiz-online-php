    <?php
// Hubungkan ke config.php
require_once 'config/config.php';

// Cek apakah user sudah login
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, arahkan sesuai role
    if ($_SESSION['role'] == 'admin') {
        redirect('/admin/dashboard.php');
                } else {
        redirect('/user/dashboard.php');
                }
            } else {
    // Jika belum login, arahkan ke halaman login
    redirect('/auth/login.php');
}
?> 