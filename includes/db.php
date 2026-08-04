<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Conexión MySQL
// Base compartida con WordPress. Las tablas de Tecnomedic
// usan prefijo "tm_" para no colisionar con las "wp_" de WP.
//
// Toda la configuración sensible (DB, Brevo, Twilio, admin) vive
// ahora en el archivo .env (NO se sube a git). Este archivo solo
// define constantes a partir de esas variables. Para cambiar de
// entorno (local / staging / producción) simplemente se usa un
// .env distinto en cada servidor — no hace falta comentar/
// descomentar bloques acá adentro nunca más.
// ══════════════════════════════════════════════════════════════

require_once __DIR__ . '/env_loader.php';
cargar_env(__DIR__ . '/../.env');

define('BASE_URL', env('BASE_URL', 'http://localhost/tecnomedic_php'));
define('HOME_URL', env('HOME_URL', env('BASE_URL', 'http://localhost/tecnomedic_php')));

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'tecnomedic_local'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Credenciales admin (panel de turnos histórico)
define('ADMIN_USER', env('ADMIN_USER', 'admin'));
define('ADMIN_PASS', env('ADMIN_PASS', 'admin'));

// Brevo
define('BREVO_API_KEY', env('BREVO_API_KEY', ''));
define('MAIL_FROM', env('MAIL_FROM', 'noreply@tecnomedic.com.ar'));
define('MAIL_NAME', env('MAIL_NAME', 'TECNOMEDIC Turnos'));

// Twilio
define('TWILIO_SID', env('TWILIO_SID', ''));
define('TWILIO_TOKEN', env('TWILIO_TOKEN', ''));
define('TWILIO_WA_FROM', env('TWILIO_WA_FROM', 'whatsapp:+14155238886'));

// Meta WhatsApp Cloud API (si en algún momento se retoma)
define('META_WA_TOKEN', env('META_WA_TOKEN', ''));
define('META_WA_PHONE_ID', env('META_WA_PHONE_ID', ''));
define('META_WA_VERIFY_TOKEN', env('META_WA_VERIFY_TOKEN', ''));

// Agenda
define('MAX_POR_HORARIO', (int) env('MAX_POR_HORARIO', 2));
$HORARIOS = ['08:30','09:45','11:00','16:30','17:45','19:00'];

// ── Conexión ────────────────────────────────────────────────────
function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        // Sin esto, mysqli falla en SILENCIO ante errores de SQL
        // (INSERT/UPDATE que no se guardan sin ningún aviso).
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset(DB_CHARSET);
    }
    return $conn;
}

// ── CRUD Turnos ─────────────────────────────────────────────────

