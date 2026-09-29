<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'id inválido']);
    exit;
}

try {
    $st = db()->prepare("DELETE FROM tm_emails WHERE id = ? LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
