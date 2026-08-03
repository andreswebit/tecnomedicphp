<?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " ERROR: $errstr en $errfile:$errline\n", FILE_APPEND);
});
register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " FATAL: {$e['message']} en {$e['file']}:{$e['line']}\n", FILE_APPEND);
    }
});

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/whatsapp.php';


// ══════════════════════════════════════════════════════════════
// CONSTANTES DEL BOT
// ══════════════════════════════════════════════════════════════

const HORARIOS_BOT = ['08:30','09:45','11:00','16:30','17:45','19:00'];
const OBRAS = [
    'Particular','PAMI','IOSCOR','OSDE','Swiss Medical',
    'Galeno','Medifé','OSECAC','OSPAT','IOMA','Otra'
];
const SALUDOS_BOT = [
    'hola','buenas','buenos','hi','hello','ola','buen dia','buen día',
    'buenas tardes','buenas noches','menu','menú','inicio','start',
    'turno','quiero un turno','que tal','como estan','cómo están'
];
const MENU_BOT = "🏥 *TECNOMEDIC* · Soluciones para la Salud\n\n"
    . "1️⃣  Sacar turno\n"
    . "2️⃣  Consultar mi turno\n"
    . "3️⃣  Modificar turno\n"
    . "4️⃣  Cancelar turno\n"
    . "5️⃣  Info y horarios\n"
    . "6️⃣  Nuestras áreas\n"
    . "7️⃣  Salir\n\n"
    . "_Respondé con el número de opción_";
const AREAS_BOT = "🏥 *Nuestras áreas:*\n\n"
    . "👂 *Audiología* — diagnóstico, audífonos y rehabilitación auditiva\n"
    . "🫁 *Medicina Hiperbárica* — oxigenoterapia y recuperación\n"
    . "🍎 *Nutrición* — consulta clínica, funcional y deportiva\n"
    . "🦽 *Ortopedia y Rehabilitación* — movilidad y soportes\n"
    . "⚕️ *Equipamiento Médico y Quirúrgico* — venta y alquiler\n\n"
    . "🌐 Más info: tecnomedic.com.ar\n\n"
    . "_Escribí *0* para volver al menú_";
const INFO_BOT = "ℹ️ *TECNOMEDIC* · Soluciones para la Salud\n\n"
    . "Audiología · Medicina Hiperbárica · Nutrición\nOrtopedia y Rehabilitación · Equipamiento Médico\n\n"
    . "🕐 *Mañana:* 8:30 a 13:00hs\n"
    . "🌙 *Tarde:*   16:30 a 20:30hs\n\n"
    . "📍 C. Pellegrini 799, Corrientes\n"
    . "📞 *(3794) 34-9278*\n\n"
    . "_Escribí *0* para volver al menú_";
const DESPEDIDA_BOT = "👋 ¡Hasta pronto!\n\nCuando necesites escribinos 😊\n*TECNOMEDIC* · 📞 (3794) 34-9278";

// ══════════════════════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════════════════════
function _wa(string $phone, string $msg): void {
    enviar_whatsapp(str_replace('whatsapp:','',$phone), $msg);
}


// function _wa(string $phone, string $msg): void {
//     enviar_whatsapp($phone, $msg);
// }

function _menu_fechas(array $disp): string {
    $nums = ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟'];
    $lines = [];
    foreach (array_slice($disp, 0, 10) as $i => $f)
        $lines[] = ($nums[$i] ?? ($i+1).'.') . " $f";
    return "📅 *Fechas disponibles:*\n\n" . implode("\n", $lines) . "\n\n_Respondé con el número:_";
}

function _menu_horarios(array $slots): string {
    $nums = ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣'];
    $lines = [];
    foreach ($slots as $i => $h) {
        $icono = $h <= '12:00' ? '☀️' : '🌙';
        $lines[] = ($nums[$i] ?? ($i+1).'.') . " $icono {$h}hs";
    }
    return "⏰ *Horarios disponibles:*\n\n" . implode("\n", $lines) . "\n\n_Respondé con el número:_";
}

function _menu_obras(): string {
    $nums = ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟','1️⃣1️⃣'];
    $lines = [];
    foreach (OBRAS as $i => $o)
        $lines[] = ($nums[$i] ?? ($i+1).'.') . " $o";
    return "🏥 *Cobertura médica:*\n\n" . implode("\n", $lines) . "\n\n_Respondé con el número:_";
}

