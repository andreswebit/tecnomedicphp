<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/email_reader.php';
require_once __DIR__ . '/../includes/db.php';

// Evita que avisos de PHP/IMAP ensucien el JSON
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
register_shutdown_function(function () {
    if (function_exists('imap_errors')) { @imap_errors(); @imap_alerts(); }
});

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';
$cuenta = trim($_GET['cuenta'] ?? 'contacto');
$folder = trim($_GET['folder'] ?? 'INBOX');
if ($folder === '') $folder = 'INBOX';

$folderUpper = mb_strtoupper($folder);
$standardFolderAliases = ['INBOX', 'DRAFTS', 'SENT', 'SPAM', 'TRASH', 'DELETED', 'JUNK'];
$folderForResolve = $folder;
if (in_array($folderUpper, $standardFolderAliases, true)) {
    $folderForResolve = map_folder_alias_ferozo($folderUpper);
}

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

        // Ferozo (Dovecot) usa "." como separador y exige el prefijo INBOX.
        $folder_name = str_replace('/', '.', $folder_name);
        if (stripos($folder_name, 'INBOX.') !== 0) $folder_name = 'INBOX.' . $folder_name;

        $r = imap_create_folder_ferozo($cuenta, $folder_name);
        if (function_exists('imap_errors')) { @imap_errors(); @imap_alerts(); }
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
        
        // Si la sincronización falló, reportamos el error
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'Error al sincronizar IMAP']);
            exit;
        }
        
        $resolvedFolder = imap_resolve_folder_name_ferozo($cuenta, $folderForResolve);

        // Luego devolvemos lista actualizada del folder.
        $list = _emails_list(db(), $cuenta, $resolvedFolder, 50);
        echo json_encode(['ok' => true, 'sync' => $r, 'folder' => $resolvedFolder, 'emails' => $list]);
        exit;
    }

    if ($action === 'mark') {
        // Marca leido / no leido (cache local tm_emails)
        $id   = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $flag = trim($_POST['flag'] ?? $_GET['flag'] ?? '');
        if ($id <= 0 || !in_array($flag, ['read', 'unread'], true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Parametros invalidos']);
            exit;
        }
        $v = $flag === 'read' ? 1 : 0;
        $st = db()->prepare("UPDATE tm_emails SET leido = ? WHERE id = ? AND cuenta = ?");
        $st->bind_param('iis', $v, $id, $cuenta);
        $st->execute();
        $st->close();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_draft') {
        // Guarda el borrador en Borradores del servidor (IMAP) y actualiza la cache local.
        $draftId = (int)($_POST['draft_id'] ?? 0);
        $to      = trim($_POST['to'] ?? '');
        $cc      = trim($_POST['cc'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $txt     = trim($_POST['message'] ?? '');
        $html    = ($_POST['html'] ?? '0') === '1' ? trim($_POST['message_html'] ?? '') : '';
        $from    = trim($_POST['from'] ?? '');
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $from = env('IMAP_USER_CONTACTO', 'contacto@tecnomedic.com.ar');
        }
        $draftsFolder = imap_resolve_folder_name_ferozo($cuenta, map_folder_alias_ferozo('DRAFTS'));

        // Si reeditamos un borrador, quitamos la version anterior
        if ($draftId > 0) {
            $st = db()->prepare("SELECT uid_imap FROM tm_emails WHERE id = ? AND cuenta = ? AND folder = ? LIMIT 1");
            $st->bind_param('iss', $draftId, $cuenta, $draftsFolder);
            $st->execute();
            $old = $st->get_result()->fetch_assoc();
            $st->close();
            if ($old) {
                if ((int)$old['uid_imap'] > 0) {
                    $r = imap_open_ferozo($cuenta, imap_mailbox_prefix_ferozo() . $draftsFolder);
                    if ($r['ok']) {
                        @imap_delete($r['inbox'], (string)(int)$old['uid_imap'], FT_UID);
                        @imap_expunge($r['inbox']);
                        @imap_close($r['inbox']);
                    }
                }
                $st = db()->prepare("DELETE FROM tm_emails WHERE id = ? AND cuenta = ?");
                $st->bind_param('is', $draftId, $cuenta);
                $st->execute();
                $st->close();
            }
        }

        $raw = imap_build_message_ferozo($from, $to, $cc, $subject !== '' ? $subject : '(Sin Asunto)', $txt, $html);
        $r = imap_append_ferozo($cuenta, 'DRAFTS', $raw, '\\Draft \\Seen');
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'No se pudo guardar el borrador en IMAP']);
            exit;
        }

        // Traemos el nuevo borrador a la cache y buscamos su id
        sync_emails_ferozo($cuenta, 'DRAFTS');
        $newId = 0;
        $subj = $subject !== '' ? $subject : '(Sin Asunto)';
        $st = db()->prepare("SELECT id FROM tm_emails WHERE cuenta = ? AND folder = ? AND asunto = ? ORDER BY id DESC LIMIT 1");
        $st->bind_param('sss', $cuenta, $draftsFolder, $subj);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        if ($row) $newId = (int)$row['id'];

        echo json_encode(['ok' => true, 'id' => $newId]);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'id invalido (metodo ' . $_SERVER['REQUEST_METHOD'] . ')']);
            exit;
        }
        $st = db()->prepare("SELECT folder, uid_imap FROM tm_emails WHERE id = ? AND cuenta = ? LIMIT 1");
        $st->bind_param('is', $id, $cuenta);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        if (!$row) {
            echo json_encode(['ok' => false, 'error' => 'Correo no encontrado']);
            exit;
        }

        $warning = null;
        if ((int)$row['uid_imap'] > 0) {
            $r = imap_delete_message_ferozo($cuenta, $row['folder'], (int)$row['uid_imap']);
            if (!($r['ok'] ?? false)) $warning = $r['error'] ?? 'IMAP';
        }
        $st = db()->prepare("DELETE FROM tm_emails WHERE id = ? AND cuenta = ?");
        $st->bind_param('is', $id, $cuenta);
        $st->execute();
        $st->close();

        $out = ['ok' => true];
        if ($warning) $out['warning'] = 'Se quito de la bandeja, pero IMAP respondio: ' . $warning;
        echo json_encode($out);
        exit;
    }

