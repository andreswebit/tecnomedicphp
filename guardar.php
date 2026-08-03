<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/email.php';
require_once __DIR__ . '/includes/whatsapp.php';

$base = BASE_URL;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $base . '/turnos.php');
    exit;
}

function titulo(string $s): string {
    $s = trim($s);
    return $s === '' ? '' : mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
}

$d = [
    'nombre'      => titulo($_POST['nombre'] ?? ''),
    'apellido'    => titulo($_POST['apellido'] ?? ''),
    'dni'         => trim($_POST['dni'] ?? ''),
    'obra_social' => trim($_POST['obra_social'] ?? ''),
    'telefono'    => trim($_POST['telefono'] ?? ''),
    'email'       => trim($_POST['email'] ?? ''),
    'fecha'       => trim($_POST['fecha'] ?? ''), // ya viene en DD/MM/YYYY desde el JS del form
    'hora'        => trim($_POST['hora'] ?? ''),
];

// ── Validación de campos obligatorios ──────────────────────────
$error = null;
if (!$d['nombre'] || !$d['apellido'] || !$d['telefono'] || !$d['email'] || !$d['fecha'] || !$d['hora']) {
    $error = 'Por favor completá todos los campos obligatorios.';
} elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
    $error = 'El email ingresado no es válido.';
}

// ── Validar que el horario siga disponible (evita doble reserva) ──
if (!$error) {
    $ocupados = get_ocupados($d['fecha']);
    $max      = MAX_POR_HORARIO;
    $actual   = $ocupados[$d['hora']] ?? 0;
    if (!in_array($d['hora'], $GLOBALS['HORARIOS'], true)) {
        $error = 'El horario seleccionado no es válido.';
    } elseif ($actual >= $max) {
        $error = 'Ese horario ya no tiene lugares disponibles. Por favor elegí otro.';
    }
}

// ── Si hay error, volver a mostrar el formulario con el mensaje ──
if ($error) {
    include __DIR__ . '/turnos.php';
    exit;
}

// ── Guardar en MySQL ────────────────────────────────────────────
$id = crear_turno($d);

// ── Notificaciones (no deben frenar la confirmación si fallan) ──
try {
    email_solicitud($d);
} catch (Throwable $e) {
    error_log('Error email_solicitud: ' . $e->getMessage());
}

try {
    enviar_whatsapp($d['telefono'],
        "TECNOMEDIC - Solicitud recibida\n\n" .
        "Hola {$d['nombre']}! Recibimos tu solicitud de turno.\n\n" .
        "Fecha: {$d['fecha']}\nHora: {$d['hora']}hs\n\n" .
        "Te confirmaremos a la brevedad.\n" .
        "TECNOMEDIC - (3794) 34-9278"
    );
} catch (Throwable $e) {
    error_log('Error WA solicitud: ' . $e->getMessage());
}

// ── Mostrar pantalla de confirmación ────────────────────────────
$turno = $d;
$turno['id'] = $id;
include __DIR__ . '/confirmacion.php';
