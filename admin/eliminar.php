<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/whatsapp.php';
requiere_login();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    // Traemos los datos ANTES de borrar, para poder avisarle al paciente
    $t = get_turno_by_id($id);

    eliminar_turno($id);

    if ($t) {
        $nombre = trim($t['nombre'] . ' ' . $t['apellido']);
        try {
            email_cancelacion($nombre, $t['email'], $t['fecha'], $t['hora']);
        } catch (Throwable $e) {
            error_log('Error email eliminacion: ' . $e->getMessage());
        }
        try {
            enviar_whatsapp($t['telefono'],
                "TECNOMEDIC - Turno cancelado\n\n" .
                "Hola {$t['nombre']}, tu turno del {$t['fecha']} a las {$t['hora']}hs fue cancelado.\n\n" .
                "Para sacar otro turno escribinos o llama al (3794) 34-9278."
            );
        } catch (Throwable $e) {
            error_log('Error WA eliminacion: ' . $e->getMessage());
        }
    }
}

header('Location: ' . BASE_URL . '/admin/index.php?guardado=1');
exit;
