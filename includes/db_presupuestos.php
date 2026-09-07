<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Fase H: Presupuestos
// Requiere includes/db.php.
// ══════════════════════════════════════════════════════════════

function presupuesto_crear(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_presupuestos (nombre, apellido, dni_cuit, telefono, email, area, descripcion)
         VALUES (?,?,?,?,?,?,?)"
    );
    $st->bind_param('sssssss',
        $d['nombre'], $d['apellido'], $d['dni_cuit'], $d['telefono'], $d['email'], $d['area'], $d['descripcion']
    );
    $st->execute();
    if (function_exists('persona_upsert')) {
        persona_upsert($d['dni_cuit'], $d['nombre'], $d['apellido'], $d['telefono'], $d['email']);
    }
    return db()->insert_id;
}

function presupuestos_listar(): array {
    $r = db()->query("SELECT * FROM tm_presupuestos ORDER BY FIELD(estado,'Pendiente','Elaborado','Enviado'), creado_en DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function presupuesto_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_presupuestos WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

// Última solicitud de una persona por DNI/CUIT (para el bot de WhatsApp)
function presupuesto_buscar_por_dni_cuit(string $dniCuit): ?array {
    $dniCuit = preg_replace('/\D/', '', $dniCuit);
    $st = db()->prepare(
        "SELECT * FROM tm_presupuestos WHERE dni_cuit = ? ORDER BY creado_en DESC LIMIT 1"
    );
    $st->bind_param('s', $dniCuit);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function presupuesto_adjuntar_pdf(int $id, string $rutaRelativa): void {
    $st = db()->prepare("UPDATE tm_presupuestos SET archivo_pdf=?, estado='Elaborado' WHERE id=?");
    $st->bind_param('si', $rutaRelativa, $id);
    $st->execute();
}

function presupuesto_marcar_enviado(int $id): void {
    $st = db()->prepare("UPDATE tm_presupuestos SET estado='Enviado' WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
}

function presupuesto_eliminar(int $id): ?array {
    $p = presupuesto_get($id);
    if (!$p) return null;
    $st = db()->prepare("DELETE FROM tm_presupuestos WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $p;
}
