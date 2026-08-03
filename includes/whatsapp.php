<?php
require_once __DIR__ . '/db.php';

function formatear_wa(string $tel): string {
    $d = preg_replace('/\D/', '', $tel);
    if (str_starts_with($d, '54')) {
        if (!str_starts_with($d, '549')) $d = '549' . substr($d, 2);
    } elseif (str_starts_with($d, '0')) {
        $d = '549' . substr($d, 1);
    } else {
        $d = '549' . $d;
    }
    return "whatsapp:+$d";
}

function enviar_whatsapp(string $tel, string $msg): bool {
    if (!TWILIO_SID || !TWILIO_TOKEN) {
        error_log('Twilio no configurado');
        return false;
    }
    $to = formatear_wa($tel);
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_SID . "/Messages.json";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'From' => TWILIO_WA_FROM,
            'To'   => $to,
            'Body' => $msg,
        ]),
        CURLOPT_USERPWD  => TWILIO_SID . ':' . TWILIO_TOKEN,
        CURLOPT_TIMEOUT  => 10,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 201) { error_log("WA OK → $to"); return true; }
    error_log("Twilio error $code: $resp");
    return false;
}