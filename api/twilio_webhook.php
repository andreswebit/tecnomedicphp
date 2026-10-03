<?php
/**
 * TECNOMEDIC: Webhook unico de Twilio para WhatsApp.
 * 1) Guarda el mensaje entrante en el Hub (tm_wa_chats / tm_wa_mensajes).
 * 2) Ejecuta el bot de turnos (webhook.php) y guarda sus respuestas en el Hub.
 * En Twilio, "When a message comes in" debe apuntar a ESTE archivo.
 */
require_once __DIR__ . '/../includes/db.php';
define('WA_BOT_AS_LIB', true);
require_once __DIR__ . '/../includes/wa_hub.php';

// Ubicar el bot (webhook.php). Se busca junto a este archivo y en carpetas cercanas.
$__bot = null;
foreach ([__DIR__ . '/webhook.php', dirname(__DIR__) . '/webhook.php', dirname(__DIR__) . '/whatsapp/webhook.php', dirname(__DIR__) . '/bot/webhook.php', dirname(__DIR__) . '/bot/webkook.php', dirname(__DIR__) . '/webhooks/webhook.php'] as $__c) {
    if (is_file($__c)) { $__bot = $__c; break; }
}
if ($__bot) {
    require_once $__bot;   // define procesar_bot() y no ejecuta nada mas
}

function _tw_log(string $m): void {
    @file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " TWILIO: $m\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    _tw_log('Peticion no POST: ' . $_SERVER['REQUEST_METHOD'] . ' (revisar redirecciones http/https o www en la URL del webhook)');
    http_response_code(405);
    exit('Method not allowed');
}

header('Content-Type: text/xml; charset=utf-8');

$from        = trim($_POST['From'] ?? '');          // whatsapp:+549379...
$body        = trim($_POST['Body'] ?? '');
$sid         = trim($_POST['MessageSid'] ?? '');
$profileName = trim($_POST['ProfileName'] ?? '');

if ($from === '' || $body === '') {
    echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
    exit;
}

_tw_log("Entrante de $from: " . mb_substr($body, 0, 60));

$cid = wa_hub_guardar_entrante($from, $body, $sid, $profileName);
if ($cid === -1) {  // reintento duplicado de Twilio
    echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
    exit;
}
if ($cid > 0) $GLOBALS['WA_CHAT_ID'] = $cid;

// 3) Bot de turnos (sus respuestas se guardan en el Hub via _wa())
try {
    if (!function_exists('procesar_bot')) throw new Exception('No se encontro webhook.php (bot). Copialo a la carpeta api.');
    procesar_bot($from, $body);
} catch (Throwable $e) {
    _tw_log('Error en el bot: ' . $e->getMessage() . ' en ' . basename($e->getFile()) . ':' . $e->getLine());
}

echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
