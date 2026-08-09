<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$id = (int)($_POST['id'] ?? 0);
$estudio = $id ? estudio_get($id) : null;

if (!$estudio || !ficha_puede_editar((int)$estudio['paciente_id'])) {
    http_response_code(403);
    exit('No tenés permiso.');
}

$borrado = estudio_eliminar($id);
if ($borrado) {
    $ruta = __DIR__ . '/../../' . $borrado['ruta_archivo'];
    if (is_file($ruta)) unlink($ruta);
}

echo 'ok';
