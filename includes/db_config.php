<?php
// ════════════════════════════════════════════════════════════════
// TECNOMEDIC — Gestión de configuración general
// ════════════════════════════════════════════════════════════════

function config_get(string $clave): ?string {
    $st = db()->prepare("SELECT valor FROM tm_config WHERE clave = ?");
    $st->bind_param('s', $clave);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    return $r ? $r['valor'] : null;
}

function config_set(string $clave, string $valor): void {
    $st = db()->prepare("INSERT INTO tm_config (clave, valor) VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    $st->bind_param('ss', $clave, $valor);
    $st->execute();
}