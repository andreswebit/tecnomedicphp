<?php
/**
 * TECNOMEDIC: importa a la tabla del Hub el historial de WhatsApp que ya existe en Twilio.
 * Uso: abrir una vez como admin: /api/wa_importar_twilio.php   (opcional ?max=2000)
 * Es seguro repetirlo: omite los mensajes con SID ya guardado.
 */
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/wa_hub.php';

header('Content-Type: application/json; charset=utf-8');
set_time_limit(120);

if (!TWILIO_SID || !TWILIO_TOKEN) {
    echo json_encode(['ok' => false, 'error' => 'Twilio no configurado']);
    exit;
}

$max = max(50, min(10000, (int)($_GET['max'] ?? 2000)));
$tz  = new DateTimeZone('America/Argentina/Buenos_Aires');

try {
    wa_hub_tablas();
    $db = db();

    // 1) Descargar mensajes de Twilio
    $all = [];
    $uri = '/2010-04-01/Accounts/' . TWILIO_SID . '/Messages.json?PageSize=500';
    while ($uri && count($all) < $max) {
        $ch = curl_init('https://api.twilio.com' . $uri);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => TWILIO_SID . ':' . TWILIO_TOKEN, CURLOPT_TIMEOUT => 30]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) throw new Exception("Twilio HTTP $code: " . substr((string)$resp, 0, 200));
        $j = json_decode($resp, true);
        foreach (($j['messages'] ?? []) as $m) {
            if (strpos((string)$m['from'], 'whatsapp:') === 0 || strpos((string)$m['to'], 'whatsapp:') === 0) $all[] = $m;
        }
        $uri = $j['next_page_uri'] ?? null;
    }

    // 2) Orden cronologico
    usort($all, fn($a, $b) => strtotime($a['date_created']) <=> strtotime($b['date_created']));

    $nuevos = 0; $omitidos = 0; $chatsNuevos = 0;
    $chatIds = [];
    foreach ($all as $m) {
        $sid = (string)$m['sid'];
        $inbound = (strpos((string)$m['direction'], 'inbound') === 0);
        $tel = str_replace('whatsapp:', '', $inbound ? $m['from'] : $m['to']);
        $body = (string)($m['body'] ?? '');
        if ($tel === '' || $body === '') { $omitidos++; continue; }

        $st = $db->prepare("SELECT id FROM tm_wa_mensajes WHERE sid_twilio = ? LIMIT 1");
        $st->bind_param('s', $sid);
        $st->execute();
        $ex = $st->get_result()->fetch_assoc();
        $st->close();
        if ($ex) { $omitidos++; continue; }

        $fecha = (new DateTime($m['date_created']))->setTimezone($tz)->format('Y-m-d H:i:s');

        if (!isset($chatIds[$tel])) {
            $st = $db->prepare("SELECT id FROM tm_wa_chats WHERE telefono = ? LIMIT 1");
            $st->bind_param('s', $tel);
            $st->execute();
            $c = $st->get_result()->fetch_assoc();
            $st->close();
            if ($c) {
                $chatIds[$tel] = (int)$c['id'];
            } else {
                $nom = '';
                $st = $db->prepare("INSERT INTO tm_wa_chats (telefono, nombre_contacto, ultimo_mensaje, ultimo_mensaje_at, mensajes_sin_leer) VALUES (?,?,?,?,0)");
                $st->bind_param('ssss', $tel, $nom, $body, $fecha);
                $st->execute();
                $chatIds[$tel] = (int)$st->insert_id;
                $st->close();
                $chatsNuevos++;
            }
        }
        $cid = $chatIds[$tel];
        $dir = $inbound ? 'in' : 'out';
        $est = $inbound ? 'delivered' : ((string)($m['status'] ?? 'sent'));

        $st = $db->prepare("INSERT INTO tm_wa_mensajes (chat_id, direccion, mensaje, sid_twilio, estado, creado_en) VALUES (?,?,?,?,?,?)");
        $st->bind_param('isssss', $cid, $dir, $body, $sid, $est, $fecha);
        $st->execute();
        $st->close();

        // Ultimo mensaje del chat = el mas reciente
        $st = $db->prepare("UPDATE tm_wa_chats SET ultimo_mensaje = ?, ultimo_mensaje_at = ? WHERE id = ? AND (ultimo_mensaje_at IS NULL OR ultimo_mensaje_at <= ?)");
        $st->bind_param('ssis', $body, $fecha, $cid, $fecha);
        $st->execute();
        $st->close();
        $nuevos++;
    }

    echo json_encode(['ok' => true, 'leidos_en_twilio' => count($all), 'importados' => $nuevos, 'omitidos' => $omitidos, 'chats_nuevos' => $chatsNuevos]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
