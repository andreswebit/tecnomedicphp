<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/whatsapp.php';
requiere_login();

$id = (int)($_POST['id'] ?? 0);

$fecha_raw = trim($_POST['fecha'] ?? '');
if (strpos($fecha_raw, '-') !== false) {
    // viene de <input type=date> como YYYY-MM-DD -> convertir a DD/MM/YYYY
    $dt    = DateTime::createFromFormat('Y-m-d', $fecha_raw);
    $fecha = $dt ? $dt->format('d/m/Y') : $fecha_raw;
} else {
    $fecha = $fecha_raw; // ya viene en DD/MM/YYYY
}

$d = [
    'nombre'      => trim($_POST['nombre'] ?? ''),
    'apellido'    => trim($_POST['apellido'] ?? ''),
    'dni'         => trim($_POST['dni'] ?? ''),
    'obra_social' => trim($_POST['obra_social'] ?? ''),
    'telefono'    => trim($_POST['telefono'] ?? ''),
    'email'       => trim($_POST['email'] ?? ''),
    'fecha'       => $fecha,
    'hora'        => trim($_POST['hora'] ?? ''),
    'estado'      => trim($_POST['estado'] ?? ''),
];

if ($id) {
    modificar_turno($id, $d);

    $nombreCompleto = trim($d['nombre'] . ' ' . $d['apellido']);

    if ($d['estado'] === 'Confirmado') {
        try { email_confirmacion($nombreCompleto, $d['email'], $d['fecha'], $d['hora']); }
        catch (Throwable $e) { error_log('Error email confirmacion: ' . $e->getMessage()); }
        try {
            enviar_whatsapp($d['telefono'],
                "TECNOMEDIC - Turno CONFIRMADO\n\n" .
                "Hola {$d['nombre']}! Tu turno fue CONFIRMADO.\n\n" .
                "Fecha: {$d['fecha']}  Hora: {$d['hora']}hs\n" .
                "C. Pellegrini 799, Corrientes - Tel: (3794) 34-9278\n\nTe esperamos!"
            );
        } catch (Throwable $e) { error_log('Error WA confirmacion: ' . $e->getMessage()); }

    } elseif ($d['estado'] === 'Pendiente') {
        try { email_modificacion($nombreCompleto, $d['email'], $d['fecha'], $d['hora']); }
        catch (Throwable $e) { error_log('Error email modificacion: ' . $e->getMessage()); }
        try {
            enviar_whatsapp($d['telefono'],
                "TECNOMEDIC - Turno modificado\n\n" .
                "Hola {$d['nombre']}! Tu turno fue reprogramado.\n\n" .
                "Nueva fecha: {$d['fecha']}  Nueva hora: {$d['hora']}hs\n\n" .
                "Te confirmaremos a la brevedad. Tel: (3794) 34-9278"
            );
        } catch (Throwable $e) { error_log('Error WA modificacion: ' . $e->getMessage()); }

    } elseif ($d['estado'] === 'Cancelado') {
        try {
            enviar_whatsapp($d['telefono'],
                "TECNOMEDIC - Turno cancelado\n\n" .
                "Hola {$d['nombre']}, tu turno del {$d['fecha']} a las {$d['hora']}hs fue cancelado.\n\n" .
                "Para sacar otro turno escribinos o llama al (3794) 34-9278."
            );
        } catch (Throwable $e) { error_log('Error WA cancelacion: ' . $e->getMessage()); }
    }
}

header('Location: ' . BASE_URL . '/admin/index.php?guardado=1');
exit;
