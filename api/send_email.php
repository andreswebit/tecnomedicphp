<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/email_reader.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed (recibido: ' . $_SERVER['REQUEST_METHOD'] . ')']);
    exit;
}

/** Devuelve lista de emails validos desde "a@b.com, Nombre <c@d.com>; e@f.com". */
function parse_addr_list(string $s): array {
    $out = [];
    foreach (preg_split('/[,;]+/', $s) as $part) {
        $part = trim($part);
        if ($part === '') continue;
        if (preg_match('/<([^>]+)>/', $part, $m)) $part = trim($m[1]);
        if (filter_var($part, FILTER_VALIDATE_EMAIL)) $out[strtolower($part)] = $part;
    }
    return array_values($out);
}

$toRaw   = trim($_POST['to'] ?? '');
$ccRaw   = trim($_POST['cc'] ?? '');
$bccRaw  = trim($_POST['bcc'] ?? '');
$replyTo = trim($_POST['reply_to'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$text    = trim($_POST['message'] ?? '');
$isHtml  = ($_POST['html'] ?? '0') === '1';
$htmlIn  = trim($_POST['message_html'] ?? '');
$prio    = (int)($_POST['priority'] ?? 3);
$receipt = ($_POST['receipt'] ?? '0') === '1';

$toList  = parse_addr_list($toRaw);
$ccList  = parse_addr_list($ccRaw);
$bccList = parse_addr_list($bccRaw);

if (!$toList) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Destinatario invalido o vacio']);
    exit;
}
if ($subject === '') $subject = '(Sin asunto)';

// Cuerpo
$html = '';
if ($isHtml && $htmlIn !== '') {
    $html = $htmlIn;
    if ($text === '') $text = trim(html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>#i', "\n", $htmlIn))));
}
if ($text === '' && $html === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El mensaje esta vacio']);
    exit;
}
if ($text === '') $text = '(sin texto)';

// Adjuntos: attachments[] (multipart)
$adjuntos = [];
if (!empty($_FILES['attachments']['name'])) {
    $names = (array)$_FILES['attachments']['name'];
    $tmps  = (array)$_FILES['attachments']['tmp_name'];
    $errs  = (array)$_FILES['attachments']['error'];
    $sizes = (array)$_FILES['attachments']['size'];

    $maxFiles = 20;
    $maxTotal = 50 * 1024 * 1024;
    $total = 0;
    $allowed = ['pdf','png','jpg','jpeg','gif','webp','doc','docx','xls','xlsx','ppt','pptx','txt','csv','zip','rar','odt','ods'];

    $storageDir = __DIR__ . '/../storage/uploads_email_tmp';
    if (!is_dir($storageDir)) mkdir($storageDir, 0775, true);

    foreach ($names as $i => $orig) {
        if (count($adjuntos) >= $maxFiles) break;
        if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
        if (!is_uploaded_file($tmps[$i] ?? '')) continue;
        $size = (int)($sizes[$i] ?? 0);
        if ($total + $size > $maxTotal) continue;

        $orig = trim((string)$orig);
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if ($ext && !in_array($ext, $allowed, true)) continue;

        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
        $dest = $storageDir . '/' . date('Ymd_His') . '_' . $i . '_' . $safe . ($ext ? '.' . $ext : '');
        if (!move_uploaded_file($tmps[$i], $dest)) continue;

        $adjuntos[] = $dest;
        $total += $size;
    }
}

try {
    $replyOne = $replyTo !== '' ? (parse_addr_list($replyTo)[0] ?? '') : '';
    $opts = [
        'cc' => $ccList, 'bcc' => $bccList, 'reply_to' => $replyOne,
        'priority' => $prio, 'receipt' => $receipt,
    ];

    $ok = enviar_email(implode(',', $toList), $subject, $text, $html, $adjuntos, $opts);
    $mime = function_exists('enviar_email_ultimo_mime') ? enviar_email_ultimo_mime() : '';

    foreach ($adjuntos as $p) { if (is_file($p)) @unlink($p); }

    $res = ['ok' => (bool)$ok];

    // Copia en Enviados del servidor, para verla en la webmail de Ferozo
    if ($ok && ($_POST['save_sent_in'] ?? 'SENT') !== '' && $mime !== '') {
        $cuenta = in_array($_POST['cuenta'] ?? 'contacto', ['contacto', 'noreply'], true) ? $_POST['cuenta'] : 'contacto';
        $a = imap_append_ferozo($cuenta, 'SENT', $mime, '\\Seen');
        if (!($a['ok'] ?? false)) $res['warning'] = 'Enviado, pero no se copio a Enviados: ' . ($a['error'] ?? '');
    }
    echo json_encode($res);
} catch (Throwable $e) {
    foreach ($adjuntos as $p) { if (is_file($p)) @unlink($p); }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
