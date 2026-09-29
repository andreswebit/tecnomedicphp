<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/email_reader.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';
$cuenta = trim($_GET['cuenta'] ?? 'contacto');
$folder = trim($_GET['folder'] ?? 'INBOX');

if (!in_array($cuenta, ['contacto', 'noreply'], true)) {
    $cuenta = 'contacto';
}

try {
    if ($action === 'folders') {
        $r = imap_list_folders_ferozo($cuenta);
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'desconocido']);
            exit;
        }
        echo json_encode(['ok' => true, 'folders' => $r['folders'] ?? []]);
        exit;
    }

    if ($action === 'create_folder') {
        $folder_name = trim($_POST['folder_name'] ?? $_GET['folder_name'] ?? '');

        // Validación simple: evitamos caracteres raros/sobrantes.
        // Permitimos / . _ - y letras/números. (IMAP puede devolver nombres con otros chars, pero para creación acotamos.)
        if (!preg_match('/^[A-Za-z0-9._\/-]{1,120}$/', $folder_name)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Nombre de carpeta inválido']);
            exit;
        }

        $r = imap_create_folder_ferozo($cuenta, $folder_name);
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'desconocido']);
            exit;
        }

        echo json_encode(['ok' => true, 'folder' => $r['folder'] ?? $folder_name]);
        exit;
    }

    if ($action === 'sync') {
        $r = sync_emails_ferozo($cuenta, $folder);
        $resolvedFolder = imap_resolve_folder_name_ferozo($cuenta, $folder);

        // Luego devolvemos lista actualizada del folder.
        $list = _emails_list(db(), $cuenta, $resolvedFolder, 50);
        echo json_encode(['ok' => true, 'sync' => $r, 'folder' => $resolvedFolder, 'emails' => $list]);
        exit;
    }

    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido');

        $st = db()->prepare("SELECT * FROM tm_emails WHERE id = ? LIMIT 1");
        $st->bind_param('i', $id);
        $st->execute();
        $m = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$m) {
            echo json_encode(['ok' => false, 'error' => 'No encontrado']);
            exit;
        }

        // Marcar como leído al abrir
        $st2 = db()->prepare("UPDATE tm_emails SET leido = 1 WHERE id = ?");
        $st2->bind_param('i', $id);
        $st2->execute();
        $st2->close();

        echo json_encode(['ok' => true, 'email' => $m]);
        exit;
    }

    // default: list
    // Si no viene folder, usa INBOX.
    if ($folder === '') $folder = 'INBOX';

    $list = _emails_list(db(), $cuenta, $folder, 50);
    echo json_encode(['ok' => true, 'emails' => $list]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

function _emails_list(mysqli $db, string $cuenta, string $folder, int $limit): array {
    $st = $db->prepare(
        "SELECT id, cuenta, folder, uid_imap, remitente_nombre, remitente_email, destinatario, asunto, cuerpo_txt, cuerpo_html,
                tiene_adjuntos, leido, destacado, creado_en
         FROM tm_emails
         WHERE cuenta = ? AND folder = ?
         ORDER BY leido ASC, destacado DESC, creado_en DESC
         LIMIT ?"
    );
    $st->bind_param('ssi', $cuenta, $folder, $limit);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    foreach ($rows as &$r) {
        $txt = $r['cuerpo_txt'] ?? '';
        $html = $r['cuerpo_html'] ?? '';
        $previewSource = trim($txt) !== '' ? $txt : strip_tags($html);
        $r['preview'] = mb_substr(preg_replace('/\s+/', ' ', strip_tags($previewSource)), 0, 120);
        $r['remitente_display'] = ($r['remitente_nombre'] ?: $r['remitente_email']);
    }

    return $rows;
}
