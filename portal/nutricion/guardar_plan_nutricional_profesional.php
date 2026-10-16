<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';

portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso.');
}

$modo = $_POST['modo'] ?? 'ambos';
$mesYM = trim($_POST['mes'] ?? '');

if ($modo !== 'semanal' && $modo !== 'mensual' && $modo !== 'ambos') {
    http_response_code(400);
    die('Modo inválido.');
}

$VALORES_COMIDAS = ['desayuno', 'almuerzo', 'merienda', 'cena', 'colacion'];

function plan_sanitizar_dia(array $dia, array $valoresComidas): array {
    $out = [];
    foreach ($valoresComidas as $comida) {
        $v = trim((string)($dia[$comida] ?? ''));
        // Limitamos tamaño para evitar JSON gigantes.
        if (mb_strlen($v) > 800) $v = mb_substr($v, 0, 800);
        $out[$comida] = $v;
    }
    return $out;
}

function plan_sanitizar_semana(array $semana, array $valoresComidas): array {
    $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    $out = ['dias' => []];
    foreach ($diasSemana as $dia) {
        $out['dias'][$dia] = plan_sanitizar_dia((array)($semana[$dia] ?? []), $valoresComidas);
    }
    return $out;
}

function plan_sanitizar_mes(array $mes, array $valoresComidas): array {
    // Esperamos un array con estructura: semanas[<idx>][<dia>][<comida>]
    // Importante: si desde UI no enviamos celdas fuera del mes, NO deberíamos
    // sobreescribirlas con vacíos. Por eso: solo incluimos días que vengan en $semData.
    $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    $out = ['mes' => '', 'semanas' => []];

    $out['mes'] = trim((string)($mes['mes'] ?? ''));
    if ($out['mes'] === '') {
        // Si no viene informado desde UI, lo completamos en handler por regex.
        $out['mes'] = $GLOBALS['__mesYM'] ?? '';
    }

    $semanas = $mes['semanas'] ?? [];
    if (!is_array($semanas)) $semanas = [];

    foreach ($semanas as $semIndex => $semData) {
        if (!is_numeric($semIndex)) continue;
        $semIndexInt = (int)$semIndex;
        if (!is_array($semData)) $semData = [];

        $diasOut = [];
        foreach ($diasSemana as $dia) {
            if (!isset($semData[$dia]) || !is_array($semData[$dia])) continue;
            $diasOut[$dia] = plan_sanitizar_dia((array)$semData[$dia], $valoresComidas);
        }

        $out['semanas'][] = [
            'semana_index' => $semIndexInt,
            'dias' => $diasOut
        ];
    }

    // Orden estable
    usort($out['semanas'], function($a, $b) {
        return ($a['semana_index'] ?? 0) <=> ($b['semana_index'] ?? 0);
    });

    return $out;
}

function validar_mes(string $mesYM): string {
    if (!preg_match('/^\\d{4}-\\d{2}$/', $mesYM)) {
        http_response_code(400);
        die('Mes inválido.');
    }
    return $mesYM;
}

$usuarioId = (int)($_SESSION['portal_uid'] ?? 0);
if (!$usuarioId) {
    http_response_code(403);
    die('Sesión inválida.');
}

$__mesYM = $mesYM;

// Separamos inputs: semana y mes llegan como arrays.
$semanaInput = $_POST['semana'] ?? [];
$mesInput = $_POST['mes_plan'] ?? [];
// Debug: aseguramos que la estructura de mes esté completa
// (si la UI enviara solo celdas fuera de mes, $planMensual terminaría vacío, pero se persiste igual).

if ($modo === 'semanal' || $modo === 'ambos') {
    if (!is_array($semanaInput)) $semanaInput = [];
    $planSemana = plan_sanitizar_semana($semanaInput, $VALORES_COMIDAS);
    plan_nutricional_semanal_guardar($pacienteId, $usuarioId, $planSemana);
}

if ($modo === 'mensual' || $modo === 'ambos') {
    if ($mesYM === '') {
        http_response_code(400);
        die('Tenés que indicar el mes.');
    }
    validar_mes($mesYM);

    // Normalizamos el mes con la key mes YM.
    if (!is_array($mesInput)) $mesInput = [];
    $mesInput['mes'] = $mesYM;

    $planMensual = plan_sanitizar_mes($mesInput, $VALORES_COMIDAS);
    plan_nutricional_mensual_guardar($pacienteId, $usuarioId, $mesYM, $planMensual);
}

header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId . '&seccion=plan&ok=plan'));
exit;
