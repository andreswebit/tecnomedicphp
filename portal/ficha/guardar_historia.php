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
if (!$pacienteId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ID de paciente requerido.']);
    exit;
}

if (!ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No tenés permiso para editar esta ficha.']);
    exit;
}

$antecedentes  = trim($_POST['antecedentes'] ?? '');
$diagnostico   = trim($_POST['diagnostico'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

if ($antecedentes === '' && $diagnostico === '' && $observaciones === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Al menos un campo es obligatorio.']);
    exit;
}

try {
    $actualizadoPor = (int)($_SESSION['portal_uid'] ?? 0);
    historia_clinica_guardar(
        $pacienteId,
        $antecedentes,
        $diagnostico,
        $observaciones,
        $actualizadoPor
    );

    echo json_encode(['ok' => true, 'message' => 'Historia guardada correctamente.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar en base de datos: ' . $e->getMessage()]);
}
