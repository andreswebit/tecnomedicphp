<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';

portal_require_login();

// Intentamos generar PDF real con dompdf si existe vendor/autoload.php.
$dompdfPath = __DIR__ . '/../../vendor/autoload.php';
if (is_file($dompdfPath)) {
    require_once $dompdfPath;
}

$usarDompdf = class_exists('Dompdf\\Dompdf');

$pacienteId = (int)($_GET['paciente_id'] ?? 0);
$mesYM = trim((string)($_GET['mes'] ?? ''));

if (!$pacienteId || $mesYM === '') {
    http_response_code(400);
    exit('Parámetros inválidos.');
}

// Validación simple del mes: YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $mesYM)) {
    http_response_code(400);
    exit('Mes inválido.');
}

// Permiso: si es profesional, debe poder ver/editar según la lógica de ficha.
// Para descargar plan, usamos visibilidad/edición.
if (!ficha_puede_ver($pacienteId)) {
    http_response_code(403);
    exit('No tenés permiso.');
}

$planMensualJson = plan_nutricional_mensual_get($pacienteId, $mesYM);
$planMensualJson = is_array($planMensualJson) ? $planMensualJson : [];

// Buscamos el profesional asignado al paciente (para que el nombre del médico
// salga en el encabezado). Si hay más de uno, mostramos el primero activo.
$profNombre = '';
$usuarioSesion = portal_current_user();
$uid = $usuarioSesion ? (int)($usuarioSesion['id'] ?? 0) : 0;

$stProf = db()->prepare(
    "SELECT uf.nombre, uf.apellido
     FROM tm_asignaciones a
     JOIN tm_usuarios uf ON uf.id = a.profesional_id
     WHERE a.paciente_id=? AND a.activa=1
     ORDER BY a.id DESC
     LIMIT 1"
);
$stProf->bind_param('i', $pacienteId);
$stProf->execute();
$rowProf = $stProf->get_result()->fetch_assoc();
if ($rowProf) {
    $profNombre = trim(($rowProf['nombre'] ?? '') . ' ' . ($rowProf['apellido'] ?? ''));
}

// Fallback: si por algún motivo no hay asignación (o no coincide), usamos el nombre de la sesión.
if ($profNombre === '' && $usuarioSesion) {
    $profNombre = trim(($usuarioSesion['nombre'] ?? '') . ' ' . ($usuarioSesion['apellido'] ?? ''));
}

// Nombre paciente y DNI para el encabezado
$paciente = usuario_by_id($pacienteId);
$pacNombre = $paciente ? trim(($paciente['apellido'] ?? '') . ', ' . ($paciente['nombre'] ?? '')) : 'Paciente';
$pacDni = $paciente['dni'] ?? '';

// Si no hay plan cargado, igual generamos PDF con texto vacío.
$semanasGuardadas = $planMensualJson['semanas'] ?? [];
$mapSemanas = [];
if (is_array($semanasGuardadas)) {
    foreach ($semanasGuardadas as $sem) {
        $idx = (int)($sem['semana_index'] ?? 0);
        $mapSemanas[$idx] = $sem['dias'] ?? [];
    }
}

// Render mensual por grilla real (mismo criterio que la UI)
$mesDate = $mesYM . '-01';
$dtMes = new DateTime($mesDate);
$mesNum = (int)$dtMes->format('m');

$firstDow = (int)$dtMes->format('N');
$gridStart = clone $dtMes;
$gridStart->modify('-' . ($firstDow - 1) . ' days');

$dtLast = clone $dtMes;
$dtLast->modify('last day of this month');
$lastDow = (int)$dtLast->format('N');
$gridEnd = clone $dtLast;
$gridEnd->modify('+' . (7 - $lastDow) . ' days');

$diasSemanaFull = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
$semanaCount = (int)floor($gridStart->diff($gridEnd)->days / 7) + 1;
$semanasRender = range(1, $semanaCount);

// Nota: En este repo no detectamos librerías PDF (dompdf/tcpdf/mpdf).
// Para no romper despliegues, devolvemos un PDF "plano" solo si existen librerías.
// Si no existen, devolvemos un HTML maquetado con Content-Type text/html.
// En tu siguiente paso podemos integrar dompdf/mpdf si ya lo tenés en vendor/.

$havePdfLib = $usarDompdf;

