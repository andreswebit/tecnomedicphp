<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$fecha_raw = $_GET['fecha'] ?? '';
if (!$fecha_raw) { echo json_encode(['error'=>'fecha requerida']); exit; }

$dt = DateTime::createFromFormat('Y-m-d', $fecha_raw);
if (!$dt) { echo json_encode(['error'=>'formato inválido, usar YYYY-MM-DD']); exit; }

$fecha = $dt->format('d/m/Y');
$ocupados = get_ocupados($fecha);

$slots = [];
foreach ($GLOBALS['HORARIOS'] as $h) {
    $c = $ocupados[$h] ?? 0;
    $slots[] = [
        'hora'       => $h,
        'ocupados'   => $c,
        'max'        => MAX_POR_HORARIO,
        'disponible' => $c < MAX_POR_HORARIO,
        'libres'     => MAX_POR_HORARIO - $c,
    ];
}
echo json_encode(['fecha' => $fecha, 'slots' => $slots]);
