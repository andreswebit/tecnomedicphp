<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId) || portal_rol() === 'paciente') {
    http_response_code(403);
    exit('No tenés permiso.');
}

$fechaRaw = trim($_POST['fecha'] ?? '');
$dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
if (!$dt) { http_response_code(400); exit('Fecha inválida.'); }
$fechaDMY = $dt->format('d/m/Y');

$descripcion = trim($_POST['descripcion'] ?? '');
if ($descripcion === '') { http_response_code(400); exit('Descripción requerida.'); }

// Si el que carga es admin (no tiene "área" propia), se guarda sin área específica
$profesionalId = (int)($_SESSION['portal_uid'] ?? 0);
$area = trim($_POST['area'] ?? '');

tratamiento_crear($pacienteId, $profesionalId, $area, $fechaDMY, $descripcion);

echo 'ok';
