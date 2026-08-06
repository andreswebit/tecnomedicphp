<?php
require_once __DIR__ . '/db.php';

// function enviar_email(string $dest, string $asunto, string $txt, string $html = ''): bool {
//     // Modo local de desarrollo: en vez de llamar a Brevo, guarda el mail
//     // como un archivo .html para poder revisarlo sin depender de la API.
//     // Se activa poniendo MAIL_DRIVER=log en el .env (dejar sin definir,
//     // o MAIL_DRIVER=brevo, para el comportamiento normal con Brevo).
//     if (function_exists('env') && env('MAIL_DRIVER', 'brevo') === 'log') {
//         $carpeta = __DIR__ . '/../storage/mails_log';
//         if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
//         $archivo = $carpeta . '/' . date('Y-m-d_His') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $dest) . '.html';
//         $contenido = "<h3>Para: " . htmlspecialchars($dest) . "</h3>"
//             . "<h3>Asunto: " . htmlspecialchars($asunto) . "</h3><hr>"
//             . ($html ?: '<pre>' . htmlspecialchars($txt) . '</pre>');
//         file_put_contents($archivo, $contenido);
//         error_log("Email (modo log, no enviado por Brevo) → $dest → guardado en $archivo");
//         return true;
//     }

//     if (!BREVO_API_KEY) { error_log('BREVO_API_KEY no configurada'); return false; }
//     $payload = [
//         'sender'      => ['name' => MAIL_NAME, 'email' => MAIL_FROM],
//         'to'          => [['email' => $dest]],
//         'subject'     => $asunto,
//         'textContent' => $txt,
//     ];
//     if ($html) $payload['htmlContent'] = $html;

//     $ch = curl_init('https://api.brevo.com/v3/smtp/email');
//     curl_setopt_array($ch, [
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_POST           => true,
//         CURLOPT_POSTFIELDS     => json_encode($payload),
//         CURLOPT_HTTPHEADER     => [
//             'api-key: ' . BREVO_API_KEY,
//             'Content-Type: application/json',
//         ],
//         CURLOPT_TIMEOUT => 15,
//     ]);
//     $resp = curl_exec($ch);
//     $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     curl_close($ch);
//     if (in_array($code, [200,201])) { error_log("Email OK → $dest"); return true; }
//     error_log("Brevo error $code: $resp");
//     return false;
// }

function enviar_email(
    string $dest,
    string $asunto,
    string $txt,
    string $html=''
): bool {

    if (env('MAIL_DRIVER','smtp') == 'log') {

        $carpeta = __DIR__.'/../storage/mails_log';

        if(!is_dir($carpeta))
            mkdir($carpeta,0777,true);

        $archivo = $carpeta.'/'.date('Ymd_His').'.html';

        file_put_contents(
            $archivo,
            $html ?: nl2br($txt)
        );

        return true;
    }

    try{

        require_once __DIR__.'/config_mail.php';

        $mail = crear_mail();

        $mail->addAddress($dest);

        $mail->Subject = $asunto;

        if($html!=""){

            $mail->isHTML(true);
            $mail->Body    = $html;
            $mail->AltBody = $txt;

        }else{

            $mail->Body = $txt;

        }

        $mail->send();

        error_log("Email enviado a ".$dest);

        return true;

    }catch(Exception $e){

        error_log($e->getMessage());

        return false;

    }

}

// ── HTML base ───────────────────────────────────────────────────

function _html_email(string $titulo, string $nombre, string $cuerpo): string {
    return <<<HTML
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0"
 style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1);">
 <tr><td style="background:linear-gradient(135deg,#0a2540,#023e6e);padding:32px 40px;text-align:center;">
  <div style="color:#00b4d8;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:8px;">TECNOMEDIC</div>
  <div style="color:#fff;font-size:22px;font-weight:700;">$titulo</div>
  <div style="color:rgba(255,255,255,.5);font-size:12px;margin-top:6px;">Centro de Salud · Cámara Hiperbárica</div>
 </td></tr>
 <tr><td style="padding:36px 40px;">
  <p style="margin:0 0 20px;color:#1e3a5f;font-size:16px;">Hola <strong>$nombre</strong>,</p>
  $cuerpo
  <hr style="border:none;border-top:1px solid #e8edf2;margin:28px 0;">
  <p style="color:#64748b;font-size:12px;">
   <strong style="color:#0a2540;">TECNOMEDIC</strong><br>
   📍 C. Pellegrini 799, Corrientes<br>📞 (3794) 34-9278
  </p>
 </td></tr>
 <tr><td style="background:#f8fafc;padding:16px 40px;text-align:center;color:#94a3b8;font-size:11px;">
  Este es un mensaje automático. No respondas este email.
 </td></tr>
</table></td></tr></table></body></html>
HTML;
}

function _bloque_turno(string $fecha, string $hora): string {
    return <<<HTML
<div style="background:#f0f9ff;border-left:4px solid #00b4d8;border-radius:0 12px 12px 0;padding:20px 24px;margin:20px 0;">
 <div style="color:#0a2540;font-size:15px;margin-bottom:8px;">📅 <strong>Fecha:</strong> $fecha</div>
 <div style="color:#0a2540;font-size:15px;">⏰ <strong>Hora:</strong> {$hora}hs</div>
</div>
HTML;
}

