<?php

require_once __DIR__ . '/db.php';

use PHPMailer\PHPMailer\PHPMailer;

// Esta función fue pensada para funcionar con composer (vendor/autoload.php)
// ya presente en el servidor (Ferozo) por despliegue manual.
// Para mantener compatibilidad, si no existe vendor/ el sitio debería fallar
// igual que hoy; por eso este archivo no intenta "fallback".

function crear_mail(?array $cuenta = null): PHPMailer
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new PHPMailer(true);
    $mail->isSMTP();

    $cfg = $cuenta ?: null;

    if ($cfg) {
        $mail->Host = $cfg['smtp_host'] ?? env('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['smtp_username'] ?? env('SMTP_USER', env('MAIL_FROM', 'noreply@tecnomedic.com.ar'));
        $mail->Password = $cfg['smtp_password_enc'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int)($cfg['smtp_port'] ?? env('SMTP_PORT', 465));
        $mail->CharSet = 'UTF-8';

        $fromEmail = $cfg['smtp_from_email'] ?? env('MAIL_FROM', 'noreply@tecnomedic.com.ar');
        $fromName = $cfg['smtp_from_name'] ?? env('MAIL_NAME', 'TECNOMEDIC');

        $mail->setFrom($fromEmail, $fromName);

        $replyToEmail = $cfg['smtp_reply_to_email'] ?? null;
        $replyToName = $cfg['smtp_reply_to_name'] ?? null;
        if ($replyToEmail && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyToEmail, $replyToName ?: ($fromName ?: 'TECNOMEDIC'));
        }

        return $mail;
    }

    // Modo histórico: usa env global
    $mail->Host = env('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = env('SMTP_USER', env('MAIL_FROM', 'noreply@tecnomedic.com.ar'));
    $mail->Password = env('SMTP_PASS', '');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int)env('SMTP_PORT', 465);
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        env('MAIL_FROM', 'noreply@tecnomedic.com.ar'),
        env('MAIL_NAME', 'TECNOMEDIC')
    );

    $replyTo = env('MAIL_REPLY_TO', env('MAIL_CONTACTO', 'contacto@tecnomedic.com.ar'));
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($replyTo, env('MAIL_NAME', 'TECNOMEDIC'));
    }

    return $mail;
}
