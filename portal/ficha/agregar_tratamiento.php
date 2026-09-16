<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId) || portal_rol() === 'paciente') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No tenés permiso para agregar tratamientos a este paciente.']);
    exit;
}

$fechaRaw = trim($_POST['fecha'] ?? '');
if ($fechaRaw === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'La fecha es obligatoria.']);
    exit;
}

// Aceptar formato Y-m-d o d/m/Y
$dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
if (!$dt) {
    $dt = DateTime::createFromFormat('d/m/Y', $fechaRaw);
}
if (!$dt) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Formato de fecha inválido.']);
    exit;
}
$fechaDMY = $dt->format('d/m/Y');

$descripcion = trim($_POST['descripcion'] ?? '');
if ($descripcion === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'La descripción del tratamiento es obligatoria.']);
    exit;
}

$profesionalId = (int)($_SESSION['portal_uid'] ?? 0);
$area = trim($_POST['area'] ?? '');

try {
    tratamiento_crear($pacienteId, $profesionalId, $area, $fechaDMY, $descripcion);
    echo json_encode(['ok' => true, 'message' => 'Tratamiento registrado correctamente.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar en la base de datos: ' . $e->getMessage()]);
}
