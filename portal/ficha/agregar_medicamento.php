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
    echo json_encode(['ok' => false, 'error' => 'No tenés permiso para recetar medicamentos a este paciente.']);
    exit;
}

$nombreComercial   = trim($_POST['nombre_comercial'] ?? '');
$principioActivo   = trim($_POST['principio_activo'] ?? '');
$dosis             = trim($_POST['dosis'] ?? '');
$frecuencia        = trim($_POST['frecuencia'] ?? '');
$viaAdministracion = trim($_POST['via_administracion'] ?? '');
$estado            = trim($_POST['estado'] ?? 'activo');

if ($nombreComercial === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El nombre comercial o del medicamento es obligatorio.']);
    exit;
}

$estadosValidos = ['activo', 'suspendido', 'finalizado'];
if (!in_array($estado, $estadosValidos, true)) {
    $estado = 'activo';
}

try {
    $id = medicamento_crear(
        $pacienteId,
        $nombreComercial,
        $principioActivo,
        $dosis,
        $frecuencia,
        $viaAdministracion,
        $estado
    );

    echo json_encode([
        'ok' => true,
        'message' => 'Medicamento agregado correctamente.',
        'id' => $id
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar el medicamento: ' . $e->getMessage()]);
}
