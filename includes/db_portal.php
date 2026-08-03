<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Portal (Fase A+B)
// Funciones de acceso a datos para tm_usuarios, tm_perfiles_*,
// tm_asignaciones. Requiere que includes/db.php ya esté incluido
// (usa la función db() ahí definida).
// ══════════════════════════════════════════════════════════════

// ── Usuarios: alta ──────────────────────────────────────────────

// Crea un paciente. Queda con activo=0 (pendiente de aprobación admin).
// Devuelve el id nuevo, o lanza excepción con mensaje si email/dni duplicado.
function usuario_crear_paciente(array $d): int {
    if (usuario_buscar_login($d['email']) || usuario_buscar_login($d['dni'])) {
        throw new Exception('Ya existe una cuenta con ese email o DNI.');
    }
    $hash = password_hash($d['password'], PASSWORD_DEFAULT);
    $st = db()->prepare(
        "INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo)
         VALUES (?,?,?,'paciente',?,?,?,0)"
    );
    $st->bind_param('ssssss', $d['email'], $d['dni'], $hash, $d['nombre'], $d['apellido'], $d['telefono']);
    $st->execute();
    $id = db()->insert_id;

    $obraSocialId = !empty($d['obra_social_id']) ? (int)$d['obra_social_id'] : null;
    $st2 = db()->prepare("INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id) VALUES (?, ?)");
    $st2->bind_param('ii', $id, $obraSocialId);
    $st2->execute();

    persona_upsert($d['dni'], $d['nombre'], $d['apellido'], $d['telefono'] ?? '', $d['email'], $obraSocialId);

    return $id;
}

// Crea un profesional. Lo da de alta el admin, así que queda activo=1 directamente.
function usuario_crear_profesional(array $d): int {
    if (usuario_buscar_login($d['email']) || usuario_buscar_login($d['dni'])) {
        throw new Exception('Ya existe una cuenta con ese email o DNI.');
    }
    $hash = password_hash($d['password'], PASSWORD_DEFAULT);
    $st = db()->prepare(
        "INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion)
         VALUES (?,?,?,'profesional',?,?,?,1,NOW())"
    );
    $st->bind_param('ssssss', $d['email'], $d['dni'], $hash, $d['nombre'], $d['apellido'], $d['telefono']);
    $st->execute();
    $id = db()->insert_id;

    $st2 = db()->prepare("INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula) VALUES (?,?,?)");
    $st2->bind_param('iss', $id, $d['area'], $d['matricula']);
    $st2->execute();

    return $id;
}

// ── Usuarios: búsqueda / login ──────────────────────────────────

