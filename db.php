<?php
// ============================================================
//  includes/db.php  — Database connection + session helpers
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // XAMPP default
define('DB_PASS', '');           // XAMPP default (blank)
define('DB_NAME', 'engineering_admission');
define('COOKIE_NAME', 'ea_session');
define('COOKIE_EXPIRE', 60 * 60 * 24 * 7); // 7 days

// --- MySQLi connection ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;color:#c00;">
        <h2>Database Connection Failed</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure XAMPP MySQL is running and you have imported <code>database.sql</code>.</p>
    </div>');
}
$conn->set_charset('utf8mb4');

// --- Session / Cookie helpers ---
session_start();

function setLoginCookie($userId, $role) {
    $token = base64_encode($userId . ':' . $role . ':' . time());
    setcookie(COOKIE_NAME, $token, time() + COOKIE_EXPIRE, '/', '', false, true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['role']    = $role;
}

function getLoggedInUser() {
    // prefer session
    if (!empty($_SESSION['user_id'])) {
        return ['id' => $_SESSION['user_id'], 'role' => $_SESSION['role']];
    }
    // fall back to cookie
    if (!empty($_COOKIE[COOKIE_NAME])) {
        $parts = explode(':', base64_decode($_COOKIE[COOKIE_NAME]));
        if (count($parts) === 3) {
            $_SESSION['user_id'] = (int)$parts[0];
            $_SESSION['role']    = $parts[1];
            return ['id' => (int)$parts[0], 'role' => $parts[1]];
        }
    }
    return null;
}

function requireLogin() {
    $u = getLoggedInUser();
    if (!$u) { header('Location: login.php'); exit; }
    return $u;
}

function requireAdmin() {
    $u = requireLogin();
    if ($u['role'] !== 'admin') { header('Location: index.php'); exit; }
    return $u;
}

function logout() {
    session_destroy();
    setcookie(COOKIE_NAME, '', time() - 3600, '/');
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(trim(strip_tags($val)));
}
?>
