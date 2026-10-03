<?php
/**
 * TECNOMEDIC: diagnostico del Hub de WhatsApp. Abrir como admin: /api/wa_diagnostico.php
 * No deja datos: la prueba de guardado se revierte.
 */
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/wa_hub.php';

header('Content-Type: application/json; charset=utf-8');
$out = ['ok' => true];

try {
    $db = db();
    foreach (['tm_wa_chats', 'tm_wa_mensajes'] as $t) {
        $r = $db->query("SHOW TABLES LIKE '$t'");
        $existe = $r && $r->num_rows > 0;
        $out['tablas'][$t] = ['existe' => $existe];
        if ($existe) {
            $c = $db->query("SELECT COUNT(*) n FROM $t")->fetch_assoc();
            $out['tablas'][$t]['filas'] = (int)$c['n'];
            $cr = $db->query("SHOW CREATE TABLE $t")->fetch_assoc();
            $out['tablas'][$t]['estructura'] = $cr['Create Table'] ?? '';
        }
    }

    // Prueba de guardado (se revierte)
    $db->begin_transaction();
    $GLOBALS['WA_HUB_ERR'] = null;
    $id = wa_hub_guardar_entrante('whatsapp:+0000000000', 'prueba diagnostico', 'TEST_' . time(), 'Diagnostico');
    $out['prueba_guardado'] = ['chat_id' => $id, 'error' => $GLOBALS['WA_HUB_ERR'] ?? null];
    $db->rollback();
} catch (Throwable $e) {
    $out['error'] = $e->getMessage();
}

$log = __DIR__ . '/debug.log';
if (is_file($log)) {
    $lines = array_slice(file($log, FILE_IGNORE_NEW_LINES) ?: [], -25);
    $out['debug_log_ultimas'] = $lines;
} else {
    $out['debug_log_ultimas'] = 'api/debug.log no existe (o no se puede escribir en la carpeta api)';
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