$html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
:root{--g300:#b0bfbc;--green:#98c544;--teal:#1aa5a5;--navy:#0d1b2a;--muted:#8aada9;}
*{box-sizing:border-box}
body{font-family:"Poppins",sans-serif;background:var(--g300);margin:0;padding:0;color:#1f2b2a}
.page{width:100%;padding:20px}
.header{background:var(--navy);color:#fff;border-radius:16px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.brand{display:flex;align-items:center;gap:14px}
.logo{width:58px;height:58px;border-radius:14px;background:#fff;display:grid;place-items:center;overflow:hidden}
.logo img{width:100%;height:100%;object-fit:cover}
.brandText .t1{font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px;opacity:.92}
.brandText .t2{font-weight:700;font-size:18px;margin-top:3px}
.meta{font-size:12px;opacity:.95;text-align:right}
.card{margin-top:16px;background:#fff;border-radius:16px;padding:14px 16px;border:1px solid rgba(13,27,42,.08)}
.h2{font-size:14px;font-weight:800;color:var(--navy);margin-bottom:10px}
.kv{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:10px}
.kvItem{background:rgba(26,165,165,.08);border:1px solid rgba(26,165,165,.18);border-radius:12px;padding:8px 10px}
.kvLabel{font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase}
.kvVal{font-size:13px;font-weight:700;margin-top:3px}
.table{width:100%;border-collapse:collapse;table-layout:fixed}
.table th{background:rgba(152,197,68,.16);color:var(--navy);font-size:11px;text-transform:uppercase;letter-spacing:.03em;padding:9px 6px;border:1px solid rgba(13,27,42,.08)}
.table td{border:1px solid rgba(13,27,42,.08);vertical-align:top;padding:8px 6px}
.badgeSemana{display:inline-block;background:rgba(152,197,68,.14);border:1px solid rgba(152,197,68,.25);color:rgba(13,27,42,.9);font-weight:800;border-radius:999px;padding:4px 10px;font-size:11px}
.dayNum{font-size:12px;font-weight:800;color:var(--navy);margin-bottom:6px}
.comidaKey{font-size:10px;color:var(--muted);font-weight:800;text-transform:uppercase;margin-bottom:3px}
.comidaVal{white-space:pre-wrap;font-size:11.5px;line-height:1.25;min-height:20px}
.comidaBlock{margin-bottom:8px}
.footer{margin-top:14px;font-size:11px;color:rgba(13,27,42,.7)}

@media print{
  body{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
}
</style>
</head><body><div class="page">
<script>
  window.addEventListener("load", function () {
    try { window.print(); } catch(e) {}
  });
</script>';


$html .= '<div class="header">'
    .'<div class="brand">'
    .'<div class="logo"><img src="' . b('/static/tecno_logo.png') . '" alt="TECNOMEDIC"/></div>'
    .'<div class="brandText"><div class="t1">TecnoMedic</div><div class="t2">Plan nutricional</div></div>'
    .'</div>'
    .'<div class="meta">'
    .'<div><strong>Profesional:</strong> ' . htmlspecialchars($profNombre ?: '—') . '</div>'
    .'<div><strong>Mes:</strong> ' . htmlspecialchars($mesYM) . '</div>'
    .'</div>'
    .'</div>';

$html .= '<div class="card">'
    .'<div class="h2">Datos del paciente</div>'
    .'<div class="kv">'
    .'<div class="kvItem"><div class="kvLabel">Paciente</div><div class="kvVal">' . htmlspecialchars($pacNombre) . '</div></div>'
    .'<div class="kvItem"><div class="kvLabel">DNI</div><div class="kvVal">' . htmlspecialchars((string)$pacDni) . '</div></div>'
    .'</div>'
    .'<table class="table">'
    .'<thead><tr>'
    .'<th style="width:86px">Semana</th>';

foreach ($diasSemanaFull as $d) {
    $label = mb_strtoupper(mb_substr($d,0,1),'UTF-8') . mb_substr($d,1);
    $html .= '<th>' . htmlspecialchars($label) . '</th>';
}

$html .= '</tr></thead><tbody>';

foreach ($semanasRender as $semIndex) {
    $diasGuard = $mapSemanas[$semIndex] ?? [];
    $weekStart = clone $gridStart;
    $weekStart->modify('+' . (($semIndex - 1) * 7) . ' days');

    $html .= '<tr>';
    $html .= '<td><span class="badgeSemana">Semana ' . (int)$semIndex . '</span></td>';

    foreach ($diasSemanaFull as $diaKey) {
        $diaOffset = array_search($diaKey, $diasSemanaFull, true);
        $cellDate = clone $weekStart;
        $cellDate->modify('+' . $diaOffset . ' days');
        $inMonth = ((int)$cellDate->format('m') === $mesNum);
        $diaNum = (int)$cellDate->format('d');
        $diaData = $diasGuard[$diaKey] ?? [];

        $html .= '<td>';
        if ($inMonth) {
            $html .= '<div class="dayNum">' . $diaNum . '</div>';

            foreach (['desayuno','almuerzo','merienda','cena','colacion'] as $comKey) {
                $comLabel = ucfirst($comKey);
                $comVal = (string)($diaData[$comKey] ?? '');
                $html .= '<div class="comidaBlock">'
                    .'<div class="comidaKey">' . htmlspecialchars($comLabel) . '</div>'
                    .'<div class="comidaVal">' . htmlspecialchars($comVal) . '</div>'
                    .'</div>';
            }
        } else {
            $html .= '<div style="opacity:.4">—</div>';
        }

        $html .= '</td>';
    }

    $html .= '</tr>';
}

$html .= '</tbody></table>'
    .'<div class="footer">Generado automáticamente desde el Portal TECNOMEDIC.</div>'
    .'</div>';

$html .= '</div></body></html>';

if ($havePdfLib) {
    // dompdf real
    // Nota: en Ferozo probablemente ya no existe vendor/, pero lo dejamos
    // preparado para cuando esté.
    $dompdf = new Dompdf\Dompdf();
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="plan_nutricional_' . (int)$pacienteId . '_' . $mesYM . '.pdf"');
    echo $dompdf->output();
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo $html;
exit;
