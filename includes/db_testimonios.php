<?php
// ════════════════════════════════════════════════════════════════
// TECNOMEDIC — CRUD Testimonios
// ════════════════════════════════════════════════════════════════

function testimonios_listar(): array {
    $r = db()->query("SELECT * FROM tm_testimonios ORDER BY orden ASC, creado_en DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function testimonio_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_testimonios WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function testimonio_crear(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_testimonios (nombre, rol, texto, resultado, media_tipo, media_url, video_thumb, orden, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $activo = isset($d['activo']) ? 1 : 0;
    $st->bind_param('sssssssii',
        $d['nombre'], $d['rol'], $d['texto'], $d['resultado'],
        $d['media_tipo'], $d['media_url'], $d['video_thumb'], $d['orden'], $activo
    );
    $st->execute();
    return db()->insert_id;
}

function testimonio_editar(int $id, array $d): void {
    $st = db()->prepare(
        "UPDATE tm_testimonios SET nombre=?, rol=?, texto=?, resultado=?, media_tipo=?, media_url=?, video_thumb=?, orden=?, activo=? WHERE id=?"
    );
    $activo = isset($d['activo']) ? 1 : 0;
    $st->bind_param('sssssssiii',
        $d['nombre'], $d['rol'], $d['texto'], $d['resultado'],
        $d['media_tipo'], $d['media_url'], $d['video_thumb'], $d['orden'], $activo, $id
    );
    $st->execute();
}

function testimonio_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_testimonios WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
}