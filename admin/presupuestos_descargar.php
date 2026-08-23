<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_presupuestos.php';
portal_require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$presupuesto = $id ? presupuesto_get($id) : null;

if (!$presupuesto || !$presupuesto['archivo_pdf']) {
    http_response_code(404);
    exit('Presupuesto no encontrado.');
}

$rutaCompleta = __DIR__ . '/../' . $presupuesto['archivo_pdf'];
if (!is_file($rutaCompleta)) {
    http_response_code(404);
    exit('El archivo ya no está disponible.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="presupuesto_' . $presupuesto['id'] . '.pdf"');
header('Content-Length: ' . filesize($rutaCompleta));
readfile($rutaCompleta);
exit;
