<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso.');
}

objetivo_nutricional_guardar($pacienteId, trim($_POST['objetivo'] ?? ''), (int)($_SESSION['portal_uid'] ?? 0));

header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId));
exit;
