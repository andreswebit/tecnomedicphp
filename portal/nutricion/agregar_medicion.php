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

$peso = ($_POST['peso'] ?? '') !== '' ? (float)$_POST['peso'] : null;
$altura = ($_POST['altura'] ?? '') !== '' ? (float)$_POST['altura'] : null;

// Si no cargó altura esta vez, reusar la última altura registrada (para poder calcular IMC igual)
if ($peso && !$altura) {
    $ultimas = mediciones_listar($pacienteId);
    foreach ($ultimas as $m) {
        if ($m['altura'] !== null) { $altura = (float)$m['altura']; break; }
    }
}

medicion_crear(
    $pacienteId,
    (int)($_SESSION['portal_uid'] ?? 0),
    $fechaDMY,
    $peso,
    $altura,
    trim($_POST['observaciones'] ?? '')
);

header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId));
exit;
