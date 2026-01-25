<?php
/**
 * Logout Page
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();
session_destroy();

header('Location: ' . APP_URL . 'admin/login');
exit;
