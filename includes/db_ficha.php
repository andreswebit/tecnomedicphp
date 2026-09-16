<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Portal (Fase D)
// Ficha médica: historia clínica, tratamientos, estudios.
// Requiere que includes/db_portal.php ya esté cargado (usa db(),
// portal_current_user(), etc.)
// ══════════════════════════════════════════════════════════════

// ── Control de acceso ────────────────────────────────────────────

// ¿Puede el usuario logueado ver/editar la ficha de este paciente?
// - admin: siempre
// - profesional: solo si tiene una asignación activa con ese paciente
// - paciente: solo la propia (y de solo lectura, ver ficha_puede_editar)
function ficha_puede_ver(int $pacienteId): bool {
    $rol = portal_rol();
    $uid = (int)($_SESSION['portal_uid'] ?? 0);
    if ($rol === 'admin') return true;
    if ($rol === 'paciente') return $uid === $pacienteId;
    if ($rol === 'profesional') {
        $st = db()->prepare(
            "SELECT 1 FROM tm_asignaciones WHERE profesional_id=? AND paciente_id=? AND activa=1"
        );
        $st->bind_param('ii', $uid, $pacienteId);
        $st->execute();
        return (bool) $st->get_result()->fetch_row();
    }
    return false;
}

// Solo profesional (asignado) o admin pueden cargar/editar datos clínicos.
// El paciente puede ver, pero no editar.
function ficha_puede_editar(int $pacienteId): bool {
    $rol = portal_rol();
    if ($rol === 'paciente') return false;
    return ficha_puede_ver($pacienteId);
}

// ── Historia clínica (resumen) ───────────────────────────────────

function historia_clinica_get(int $pacienteId): array {
    $st = db()->prepare("SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_fmt FROM tm_historia_clinica WHERE paciente_id=? ORDER BY fecha DESC, id DESC");
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function historia_clinica_guardar(int $pacienteId, string $antecedentes, string $diagnostico, string $observaciones, int $actualizadoPor): void {
    // Insertar nueva fila con fecha actual
    $st = db()->prepare(
        "INSERT INTO tm_historia_clinica (paciente_id, fecha, antecedentes, diagnostico, observaciones, actualizado_por)
         VALUES (?, CURDATE(), ?, ?, ?, ?)"
    );
    $st->bind_param('isssi', $pacienteId, $antecedentes, $diagnostico, $observaciones, $actualizadoPor);
    $st->execute();
}

function historia_clinica_actualizar(int $id, string $antecedentes, string $diagnostico, string $observaciones, int $actualizadoPor): void {
    $st = db()->prepare(
        "UPDATE tm_historia_clinica SET antecedentes = ?, diagnostico = ?, observaciones = ?, actualizado_por = ? WHERE id = ?"
    );
    $st->bind_param('sssii', $antecedentes, $diagnostico, $observaciones, $actualizadoPor, $id);
    $st->execute();
}

function historia_clinica_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_historia_clinica WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
}

// ── Tratamientos ──────────────────────────────────────────────────

function tratamientos_listar(int $pacienteId): array {
    $st = db()->prepare(
        "SELECT t.*, DATE_FORMAT(t.fecha,'%d/%m/%Y') AS fecha_fmt,
                u.nombre AS profesional_nombre, u.apellido AS profesional_apellido
         FROM tm_tratamientos t
         JOIN tm_usuarios u ON u.id = t.profesional_id
         WHERE t.paciente_id = ?
         ORDER BY t.fecha DESC, t.id DESC"
    );
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function tratamiento_crear(int $pacienteId, int $profesionalId, string $area, string $fechaDMY, string $descripcion): void {
    $st = db()->prepare(
        "INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
         VALUES (?,?,?,STR_TO_DATE(?,'%d/%m/%Y'),?)"
    );
    $st->bind_param('iisss', $pacienteId, $profesionalId, $area, $fechaDMY, $descripcion);
    $st->execute();
}

function tratamiento_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_tratamientos WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function tratamiento_eliminar(int $id): bool {
    $st = db()->prepare("DELETE FROM tm_tratamientos WHERE id = ?");
    $st->bind_param('i', $id);
    return $st->execute();
}

// ── Estudios (archivos) ────────────────────────────────────────────

function estudios_listar(int $pacienteId): array {
    $st = db()->prepare(
        "SELECT e.*, DATE_FORMAT(e.fecha_estudio,'%d/%m/%Y') AS fecha_estudio_fmt,
                u.nombre AS subido_por_nombre, u.apellido AS subido_por_apellido
         FROM tm_estudios e
         JOIN tm_usuarios u ON u.id = e.subido_por
         WHERE e.paciente_id = ?
         ORDER BY e.subido_en DESC"
    );
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function estudio_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_estudios WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function estudio_crear(int $pacienteId, int $subidoPor, string $nombreOriginal, string $rutaArchivo, string $tipo, string $notas, string $fechaEstudioDMY): int {
    // STR_TO_DATE('', ...) devuelve NULL solo, así que mandamos '' cuando no hay fecha.
    $fecha = $fechaEstudioDMY;
    $st = db()->prepare(
        "INSERT INTO tm_estudios (paciente_id, subido_por, nombre_original, ruta_archivo, tipo, notas, fecha_estudio)
         VALUES (?,?,?,?,?,?, STR_TO_DATE(?,'%d/%m/%Y'))"
    );
    $st->bind_param('issssss', $pacienteId, $subidoPor, $nombreOriginal, $rutaArchivo, $tipo, $notas, $fecha);
    $st->execute();
    return db()->insert_id;
}

function estudio_eliminar(int $id): ?array {
    $e = estudio_get($id);
    if (!$e) return null;
    $st = db()->prepare("DELETE FROM tm_estudios WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $e;
}

// ── Medicamentos recetados ──────────────────────────────────────────────

function medicamentos_listar(int $pacienteId): array {
    // Verificar si la tabla existe
    $result = db()->query("SHOW TABLES LIKE 'tm_medicamentos_recetados'");
    if ($result->num_rows === 0) return [];

    $st = db()->prepare(
        "SELECT id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta
         FROM tm_medicamentos_recetados
         WHERE paciente_id = ?
         ORDER BY fecha_receta DESC, id DESC"
    );
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function medicamento_crear(
    int $pacienteId,
    string $nombreComercial,
    string $principioActivo,
    string $dosis,
    string $frecuencia,
    string $viaAdministracion,
    string $estado
): int {
    $st = db()->prepare(
        "INSERT INTO tm_medicamentos_recetados
         (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
         VALUES (?,?,?,?,?,?,?,NOW())"
    );
    $st->bind_param('issssss', $pacienteId, $nombreComercial, $principioActivo, $dosis, $frecuencia, $viaAdministracion, $estado);
    $st->execute();
    return db()->insert_id;
}

function medicamento_get(int $id): ?array {
    $result = db()->query("SHOW TABLES LIKE 'tm_medicamentos_recetados'");
    if ($result->num_rows === 0) return null;

    $st = db()->prepare("SELECT * FROM tm_medicamentos_recetados WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function medicamento_eliminar(int $id): bool {
    $result = db()->query("SHOW TABLES LIKE 'tm_medicamentos_recetados'");
    if ($result->num_rows === 0) return false;

    $st = db()->prepare("DELETE FROM tm_medicamentos_recetados WHERE id = ?");
    $st->bind_param('i', $id);
    return $st->execute();
}