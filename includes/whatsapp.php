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
    $from = (string)TWILIO_WA_FROM;
    if (stripos($from, 'whatsapp:') !== 0) $from = 'whatsapp:' . ltrim($from);
    $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_SID . "/Messages.json";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'From' => $from,
            'To'   => $to,
            'Body' => $msg,
        ]),
        CURLOPT_USERPWD  => TWILIO_SID . ':' . TWILIO_TOKEN,
        CURLOPT_TIMEOUT  => 10,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($code === 201) { error_log("WA OK → $to"); return true; }
    error_log("Twilio error $code: $resp");
    @file_put_contents(__DIR__ . '/../api/debug.log', date('Y-m-d H:i:s') . " TWILIO SEND $to HTTP $code $cerr " . substr((string)$resp, 0, 400) . "\n", FILE_APPEND);
    return false;
}