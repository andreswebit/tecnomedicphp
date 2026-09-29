<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $st = db()->query(
        "SELECT id, telefono, nombre_contacto, ultimo_mensaje, ultimo_mensaje_at, mensajes_sin_leer
         FROM tm_wa_chats
         ORDER BY ultimo_mensaje_at DESC"
    );

    $rows = $st ? $st->fetch_all(MYSQLI_ASSOC) : [];
    echo json_encode(['ok' => true, 'chats' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
