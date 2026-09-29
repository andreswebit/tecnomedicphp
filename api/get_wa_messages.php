<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$chat_id = (int)($_GET['chat_id'] ?? 0);
if ($chat_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'chat_id inválido']);
    exit;
}

try {
    $db = db();

    // Marcar como leído
    $st0 = $db->prepare("UPDATE tm_wa_chats SET mensajes_sin_leer = 0 WHERE id = ?");
    $st0->bind_param('i', $chat_id);
    $st0->execute();
    $st0->close();

    $st = $db->prepare(
        "SELECT id, direccion, mensaje, sid_twilio, estado, creado_en
         FROM tm_wa_mensajes
         WHERE chat_id = ?
         ORDER BY creado_en ASC, id ASC"
    );
    $st->bind_param('i', $chat_id);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    echo json_encode(['ok' => true, 'messages' => $rows]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
