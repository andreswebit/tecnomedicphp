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
    echo json_encode(['ok' => false, 'error' => 'ID de estudio no especificado.']);
    exit;
}

try {
    $estudio = estudio_get($id);
    if (!$estudio) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'El estudio no existe.']);
        exit;
    }

    if (!ficha_puede_editar((int)$estudio['paciente_id']) || portal_rol() === 'paciente') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'No tenés permiso para eliminar este estudio.']);
        exit;
    }

    $borrado = estudio_eliminar($id);
    if ($borrado) {
        $ruta = __DIR__ . '/../../' . $borrado['ruta_archivo'];
        if (is_file($ruta)) {
            unlink($ruta);
        }
        echo json_encode(['ok' => true, 'message' => 'Estudio eliminado correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudo eliminar el registro del estudio.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
}
