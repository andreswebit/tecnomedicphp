<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/whatsapp.php';

// ── Token para el cron de Ferozo ────────────────────────────────
// El cron llama: recordatorio.php?token=TM_CRON_2025
// Cambiá este token por uno propio y actualizalo también en el cron job de Ferozo.
define('CRON_TOKEN', 'TM_CRON_2025');

$es_cron   = isset($_GET['token']) && $_GET['token'] === CRON_TOKEN;
$es_manual = !$es_cron && esta_logueado();

if (!$es_cron && !$es_manual) {
    http_response_code(403);
    die('No autorizado.');
}

function recordatorio_manana(): array {
    $tz     = new DateTimeZone('America/Argentina/Buenos_Aires');
    $manana = new DateTime('tomorrow', $tz);
    $fecha_str = $manana->format('d/m/Y');

    error_log("[CRON] Buscando turnos para mañana: $fecha_str");

    $turnos = get_turnos();
    $enviados = 0;
    $errores  = 0;

    foreach ($turnos as $t) {
        if ($t['fecha'] !== $fecha_str) continue;
        if (strtolower($t['estado']) === 'cancelado') continue;

        $nombre = trim($t['nombre'] . ' ' . $t['apellido']);
        error_log("[CRON] Enviando recordatorio a $nombre | $fecha_str {$t['hora']}hs");

        if (!empty($t['email'])) {
            try {
                $ok = email_recordatorio($nombre, $t['email'], $fecha_str, $t['hora']);
                error_log($ok ? "[CRON] Email OK -> {$t['email']}" : "[CRON] Email fallo -> {$t['email']}");
            } catch (Throwable $e) {
                error_log('[CRON] Error email: ' . $e->getMessage());
                $errores++;
            }
        }

        if (!empty($t['telefono'])) {
            try {
                $msg = "🔔 *TECNOMEDIC - Recordatorio de turno*\n\n" .
                       "Hola {$t['nombre']}! 👋\n" .
                       "Te recordamos que *mañana* tenés turno.\n\n" .
                       "📅 Fecha: $fecha_str\n" .
                       "⏰ Hora: {$t['hora']}hs\n\n" .
                       "📍 C. Pellegrini 799, Corrientes\n" .
                       "📞 (3794) 34-9278\n\n" .
                       "Si necesitás cancelar, avisanos con anticipación. ¡Te esperamos!";
                $ok = enviar_whatsapp($t['telefono'], $msg);
                error_log($ok ? "[CRON] WA OK -> {$t['telefono']}" : "[CRON] WA fallo -> {$t['telefono']}");
            } catch (Throwable $e) {
                error_log('[CRON] Error WA: ' . $e->getMessage());
                $errores++;
            }
        }

        $enviados++;
    }

    error_log("[CRON] Recordatorios finalizados: $enviados paciente(s) notificados, $errores error(es).");
    return ['enviados' => $enviados, 'errores' => $errores];
}

$resultado = recordatorio_manana();

if ($es_cron) {
    header('Content-Type: text/plain');
    echo "OK - {$resultado['enviados']} enviados, {$resultado['errores']} errores\n";
    exit;
}

// Disparo manual desde el panel admin → volver con query param
header('Location: ' . BASE_URL . '/admin/index.php?recordatorio=1');
exit;
