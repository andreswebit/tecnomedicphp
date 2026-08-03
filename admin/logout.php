<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
iniciar_sesion_php();
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