// ── Emails específicos ──────────────────────────────────────────

function email_solicitud(array $d): void {
    $nombre = trim("{$d['nombre']} {$d['apellido']}");
    $txt = "Hola $nombre,\n\nRecibimos tu solicitud de turno para Cámara Hiperbárica.\n\nFecha: {$d['fecha']}\nHora: {$d['hora']}hs\n\nTe confirmaremos a la brevedad.\n\nTECNOMEDIC - (3794) 34-9278";
    $html = _html_email('Solicitud de turno recibida', $nombre,
        "<p style='color:#475569;font-size:14px;line-height:1.7;'>Recibimos tu solicitud de turno para <strong>Cámara Hiperbárica</strong>. Te confirmaremos a la brevedad.</p>"
        . _bloque_turno($d['fecha'], $d['hora'])
        . "<p style='color:#64748b;font-size:13px;'>⏳ Estado actual: <strong>Pendiente de confirmación</strong></p>"
    );
    enviar_email($d['email'], 'Solicitud de turno recibida – TECNOMEDIC', $txt, $html);
}

function email_confirmacion(string $nombre, string $email, string $fecha, string $hora): void {
    $txt = "Hola $nombre,\n\nTu turno fue CONFIRMADO.\n\nFecha: $fecha\nHora: {$hora}hs\n\nC. Pellegrini 799, Corrientes\nTel: (3794) 34-9278\n\n¡Te esperamos!";
    $html = _html_email('✔️ Turno Confirmado', $nombre,
        "<p style='color:#475569;font-size:14px;line-height:1.7;'>Tu turno fue <strong style='color:#16a34a;'>CONFIRMADO</strong>. ¡Te esperamos!</p>"
        . _bloque_turno($fecha, $hora)
        . "<p style='color:#64748b;font-size:13px;'>📍 C. Pellegrini 799, Corrientes &nbsp;·&nbsp; 📞 (3794) 34-9278</p>"
    );
    enviar_email($email, '✔️ Turno confirmado – TECNOMEDIC', $txt, $html);
}

function email_modificacion(string $nombre, string $email, string $fecha, string $hora): void {
    $txt = "Hola $nombre,\n\nTu turno fue MODIFICADO.\n\nNueva fecha: $fecha\nNueva hora: {$hora}hs\n\nConsultas: (3794) 34-9278\n\nTECNOMEDIC";
    $html = _html_email('✏️ Turno Modificado', $nombre,
        "<p style='color:#475569;font-size:14px;line-height:1.7;'>Tu turno fue <strong>modificado</strong>. Los nuevos datos son:</p>"
        . _bloque_turno($fecha, $hora)
        . "<p style='color:#64748b;font-size:13px;'>Consultas: 📞 (3794) 34-9278</p>"
    );
    enviar_email($email, '✏️ Turno modificado – TECNOMEDIC', $txt, $html);
}

function email_recordatorio(string $nombre, string $email, string $fecha, string $hora): bool {
    $txt = "Hola $nombre,\n\nTe recordamos que mañana tenés turno en TECNOMEDIC.\n\nFecha: $fecha\nHora: {$hora}hs\n\nDirección: C. Pellegrini 799, Corrientes\nTel: (3794) 34-9278\n\n¡Te esperamos!";
    $html = _html_email('🔔 Recordatorio de turno', $nombre,
        "<p style='color:#475569;font-size:14px;line-height:1.7;'>Te recordamos que <strong>mañana tenés turno</strong> en TECNOMEDIC Cámara Hiperbárica.</p>"
        . _bloque_turno($fecha, $hora)
        . "<p style='color:#64748b;font-size:13px;'>📍 C. Pellegrini 799, Corrientes &nbsp;·&nbsp; 📞 (3794) 34-9278</p>"
        . "<p style='color:#94a3b8;font-size:12px;'>Si necesitás cancelar o reprogramar, contactanos con anticipación.</p>"
    );
    return enviar_email($email, '🔔 Recordatorio: tu turno en TECNOMEDIC es mañana', $txt, $html);
}

function email_cancelacion(string $nombre, string $email, string $fecha, string $hora): bool {
    $txt = "Hola $nombre,\n\nTu turno fue CANCELADO.\n\nFecha: $fecha\nHora: {$hora}hs\n\nSi querés sacar un nuevo turno, escribinos o llamá al (3794) 34-9278.\n\nTECNOMEDIC";
    $html = _html_email('❌ Turno Cancelado', $nombre,
        "<p style='color:#475569;font-size:14px;line-height:1.7;'>Te confirmamos que tu turno fue <strong style='color:#c94f4f;'>cancelado</strong>.</p>"
        . _bloque_turno($fecha, $hora)
        . "<p style='color:#64748b;font-size:13px;'>Si querés sacar un nuevo turno, escribinos o llamá al 📞 (3794) 34-9278.</p>"
    );
    return enviar_email($email, '❌ Turno cancelado – TECNOMEDIC', $txt, $html);
}
