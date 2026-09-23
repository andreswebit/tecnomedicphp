<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Portal (Fase E)
// Registro alimentario y ficha de control nutricional profesional.
// Requiere que db_ficha.php ya esté cargado para sus permisos.
// ══════════════════════════════════════════════════════════════

function objetivo_nutricional_get(int $pacienteId): ?array {
    $st = db()->prepare("SELECT * FROM tm_objetivo_nutricional WHERE paciente_id=?");
    $st->bind_param('i', $pacienteId);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function objetivo_nutricional_guardar(int $pacienteId, string $objetivo, int $actualizadoPor): void {
    $st = db()->prepare("INSERT INTO tm_objetivo_nutricional (paciente_id, objetivo, actualizado_por) VALUES (?,?,?) ON DUPLICATE KEY UPDATE objetivo=VALUES(objetivo), actualizado_por=VALUES(actualizado_por)");
    $st->bind_param('isi', $pacienteId, $objetivo, $actualizadoPor);
    $st->execute();
}

function mediciones_listar(int $pacienteId): array {
    $st = db()->prepare("SELECT m.*, DATE_FORMAT(m.fecha,'%d/%m/%Y') AS fecha_fmt, u.nombre AS registrado_por_nombre, u.apellido AS registrado_por_apellido FROM tm_mediciones m JOIN tm_usuarios u ON u.id = m.registrado_por WHERE m.paciente_id = ? ORDER BY m.fecha DESC, m.id DESC");
    $st->bind_param('i', $pacienteId); $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function medicion_crear(int $pacienteId, int $registradoPor, string $fechaDMY, ?float $peso, ?float $altura, string $observaciones): int {
    $imc = null;
    if ($peso && $altura) { $alturaMts = $altura / 100; $imc = round($peso / ($alturaMts * $alturaMts), 2); }
    $st = db()->prepare("INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por) VALUES (?, STR_TO_DATE(?,'%d/%m/%Y'), ?, ?, ?, ?, ?)");
    $st->bind_param('isdddsi', $pacienteId, $fechaDMY, $peso, $altura, $imc, $observaciones, $registradoPor);
    $st->execute(); return db()->insert_id;
}
function medicion_eliminar(int $id): void { $st = db()->prepare("DELETE FROM tm_mediciones WHERE id=?"); $st->bind_param('i', $id); $st->execute(); }
function medicion_get(int $id): ?array { $st = db()->prepare("SELECT * FROM tm_mediciones WHERE id=?"); $st->bind_param('i', $id); $st->execute(); return $st->get_result()->fetch_assoc() ?: null; }

function comidas_listar(int $pacienteId, int $limite = 60): array {
    $st = db()->prepare("SELECT c.*, DATE_FORMAT(c.fecha,'%d/%m/%Y') AS fecha_fmt, u.nombre AS registrado_por_nombre, u.apellido AS registrado_por_apellido FROM tm_comidas c JOIN tm_usuarios u ON u.id = c.registrado_por WHERE c.paciente_id = ? ORDER BY c.fecha DESC, FIELD(c.tipo_comida,'desayuno','colacion','almuerzo','merienda','cena'), c.id DESC LIMIT ?");
    $st->bind_param('ii', $pacienteId, $limite); $st->execute(); return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
function comida_crear(int $pacienteId, int $registradoPor, string $fechaDMY, string $tipoComida, string $descripcion): int {
    $st = db()->prepare("INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por) VALUES (?, STR_TO_DATE(?,'%d/%m/%Y'), ?, ?, ?)");
    $st->bind_param('isssi', $pacienteId, $fechaDMY, $tipoComida, $descripcion, $registradoPor); $st->execute(); return db()->insert_id;
}
function comida_get(int $id): ?array { $st = db()->prepare("SELECT * FROM tm_comidas WHERE id=?"); $st->bind_param('i', $id); $st->execute(); return $st->get_result()->fetch_assoc() ?: null; }
function comida_eliminar(int $id): void { $st = db()->prepare("DELETE FROM tm_comidas WHERE id=?"); $st->bind_param('i', $id); $st->execute(); }

function nutricion_ficha_get(int $pacienteId): ?array {
    $st = db()->prepare("SELECT * FROM tm_fichas_nutricionales WHERE paciente_id=?");
    $st->bind_param('i', $pacienteId); $st->execute(); return $st->get_result()->fetch_assoc() ?: null;
}
function nutricion_ultimo_registro(string $tabla, int $pacienteId): ?array {
    $tablas = ['tm_antropometrias_nutricionales', 'tm_composiciones_nutricionales', 'tm_laboratorios_nutricionales', 'tm_habitos_nutricionales'];
    if (!in_array($tabla, $tablas, true)) return null;
    $st = db()->prepare("SELECT * FROM $tabla WHERE paciente_id=? ORDER BY fecha DESC, id DESC LIMIT 1");
    $st->bind_param('i', $pacienteId); $st->execute(); return $st->get_result()->fetch_assoc() ?: null;
}
function nutricion_historial(int $pacienteId, string $tabla, int $limite = 5): array {
    $tablas = ['tm_antropometrias_nutricionales', 'tm_composiciones_nutricionales', 'tm_laboratorios_nutricionales', 'tm_habitos_nutricionales'];
    if (!in_array($tabla, $tablas, true)) return [];
    $st = db()->prepare("SELECT *, DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha_fmt FROM $tabla WHERE paciente_id=? ORDER BY fecha DESC, id DESC LIMIT ?");
    $st->bind_param('ii', $pacienteId, $limite); $st->execute(); return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
function nutricion_ficha_guardar(int $pacienteId, int $usuarioId, array $datos): void {
    $sexo = in_array($datos['sexo'] ?? '', ['Masc', 'Fem', 'Otro'], true) ? $datos['sexo'] : null;
    $ocupacion = trim($datos['ocupacion'] ?? ''); $patologias = trim($datos['patologias'] ?? '');
    $medicacion = trim($datos['medicacion'] ?? ''); $suplementacion = trim($datos['suplementacion'] ?? '');
    $st = db()->prepare("INSERT INTO tm_fichas_nutricionales (paciente_id,sexo_biologico,ocupacion,patologias,medicacion,suplementacion,actualizado_por) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE sexo_biologico=VALUES(sexo_biologico),ocupacion=VALUES(ocupacion),patologias=VALUES(patologias),medicacion=VALUES(medicacion),suplementacion=VALUES(suplementacion),actualizado_por=VALUES(actualizado_por)");
    $st->bind_param('isssssi', $pacienteId, $sexo, $ocupacion, $patologias, $medicacion, $suplementacion, $usuarioId); $st->execute();
    objetivo_nutricional_guardar($pacienteId, trim($datos['objetivo'] ?? ''), $usuarioId);
}
function nutricion_crear_registro(string $tabla, int $pacienteId, int $usuarioId, array $permitidos, array $datos, string $fecha = ''): void {
    $tablas = ['tm_antropometrias_nutricionales', 'tm_composiciones_nutricionales', 'tm_laboratorios_nutricionales', 'tm_habitos_nutricionales'];
    if (!in_array($tabla, $tablas, true)) throw new InvalidArgumentException('Tabla nutricional inválida.');
    $fecha = $fecha ?: date('Y-m-d');
    $campos = ['paciente_id', 'fecha']; $valores = [$pacienteId, $fecha]; $tipos = 'is';
    foreach ($permitidos as $campo) {
        $campos[] = $campo;
        $valor = trim((string)($datos[$campo] ?? ''));
        $valores[] = $valor === '' ? null : $valor;
        $tipos .= 's';
    }
    $campos[] = 'registrado_por'; $valores[] = $usuarioId; $tipos .= 'i';
    $sql = 'INSERT INTO ' . $tabla . ' (' . implode(',', $campos) . ') VALUES (' . implode(',', array_fill(0, count($campos), '?')) . ')';
    $st = db()->prepare($sql);
    $refs = [$tipos]; foreach ($valores as $i => $valor) $refs[] = &$valores[$i];
    call_user_func_array([$st, 'bind_param'], $refs); $st->execute();
}
