<?php
require_once '../config/config.php';

// Hapus semua data session
session_destroy();

// Redirect ke halaman login
redirect('/auth/login.php');