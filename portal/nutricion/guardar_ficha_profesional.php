<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$pacienteId || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso.');
}

nutricion_ficha_guardar($pacienteId, (int)$_SESSION['portal_uid'], $_POST);
header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId . '&seccion=resumen&ok=ficha'));
exit;
