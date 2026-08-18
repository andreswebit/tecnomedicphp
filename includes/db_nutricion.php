<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Portal (Fase E)
// Registro alimentario: objetivo nutricional, mediciones (peso/altura/
// IMC) y comidas cargadas por día. Reutiliza el control de permisos
// de db_ficha.php (ficha_puede_ver / ficha_puede_editar) — requiere
// que ese archivo ya esté cargado.
// ══════════════════════════════════════════════════════════════

// ── Objetivo nutricional ──────────────────────────────────────

function objetivo_nutricional_get(int $pacienteId): ?array {
    $st = db()->prepare("SELECT * FROM tm_objetivo_nutricional WHERE paciente_id=?");
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function objetivo_nutricional_guardar(int $pacienteId, string $objetivo, int $actualizadoPor): void {
    $st = db()->prepare(
        "INSERT INTO tm_objetivo_nutricional (paciente_id, objetivo, actualizado_por)
         VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE objetivo=VALUES(objetivo), actualizado_por=VALUES(actualizado_por)"
    );
    $st->bind_param('isi', $pacienteId, $objetivo, $actualizadoPor);
    $st->execute();
}

// ── Mediciones (peso/altura/IMC) ────────────────────────────────

function mediciones_listar(int $pacienteId): array {
    $st = db()->prepare(
        "SELECT m.*, DATE_FORMAT(m.fecha,'%d/%m/%Y') AS fecha_fmt,
                u.nombre AS registrado_por_nombre, u.apellido AS registrado_por_apellido
         FROM tm_mediciones m
         JOIN tm_usuarios u ON u.id = m.registrado_por
         WHERE m.paciente_id = ?
         ORDER BY m.fecha DESC, m.id DESC"
    );
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function medicion_crear(int $pacienteId, int $registradoPor, string $fechaDMY, ?float $peso, ?float $altura, string $observaciones): int {
    $imc = null;
    if ($peso && $altura) {
        $alturaMts = $altura / 100;
        $imc = round($peso / ($alturaMts * $alturaMts), 2);
    }
    $st = db()->prepare(
        "INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
         VALUES (?, STR_TO_DATE(?,'%d/%m/%Y'), ?, ?, ?, ?, ?)"
    );
    $st->bind_param('isdddsi', $pacienteId, $fechaDMY, $peso, $altura, $imc, $observaciones, $registradoPor);
    $st->execute();
    return db()->insert_id;
}

function medicion_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_mediciones WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
}

function medicion_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_mediciones WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

// ── Comidas (registro alimentario diario) ───────────────────────

function comidas_listar(int $pacienteId, int $limite = 60): array {
    $st = db()->prepare(
        "SELECT c.*, DATE_FORMAT(c.fecha,'%d/%m/%Y') AS fecha_fmt,
                u.nombre AS registrado_por_nombre, u.apellido AS registrado_por_apellido
         FROM tm_comidas c
         JOIN tm_usuarios u ON u.id = c.registrado_por
         WHERE c.paciente_id = ?
         ORDER BY c.fecha DESC, FIELD(c.tipo_comida,'desayuno','colacion','almuerzo','merienda','cena'), c.id DESC
         LIMIT ?"
    );
    $st->bind_param('ii', $pacienteId, $limite);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function comida_crear(int $pacienteId, int $registradoPor, string $fechaDMY, string $tipoComida, string $descripcion): int {
    $st = db()->prepare(
        "INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
         VALUES (?, STR_TO_DATE(?,'%d/%m/%Y'), ?, ?, ?)"
    );
    $st->bind_param('isssi', $pacienteId, $fechaDMY, $tipoComida, $descripcion, $registradoPor);
    $st->execute();
    return db()->insert_id;
}

function comida_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_comidas WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function comida_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_comidas WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
}
