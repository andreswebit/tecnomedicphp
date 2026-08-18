<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Portal (Fase F)
// Recursos / formularios descargables. Requiere includes/db.php.
// ══════════════════════════════════════════════════════════════

function recursos_listar_publicos(): array {
    $r = db()->query(
        "SELECT * FROM tm_recursos WHERE publico = 1 ORDER BY creado_en DESC"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// Todos los recursos (públicos + solo-portal), para usuarios logueados
function recursos_listar_todos(): array {
    $r = db()->query(
        "SELECT r.*, u.nombre AS subido_por_nombre, u.apellido AS subido_por_apellido
         FROM tm_recursos r
         JOIN tm_usuarios u ON u.id = r.subido_por
         ORDER BY r.creado_en DESC"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function recurso_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_recursos WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function recurso_crear(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_recursos (titulo, descripcion, area, archivo_nombre, archivo_ruta, tipo, publico, subido_por)
         VALUES (?,?,?,?,?,?,?,?)"
    );
    $publico = (int)$d['publico'];
    $st->bind_param('ssssssii',
        $d['titulo'], $d['descripcion'], $d['area'],
        $d['archivo_nombre'], $d['archivo_ruta'], $d['tipo'],
        $publico, $d['subido_por']
    );
    $st->execute();
    return db()->insert_id;
}

function recurso_eliminar(int $id): ?array {
    $r = recurso_get($id);
    if (!$r) return null;
    $st = db()->prepare("DELETE FROM tm_recursos WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $r;
}