function get_turnos(): array {
    $r = db()->query(
        "SELECT id, nombre, apellido, dni, obra_social, telefono, email,
                DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha, hora, estado, creado_en
         FROM tm_turnos ORDER BY fecha, hora"
    );
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function get_turno_by_id(int $id): ?array {
    $st = db()->prepare(
        "SELECT id, nombre, apellido, dni, obra_social, telefono, email,
                DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha, hora, estado, creado_en
         FROM tm_turnos WHERE id=?"
    );
    $st->bind_param('i', $id);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    return $r ?: null;
}

function get_turnos_por_fecha(string $fecha): array {
    $st = db()->prepare("SELECT hora, estado FROM tm_turnos WHERE fecha = STR_TO_DATE(?, '%d/%m/%Y')");
    $st->bind_param('s', $fecha);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function crear_turno(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_turnos (nombre,apellido,dni,obra_social,telefono,email,fecha,hora,estado)
        VALUES (?,?,?,?,?,?,STR_TO_DATE(?, '%d/%m/%Y'),?,'Pendiente')"
    );
    $st->bind_param('ssssssss',
        $d['nombre'],$d['apellido'],$d['dni'],$d['obra_social'],
        $d['telefono'],$d['email'],$d['fecha'],$d['hora']
    );
    $st->execute();
    if (function_exists('persona_upsert')) {
        persona_upsert($d['dni'], $d['nombre'], $d['apellido'], $d['telefono'], $d['email']);
    }
    return db()->insert_id;
}

function actualizar_estado(int $id, string $estado): void {
    $st = db()->prepare("UPDATE tm_turnos SET estado=? WHERE id=?");
    $st->bind_param('si', $estado, $id);
    $st->execute();
}

function modificar_turno(int $id, array $d): void {
    $st = db()->prepare(
        "UPDATE tm_turnos SET nombre=?,apellido=?,dni=?,obra_social=?,
        telefono=?,email=?,fecha=STR_TO_DATE(?, '%d/%m/%Y'),hora=?,estado=? WHERE id=?"
    );
    $st->bind_param('sssssssssi',
        $d['nombre'],$d['apellido'],$d['dni'],$d['obra_social'],
        $d['telefono'],$d['email'],$d['fecha'],$d['hora'],$d['estado'],$id
    );
    $st->execute();
}

function eliminar_turno(int $id): void {
    $st = db()->prepare("DELETE FROM tm_turnos WHERE id=?");
    $st->bind_param('i', $id);
    $st->execute();
}

function get_ocupados(string $fecha): array {
    global $HORARIOS;
    $conteo = array_fill_keys($HORARIOS, 0);
    foreach (get_turnos_por_fecha($fecha) as $r) {
        if (strtolower($r['estado']) === 'cancelado') continue;
        $h = trim($r['hora']);
        if (isset($conteo[$h])) $conteo[$h]++;
    }
    return $conteo;
}

// Igual que get_ocupados(), pero sin contar el propio turno que se está
// editando (para poder validar disponibilidad al modificar sin que el
// turno se bloquee a sí mismo).
function get_ocupados_excluyendo(string $fecha, int $idExcluir): array {
    global $HORARIOS;
    $conteo = array_fill_keys($HORARIOS, 0);
    $st = db()->prepare("SELECT id, hora, estado FROM tm_turnos WHERE fecha = STR_TO_DATE(?, '%d/%m/%Y')");
    $st->bind_param('s', $fecha);
    $st->execute();
    foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        if ((int)$r['id'] === $idExcluir) continue;
        if (strtolower($r['estado']) === 'cancelado') continue;
        $h = trim($r['hora']);
        if (isset($conteo[$h])) $conteo[$h]++;
    }
    return $conteo;
}

// ── CRUD Sesiones Bot ───────────────────────────────────────────

function get_sesion(string $phone): array {
    $st = db()->prepare("SELECT * FROM tm_sesiones_bot WHERE phone=?");
    $st->bind_param('s', $phone);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if ($row) {
        $row['disp'] = $row['disp'] ? explode('|', $row['disp']) : [];
        return $row;
    }
    // Nueva sesión
    $st2 = db()->prepare("INSERT INTO tm_sesiones_bot (phone) VALUES (?)");
    $st2->bind_param('s', $phone);
    $st2->execute();
    return nueva_sesion($phone);
}

function nueva_sesion(string $phone): array {
    return [
        'phone'=>$phone,'step'=>'menu','nombre'=>'','apellido'=>'',
        'dni'=>'','obra_social'=>'','telefono'=>'','email'=>'',
        'fecha'=>'','hora'=>'','disp'=>[],'fila_turno'=>0
    ];
}

function save_sesion(array $s): void {
    $disp = implode('|', $s['disp'] ?? []);
    $st = db()->prepare(
        "UPDATE tm_sesiones_bot
        SET step=?,nombre=?,apellido=?,dni=?,obra_social=?,
            telefono=?,email=?,fecha=?,hora=?,disp=?,fila_turno=?
        WHERE phone=?"
    );
    $st->bind_param('ssssssssssis',
        $s['step'],$s['nombre'],$s['apellido'],$s['dni'],$s['obra_social'],
        $s['telefono'],$s['email'],$s['fecha'],$s['hora'],$disp,$s['fila_turno'],$s['phone']
    );
    $st->execute();
}

function reset_sesion(array &$s): void {
    foreach (['nombre','apellido','dni','obra_social','telefono','email','fecha','hora'] as $k)
        $s[$k] = '';
    $s['step'] = 'menu';
    $s['disp'] = [];
    $s['fila_turno'] = 0;
    save_sesion($s);
}

function buscar_turno_dni(string $dni): ?array {
    $dni = preg_replace('/\D/','',$dni);
    $st = db()->prepare(
        "SELECT id, nombre, apellido, dni, obra_social, telefono, email,
                DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha, hora, estado, creado_en
         FROM tm_turnos WHERE dni=? AND estado != 'Cancelado' LIMIT 1"
    );
    $st->bind_param('s', $dni);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

// Atajo para rutas: b('/static/css/tecnomedic.css') → BASE_URL . '/static/css/...'
function b(string $path = ''): string { return BASE_URL . $path; }