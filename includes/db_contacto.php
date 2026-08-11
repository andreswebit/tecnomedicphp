<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Formulario de contacto
// Requiere que includes/db.php ya esté cargado (usa db()).
// ══════════════════════════════════════════════════════════════

function contacto_crear(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_contactos (nombre, email, telefono, motivo, mensaje)
         VALUES (?,?,?,?,?)"
    );
    $st->bind_param('sssss', $d['nombre'], $d['email'], $d['telefono'], $d['motivo'], $d['mensaje']);
    $st->execute();
    return db()->insert_id;
}

function contactos_listar(bool $soloPendientes = false): array {
    $sql = "SELECT * FROM tm_contactos" . ($soloPendientes ? " WHERE atendido = 0" : "") . " ORDER BY creado_en DESC";
    $r = db()->query($sql);
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function contacto_marcar_atendido(int $id): void {
    $st = db()->prepare("UPDATE tm_contactos SET atendido = 1 WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
}