// Busca por email O dni (para login flexible)
function usuario_buscar_login(string $identificador): ?array {
    $st = db()->prepare("SELECT * FROM tm_usuarios WHERE email=? OR dni=? LIMIT 1");
    $st->bind_param('ss', $identificador, $identificador);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function usuario_by_id(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_usuarios WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function perfil_paciente(int $usuarioId): ?array {
    $st = db()->prepare(
        "SELECT pp.*, o.nombre AS obra_social_nombre
         FROM tm_perfiles_paciente pp
         LEFT JOIN tm_obras_sociales o ON o.id = pp.obra_social_id
         WHERE pp.usuario_id=?"
    );
    $st->bind_param('i', $usuarioId);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function perfil_profesional(int $usuarioId): ?array {
    $st = db()->prepare("SELECT * FROM tm_perfiles_profesional WHERE usuario_id=?");
    $st->bind_param('i', $usuarioId);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function perfil_paciente_actualizar(int $usuarioId, array $d): void {
    $obraSocialId = !empty($d['obra_social_id']) ? (int)$d['obra_social_id'] : null;
    $st = db()->prepare(
        "UPDATE tm_perfiles_paciente SET fecha_nacimiento=?, obra_social_id=? WHERE usuario_id=?"
    );
    $fn = $d['fecha_nacimiento'] !== '' ? $d['fecha_nacimiento'] : null;
    $st->bind_param('sii', $fn, $obraSocialId, $usuarioId);
    $st->execute();

    if (!empty($d['telefono'])) {
        $st2 = db()->prepare("UPDATE tm_usuarios SET telefono=? WHERE id=?");
        $st2->bind_param('si', $d['telefono'], $usuarioId);
        $st2->execute();
    }

    $u = usuario_by_id($usuarioId);
    if ($u) {
        persona_upsert($u['dni'], $u['nombre'], $u['apellido'], $d['telefono'] ?? $u['telefono'], $u['email'], $obraSocialId);
    }
}

function usuario_cambiar_password(int $usuarioId, string $nuevaPassword): void {
    $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    $st = db()->prepare("UPDATE tm_usuarios SET password_hash=? WHERE id=?");
    $st->bind_param('si', $hash, $usuarioId);
    $st->execute();
}

// ── Admin: aprobación de pacientes ──────────────────────────────

function usuarios_pendientes(): array {
    $r = db()->query(
        "SELECT * FROM tm_usuarios WHERE rol='paciente' AND activo=0 ORDER BY fecha_alta"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function usuario_aprobar(int $id): void {
    $st = db()->prepare("UPDATE tm_usuarios SET activo=1, fecha_aprobacion=NOW() WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
}

function usuario_rechazar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_usuarios WHERE id=? AND rol='paciente' AND activo=0");
    $st->bind_param('i', $id);
    $st->execute();
}

function usuarios_todos(): array {
    $r = db()->query("SELECT * FROM tm_usuarios ORDER BY rol, apellido, nombre");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function profesionales_todos(): array {
    $r = db()->query(
        "SELECT u.*, p.area, p.matricula
         FROM tm_usuarios u
         JOIN tm_perfiles_profesional p ON p.usuario_id = u.id
         WHERE u.rol='profesional'
         ORDER BY p.area, u.apellido"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ── Asignaciones paciente ↔ profesional ─────────────────────────

function asignar_paciente_profesional(int $pacienteId, int $profesionalId, string $area): void {
    $st = db()->prepare(
        "INSERT INTO tm_asignaciones (paciente_id, profesional_id, area, activa)
         VALUES (?,?,?,1)
         ON DUPLICATE KEY UPDATE area=VALUES(area), activa=1"
    );
    $st->bind_param('iis', $pacienteId, $profesionalId, $area);
    $st->execute();
}

function desasignar(int $asignacionId): void {
    $st = db()->prepare("UPDATE tm_asignaciones SET activa=0 WHERE id=?");
    $st->bind_param('i', $asignacionId);
    $st->execute();
}

function asignaciones_todas(): array {
    $r = db()->query(
        "SELECT a.*, 
                up.nombre AS paciente_nombre, up.apellido AS paciente_apellido,
                uf.nombre AS profesional_nombre, uf.apellido AS profesional_apellido
         FROM tm_asignaciones a
         JOIN tm_usuarios up ON up.id = a.paciente_id
         JOIN tm_usuarios uf ON uf.id = a.profesional_id
         WHERE a.activa = 1
         ORDER BY up.apellido"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// Pacientes asignados a un profesional puntual (lo que ve en su dashboard)
function pacientes_de_profesional(int $profesionalId): array {
    $st = db()->prepare(
        "SELECT u.*, a.area, a.id AS asignacion_id, o.nombre AS obra_social, pp.fecha_nacimiento
         FROM tm_asignaciones a
         JOIN tm_usuarios u ON u.id = a.paciente_id
         LEFT JOIN tm_perfiles_paciente pp ON pp.usuario_id = u.id
         LEFT JOIN tm_obras_sociales o ON o.id = pp.obra_social_id
         WHERE a.profesional_id = ? AND a.activa = 1
         ORDER BY u.apellido, u.nombre"
    );
    $st->bind_param('i', $profesionalId);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function pacientes_todos_activos(): array {
    $r = db()->query(
        "SELECT * FROM tm_usuarios WHERE rol='paciente' AND activo=1 ORDER BY apellido, nombre"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ── Catálogo de obras sociales ──────────────────────────────────

function obras_sociales_todas(): array {
    $r = db()->query(
        "SELECT * FROM tm_obras_sociales WHERE activa=1
         ORDER BY (nombre='Particular / Sin cobertura') DESC, (nombre='Otra') ASC, nombre"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

// ── Padrón único de personas (cruce turnos ↔ portal por DNI) ────

// Crea o actualiza la fila del padrón para ese DNI. Se llama automáticamente
// al registrar un paciente o editar su perfil. También se puede llamar desde
// la creación de turnos (ver nota en crear_turno más abajo).
function persona_upsert(string $dni, string $nombre, string $apellido, string $telefono, string $email, ?int $obraSocialId = null): void {
    $dni = preg_replace('/\D/', '', $dni);
    if (!$dni) return;
    $st = db()->prepare(
        "INSERT INTO tm_personas (dni, nombre, apellido, telefono, email, obra_social_id)
         VALUES (?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE
            nombre=VALUES(nombre), apellido=VALUES(apellido),
            telefono=IF(VALUES(telefono)<>'', VALUES(telefono), telefono),
            email=IF(VALUES(email)<>'', VALUES(email), email),
            obra_social_id=COALESCE(VALUES(obra_social_id), obra_social_id)"
    );
    $st->bind_param('sssssi', $dni, $nombre, $apellido, $telefono, $email, $obraSocialId);
    $st->execute();
}

function persona_buscar(string $dni): ?array {
    $dni = preg_replace('/\D/', '', $dni);
    $st = db()->prepare(
        "SELECT p.*, o.nombre AS obra_social
         FROM tm_personas p LEFT JOIN tm_obras_sociales o ON o.id = p.obra_social_id
         WHERE p.dni = ?"
    );
    $st->bind_param('s', $dni);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

// Cruce central: dado un DNI, ¿existe en el padrón?, ¿tiene cuenta en el portal?,
// ¿tiene turnos cargados? Pensado para el buscador de DNI del admin.
function persona_estado(string $dni): array {
    $dni = preg_replace('/\D/', '', $dni);
    $persona = persona_buscar($dni);

    $st1 = db()->prepare("SELECT id, rol, activo, nombre, apellido, email FROM tm_usuarios WHERE dni=?");
    $st1->bind_param('s', $dni);
    $st1->execute();
    $usuario = $st1->get_result()->fetch_assoc() ?: null;

    $st2 = db()->prepare("SELECT COUNT(*) AS cant FROM tm_turnos WHERE dni=?");
    $st2->bind_param('s', $dni);
    $st2->execute();
    $cantTurnos = (int)($st2->get_result()->fetch_assoc()['cant'] ?? 0);

    return [
        'dni' => $dni,
        'persona' => $persona,
        'tiene_cuenta' => $usuario !== null,
        'usuario' => $usuario,
        'tiene_turnos' => $cantTurnos > 0,
        'cantidad_turnos' => $cantTurnos,
    ];
}

// ── Turnos del paciente logueado (reutiliza tm_turnos existente) ─

function turnos_de_paciente(string $dni): array {
    $st = db()->prepare(
        "SELECT * FROM tm_turnos
         WHERE dni = ? AND estado != 'Cancelado'
         ORDER BY STR_TO_DATE(fecha,'%d/%m/%Y') DESC, hora DESC"
    );
    $st->bind_param('s', $dni);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