function _fechas_con_slots(): array {
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hoy  = new DateTime();
    $fin  = new DateTime('last day of this month');
    $oc   = _get_ocupados_todos();
    $disp = [];
    for ($d = clone $hoy; $d <= $fin; $d->modify('+1 day')) {
        if ((int)$d->format('N') >= 6) continue; // sáb/dom
        $f = $d->format('d/m/Y');
        $libres = count(array_filter(HORARIOS_BOT, fn($h) => ($oc[$f][$h] ?? 0) < MAX_POR_HORARIO));
        if ($libres > 0) $disp[] = $f;
    }
    return [$disp, $oc];
}

function _get_ocupados_todos(): array {
    $oc = [];
    $r = db()->query("SELECT fecha, hora FROM tm_turnos WHERE estado != 'Cancelado'");
    while ($row = $r->fetch_assoc()) {
        $f = trim($row['fecha']); $h = trim($row['hora']);
        $oc[$f][$h] = ($oc[$f][$h] ?? 0) + 1;
    }
    return $oc;
}

function _slots_para_fecha(string $fecha, array $oc): array {
    return array_values(array_filter(HORARIOS_BOT, fn($h) => ($oc[$fecha][$h] ?? 0) < MAX_POR_HORARIO));
}

function _buscar_turno_dni_bot(string $dni): ?array {
    $dni = preg_replace('/\D/','',$dni);
    $st = db()->prepare("SELECT * FROM tm_turnos WHERE dni=? AND estado != 'Cancelado' LIMIT 1");
    $st->bind_param('s', $dni);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function _guardar_turno_bot(array $s, string $hora): bool {
    $st = db()->prepare(
        "INSERT INTO tm_turnos (nombre,apellido,dni,obra_social,telefono,email,fecha,hora,estado)
        VALUES (?,?,?,?,?,?,?,?,'Pendiente')"
    );
    $st->bind_param('ssssssss',
        $s['nombre'],$s['apellido'],$s['dni'],$s['obra_social'],
        $s['telefono'],$s['email'],$s['fecha'],$hora
    );
    return $st->execute();
}

function _modificar_fecha_hora_bot(int $id, string $fecha, string $hora): void {
    $st = db()->prepare("UPDATE tm_turnos SET fecha=?,hora=?,estado='Pendiente' WHERE id=?");
    $st->bind_param('ssi',$fecha,$hora,$id);
    $st->execute();
}

function _cancelar_turno_bot(int $id): void {
    $st = db()->prepare("UPDATE tm_turnos SET estado='Cancelado' WHERE id=?");
    $st->bind_param('i',$id);
    $st->execute();
}

function _tel_desde_phone(string $phone): string {
    return preg_replace('/\D/','',$phone);
}

// ══════════════════════════════════════════════════════════════
// PROCESADOR PRINCIPAL
// ══════════════════════════════════════════════════════════════

function procesar_bot(string $phone, string $msg): void {
    $sess = get_sesion($phone);
    $txt  = trim($msg);
    $low  = strtolower(trim($txt));
    $step = $sess['step'];

    // "0" → menú siempre
    if ($txt === '0') {
        reset_sesion($sess);
        _wa($phone, MENU_BOT); return;
    }
    // "7" → salir (salvo pasos que usen ese número)
    $pasos_con_7 = ['nuevo_obra_social','nuevo_telefono','nuevo_fecha','nuevo_hora',
                    'mod_fecha','mod_hora','cancel_conf'];
    if ($txt === '7' && !in_array($step, $pasos_con_7)) {
        reset_sesion($sess);
        _wa($phone, DESPEDIDA_BOT); return;
    }
    // ── MENÚ ────────────────────────────────────────────────────
    if ($step === 'menu') {
        if (in_array($low, SALUDOS_BOT) || !in_array($txt, ['1','2','3','4','5','6','7'])) {
            _wa($phone, MENU_BOT); return;
        }
            match ($txt) {
            '1' => (function() use (&$sess, $phone) {
                $sess['step'] = 'nuevo_nombre'; save_sesion($sess);
                _wa($phone, "📝 *Nuevo turno*\n\nIngresá tu *nombre*:");
            })(),
            '2' => (function() use (&$sess, $phone) {
                $sess['step'] = 'consultar_dni'; save_sesion($sess);
                _wa($phone, "🔍 *Consultar turno*\n\nIngresá tu *DNI* (solo números):");
            })(),
            '3' => (function() use (&$sess, $phone) {
                $sess['step'] = 'mod_dni'; save_sesion($sess);
                _wa($phone, "✏️ *Modificar turno*\n\nIngresá tu *DNI*:");
            })(),
            '4' => (function() use (&$sess, $phone) {
                $sess['step'] = 'cancel_dni'; save_sesion($sess);
                _wa($phone, "❌ *Cancelar turno*\n\nIngresá tu *DNI*:");
            })(),
            '5' => _wa($phone, INFO_BOT),
            '6' => _wa($phone, AREAS_BOT),
            '7' => (function() use (&$sess, $phone) {
                reset_sesion($sess);
                _wa($phone, DESPEDIDA_BOT);
            })(),
        };
        return;
    }

    // ── CONSULTAR ───────────────────────────────────────────────
    if ($step === 'consultar_dni') {
        $dni = preg_replace('/\D/','',$txt);
        if (strlen($dni) < 7) { _wa($phone,"⚠️ DNI inválido. Solo números (ej: 32456789):"); return; }
        $t = _buscar_turno_dni_bot($dni);
        if (!$t) {
            _wa($phone,"🔍 No encontré turno activo con ese DNI.\n\n_Escribí *0* para el menú._");
            reset_sesion($sess); return;
        }
        $emoji = ['Confirmado'=>'✅','Pendiente'=>'⏳','Cancelado'=>'❌'][$t['estado']] ?? '📋';
        _wa($phone,
            "📋 *Tu turno:*\n\n"
            . "👤 {$t['nombre']} {$t['apellido']}\n"
            . "🆔 DNI: {$t['dni']}\n"
            . "🏥 {$t['obra_social']}\n"
            . "📅 {$t['fecha']}  ⏰ {$t['hora']}hs\n"
            . "$emoji Estado: *{$t['estado']}*\n\n"
            . "_Escribí *0* para el menú_"
        );
        reset_sesion($sess); return;
    }

    // ── SACAR TURNO ─────────────────────────────────────────────
    if ($step === 'nuevo_nombre') {
        if (strlen($txt) < 2) { _wa($phone,"⚠️ Nombre muy corto. Ingresá tu nombre:"); return; }
        $sess['nombre'] = ucwords(strtolower($txt));
        $sess['step']   = 'nuevo_apellido';
        save_sesion($sess);
        _wa($phone,"👤 *{$sess['nombre']}*\n\nIngresá tu *apellido*:"); return;
    }
    if ($step === 'nuevo_apellido') {
        if (strlen($txt) < 2) { _wa($phone,"⚠️ Apellido muy corto:"); return; }
        $sess['apellido'] = ucwords(strtolower($txt));
        $sess['step']     = 'nuevo_dni';
        save_sesion($sess);
        _wa($phone,"👤 {$sess['nombre']} *{$sess['apellido']}*\n\n🆔 ¿Tu *DNI*? (solo números)"); return;
    }
    if ($step === 'nuevo_dni') {
        $dni = preg_replace('/\D/','',$txt);
        if (strlen($dni) < 7) { _wa($phone,"⚠️ DNI inválido. Solo números:"); return; }
        $sess['dni']  = $dni;
        $sess['step'] = 'nuevo_obra_social';
        save_sesion($sess);
        _wa($phone, _menu_obras()); return;
    }
    if ($step === 'nuevo_obra_social') {
        if (!ctype_digit($txt) || (int)$txt < 1 || (int)$txt > count(OBRAS)) {
            _wa($phone,"⚠️ Elegí un número del 1 al " . count(OBRAS) . "."); return;
        }
        $sess['obra_social'] = OBRAS[(int)$txt - 1];
        $sess['step']        = 'nuevo_telefono';
        save_sesion($sess);
        $tel = _tel_desde_phone($phone);
        _wa($phone,
            "🏥 *{$sess['obra_social']}*\n\n"
            . "📱 Tu número de WhatsApp es: *+$tel*\n\n"
            . "1️⃣  Sí, usar ese número\n"
            . "2️⃣  No, ingresar otro\n\n_Respondé con 1 o 2:_"
        ); return;
    }
    if ($step === 'nuevo_telefono') {
        if ($txt === '1') {
            $sess['telefono'] = _tel_desde_phone($phone);
            $sess['step']     = 'nuevo_email';
            save_sesion($sess);
            _wa($phone,"✉️ Ingresá tu *email* para la confirmación:");
        } elseif ($txt === '2') {
            $sess['step'] = 'nuevo_telefono_manual';
            save_sesion($sess);
            _wa($phone,"📱 Ingresá el teléfono (con código de área, ej: 3794123456):");
        } else {
            _wa($phone,"⚠️ Respondé con 1️⃣ (mi número) o 2️⃣ (ingresar otro):");
        }
        return;
    }
    if ($step === 'nuevo_telefono_manual') {
        $tel = preg_replace('/\D/','',$txt);
        if (strlen($tel) < 8) { _wa($phone,"⚠️ Número inválido. Ej: 3794123456:"); return; }
        $sess['telefono'] = $tel;
        $sess['step']     = 'nuevo_email';
        save_sesion($sess);
        _wa($phone,"✉️ Ingresá tu *email*:"); return;
    }
    if ($step === 'nuevo_email') {
        if (!str_contains($txt,'@') || !str_contains(explode('@',$txt)[1] ?? '','.')  ) {
            _wa($phone,"⚠️ Email inválido. Ej: nombre@mail.com"); return;
        }
        $sess['email'] = strtolower($txt);
        save_sesion($sess);
        [$disp] = _fechas_con_slots();
        if (!$disp) {
            _wa($phone,"😔 No hay fechas disponibles este mes.\n\n📞 Llamanos al (3794) 34-9278.");
            reset_sesion($sess); return;
        }
        $sess['disp'] = $disp;
        $sess['step'] = 'nuevo_fecha';
        save_sesion($sess);
        _wa($phone, _menu_fechas($disp)); return;
    }
    if ($step === 'nuevo_fecha') {
        $disp = $sess['disp'];
        if (!ctype_digit($txt) || (int)$txt < 1 || (int)$txt > count($disp)) {
            _wa($phone,"⚠️ Elegí un número del 1 al " . min(count($disp),10) . "."); return;
        }
        $fecha = $disp[(int)$txt - 1];
        [,$oc] = _fechas_con_slots();
        $slots = _slots_para_fecha($fecha, $oc);
        if (!$slots) {
            _wa($phone,"😔 Esa fecha se llenó. Elegí otra:\n\n" . _menu_fechas($disp)); return;
        }
        $sess['fecha'] = $fecha;
        $sess['disp']  = $slots;
        $sess['step']  = 'nuevo_hora';
        save_sesion($sess);
        _wa($phone,"📅 *$fecha*\n\n" . _menu_horarios($slots)); return;
    }
    if ($step === 'nuevo_hora') {
        $slots = $sess['disp'];
        if (!ctype_digit($txt) || (int)$txt < 1 || (int)$txt > count($slots)) {
            _wa($phone,"⚠️ Elegí un número del 1 al " . count($slots) . "."); return;
        }
        $hora = $slots[(int)$txt - 1];
        if (!_guardar_turno_bot($sess, $hora)) {
            _wa($phone,"❌ No se pudo guardar. Llamanos al 📞 (3794) 34-9278."); return;
        }
        _wa($phone,
            "🎉 *¡Turno solicitado!*\n\n"
            . "👤 {$sess['nombre']} {$sess['apellido']}\n"
            . "🆔 DNI: {$sess['dni']}\n🏥 {$sess['obra_social']}\n"
            . "📱 {$sess['telefono']}\n✉️ {$sess['email']}\n\n"
            . "📅 {$sess['fecha']}  ⏰ {$hora}hs\n\n"
            . "⏳ Te avisamos cuando esté *confirmado*.\n\n"
            . "📍 C. Pellegrini 799, Corrientes\n📞 (3794) 34-9278\n\n"
            . "_Escribí *0* para el menú_ 😊"
        );
        reset_sesion($sess); return;
    }

    // ── MODIFICAR ───────────────────────────────────────────────
    if ($step === 'mod_dni') {
        $dni = preg_replace('/\D/','',$txt);
        if (strlen($dni) < 7) { _wa($phone,"⚠️ DNI inválido:"); return; }
        $t = _buscar_turno_dni_bot($dni);
        if (!$t) {
            _wa($phone,"🔍 No encontré turno activo con ese DNI.\n\n_Escribí *0* para el menú._");
            reset_sesion($sess); return;
        }
        $sess['fila_turno'] = $t['id'];
        [$disp] = _fechas_con_slots();
        $sess['disp'] = $disp;
        $sess['step'] = 'mod_fecha';
        save_sesion($sess);
        _wa($phone,
            "📋 *Turno actual:*\n\n"
            . "👤 {$t['nombre']} {$t['apellido']}\n"
            . "📅 {$t['fecha']}  ⏰ {$t['hora']}hs\n\n"
            . _menu_fechas($disp)
        ); return;
    }
    if ($step === 'mod_fecha') {
        $disp = $sess['disp'];
        if (!ctype_digit($txt) || (int)$txt < 1 || (int)$txt > count($disp)) {
            _wa($phone,"⚠️ Elegí un número del 1 al " . min(count($disp),10) . "."); return;
        }
        $fecha = $disp[(int)$txt - 1];
        [,$oc] = _fechas_con_slots();
        $slots = _slots_para_fecha($fecha, $oc);
        $sess['fecha'] = $fecha;
        $sess['disp']  = $slots;
        $sess['step']  = 'mod_hora';
        save_sesion($sess);
        _wa($phone,"📅 *$fecha*\n\n" . _menu_horarios($slots)); return;
    }
    if ($step === 'mod_hora') {
        $slots = $sess['disp'];
        if (!ctype_digit($txt) || (int)$txt < 1 || (int)$txt > count($slots)) {
            _wa($phone,"⚠️ Elegí un número del 1 al " . count($slots) . "."); return;
        }
        $hora = $slots[(int)$txt - 1];
        _modificar_fecha_hora_bot((int)$sess['fila_turno'], $sess['fecha'], $hora);
        _wa($phone,
            "✏️ *¡Turno modificado!*\n\n"
            . "📅 Nueva fecha: {$sess['fecha']}\n⏰ Nueva hora: {$hora}hs\n\n"
            . "⏳ Te confirmaremos a la brevedad.\n\n_Escribí *0* para el menú._"
        );
        reset_sesion($sess); return;
    }

    // ── CANCELAR ────────────────────────────────────────────────
    if ($step === 'cancel_dni') {
        $dni = preg_replace('/\D/','',$txt);
        if (strlen($dni) < 7) { _wa($phone,"⚠️ DNI inválido:"); return; }
        $t = _buscar_turno_dni_bot($dni);
        if (!$t) {
            _wa($phone,"🔍 No encontré turno activo con ese DNI.\n\n_Escribí *0* para el menú._");
            reset_sesion($sess); return;
        }
        $sess['fila_turno'] = $t['id'];
        $sess['step']       = 'cancel_conf';
        save_sesion($sess);
        _wa($phone,
            "⚠️ *¿Confirmás la cancelación?*\n\n"
            . "👤 {$t['nombre']} {$t['apellido']}\n"
            . "🆔 DNI: {$t['dni']}\n"
            . "📅 {$t['fecha']}  ⏰ {$t['hora']}hs\n\n"
            . "1️⃣  Sí, cancelar el turno\n"
            . "2️⃣  No, mantener el turno\n\n_Respondé con 1 o 2:_"
        ); return;
    }
    if ($step === 'cancel_conf') {
        if ($txt === '1') {
            _cancelar_turno_bot((int)$sess['fila_turno']);
            _wa($phone,
                "✅ *Turno cancelado.*\n\n"
                . "Para otro turno escribinos o llamá al 📞 (3794) 34-9278.\n\n"
                . "_Escribí *0* para el menú._"
            );
        } elseif ($txt === '2') {
            _wa($phone,"👍 Tu turno sigue activo.\n\n_Escribí *0* para el menú._");
        } else {
            _wa($phone,"⚠️ Respondé con 1️⃣ (cancelar) o 2️⃣ (mantener):"); return;
        }
        reset_sesion($sess); return;
    }

    // Step desconocido
    error_log("Step desconocido '$step' para $phone");
    reset_sesion($sess);
    _wa($phone, MENU_BOT);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}

$phone = trim($_POST['From'] ?? '');
$msg   = trim($_POST['Body'] ?? '');

if (!$phone || !$msg) {
    header('Content-Type: text/xml');
    echo '<Response></Response>'; exit;
}

error_log("WA recibido de $phone: " . substr($msg, 0, 60));

procesar_bot($phone, $msg);

header('Content-Type: text/xml');
echo '<Response></Response>';