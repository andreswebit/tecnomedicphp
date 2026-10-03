<?php
/**
 * TECNOMEDIC: nuevo chat de WhatsApp desde el Centro de comunicaciones.
 *  GET  ?action=buscar&q=texto   -> contactos con telefono (chats, turnos, consultas web)
 *  POST action=crear (telefono, nombre) -> crea o devuelve el chat y lo deja listo para escribir
 */
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/whatsapp.php';   // formatear_wa()

header('Content-Type: application/json; charset=utf-8');

function nc_normalizar(string $tel): string {
    // Mismo formato con el que el webhook guarda los chats: +549XXXXXXXXXX
    return str_replace('whatsapp:', '', formatear_wa($tel));
}

try {
    $db = db();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'buscar';

    if ($action === 'crear') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            exit;
        }
        $telRaw = trim($_POST['telefono'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $digits = preg_replace('/\D/', '', $telRaw);
        if (strlen($digits) < 8) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Telefono invalido. Ej: 3794123456']);
            exit;
        }
        $tel = nc_normalizar($telRaw);

        $st = $db->prepare("SELECT id, telefono, nombre_contacto FROM tm_wa_chats WHERE telefono = ? LIMIT 1");
        $st->bind_param('s', $tel);
        $st->execute();
        $chat = $st->get_result()->fetch_assoc();
        $st->close();

        if ($chat) {
            if ($nombre !== '' && trim((string)$chat['nombre_contacto']) === '') {
                $st = $db->prepare("UPDATE tm_wa_chats SET nombre_contacto = ?, ultimo_mensaje_at = ultimo_mensaje_at WHERE id = ?");
                $id = (int)$chat['id'];
                $st->bind_param('si', $nombre, $id);
                $st->execute();
                $st->close();
                $chat['nombre_contacto'] = $nombre;
            }
        } else {
            $st = $db->prepare("INSERT INTO tm_wa_chats (telefono, nombre_contacto, ultimo_mensaje, ultimo_mensaje_at, mensajes_sin_leer) VALUES (?, ?, NULL, NOW(), 0)");
            $st->bind_param('ss', $tel, $nombre);
            $st->execute();
            $chat = ['id' => (int)$st->insert_id, 'telefono' => $tel, 'nombre_contacto' => $nombre];
            $st->close();
        }
        echo json_encode(['ok' => true, 'chat' => $chat]);
        exit;
    }

    // ── buscar ──
    $q = trim($_GET['q'] ?? '');
    $like = '%' . $q . '%';
    $res = [];   // clave: telefono normalizado

    $add = function (string $tel, string $nombre, string $origen, ?int $chatId = null) use (&$res) {
        $tel = trim($tel);
        if (strlen(preg_replace('/\D/', '', $tel)) < 8) return;
        $key = nc_normalizar($tel);
        if (isset($res[$key])) {
            if ($res[$key]['nombre'] === '' && $nombre !== '') $res[$key]['nombre'] = $nombre;
            if ($chatId && !$res[$key]['chat_id']) $res[$key]['chat_id'] = $chatId;
            return;
        }
        $res[$key] = ['telefono' => $key, 'nombre' => $nombre, 'origen' => $origen, 'chat_id' => $chatId];
    };

    // Chats existentes
    try {
        $st = $db->prepare("SELECT id, telefono, nombre_contacto FROM tm_wa_chats WHERE telefono LIKE ? OR nombre_contacto LIKE ? ORDER BY ultimo_mensaje_at DESC LIMIT 15");
        $st->bind_param('ss', $like, $like);
        $st->execute();
        foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $add((string)$r['telefono'], (string)($r['nombre_contacto'] ?? ''), 'Chat', (int)$r['id']);
        $st->close();
    } catch (Throwable $e) {}

    // Pacientes con turno
    try {
        $st = $db->prepare(
            "SELECT DISTINCT nombre, apellido, telefono FROM tm_turnos
             WHERE telefono IS NOT NULL AND telefono <> ''
               AND (nombre LIKE ? OR apellido LIKE ? OR CONCAT(nombre, ' ', apellido) LIKE ? OR telefono LIKE ? OR dni LIKE ?)
             ORDER BY id DESC LIMIT 25"
        );
        $st->bind_param('sssss', $like, $like, $like, $like, $like);
        $st->execute();
        foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $add((string)$r['telefono'], trim($r['nombre'] . ' ' . $r['apellido']), 'Turno');
        $st->close();
    } catch (Throwable $e) {}

    // Consultas web
    try {
        $st = $db->prepare(
            "SELECT DISTINCT nombre, telefono FROM tm_contactos
             WHERE telefono IS NOT NULL AND telefono <> ''
               AND (nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)
             ORDER BY id DESC LIMIT 25"
        );
        $st->bind_param('sss', $like, $like, $like);
        $st->execute();
        foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $add((string)$r['telefono'], (string)($r['nombre'] ?? ''), 'Consulta web');
        $st->close();
    } catch (Throwable $e) {}

    echo json_encode(['ok' => true, 'results' => array_slice(array_values($res), 0, 40)]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