if ($action === 'move') {
        // Mueve un email (por id de tm_emails) desde su carpeta actual a folder_dest (alias o nombre real)
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $folder_dest = trim($_POST['folder_dest'] ?? $_GET['folder_dest'] ?? '');

        if ($id <= 0 || $folder_dest === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Parametros invalidos']);
            exit;
        }

        $st = db()->prepare("SELECT cuenta, folder, uid_imap FROM tm_emails WHERE id = ? AND cuenta = ? LIMIT 1");
        $st->bind_param('is', $id, $cuenta);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$row) {
            echo json_encode(['ok' => false, 'error' => 'Correo no encontrado']);
            exit;
        }

        $srcFolder = (string)($row['folder'] ?? '');
        $uidImap = (int)($row['uid_imap'] ?? 0);
        if ($srcFolder === '' || $uidImap <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Correo sin folder/uid_imap válido']);
            exit;
        }

        // Resolver destino (alias -> nombre real del servidor)
        $folderDestUpper = mb_strtoupper(trim($folder_dest));
        $folderDestForResolve = $folder_dest;
        if (in_array($folderDestUpper, $standardFolderAliases, true)) {
            $folderDestForResolve = map_folder_alias_ferozo($folderDestUpper);
        }
        $destResolved = imap_resolve_folder_name_ferozo($cuenta, $folderDestForResolve);

        $r = imap_move_message_ferozo($cuenta, $srcFolder, $destResolved, $uidImap);
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'IMAP']);
            exit;
        }

        $st = db()->prepare("UPDATE tm_emails SET folder = ? WHERE id = ? AND cuenta = ? LIMIT 1");
        $st->bind_param('sis', $destResolved, $id, $cuenta);
        $st->execute();
        $st->close();

        echo json_encode(['ok' => true, 'folder' => $destResolved]);
        exit;
    }


    // if ($action === 'move') {
    //     // Mueve un email (por id de tm_emails) desde su carpeta actual a folder_dest (alias o nombre real)
    //     $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    //     $folder_dest = trim($_POST['folder_dest'] ?? $_GET['folder_dest'] ?? '');

    //     if ($id <= 0 || $folder_dest === '') {
    //         http_response_code(400);
    //         echo json_encode(['ok' => false, 'error' => 'Parametros invalidos']);
    //         exit;
    //     }

    //     $st = db()->prepare("SELECT cuenta, folder, uid_imap FROM tm_emails WHERE id = ? AND cuenta = ? LIMIT 1");
    //     $st->bind_param('is', $id, $cuenta);
    //     $st->execute();
    //     $row = $st->get_result()->fetch_assoc();
    //     $st->close();

    //     if (!$row) {
    //         echo json_encode(['ok' => false, 'error' => 'Correo no encontrado']);
    //         exit;
    //     }

    //     $srcFolder = (string)($row['folder'] ?? '');
    //     $uidImap = (int)($row['uid_imap'] ?? 0);
    //     if ($srcFolder === '' || $uidImap <= 0) {
    //         http_response_code(400);
    //         echo json_encode(['ok' => false, 'error' => 'Correo sin folder/uid_imap válido']);
    //         exit;
    //     }

    //     // Resolver destino (alias -> nombre real del servidor)
    //     $folderDestUpper = mb_strtoupper(trim($folder_dest));
    //     $folderDestForResolve = $folder_dest;
    //     if (in_array($folderDestUpper, $standardFolderAliases, true)) {
    //         $folderDestForResolve = map_folder_alias_ferozo($folderDestUpper);
    //     }
    //     $destResolved = imap_resolve_folder_name_ferozo($cuenta, $folderDestForResolve);

    //     $r = imap_move_message_ferozo($cuenta, $srcFolder, $destResolved, $uidImap);
    //     if (!($r['ok'] ?? false)) {
    //         http_response_code(500);
    //         echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'IMAP']);
    //         exit;
    //     }

    //     $st = db()->prepare("UPDATE tm_emails SET folder = ? WHERE id = ? AND cuenta = ? LIMIT 1");
    //     $st->bind_param('sis', $destResolved, $id, $cuenta);
    //     $st->execute();
    //     $st->close();

    //     echo json_encode(['ok' => true, 'folder' => $destResolved]);
    //     exit;
    // }

    if ($action === 'rename_folder') {
        $old = trim($_POST['folder'] ?? '');
        $new = trim($_POST['new_name'] ?? '');
        $new = preg_replace('/^INBOX[.\/]/i', '', $new);
        if ($old === '' || !preg_match('/^[A-Za-z0-9._ -]{1,100}$/', $new)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Nombre invalido. Usa letras, numeros, espacio, punto, guion.']);
            exit;
        }
        $r = imap_rename_folder_ferozo($cuenta, $old, $new);
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'desconocido']);
            exit;
        }
        $st = db()->prepare("UPDATE tm_emails SET folder = ? WHERE cuenta = ? AND folder = ?");
        $st->bind_param('sss', $r['folder'], $cuenta, $old);
        $st->execute();
        $st->close();
        echo json_encode(['ok' => true, 'folder' => $r['folder']]);
        exit;
    }

    if ($action === 'delete_folder') {
        $name = trim($_POST['folder'] ?? '');
        if ($name === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Carpeta vacia']);
            exit;
        }
        $r = imap_delete_folder_ferozo($cuenta, $name);
        if (!($r['ok'] ?? false)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $r['error'] ?? 'desconocido']);
            exit;
        }
        $st = db()->prepare("DELETE FROM tm_emails WHERE cuenta = ? AND folder = ?");
        $st->bind_param('ss', $cuenta, $name);
        $st->execute();
        $st->close();
        echo json_encode(['ok' => true]);
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

    // Importante: resolvemos el nombre real del folder del servidor.
    // Así evitamos que 'SENT' o 'TRASH' no coincidan con el nombre real (ej: "Sent Items").
    $resolvedFolder = imap_resolve_folder_name_ferozo($cuenta, $folderForResolve);

    $list = _emails_list(db(), $cuenta, $resolvedFolder, 50);
    echo json_encode(['ok' => true, 'emails' => $list, 'folder' => $resolvedFolder]);

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
