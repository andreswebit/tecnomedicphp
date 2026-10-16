<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';

function account_table_exists(mysqli $db): bool {
    try {
        $r = $db->query("SHOW TABLES LIKE 'tm_mail_accounts' LIMIT 1");
        return (bool)($r && $r->num_rows > 0);
    } catch (Throwable $e) {
        return false;
    }
}

function normalize_slug(string $s): string {
    $s = strtolower(trim($s));
    // Permitimos letras/números, '_' y '-'
    $s = preg_replace('/[^a-z0-9_-]/', '', $s);
    return $s;
}

function is_valid_account_key(string $k): bool {
    // ^[a-z0-9][a-z0-9_-]{1,49}$
    return (bool)preg_match('/^[a-z0-9][a-z0-9_-]{1,49}$/', $k);
}

function get_account_by_key(mysqli $db, string $account_key): ?array {
    $st = $db->prepare(
        "SELECT * FROM tm_mail_accounts WHERE account_key = ? LIMIT 1"
    );
    $st->bind_param('s', $account_key);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ?: null;
}

function respond(bool $ok, array $data = [], int $httpCode = 200): void {
    if (!$ok) http_response_code($httpCode);
    echo json_encode(array_merge(['ok' => $ok], $data));
    exit;
}

$db = db();
$hasTable = account_table_exists($db);
if (!$hasTable) {
    if ($action === 'list') {
        respond(true, ['accounts' => []]);
    }
    respond(false, ['error' => 'La tabla tm_mail_accounts no existe. Ejecutá la migración.'], 500);
}

