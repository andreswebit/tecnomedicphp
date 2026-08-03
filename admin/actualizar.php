<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/whatsapp.php';
requiere_login();

$id     = (int)($_POST['id'] ?? 0);
$estado = trim($_POST['estado'] ?? '');

if ($id && $estado) {
    actualizar_estado($id, $estado);

    if ($estado === 'Confirmado') {
        $t = get_turno_by_id($id);
        if ($t) {
            $nombre = trim($t['nombre'] . ' ' . $t['apellido']);
            try {
                email_confirmacion($nombre, $t['email'], $t['fecha'], $t['hora']);
            } catch (Throwable $e) {
                error_log('Error email confirmacion: ' . $e->getMessage());
            }
            try {
                enviar_whatsapp($t['telefono'],
                    "TECNOMEDIC - Turno CONFIRMADO\n\n" .
                    "Hola {$t['nombre']}! Tu turno fue CONFIRMADO.\n\n" .
                    "Fecha: {$t['fecha']}  Hora: {$t['hora']}hs\n" .
                    "Direccion: C. Pellegrini 799, Corrientes\n" .
                    "Tel: (3794) 34-9278\n\nTe esperamos!"
                );
            } catch (Throwable $e) {
                error_log('Error WA confirmacion: ' . $e->getMessage());
            }
        }
    }
}

header('Location: ' . BASE_URL . '/admin/index.php?guardado=1');
exit;
