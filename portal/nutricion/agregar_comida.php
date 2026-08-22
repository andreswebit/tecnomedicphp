<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_ver($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso.');
}

$fechaRaw = trim($_POST['fecha'] ?? '');
$dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
if (!$dt) { http_response_code(400); die('Fecha inválida.'); }
$fechaDMY = $dt->format('d/m/Y');

$tiposValidos = ['desayuno', 'almuerzo', 'merienda', 'cena', 'colacion'];
$tipo = $_POST['tipo_comida'] ?? '';
if (!in_array($tipo, $tiposValidos, true)) { http_response_code(400); die('Tipo de comida inválido.'); }

$descripcion = trim($_POST['descripcion'] ?? '');
if ($descripcion === '') { http_response_code(400); die('Descripción requerida.'); }

comida_crear($pacienteId, (int)($_SESSION['portal_uid'] ?? 0), $fechaDMY, $tipo, $descripcion);

header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId));
exit;
