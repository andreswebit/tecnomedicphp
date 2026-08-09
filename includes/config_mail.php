<?php

require_once __DIR__ . '/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function crear_mail(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();

    $mail->Host       = env('SMTP_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('SMTP_USER');
    $mail->Password   = env('SMTP_PASS');

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    $mail->Port       = (int) env('SMTP_PORT',465);

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        env('MAIL_FROM'),
        env('MAIL_NAME','TECNOMEDIC')
    );

    return $mail;
}