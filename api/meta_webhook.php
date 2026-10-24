<?php
/**
 * TECNOMEDIC: Webhook Meta WhatsApp (Cloud API)
 *
 * Objetivo:
 * - Verificar webhook (GET)
 * - Parsear mensaje de texto entrante (POST)
 * - Normalizar a formato compatible con el bot actual (bot/webhook.php)
 * - Persistir en el Hub (wa_hub_guardar_entrante)
 * - Ejecutar el bot (procesar_bot)
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/wa_hub.php';

// Importante: el bot actual parsea Twilio style (From/Body). Para reutilizar la lógica,
// incluimos el bot como librería (WA_BOT_AS_LIB) y llamamos procesar_bot($phone, $msg).
if (!defined('WA_BOT_AS_LIB')) {
    define('WA_BOT_AS_LIB', true);
}
require_once __DIR__ . '/../bot/webhook.php';

$mode = $_GET['hub.mode'] ?? '';
$token = $_GET['hub.verify_token'] ?? '';
$challenge = $_GET['hub.challenge'] ?? '';

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
    if ($mode === 'subscribe' && $token !== '' && $token === (string)META_WA_VERIFY_TOKEN) {
        header('Content-Type: text/plain; charset=utf-8');
        echo (string)$challenge;
        exit;
    }
    http_response_code(403);
    exit('Forbidden');
}

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    exit('Invalid JSON');
}

$from = '';
$body = '';
$sid = '';
$profileName = '';

try {
    // Estructura típica:
    // data.entry[0].changes[0].value.messages[0]
    $entries = $data['entry'] ?? [];
    $entry0 = is_array($entries) && isset($entries[0]) ? $entries[0] : null;

    $changes = $entry0['changes'] ?? [];
    $change0 = is_array($changes) && isset($changes[0]) ? $changes[0] : null;

    $value = $change0['value'] ?? [];

    $messages = $value['messages'] ?? [];
    $msgNode = is_array($messages) && isset($messages[0]) ? $messages[0] : null;

    if ($msgNode) {
        $from = trim((string)($msgNode['from'] ?? ''));
        $sid = trim((string)($msgNode['id'] ?? ''));

        $textNode = $msgNode['text'] ?? null;
        if (is_array($textNode)) {
            $body = trim((string)($textNode['body'] ?? ''));
        }

        // Profile puede venir o no
        $contacts = $value['contacts'] ?? [];
        $c0 = is_array($contacts) && isset($contacts[0]) ? $contacts[0] : null;
        if ($c0 && isset($c0['profile']['name'])) {
            $profileName = trim((string)$c0['profile']['name']);
        }
    }
} catch (Throwable $e) {
    $from = '';
    $body = '';
    $sid = '';
    $profileName = '';
}

// Solo mensajes de texto
if ($from === '' || $body === '') {
    http_response_code(200);
    exit('ignored');
}

require_once __DIR__ . '/../includes/whatsapp.php';

// El Hub espera $from con prefijo 'whatsapp:' para poder quitarlo.
// Normalizamos con formatear_wa().
$fromNorm = formatear_wa($from);

// Persistir entrante
$__cid = wa_hub_guardar_entrante($fromNorm, $body, $sid, $profileName);
if ($__cid > 0) {
    $GLOBALS['WA_CHAT_ID'] = $__cid;
}

// Ejecutar bot
if (function_exists('procesar_bot')) {
    procesar_bot($fromNorm, $body);
}

header('Content-Type: text/plain; charset=utf-8');
echo 'ok';
