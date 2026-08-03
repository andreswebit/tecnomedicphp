<?php
require_once __DIR__ . '/includes/auth.php';
portal_logout();
header('Location: ' . b('/login.php'));
exit;
