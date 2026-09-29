<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/whatsapp.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$chat_id = (int)($_POST['chat_id'] ?? 0);
$mensaje = trim($_POST['mensaje'] ?? '');

if ($chat_id <= 0 || $mensaje === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'chat_id y mensaje requeridos']);
    exit;
}

try {
    $st = db()->prepare("SELECT telefono FROM tm_wa_chats WHERE id = ? LIMIT 1");
    $st->bind_param('i', $chat_id);
    $st->execute();
    $chat = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$chat) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Chat no encontrado']);
        exit;
    }

    $telefono = $chat['telefono'];

    // Enviar vía Twilio
    $ok = enviar_whatsapp($telefono, $mensaje);

    // Registrar en historial
    $st2 = db()->prepare(
        "INSERT INTO tm_wa_mensajes (chat_id, direccion, mensaje, estado) VALUES (?, 'out', ?, ?)"
    );
    $estado = $ok ? 'sent' : 'failed';
    $st2->bind_param('iss', $chat_id, $mensaje, $estado);
    $st2->execute();
    $st2->close();

    // Actualizar chat
    $st3 = db()->prepare(
        "UPDATE tm_wa_chats SET ultimo_mensaje = ?, ultimo_mensaje_at = NOW() WHERE id = ?"
    );
    $st3->bind_param('si', $mensaje, $chat_id);
    $st3->execute();
    $st3->close();

    echo json_encode(['ok' => true, 'twilio_ok' => $ok]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
