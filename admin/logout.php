<?php
require_once __DIR__ . '/../includes/auth.php';
log_activity('Déconnexion admin', null);
logout();
redirect(SITE_URL . '/admin/login.php');
