<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';
portal_require_login();

$pacienteId = (int)($_GET['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_ver($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso para ver este registro.');
}

$paciente = usuario_by_id($pacienteId);
if (!$paciente || $paciente['rol'] !== 'paciente') {
    http_response_code(404);
    die('Paciente no encontrado.');
}

$puedeEditar = ficha_puede_editar($pacienteId);
$perfil = perfil_paciente($pacienteId);
$objetivo = objetivo_nutricional_get($pacienteId);
$mediciones = mediciones_listar($pacienteId);
$comidas = comidas_listar($pacienteId);
$fichaNutricional = nutricion_ficha_get($pacienteId) ?? [];
$antropometria = nutricion_ultimo_registro('tm_antropometrias_nutricionales', $pacienteId) ?? [];
$composicion = nutricion_ultimo_registro('tm_composiciones_nutricionales', $pacienteId) ?? [];
$laboratorio = nutricion_ultimo_registro('tm_laboratorios_nutricionales', $pacienteId) ?? [];
$habitos = nutricion_ultimo_registro('tm_habitos_nutricionales', $pacienteId) ?? [];

$fechaNacimiento = $perfil['fecha_nacimiento'] ?? null;
$edad = null;
if ($fechaNacimiento) {
    try {
        $edad = (new DateTime($fechaNacimiento))->diff(new DateTime('today'))->y;
    } catch (Throwable $e) {
        $edad = null;
    }
}

$portal_titulo = 'Ficha de control nutricional · ' . $paciente['nombre'];
require __DIR__ . '/../../includes/portal_header.php';

$readonly = $puedeEditar ? '' : 'disabled';
$ultimaMedicion = $mediciones[0] ?? null;
$secciones = [
    'resumen' => ['icono' => '▦', 'texto' => 'Resumen'],
    'antropometria' => ['icono' => '◌', 'texto' => 'Antropometría'],
    'composicion' => ['icono' => '◒', 'texto' => 'Composición'],
    'laboratorio' => ['icono' => '⌁', 'texto' => 'Laboratorio'],
    'habitos' => ['icono' => '◷', 'texto' => 'Hábitos'],
    'diario' => ['icono' => '☷', 'texto' => 'Diario'],
];

function campoNutricion(string $name, string $label, string $unidad = '', string $type = 'number', string $placeholder = ''): void {
    global $nutricionValores, $readonly;
    $valor = $nutricionValores[$name] ?? '';
    ?>
    <label class="nutri-field">
        <span><?= htmlspecialchars($label) ?></span>
        <div class="nutri-control">
            <input type="<?= $type ?>" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars((string)$valor) ?>" <?= $type === 'number' ? 'step="any"' : '' ?> placeholder="<?= htmlspecialchars($placeholder) ?>" <?= $readonly ?>>
            <?php if ($unidad): ?><small><?= htmlspecialchars($unidad) ?></small><?php endif; ?>
        </div>
    </label>
    <?php
}
?>

