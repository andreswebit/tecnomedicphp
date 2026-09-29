<?php
/**
 * TECNOMEDIC — Helper para lectura de correos vía IMAP (Ferozo)
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/env_loader.php';

function imap_mailbox_prefix_ferozo(): string {
    $server_imap = env('IMAP_HOST', 'c1452348.ferozo.com');
    $port_imap = env('IMAP_PORT', '993');
    return "{{$server_imap}:{$port_imap}/imap/ssl}";
}

function imap_open_ferozo(string $cuenta_name, string $mailbox): array {
    $server_imap = env('IMAP_HOST', 'c1452348.ferozo.com');
    $port_imap   = env('IMAP_PORT', '993');

    if ($cuenta_name === 'contacto') {
        $username = env('IMAP_USER_CONTACTO', 'contacto@tecnomedic.com.ar');
        $password = env('IMAP_PASS_CONTACTO', '');
    } else {
        $username = env('IMAP_USER_NOREPLY', 'noreply@tecnomedic.com.ar');
        $password = env('IMAP_PASS_NOREPLY', '');
    }

    if (empty($password)) {
        return ['ok' => false, 'error' => "Credenciales IMAP no configuradas para $cuenta_name"];
    }

    $inbox = @imap_open($mailbox, $username, $password);
    if (!$inbox) {
        return ['ok' => false, 'error' => 'No se pudo conectar al servidor IMAP: ' . imap_last_error()];
    }

    return ['ok' => true, 'inbox' => $inbox, 'username' => $username];
}

function imap_strip_prefix(string $mailboxPrefix, string $mailboxFull): string {
    // Ej: {host:993/imap/ssl}INBOX -> INBOX
    $pos = strpos($mailboxFull, '}');
    if ($pos !== false) {
        return substr($mailboxFull, $pos + 1);
    }
    return $mailboxFull;
}

function imap_list_folders_ferozo(string $cuenta_name = 'contacto'): array {
    $mailboxPrefix = imap_mailbox_prefix_ferozo();

    // Conectamos a INBOX para poder listar
    $r = imap_open_ferozo($cuenta_name, $mailboxPrefix . 'INBOX');
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }

    $inbox = $r['inbox'];
    $raw = @imap_list($inbox, $mailboxPrefix, '*');
    @imap_close($inbox);

    if (!$raw || !is_array($raw)) {
        return ['ok' => true, 'folders' => []];
    }

    $folders = [];
    foreach ($raw as $f) {
        $name = imap_strip_prefix($mailboxPrefix, (string)$f);
        $name = trim($name);
        if ($name !== '') $folders[] = $name;
    }

    $folders = array_values(array_unique($folders));
    usort($folders, fn($a, $b) => strcasecmp($a, $b));

    return ['ok' => true, 'folders' => $folders];
}

function imap_resolve_folder_name_ferozo(string $cuenta_name, string $requestedFolder): string {
    // Caso-insensibilidad contra carpetas reales del servidor.
    // Si no existe, devolvemos lo que pidieron.
    $mailboxPrefix = imap_mailbox_prefix_ferozo();

    $r = imap_open_ferozo($cuenta_name, $mailboxPrefix . 'INBOX');
    if (!$r['ok']) {
        return $requestedFolder;
    }

    $inbox = $r['inbox'];
    $availableRaw = @imap_list($inbox, $mailboxPrefix, '*');
    @imap_close($inbox);

    if (!$availableRaw || !is_array($availableRaw)) {
        return $requestedFolder;
    }

    $requestedLower = mb_strtolower(trim($requestedFolder));
    foreach ($availableRaw as $f) {
        $name = imap_strip_prefix($mailboxPrefix, (string)$f);
        if (mb_strtolower(trim($name)) === $requestedLower) {
            return $name;
        }
    }

    // fallback: algunas cuentas devuelven sin respetar mayúsculas/minúsculas
    return $requestedFolder;
}

function imap_create_folder_ferozo(string $cuenta_name = 'contacto', string $folder_name = ''): array {
    $folder_name = trim($folder_name);
    if ($folder_name === '') {
        return ['ok' => false, 'error' => 'folder_name vacío'];
    }

    $mailboxPrefix = imap_mailbox_prefix_ferozo();

    // Conectamos a INBOX
    $r = imap_open_ferozo($cuenta_name, $mailboxPrefix . 'INBOX');
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }

    $inbox = $r['inbox'];

    $fullBox = $mailboxPrefix . $folder_name;
    $ok = @imap_createmailbox($inbox, $fullBox);
    @imap_close($inbox);

    if (!$ok) {
        return ['ok' => false, 'error' => 'No se pudo crear la carpeta en IMAP: ' . (imap_last_error() ?: 'desconocido')];
    }

    // Intentamos resolver el nombre normalizado por el servidor
    $foldersRes = imap_list_folders_ferozo($cuenta_name);
    if (!empty($foldersRes['ok']) && !empty($foldersRes['folders']) && is_array($foldersRes['folders'])) {
        $targetLower = mb_strtolower(trim($folder_name));
        foreach ($foldersRes['folders'] as $f) {
            if (mb_strtolower(trim($f)) === $targetLower) {
                return ['ok' => true, 'folder' => $f];
            }
        }
    }

    return ['ok' => true, 'folder' => $folder_name];
}

function sync_emails_ferozo(string $cuenta_name = 'contacto', string $folder = 'INBOX'): array {
    // Falla controlada si IMAP no está habilitado
    if (!function_exists('imap_open')) {
        return ['ok' => false, 'error' => 'La extensión PHP IMAP no está habilitada (falta imap_open).'];
    }

    $folder = trim($folder) !== '' ? trim($folder) : 'INBOX';
    $resolvedFolder = imap_resolve_folder_name_ferozo($cuenta_name, $folder);
    $mailboxPrefix = imap_mailbox_prefix_ferozo();

    $r = imap_open_ferozo($cuenta_name, $mailboxPrefix . $resolvedFolder);
    if (!$r['ok']) {
        return $r;
    }

    $inbox = $r['inbox'];
    $destinatarioUser = $r['username'] ?? $cuenta_name;

    $emails = @imap_search($inbox, 'ALL');
    $procesados = 0;

    if ($emails) {
        rsort($emails);
        $emails = array_slice($emails, 0, 50);

        foreach ($emails as $email_number) {
            $uid = imap_uid($inbox, $email_number);

            // Chequear si el UID ya está en caché DB (por cuenta + folder)
            $db = db();
            $stmt = $db->prepare(
                "SELECT id FROM tm_emails WHERE cuenta = ? AND uid_imap = ? AND folder = ? LIMIT 1"
            );
            $stmt->bind_param('sis', $cuenta_name, $uid, $resolvedFolder);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                continue;
            }
            $stmt->close();

            $headerInfo = @imap_headerinfo($inbox, $email_number);

            $asunto = isset($headerInfo->subject) ? mb_decode_mimeheader($headerInfo->subject) : '(Sin Asunto)';
            $remitente_email = isset($headerInfo->from[0]->mailbox) ? $headerInfo->from[0]->mailbox . '@' . $headerInfo->from[0]->host : '';

            $remitente_nombre = '';
            if (isset($headerInfo->from[0]->personal)) {
                $remitente_nombre = mb_decode_mimeheader($headerInfo->from[0]->personal);
            }

            $fecha = isset($headerInfo->date) ? date('Y-m-d H:i:s', strtotime($headerInfo->date)) : date('Y-m-d H:i:s');

            // Obtener cuerpos
            $cuerpo_txt = '';
            $cuerpo_html = '';

            $structure = @imap_fetchstructure($inbox, $email_number);
            if (isset($structure->parts) && is_array($structure->parts)) {
                foreach ($structure->parts as $part_number => $part) {
                    $partNo = $part_number + 1;
                    $data = @imap_fetchbody($inbox, $email_number, $partNo);
                    $data = decode_imap_body($data, $part->encoding);

                    if ($part->subtype == 'PLAIN' && empty($cuerpo_txt)) {
                        $cuerpo_txt = $data;
                    } elseif ($part->subtype == 'HTML' && empty($cuerpo_html)) {
                        $cuerpo_html = $data;
                    }
                }
            } else {
                $data = @imap_fetchbody($inbox, $email_number, 1);
                $encoding = isset($structure->encoding) ? $structure->encoding : 0;
                $data = decode_imap_body($data, $encoding);
                if ($structure->subtype == 'HTML') {
                    $cuerpo_html = $data;
                } else {
                    $cuerpo_txt = $data;
                }
            }

            if (empty($cuerpo_html) && !empty($cuerpo_txt)) {
                $cuerpo_html = nl2br(htmlspecialchars($cuerpo_txt));
            }

            $stmtInsert = $db->prepare(
                "INSERT INTO tm_emails
                    (cuenta, uid_imap, folder, remitente_nombre, remitente_email, destinatario, asunto, cuerpo_txt, cuerpo_html, leido, creado_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)"
            );

            // Tipos: cuenta(s), uid(i), folder(s), resto (s)
            $stmtInsert->bind_param(
                'sissssssss',
                $cuenta_name,
                $uid,
                $resolvedFolder,
                $remitente_nombre,
                $remitente_email,
                $destinatarioUser,
                $asunto,
                $cuerpo_txt,
                $cuerpo_html,
                $fecha
            );

            $stmtInsert->execute();
            $stmtInsert->close();

            $procesados++;
        }
    }

    @imap_close($inbox);

    return ['ok' => true, 'nuevos' => $procesados, 'folder' => $resolvedFolder];
}

// Helpers para decoding
function decode_imap_body($data, $encoding) {
    if ($encoding == 3) {
        $data = base64_decode($data);
    } elseif ($encoding == 4) {
        $data = quoted_printable_decode($data);
    }

    $data = mb_convert_encoding($data, 'UTF-8', 'auto');
    return trim($data);
}
