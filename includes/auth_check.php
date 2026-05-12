<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireAuth($roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /index.php?error=not_logged_in');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['user_role'], $roles)) {
        header('Location: /index.php?error=access_denied');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Utilisateur';
}
?>