<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('ID faltante');
}

$ante = trim($_POST['antecedentes'] ?? '');
$dia  = trim($_POST['diagnostico'] ?? '');
$obs  = trim($_POST['observaciones'] ?? '');
if (!strlen($ante) && !strlen($dia) && !strlen($obs)) {
    http_response_code(400);
    exit('Sin datos');
}

// Verificar que el registro exista y que el usuario pueda editar el paciente asociado
$st = db()->prepare("SELECT paciente_id FROM tm_historia_clinica WHERE id = ?");
$st->bind_param('i', $id);
$st->execute();
$row = $st->get_result()->fetch_assoc();
if (!$row) {
    http_response_code(404);
    exit('No existe');
}
if (!ficha_puede_editar($row['paciente_id'])) {
    http_response_code(403);
    exit('No tenés permiso');
}

$st = db()->prepare("UPDATE tm_historia_clinica SET antecedentes = ?, diagnostico = ?, observaciones = ?, actualizado_por = ? WHERE id = ?");
$actualizadoPor = (int)($_SESSION['portal_uid'] ?? 0);
$st->bind_param('sssii', $ante, $dia, $obs, $actualizadoPor, $id);
$st->execute();
echo 'ok';
