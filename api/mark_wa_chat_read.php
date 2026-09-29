<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$chat_id = (int)($_POST['chat_id'] ?? 0);
if ($chat_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'chat_id inválido']);
    exit;
}

try {
    $st = db()->prepare("UPDATE tm_wa_chats SET mensajes_sin_leer = 0 WHERE id = ?");
    $st->bind_param('i', $chat_id);
    $st->execute();
    $st->close();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
