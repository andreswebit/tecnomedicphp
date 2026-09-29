<?php
/**
 * TECNOMEDIC — Webhook de Twilio para recepción de WhatsApp
 */
require_once __DIR__ . '/../includes/db.php';

// Twilio envía datos por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$from = trim($_POST['From'] ?? ''); // ej: whatsapp:+5493794123456
$body = trim($_POST['Body'] ?? '');
$sid  = trim($_POST['MessageSid'] ?? '');
$profileName = trim($_POST['ProfileName'] ?? '');

if (empty($from) || empty($body)) {
    http_response_code(400);
    exit('Bad Request');
}

// Limpiar prefijo whatsapp: para dejar solo el teléfono
$telefono = str_replace('whatsapp:', '', $from);

$db = db();

// 1. Obtener o crear chat
$stmt = $db->prepare("SELECT id FROM tm_wa_chats WHERE telefono = ?");
$stmt->bind_param('s', $telefono);
$stmt->execute();
$res = $stmt->get_result();
$chat = $res->fetch_assoc();
$stmt->close();

$chat_id = 0;
if ($chat) {
    $chat_id = $chat['id'];
    // Actualizar último mensaje y contador
    $stmtUpd = $db->prepare("UPDATE tm_wa_chats SET ultimo_mensaje = ?, ultimo_mensaje_at = NOW(), mensajes_sin_leer = mensajes_sin_leer + 1 " . ($profileName ? ", nombre_contacto = ?" : "") . " WHERE id = ?");
    if ($profileName) {
        $stmtUpd->bind_param('ssi', $body, $profileName, $chat_id);
    } else {
        $stmtUpd->bind_param('si', $body, $chat_id);
    }
    $stmtUpd->execute();
    $stmtUpd->close();
} else {
    // Crear chat nuevo
    $stmtIns = $db->prepare("INSERT INTO tm_wa_chats (telefono, nombre_contacto, ultimo_mensaje, mensajes_sin_leer) VALUES (?, ?, ?, 1)");
    $stmtIns->bind_param('sss', $telefono, $profileName, $body);
    $stmtIns->execute();
    $chat_id = $stmtIns->insert_id;
    $stmtIns->close();
}

// 2. Registrar el mensaje en tm_wa_mensajes
$stmtMsg = $db->prepare("INSERT INTO tm_wa_mensajes (chat_id, direccion, mensaje, sid_twilio, estado) VALUES (?, 'in', ?, ?, 'delivered')");
$stmtMsg->bind_param('iss', $chat_id, $body, $sid);
$stmtMsg->execute();
$stmtMsg->close();

// Respuesta TwiML estándar (vacía o confirmación)
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<Response></Response>';
exit;