try {
    if ($action === 'list') {
        $st = $db->prepare(
            "SELECT id, account_key, display_name, active,
                    smtp_from_email, smtp_from_name, smtp_reply_to_email, smtp_reply_to_name
             FROM tm_mail_accounts
             WHERE active = 1
             ORDER BY display_name ASC"
        );
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
        $st->close();

        // Para inicializar el webmail, también devolvemos hosts/puertos/user sin passwords.
        $keys = array_values(array_map(fn($r) => (string)$r['account_key'], $rows));
        if ($keys) {
            $inPlace = implode(',', array_fill(0, count($keys), '?'));
            $types = str_repeat('s', count($keys));
            $sql = "SELECT account_key,
                           imap_host, imap_port, imap_username,
                           smtp_host, smtp_port, smtp_username
                    FROM tm_mail_accounts
                    WHERE account_key IN ($inPlace)";
            $st2 = $db->prepare($sql);
            $st2->bind_param($types, ...$keys);
            $st2->execute();
            $extra = $st2->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
            $st2->close();

            $byKey = [];
            foreach ($extra as $e) {
                $byKey[(string)$e['account_key']] = $e;
            }

            foreach ($rows as &$r) {
                $k = (string)$r['account_key'];
                if (isset($byKey[$k])) {
                    $r['imap_host'] = $byKey[$k]['imap_host'] ?? null;
                    $r['imap_port'] = isset($byKey[$k]['imap_port']) ? (int)$byKey[$k]['imap_port'] : null;
                    $r['imap_username'] = $byKey[$k]['imap_username'] ?? null;
                    $r['smtp_host'] = $byKey[$k]['smtp_host'] ?? null;
                    $r['smtp_port'] = isset($byKey[$k]['smtp_port']) ? (int)$byKey[$k]['smtp_port'] : null;
                    $r['smtp_username'] = $byKey[$k]['smtp_username'] ?? null;
                }
            }
            unset($r);
        }

        respond(true, ['accounts' => $rows]);
    }

    if ($action === 'get') {
        $account_key = trim((string)($_GET['account_key'] ?? ''));
        if ($account_key === '') respond(false, ['error' => 'account_key requerido'], 400);

        $acc = get_account_by_key($db, $account_key);
        if (!$acc) respond(false, ['error' => 'Cuenta no encontrada'], 404);

        respond(true, [
            'account' => [
                'id' => (int)$acc['id'],
                'account_key' => $acc['account_key'],
                'display_name' => $acc['display_name'],
                'active' => (int)$acc['active'],

                'imap_host' => $acc['imap_host'],
                'imap_port' => (int)$acc['imap_port'],
                'imap_username' => $acc['imap_username'],

                'smtp_host' => $acc['smtp_host'],
                'smtp_port' => (int)$acc['smtp_port'],
                'smtp_username' => $acc['smtp_username'],

                'smtp_from_email' => $acc['smtp_from_email'],
                'smtp_from_name' => $acc['smtp_from_name'],

                'smtp_reply_to_email' => $acc['smtp_reply_to_email'],
                'smtp_reply_to_name' => $acc['smtp_reply_to_name'],
            ]
        ]);
    }

    if (!in_array($action, ['create', 'update', 'delete'], true)) {
        respond(false, ['error' => 'Acción inválida'], 400);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(false, ['error' => 'Método inválido'], 405);
    }

    if ($action === 'create') {
        $account_key = normalize_slug((string)($_POST['account_key'] ?? ''));
        if (!is_valid_account_key($account_key)) {
            respond(false, ['error' => 'account_key inválido (usa minúsculas, números, _ o -)'], 400);
        }

        $display_name = trim((string)($_POST['display_name'] ?? ''));
        $active = ($_POST['active'] ?? '1') === '1' ? 1 : 0;

        $imap_host = trim((string)($_POST['imap_host'] ?? ''));
        $imap_port = (int)($_POST['imap_port'] ?? 993);
        $imap_username = trim((string)($_POST['imap_username'] ?? ''));
        $imap_password = (string)($_POST['imap_password'] ?? '');

        $smtp_host = trim((string)($_POST['smtp_host'] ?? ''));
        $smtp_port = (int)($_POST['smtp_port'] ?? 465);
        $smtp_username = trim((string)($_POST['smtp_username'] ?? ''));
        $smtp_password = (string)($_POST['smtp_password'] ?? '');

        $smtp_from_email = trim((string)($_POST['smtp_from_email'] ?? ''));
        $smtp_from_name = trim((string)($_POST['smtp_from_name'] ?? ''));
        $smtp_reply_to_email = trim((string)($_POST['smtp_reply_to_email'] ?? ''));
        $smtp_reply_to_name = trim((string)($_POST['smtp_reply_to_name'] ?? ''));

        if ($imap_host === '' || $imap_username === '' || $imap_password === '') {
            respond(false, ['error' => 'IMAP requiere imap_host, imap_username e imap_password'], 400);
        }
        if ($smtp_host === '' || $smtp_username === '' || $smtp_password === '') {
            respond(false, ['error' => 'SMTP requiere smtp_host, smtp_username y smtp_password'], 400);
        }
        if (!filter_var($smtp_from_email, FILTER_VALIDATE_EMAIL)) {
            respond(false, ['error' => 'smtp_from_email inválido'], 400);
        }
        if ($smtp_reply_to_email !== '' && !filter_var($smtp_reply_to_email, FILTER_VALIDATE_EMAIL)) {
            respond(false, ['error' => 'smtp_reply_to_email inválido'], 400);
        }

        if ($imap_port < 1 || $imap_port > 65535) {
            respond(false, ['error' => 'imap_port inválido'], 400);
        }
        if ($smtp_port < 1 || $smtp_port > 65535) {
            respond(false, ['error' => 'smtp_port inválido'], 400);
        }

        if (get_account_by_key($db, $account_key)) {
            respond(false, ['error' => 'account_key ya existe'], 409);
        }

        $st = $db->prepare(
            "INSERT INTO tm_mail_accounts (
                account_key, display_name, active,
                imap_host, imap_port, imap_username, imap_password_enc,
                smtp_host, smtp_port, smtp_username, smtp_password_enc,
                smtp_from_email, smtp_from_name,
                smtp_reply_to_email, smtp_reply_to_name
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $params = [
            $account_key,
            $display_name,
            $active,
            $imap_host,
            $imap_port,
            $imap_username,
            $imap_password,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_from_email,
            $smtp_from_name,
            $smtp_reply_to_email !== '' ? $smtp_reply_to_email : null,
            $smtp_reply_to_name !== '' ? $smtp_reply_to_name : null,
        ];

        // Evitamos bugs de tipado: bind_param coacciona a tipos de columnas.
        $types = str_repeat('s', count($params));
        $st->bind_param($types, ...$params);
        $st->execute();
        $id = $db->insert_id;
        $st->close();

        // Limpieza de cache por si hubiera registros previos
        $st2 = $db->prepare("DELETE FROM tm_emails WHERE cuenta = ?");
        $st2->bind_param('s', $account_key);
        $st2->execute();
        $st2->close();

        respond(true, ['id' => (int)$id]);
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) respond(false, ['error' => 'id requerido'], 400);

        $st0 = $db->prepare("SELECT * FROM tm_mail_accounts WHERE id = ? LIMIT 1");
        $st0->bind_param('i', $id);
        $st0->execute();
        $acc0 = $st0->get_result()->fetch_assoc();
        $st0->close();
        if (!$acc0) respond(false, ['error' => 'Cuenta no encontrada'], 404);

        $display_name = trim((string)($_POST['display_name'] ?? $acc0['display_name'] ?? ''));
        $active = ($_POST['active'] ?? (string)($acc0['active'] ?? '1')) === '1' ? 1 : 0;

        $imap_host = trim((string)($_POST['imap_host'] ?? $acc0['imap_host'] ?? ''));
        $imap_port = (int)($_POST['imap_port'] ?? (int)($acc0['imap_port'] ?? 993));
        $imap_username = trim((string)($_POST['imap_username'] ?? $acc0['imap_username'] ?? ''));
        $imap_password = (string)($_POST['imap_password'] ?? '');

        $smtp_host = trim((string)($_POST['smtp_host'] ?? $acc0['smtp_host'] ?? ''));
        $smtp_port = (int)($_POST['smtp_port'] ?? (int)($acc0['smtp_port'] ?? 465));
        $smtp_username = trim((string)($_POST['smtp_username'] ?? $acc0['smtp_username'] ?? ''));
        $smtp_password = (string)($_POST['smtp_password'] ?? '');

        $smtp_from_email = trim((string)($_POST['smtp_from_email'] ?? $acc0['smtp_from_email'] ?? ''));
        $smtp_from_name = trim((string)($_POST['smtp_from_name'] ?? $acc0['smtp_from_name'] ?? ''));
        $smtp_reply_to_email = trim((string)($_POST['smtp_reply_to_email'] ?? $acc0['smtp_reply_to_email'] ?? ''));
        $smtp_reply_to_name = trim((string)($_POST['smtp_reply_to_name'] ?? $acc0['smtp_reply_to_name'] ?? ''));

        if ($imap_host === '' || $imap_username === '') {
            respond(false, ['error' => 'IMAP requiere imap_host e imap_username'], 400);
        }
        if ($smtp_host === '' || $smtp_username === '') {
            respond(false, ['error' => 'SMTP requiere smtp_host e smtp_username'], 400);
        }
        if (!filter_var($smtp_from_email, FILTER_VALIDATE_EMAIL)) {
            respond(false, ['error' => 'smtp_from_email inválido'], 400);
        }
        if ($smtp_reply_to_email !== '' && !filter_var($smtp_reply_to_email, FILTER_VALIDATE_EMAIL)) {
            respond(false, ['error' => 'smtp_reply_to_email inválido'], 400);
        }

        if ($imap_port < 1 || $imap_port > 65535) {
            respond(false, ['error' => 'imap_port inválido'], 400);
        }
        if ($smtp_port < 1 || $smtp_port > 65535) {
            respond(false, ['error' => 'smtp_port inválido'], 400);
        }

        $imap_password_enc_final = $imap_password !== '' ? $imap_password : ($acc0['imap_password_enc'] ?? null);
        $smtp_password_enc_final = $smtp_password !== '' ? $smtp_password : ($acc0['smtp_password_enc'] ?? null);

        $st = $db->prepare(
            "UPDATE tm_mail_accounts SET
                display_name = ?, active = ?,
                imap_host = ?, imap_port = ?, imap_username = ?, imap_password_enc = ?,
                smtp_host = ?, smtp_port = ?, smtp_username = ?, smtp_password_enc = ?,
                smtp_from_email = ?, smtp_from_name = ?,
                smtp_reply_to_email = ?, smtp_reply_to_name = ?
             WHERE id = ?"
        );

        $params = [
            $display_name,
            $active,
            $imap_host,
            $imap_port,
            $imap_username,
            $imap_password_enc_final,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password_enc_final,
            $smtp_from_email,
            $smtp_from_name,
            $smtp_reply_to_email !== '' ? $smtp_reply_to_email : null,
            $smtp_reply_to_name !== '' ? $smtp_reply_to_name : null,
            $id,
        ];

        $types = str_repeat('s', count($params));
        $st->bind_param($types, ...$params);
        $st->execute();
        $st->close();

        $acc_key = (string)($acc0['account_key'] ?? '');
        if ($acc_key) {
            $st3 = $db->prepare("DELETE FROM tm_emails WHERE cuenta = ?");
            $st3->bind_param('s', $acc_key);
            $st3->execute();
            $st3->close();
        }

        respond(true, ['id' => (int)$id]);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) respond(false, ['error' => 'id requerido'], 400);

        $st0 = $db->prepare("SELECT account_key FROM tm_mail_accounts WHERE id = ? LIMIT 1");
        $st0->bind_param('i', $id);
        $st0->execute();
        $acc0 = $st0->get_result()->fetch_assoc();
        $st0->close();

        if (!$acc0) respond(false, ['error' => 'Cuenta no encontrada'], 404);

        $acc_key = (string)$acc0['account_key'];

        $st1 = $db->prepare("DELETE FROM tm_mail_accounts WHERE id = ?");
        $st1->bind_param('i', $id);
        $st1->execute();
        $st1->close();

        $st2 = $db->prepare("DELETE FROM tm_emails WHERE cuenta = ?");
        $st2->bind_param('s', $acc_key);
        $st2->execute();
        $st2->close();

        respond(true, ['deleted' => true]);
    }

    respond(false, ['error' => 'Acción no implementada'], 400);
} catch (Throwable $e) {
    respond(false, ['error' => $e->getMessage()], 500);
}
