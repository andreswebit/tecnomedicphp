<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$id = (int)($_GET['id'] ?? 0);
$estudio = $id ? estudio_get($id) : null;

if (!$estudio || !ficha_puede_ver((int)$estudio['paciente_id'])) {
    http_response_code(403);
    exit('No tenés permiso para descargar este archivo.');
}

$rutaCompleta = __DIR__ . '/../../' . $estudio['ruta_archivo'];
if (!is_file($rutaCompleta)) {
    http_response_code(404);
    exit('El archivo ya no está disponible.');
}

$ext = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
$mime = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'][$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($estudio['nombre_original']) . '"');
header('Content-Length: ' . filesize($rutaCompleta));
readfile($rutaCompleta);
exit;
