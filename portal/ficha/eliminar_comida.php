<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
$id = (int)($_POST['id'] ?? 0);
$comida = $id ? comida_get($id) : null;

if (!$comida || (int)$comida['paciente_id'] !== $pacienteId || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso.');
}

comida_eliminar($id);

header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId));
exit;
