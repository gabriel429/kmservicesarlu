<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'session' => $_SESSION,
    'cookie' => $_COOKIE,
    'server' => $_SERVER['HTTP_HOST']
]);
