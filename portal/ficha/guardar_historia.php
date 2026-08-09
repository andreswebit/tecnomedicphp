<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    exit('No tenés permiso.');
}

historia_clinica_guardar(
    $pacienteId,
    trim($_POST['antecedentes'] ?? ''),
    trim($_POST['diagnostico'] ?? ''),
    trim($_POST['observaciones'] ?? ''),
    (int)($_SESSION['portal_uid'] ?? 0)
);

echo 'ok';
