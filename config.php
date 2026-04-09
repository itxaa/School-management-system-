<?php
// ============================================================
//  Prototype by  : Muhammad Ali
//  Student ID    : BC220407460
//  Group ID      : F25PROJECT36276
// ============================================================
//  Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');          // MAMP default password
define('DB_NAME', 'alee');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">
         <strong>Database Connection Failed:</strong> ' . $conn->connect_error . '
         <br><small>Make sure MAMP is running and the database has been imported.</small>
         </div>');
}

$conn->set_charset('utf8mb4');

// ── Helper: redirect to login if no session ─────────────────
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
?>
