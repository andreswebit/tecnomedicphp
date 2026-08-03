<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requiere_login();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    eliminar_turno($id);
}

header('Location: ' . BASE_URL . '/admin/index.php?guardado=1');
exit;