<style>
    :root { --nm-navy:#0d1b2a; --nm-teal:#1aa5a5; --nm-green:#98c544; --nm-green-dk:#7aad2e; --nm-bg:#eef3f2; --nm-ink:#20312f; --nm-muted:#71817f; --nm-line:#dce7e4; --nm-soft:#f7faf9; }
    .nutri-record { max-width:1440px; margin:0 auto 36px; font-family:'Montserrat',sans-serif; color:var(--nm-ink); }
    .nutri-hero { background:linear-gradient(115deg,#0d1b2a 0%,#14394a 58%,#148080 130%); border-radius:18px 18px 0 0; padding:24px 28px; color:#fff; display:flex; align-items:center; gap:17px; box-shadow:0 14px 32px rgba(13,27,42,.15); }
    .nutri-avatar { width:54px; height:54px; display:grid; place-items:center; border-radius:16px; font-weight:700; font-size:1.08rem; color:#fff; background:linear-gradient(135deg,var(--nm-teal),var(--nm-green)); box-shadow:inset 0 1px 1px rgba(255,255,255,.25); }
    .nutri-hero-info { flex:1; min-width:0; } .nutri-eyebrow { margin:0 0 4px; color:#c9f06b; font:600 .67rem 'Poppins',sans-serif; letter-spacing:.13em; text-transform:uppercase; }
    .nutri-hero h1 { margin:0; font:600 1.2rem 'Poppins',sans-serif; } .nutri-hero-meta { display:flex; flex-wrap:wrap; gap:8px 16px; margin-top:7px; font-size:.74rem; color:rgba(255,255,255,.78); }
    .nutri-hero-action { border:1px solid rgba(255,255,255,.28); color:#fff; background:rgba(255,255,255,.08); border-radius:8px; padding:8px 12px; font:600 .72rem 'Poppins',sans-serif; text-decoration:none; white-space:nowrap; }
    .nutri-shell { display:grid; grid-template-columns:205px minmax(0,1fr); background:none; min-height:calc(100vh - 220px); border-radius:0 0 18px 18px; overflow:hidden; box-shadow:0 10px 28px rgba(13,27,42,.08); }
    .nutri-nav { padding:18px 12px; border-right:1px solid var(--nm-line); background:#f8fbfa; position:sticky; top:0; align-self:start; }
    .nutri-nav-title { padding:0 10px 9px; color:var(--nm-muted); font:600 .62rem 'Poppins',sans-serif; letter-spacing:.1em; text-transform:uppercase; }
    .nutri-nav button { width:100%; display:flex; align-items:center; gap:9px; padding:10px; border:0; border-radius:8px; background:transparent; color:#596966; cursor:pointer; text-align:left; font:600 .73rem 'Poppins',sans-serif; transition:.18s; }
    .nutri-nav button:hover,.nutri-nav button.active { color:#0e7171; background:rgba(26,165,165,.1); } .nutri-nav button i { width:16px; font-style:normal; text-align:center; font-size:.95rem; }
    .nutri-content { padding:22px; min-width:0; } .nutri-panel { display:none; animation:nutriFade .2s ease; } .nutri-panel.active { display:block; } @keyframes nutriFade { from{opacity:0;transform:translateY(5px)} to{opacity:1;transform:translateY(0)} }
    .nutri-section-head { display:flex; align-items:flex-start; gap:12px; margin:0 0 18px; } .nutri-section-icon { width:36px;height:36px;border-radius:10px;display:grid;place-items:center;background:rgba(26,165,165,.12);color:#148080;font-size:1rem; }
    .nutri-section-head h2 { margin:0 0 3px; color:var(--nm-navy); font:600 1rem 'Poppins',sans-serif; }.nutri-section-head p { margin:0; color:var(--nm-muted); font-size:.72rem; }
    .nutri-grid { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:14px; }.nutri-card { grid-column:span 12; background:#fff;border:1px solid var(--nm-line);border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(13,27,42,.03); }.nutri-card.half{grid-column:span 6}.nutri-card.third{grid-column:span 4}
    .nutri-card-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 15px;border-bottom:1px solid var(--nm-line);background:#7aad2e; }.nutri-card-head h3 { margin:0;color:var(--nm-navy);font:600 .8rem 'Poppins',sans-serif; }.nutri-card-head small { color:var(--nm-muted);font-size:.65rem; }.nutri-card-body { padding:15px; }
    .nutri-form-grid { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px; }.nutri-field { display:block;min-width:0; }.nutri-field.wide { grid-column:span 2; }.nutri-field.full { grid-column:1/-1; }.nutri-field>span { display:block;margin:0 0 5px;color:#536461;font:600 .64rem 'Poppins',sans-serif;letter-spacing:.02em; }.nutri-control { position:relative; }.nutri-control input,.nutri-control select,.nutri-control textarea { width:100%;box-sizing:border-box;border:1px solid #d7e3e0;border-radius:7px;background:#fff;color:var(--nm-ink);font:500 .76rem 'Montserrat',sans-serif;padding:9px 10px;min-height:36px;transition:.16s; }.nutri-control textarea{resize:vertical;min-height:72px}.nutri-control input:focus,.nutri-control select:focus,.nutri-control textarea:focus{outline:0;border-color:var(--nm-teal);box-shadow:0 0 0 3px rgba(26,165,165,.11)}.nutri-control input:disabled,.nutri-control select:disabled,.nutri-control textarea:disabled{background:#f3f6f5;color:#84918f;cursor:not-allowed}.nutri-control small { position:absolute;right:8px;top:50%;transform:translateY(-50%);color:#91a19e;font:600 .61rem 'Poppins',sans-serif;pointer-events:none; }.nutri-control:has(small) input { padding-right:42px; }
    .nutri-static { padding:10px;border-radius:7px;background:var(--nm-soft);border:1px solid #e4ece9;min-height:17px;font-size:.76rem;color:#344542; }.nutri-static.empty { color:#95a3a0;font-style:italic; }
    .nutri-note { padding:10px 12px;border-left:3px solid var(--nm-green);border-radius:0 7px 7px 0;background:#f6fbed;color:#65735a;font-size:.72rem;line-height:1.5; }.nutri-savebar { margin-top:18px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border:1px solid var(--nm-line);border-radius:10px;background:#fff; }.nutri-savebar span { color:var(--nm-muted);font-size:.69rem; }.nutri-save { border:0;border-radius:8px;background:linear-gradient(135deg,#7aad2e,#98c544);color:#fff;padding:10px 15px;cursor:pointer;font:600 .73rem 'Poppins',sans-serif;box-shadow:0 4px 10px rgba(122,173,46,.22); }.nutri-save:hover{transform:translateY(-1px)}
    .nutri-data-table { width:100%;border-collapse:collapse;font-size:.73rem; }.nutri-data-table th { text-align:left;color:var(--nm-muted);font:600 .63rem 'Poppins',sans-serif;text-transform:uppercase;letter-spacing:.04em;background:#f7faf9; }.nutri-data-table th,.nutri-data-table td { padding:9px;border-bottom:1px solid var(--nm-line); }.nutri-data-table tr:last-child td{border-bottom:0}.nutri-tag{display:inline-block;background:rgba(152,197,68,.13);color:#608c24;border-radius:99px;padding:3px 7px;font:600 .62rem 'Poppins',sans-serif;}.nutri-empty{color:#859490;text-align:center;padding:24px;font-size:.76rem}.nutri-toast{position:fixed;z-index:10020;right:24px;bottom:24px;background:var(--nm-navy);color:#fff;padding:12px 16px;border-radius:9px;font:500 .75rem 'Montserrat',sans-serif;box-shadow:0 10px 30px rgba(0,0,0,.22);opacity:0;transform:translateY(10px);pointer-events:none;transition:.2s}.nutri-toast.show{opacity:1;transform:none}
    @media(max-width:960px){.nutri-shell{grid-template-columns:1fr}.nutri-nav{position:relative;top:auto;overflow-x:auto;display:flex;padding:10px;border-right:0;border-bottom:1px solid var(--nm-line)}.nutri-nav-title{display:none}.nutri-nav button{width:auto;flex:0 0 auto}.nutri-nav button span{display:none}.nutri-card.half,.nutri-card.third{grid-column:span 12}.nutri-form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:620px){.nutri-record{margin:0 -10px 24px}.nutri-hero{border-radius:0;padding:17px;align-items:flex-start}.nutri-hero h1{font-size:1rem}.nutri-hero-action{padding:7px;font-size:0}.nutri-hero-action:first-letter{font-size:1rem}.nutri-content{padding:14px}.nutri-form-grid{grid-template-columns:1fr}.nutri-field.wide{grid-column:auto}.nutri-card-body{padding:12px}.nutri-data-table{display:block;overflow-x:auto;white-space:nowrap}.nutri-savebar{align-items:flex-start;flex-direction:column}.nutri-avatar{width:43px;height:43px;border-radius:12px}}
</style>

<div class="nutri-record">
    <header class="nutri-hero">
        <div class="nutri-avatar"><?= htmlspecialchars(strtoupper(substr($paciente['nombre'], 0, 1) . substr($paciente['apellido'], 0, 1))) ?></div>
        <div class="nutri-hero-info">
            <p class="nutri-eyebrow">Control nutricional profesional</p>
            <h1><?= htmlspecialchars($paciente['apellido'] . ', ' . $paciente['nombre']) ?></h1>
            <div class="nutri-hero-meta"><span>DNI <?= htmlspecialchars($paciente['dni']) ?></span><span><?= htmlspecialchars($paciente['telefono'] ?: 'Sin teléfono') ?></span><span><?= $edad !== null ? $edad . ' años' : 'Edad sin registrar' ?></span></div>
        </div>
        <a class="nutri-hero-action" href="javascript:history.back()">← Volver</a>
    </header>

    <div class="nutri-shell">
        <nav class="nutri-nav" aria-label="Secciones de ficha nutricional">
            <div class="nutri-nav-title">Ficha profesional</div>
            <?php foreach ($secciones as $id => $seccion): ?>
                <button type="button" data-panel="<?= $id ?>" class="<?= $id === 'resumen' ? 'active' : '' ?>"><i><?= $seccion['icono'] ?></i><span><?= $seccion['texto'] ?></span></button>
            <?php endforeach; ?>
        </nav>

        <main class="nutri-content">
            <section id="resumen" class="nutri-panel active">
                <div class="nutri-section-head"><div class="nutri-section-icon">▦</div><div><h2>Resumen clínico y contexto</h2><p>Información general para orientar la consulta nutricional.</p></div></div>
                <?php $nutricionValores = $fichaNutricional; ?>
                <form method="post" action="<?= b('/portal/nutricion/guardar_ficha_profesional.php') ?>">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="nutri-grid">
                        <article class="nutri-card half"><div class="nutri-card-head"><h3>Datos del paciente</h3><small>Información de perfil</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                            <label class="nutri-field"><span>Fecha de nacimiento</span><div class="nutri-static <?= !$fechaNacimiento ? 'empty' : '' ?>"><?= $fechaNacimiento ? htmlspecialchars(date('d/m/Y', strtotime($fechaNacimiento))) : 'Sin registrar' ?></div></label>
                            <label class="nutri-field"><span>Edad</span><div class="nutri-static <?= $edad === null ? 'empty' : '' ?>"><?= $edad !== null ? $edad . ' años' : 'Sin registrar' ?></div></label>
                            <label class="nutri-field"><span>Sexo biológico</span><div class="nutri-control"><select name="sexo" <?= $readonly ?>><option value="">Seleccionar</option><?php foreach (['Masc','Fem','Otro'] as $sexo): ?><option value="<?= $sexo ?>" <?= ($fichaNutricional['sexo_biologico'] ?? '') === $sexo ? 'selected' : '' ?>><?= $sexo ?></option><?php endforeach; ?></select></div></label>
                            <label class="nutri-field"><span>Ocupación / Tipo de trabajo</span><div class="nutri-control"><input type="text" name="ocupacion" value="<?= htmlspecialchars($fichaNutricional['ocupacion'] ?? '') ?>" placeholder="Ej.: oficina, turnos rotativos" <?= $readonly ?>></div></label>
                        </div></div></article>
                        <article class="nutri-card half"><div class="nutri-card-head"><h3>Motivo de consulta</h3><small>Contexto profesional</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                            <label class="nutri-field full"><span>Objetivo principal de la consulta</span><div class="nutri-control"><textarea name="objetivo" <?= $readonly ?> placeholder="Objetivo definido junto al paciente"><?= htmlspecialchars($objetivo['objetivo'] ?? '') ?></textarea></div></label>
                            <label class="nutri-field wide"><span>Patologías o condiciones diagnosticadas</span><div class="nutri-control"><textarea name="patologias" <?= $readonly ?> placeholder="Ej.: hipertensión, diabetes, celiaquía..."><?= htmlspecialchars($fichaNutricional['patologias'] ?? '') ?></textarea></div></label>
                            <label class="nutri-field wide"><span>Medicación actual</span><div class="nutri-control"><textarea name="medicacion" <?= $readonly ?> placeholder="Nombre, dosis y frecuencia"><?= htmlspecialchars($fichaNutricional['medicacion'] ?? '') ?></textarea></div></label>
                            <label class="nutri-field full"><span>Suplementación actual</span><div class="nutri-control"><input type="text" name="suplementacion" value="<?= htmlspecialchars($fichaNutricional['suplementacion'] ?? '') ?>" <?= $readonly ?> placeholder="Ej.: vitamina D, proteína, creatina"></div></label>
                        </div></div></article>
                        <article class="nutri-card"><div class="nutri-card-head"><h3>Última medición registrada</h3><small><?= $ultimaMedicion ? htmlspecialchars($ultimaMedicion['fecha_fmt']) : 'Sin registros previos' ?></small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                            <div class="nutri-field"><span>Peso</span><div class="nutri-static"><?= $ultimaMedicion && $ultimaMedicion['peso'] !== null ? htmlspecialchars($ultimaMedicion['peso']) . ' kg' : '-' ?></div></div>
                            <div class="nutri-field"><span>Altura</span><div class="nutri-static"><?= $ultimaMedicion && $ultimaMedicion['altura'] !== null ? htmlspecialchars($ultimaMedicion['altura']) . ' cm' : '-' ?></div></div>
                            <div class="nutri-field"><span>IMC</span><div class="nutri-static"><?= $ultimaMedicion && $ultimaMedicion['imc'] !== null ? htmlspecialchars($ultimaMedicion['imc']) : '-' ?></div></div>
                            <div class="nutri-field"><span>Observaciones</span><div class="nutri-static"><?= htmlspecialchars($ultimaMedicion['observaciones'] ?? '-') ?></div></div>
                        </div></div></article>
                    </div>
                    <div class="nutri-savebar"><span>Los cambios se guardan en la ficha profesional del paciente.</span><?php if ($puedeEditar): ?><button class="nutri-save" type="submit">✓ Guardar ficha profesional</button><?php endif; ?></div>
                </form>
            </section>

            <section id="antropometria" class="nutri-panel">
                <div class="nutri-section-head"><div class="nutri-section-icon">◌</div><div><h2>Antropometría básica</h2><p>Mediciones corporales para el seguimiento longitudinal.</p></div></div>
                <?php $nutricionValores = array_merge($antropometria, ['peso_actual' => $antropometria['peso'] ?? '', 'fecha_medicion' => $antropometria['fecha'] ?? date('Y-m-d')]); ?>
                <form method="post" action="<?= b('/portal/nutricion/guardar_control_profesional.php') ?>"><input type="hidden" name="paciente_id" value="<?= $pacienteId ?>"><input type="hidden" name="tipo" value="antropometria"><article class="nutri-card"><div class="nutri-card-head"><h3>Registro de medición</h3><small>Campos numéricos</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                    <?php campoNutricion('fecha_medicion', 'Fecha de medición', '', 'date'); campoNutricion('peso_actual', 'Peso actual', 'kg'); campoNutricion('altura', 'Altura / Estatura', 'cm'); campoNutricion('imc', 'IMC'); campoNutricion('cintura', 'Circunferencia de cintura', 'cm'); campoNutricion('cadera', 'Circunferencia de cadera', 'cm'); campoNutricion('brazo_relajado', 'Brazo relajado', 'cm'); campoNutricion('brazo_contraido', 'Brazo contraído', 'cm'); campoNutricion('muslo_medial', 'Muslo medial', 'cm'); ?>
                </div></div></article><div class="nutri-savebar"><span>Se guardará como un nuevo control antropométrico.</span><?php if ($puedeEditar): ?><button class="nutri-save" type="submit">Guardar antropometría</button><?php endif; ?></div></form>
                <article class="nutri-card" style="margin-top:16px"><div class="nutri-card-head"><h3>Historial de mediciones básicas</h3><small><?= count($mediciones) ?> registro(s)</small></div><div class="nutri-card-body"><?php if ($mediciones): ?><table class="nutri-data-table"><thead><tr><th>Fecha</th><th>Peso</th><th>Altura</th><th>IMC</th><th>Observaciones</th></tr></thead><tbody><?php foreach ($mediciones as $m): ?><tr><td><?= htmlspecialchars($m['fecha_fmt']) ?></td><td><?= $m['peso'] !== null ? htmlspecialchars($m['peso']) . ' kg' : '-' ?></td><td><?= $m['altura'] !== null ? htmlspecialchars($m['altura']) . ' cm' : '-' ?></td><td><?= htmlspecialchars($m['imc'] ?? '-') ?></td><td><?= htmlspecialchars($m['observaciones'] ?: '-') ?></td></tr><?php endforeach; ?></tbody></table><?php else: ?><div class="nutri-empty">Todavía no hay mediciones cargadas.</div><?php endif; ?></div></article>
            </section>

            <section id="composicion" class="nutri-panel">
                <div class="nutri-section-head"><div class="nutri-section-icon">◒</div><div><h2>Composición corporal</h2><p>Pliegues cutáneos y resultados de bioimpedancia.</p></div></div>
                <?php $nutricionValores = array_merge($composicion, ['imc_composicion' => $composicion['imc'] ?? '']); ?>
                <form method="post" action="<?= b('/portal/nutricion/guardar_control_profesional.php') ?>"><input type="hidden" name="paciente_id" value="<?= $pacienteId ?>"><input type="hidden" name="tipo" value="composicion"><input type="hidden" name="fecha_composicion" value="<?= date('Y-m-d') ?>"><div class="nutri-grid"><article class="nutri-card half"><div class="nutri-card-head"><h3>Pliegues cutáneos</h3><small>Milímetros</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                    <?php campoNutricion('pliegue_tricipital', 'Tricipital', 'mm'); campoNutricion('pliegue_bicipital', 'Bicipital', 'mm'); campoNutricion('pliegue_subescapular', 'Subescapular', 'mm'); campoNutricion('pliegue_suprailíaco', 'Suprailíaco', 'mm'); campoNutricion('pliegue_abdominal', 'Abdominal', 'mm'); campoNutricion('pliegue_muslo_anterior', 'Muslo anterior', 'mm'); campoNutricion('pliegue_pierna_medial', 'Pierna medial', 'mm'); ?>
                </div></div></article><article class="nutri-card half"><div class="nutri-card-head"><h3>Bioimpedancia</h3><small>Composición global</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                    <?php campoNutricion('porcentaje_grasa', 'Masa grasa total', '%'); campoNutricion('masa_grasa', 'Masa grasa total', 'kg'); campoNutricion('porcentaje_muscular', 'Masa muscular', '%'); campoNutricion('masa_muscular', 'Masa muscular total', 'kg'); campoNutricion('porcentaje_agua', 'Agua corporal total', '%'); campoNutricion('grasa_visceral', 'Nivel grasa visceral', 'nivel'); campoNutricion('imc_composicion', 'IMC'); ?>
                </div></div></article></div><div class="nutri-savebar"><span>Registrar resultados de composición corporal.</span><?php if ($puedeEditar): ?><button class="nutri-save" type="submit">Guardar composición corporal</button><?php endif; ?></div></form>
            </section>

            <section id="laboratorio" class="nutri-panel">
                <div class="nutri-section-head"><div class="nutri-section-icon">⌁</div><div><h2>Perfil bioquímico / Laboratorio</h2><p>Resultados de análisis de sangre informados por el paciente o laboratorio.</p></div></div>
                <?php $nutricionValores = $laboratorio; ?>
                <form method="post" action="<?= b('/portal/nutricion/guardar_control_profesional.php') ?>"><input type="hidden" name="paciente_id" value="<?= $pacienteId ?>"><input type="hidden" name="tipo" value="laboratorio"><article class="nutri-card"><div class="nutri-card-head"><h3>Resultados de laboratorio</h3><small>Valores numéricos</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                    <?php $nutricionValores['fecha_analisis'] = $laboratorio['fecha'] ?? date('Y-m-d'); campoNutricion('fecha_analisis', 'Fecha del análisis', '', 'date'); campoNutricion('glucosa', 'Glucosa en ayunas', 'mg/dL'); campoNutricion('hba1c', 'Hemoglobina Glicosilada · HbA1c', '%'); campoNutricion('insulina', 'Insulina en ayunas', 'mUI/L'); campoNutricion('colesterol_total', 'Colesterol total', 'mg/dL'); campoNutricion('colesterol_ldl', 'Colesterol LDL', 'mg/dL'); campoNutricion('colesterol_hdl', 'Colesterol HDL', 'mg/dL'); campoNutricion('trigliceridos', 'Triglicéridos', 'mg/dL'); campoNutricion('got', 'Transaminasas · GOT', 'U/L'); campoNutricion('gpt', 'Transaminasas · GPT', 'U/L'); campoNutricion('creatinina', 'Creatinina sérica', 'mg/dL'); campoNutricion('urea', 'Urea', 'mg/dL'); campoNutricion('acido_urico', 'Ácido úrico', 'mg/dL'); campoNutricion('hemoglobina', 'Hemoglobina', 'g/dL'); campoNutricion('hematocrito', 'Hematocrito', '%'); campoNutricion('ferritina', 'Ferritina', 'mcg/L'); campoNutricion('hierro', 'Hierro sérico', 'mcg/dL'); campoNutricion('tsh', 'TSH', 'mUI/L'); campoNutricion('t3_libre', 'T3 libre', ''); campoNutricion('t4_libre', 'T4 libre', ''); campoNutricion('vitamina_d', 'Vitamina D', 'ng/mL'); campoNutricion('vitamina_b12', 'Vitamina B12', 'pg/mL'); campoNutricion('acido_folico', 'Ácido fólico', 'ng/mL'); ?>
                </div></div></article><div class="nutri-savebar"><span>Sin interpretación automática de valores clínicos.</span><?php if ($puedeEditar): ?><button class="nutri-save" type="submit">Guardar perfil bioquímico</button><?php endif; ?></div></form>
            </section>

            <section id="habitos" class="nutri-panel">
                <div class="nutri-section-head"><div class="nutri-section-icon">◷</div><div><h2>Variables clínicas, hábitos y estilo de vida</h2><p>Variables subjetivas y clínicas relevantes para el plan nutricional.</p></div></div>
                <?php $nutricionValores = $habitos; ?>
                <form method="post" action="<?= b('/portal/nutricion/guardar_control_profesional.php') ?>"><input type="hidden" name="paciente_id" value="<?= $pacienteId ?>"><input type="hidden" name="tipo" value="habitos"><input type="hidden" name="fecha_habitos" value="<?= date('Y-m-d') ?>"><article class="nutri-card"><div class="nutri-card-head"><h3>Registro clínico y de hábitos</h3><small>Seguimiento integral</small></div><div class="nutri-card-body"><div class="nutri-form-grid">
                    <?php campoNutricion('presion_sistolica', 'Presión arterial sistólica', 'mmHg'); campoNutricion('presion_diastolica', 'Presión arterial diastólica', 'mmHg'); campoNutricion('horas_sueno', 'Horas de sueño promedio', 'h'); ?>
                    <label class="nutri-field"><span>Calidad del sueño percibida</span><div class="nutri-control"><select name="calidad_sueno" <?= $readonly ?>><option value="">Seleccionar</option><?php foreach (['Mala','Regular','Buena','Excelente'] as $calidad): ?><option value="<?= $calidad ?>" <?= ($habitos['calidad_sueno'] ?? '') === $calidad ? 'selected' : '' ?>><?= $calidad ?></option><?php endforeach; ?></select></div></label>
                    <?php campoNutricion('bristol', 'Tipo de evacuación · Bristol', '1–7'); campoNutricion('frecuencia_evacuacion', 'Frecuencia evacuación semanal', 'veces'); campoNutricion('estres', 'Nivel de estrés percibido', '1–10'); ?>
                    <label class="nutri-field"><span>Nivel de actividad física diaria</span><div class="nutri-control"><select name="actividad_fisica" <?= $readonly ?>><option value="">Seleccionar</option><?php foreach (['Sedentario','Ligero','Moderado','Intenso'] as $actividad): ?><option value="<?= $actividad ?>" <?= ($habitos['actividad_fisica'] ?? '') === $actividad ? 'selected' : '' ?>><?= $actividad ?></option><?php endforeach; ?></select></div></label>
                    <?php campoNutricion('agua', 'Consumo diario de agua', 'L'); ?>
                </div></div></article><div class="nutri-savebar"><span>Registrar variables clínicas y de estilo de vida.</span><?php if ($puedeEditar): ?><button class="nutri-save" type="submit">Guardar hábitos y variables</button><?php endif; ?></div></form>
            </section>

            <section id="diario" class="nutri-panel">
                <div class="nutri-section-head"><div class="nutri-section-icon">☷</div><div><h2>Diario alimentario</h2><p>Registros de comidas existentes para complementar la evaluación.</p></div></div>
                <article class="nutri-card"><div class="nutri-card-head"><h3>Comidas registradas</h3><small><?= count($comidas) ?> registro(s) recientes</small></div><div class="nutri-card-body"><?php if ($comidas): ?><table class="nutri-data-table"><thead><tr><th>Fecha</th><th>Comida</th><th>Descripción</th></tr></thead><tbody><?php $tipos = ['desayuno'=>'Desayuno','almuerzo'=>'Almuerzo','merienda'=>'Merienda','cena'=>'Cena','colacion'=>'Colación']; foreach ($comidas as $comida): ?><tr><td><?= htmlspecialchars($comida['fecha_fmt']) ?></td><td><span class="nutri-tag"><?= htmlspecialchars($tipos[$comida['tipo_comida']] ?? $comida['tipo_comida']) ?></span></td><td><?= nl2br(htmlspecialchars($comida['descripcion'])) ?></td></tr><?php endforeach; ?></tbody></table><?php else: ?><div class="nutri-empty">Todavía no hay comidas cargadas.</div><?php endif; ?></div></article>
                <?php if ($puedeEditar || portal_rol() === 'paciente'): ?>
                <form class="nutri-comida-form" method="post" action="<?= b('/portal/nutricion/agregar_comida.php') ?>" style="margin-top:16px">
                    <input type="hidden" name="paciente_id" value="<?= (int)$pacienteId ?>">
                    <div class="nutri-grid" style="gap:12px">
                        <div class="nutri-card half" style="grid-column:span 6">
                            <div class="nutri-card-body" style="padding:14px 15px">
                                <label class="nutri-field" style="margin:0">
                                    <span>Fecha</span>
                                    <div class="nutri-control">
                                        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="nutri-card half" style="grid-column:span 6">
                            <div class="nutri-card-body" style="padding:14px 15px">
                                <label class="nutri-field" style="margin:0">
                                    <span>Comida</span>
                                    <div class="nutri-control">
                                        <select name="tipo_comida" required>
                                            <option value="desayuno">Desayuno</option>
                                            <option value="almuerzo">Almuerzo</option>
                                            <option value="merienda">Merienda</option>
                                            <option value="cena">Cena</option>
                                            <option value="colacion">Colación</option>
                                        </select>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="nutri-card" style="grid-column:span 12">
                            <div class="nutri-card-body" style="padding:14px 15px">
                                <label class="nutri-field full" style="margin:0">
                                    <span>Descripción</span>
                                    <div class="nutri-control">
                                        <textarea name="descripcion" rows="3" placeholder="Qué comiste (y cantidades si querés)" required></textarea>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:12px">
                        <button class="nutri-save" type="submit">＋ Agregar comida</button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="nutri-note" style="margin-top:16px">El formulario de alta de comidas previo se conserva en el módulo nutricional; esta vista integra su historial dentro de la ficha profesional.</div>
            </section>
        </main>
    </div>
</div>
<?php if (!empty($_GET['ok'])): ?><div id="nutriToast" class="nutri-toast show">✓ Registro guardado correctamente.</div><?php endif; ?>
<script>
(function () {
    var buttons = document.querySelectorAll('.nutri-nav button');
    var panels = document.querySelectorAll('.nutri-panel');
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.getAttribute('data-panel');
            buttons.forEach(function (item) { item.classList.toggle('active', item === button); });
            panels.forEach(function (panel) { panel.classList.toggle('active', panel.id === id); });
        });
    });
    var section = new URLSearchParams(window.location.search).get('seccion');
    if (section) {
        var target = document.querySelector('.nutri-nav button[data-panel="' + section + '"]');
        if (target) target.click();
    }
    var toast = document.getElementById('nutriToast');
    if (toast) window.setTimeout(function () { toast.classList.remove('show'); }, 3600);
}());
</script>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
