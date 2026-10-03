<?php
/**
 * TECNOMEDIC: guardado de chats de WhatsApp en el Hub (tm_wa_chats / tm_wa_mensajes).
 */
require_once __DIR__ . '/db.php';

function wa_hub_log(string $m): void {
    @file_put_contents(dirname(__DIR__) . '/api/debug.log', date('Y-m-d H:i:s') . " HUB: $m\n", FILE_APPEND);
}

function wa_hub_tablas(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS tm_wa_chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        telefono VARCHAR(40) NOT NULL,
        nombre_contacto VARCHAR(120) NULL,
        ultimo_mensaje TEXT NULL,
        ultimo_mensaje_at DATETIME NULL,
        mensajes_sin_leer INT NOT NULL DEFAULT 0,
        creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_telefono (telefono)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS tm_wa_mensajes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id INT NOT NULL,
        direccion VARCHAR(10) NOT NULL,
        mensaje TEXT NULL,
        sid_twilio VARCHAR(64) NULL,
        estado VARCHAR(20) NULL DEFAULT 'sent',
        creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_chat (chat_id),
        KEY idx_sid (sid_twilio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/** Guarda un mensaje entrante. Devuelve chat_id, -1 si es duplicado, 0 si fallo. */
function wa_hub_guardar_entrante(string $from, string $body, string $sid, string $profileName): int {
    try {
        wa_hub_tablas();
        $db = db();

        if ($sid !== '') {
            $st = $db->prepare("SELECT id FROM tm_wa_mensajes WHERE sid_twilio = ? LIMIT 1");
            $st->bind_param('s', $sid);
            $st->execute();
            $dup = $st->get_result()->fetch_assoc();
            $st->close();
            if ($dup) return -1;
        }

        $telefono = str_replace('whatsapp:', '', $from);

        $st = $db->prepare("SELECT id FROM tm_wa_chats WHERE telefono = ? LIMIT 1");
        $st->bind_param('s', $telefono);
        $st->execute();
        $chat = $st->get_result()->fetch_assoc();
        $st->close();

        if ($chat) {
            $chat_id = (int)$chat['id'];
            if ($profileName !== '') {
                $st = $db->prepare("UPDATE tm_wa_chats SET ultimo_mensaje = ?, ultimo_mensaje_at = NOW(), mensajes_sin_leer = mensajes_sin_leer + 1, nombre_contacto = ? WHERE id = ?");
                $st->bind_param('ssi', $body, $profileName, $chat_id);
            } else {
                $st = $db->prepare("UPDATE tm_wa_chats SET ultimo_mensaje = ?, ultimo_mensaje_at = NOW(), mensajes_sin_leer = mensajes_sin_leer + 1 WHERE id = ?");
                $st->bind_param('si', $body, $chat_id);
            }
            $st->execute();
            $st->close();
        } else {
            $st = $db->prepare("INSERT INTO tm_wa_chats (telefono, nombre_contacto, ultimo_mensaje, ultimo_mensaje_at, mensajes_sin_leer) VALUES (?, ?, ?, NOW(), 1)");
            $st->bind_param('sss', $telefono, $profileName, $body);
            $st->execute();
            $chat_id = (int)$st->insert_id;
            $st->close();
        }

        $st = $db->prepare("INSERT INTO tm_wa_mensajes (chat_id, direccion, mensaje, sid_twilio, estado) VALUES (?, 'in', ?, ?, 'delivered')");
        $st->bind_param('iss', $chat_id, $body, $sid);
        $st->execute();
        $st->close();

        return $chat_id;
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        try { if (db()->error) $msg .= ' | MySQL: ' . db()->error; } catch (Throwable $e2) {}
        $GLOBALS['WA_HUB_ERR'] = $msg;
        wa_hub_log('Error guardando entrante: ' . $msg);
        return 0;
    }
}
