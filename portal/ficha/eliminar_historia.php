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

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ID de registro faltante.']);
    exit;
}

try {
    $st = db()->prepare("SELECT paciente_id FROM tm_historia_clinica WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'El registro no existe.']);
        exit;
    }

    if (!ficha_puede_editar((int)$row['paciente_id'])) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'No tenés permiso para eliminar este registro.']);
        exit;
    }

    historia_clinica_eliminar($id);

    echo json_encode(['ok' => true, 'message' => 'Registro eliminado correctamente.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al eliminar en base de datos: ' . $e->getMessage()]);
}
