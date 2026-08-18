<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_recursos.php';

$id = (int)($_GET['id'] ?? 0);
$recurso = $id ? recurso_get($id) : null;

if (!$recurso) {
    http_response_code(404);
    exit('Recurso no encontrado.');
}

if (!$recurso['publico'] && !portal_logueado()) {
    header('Location: ' . b('/login.php'));
    exit;
}

$rutaCompleta = __DIR__ . '/' . $recurso['archivo_ruta'];
if (!is_file($rutaCompleta)) {
    http_response_code(404);
    exit('El archivo ya no está disponible.');
}

$ext = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
$mime = [
    'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
][$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($recurso['archivo_nombre']) . '"');
header('Content-Length: ' . filesize($rutaCompleta));
readfile($rutaCompleta);
exit;
