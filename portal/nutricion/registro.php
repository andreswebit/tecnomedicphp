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

$puedeEditar = ficha_puede_editar($pacienteId); // false para el propio paciente viendo su registro (no puede poner objetivo)
$puedeCargar = true; // cualquiera con acceso de vista puede auto-registrar comidas/mediciones (incluido el paciente)

$objetivo    = objetivo_nutricional_get($pacienteId);
$mediciones  = mediciones_listar($pacienteId);
$comidas     = comidas_listar($pacienteId);

$portal_titulo = 'Registro alimentario · ' . $paciente['nombre'];
require __DIR__ . '/../../includes/portal_header.php';
?>

<style>
    .ficha-medica {
        font-family: 'Montserrat', sans-serif;
        background: #edf1f0;
        color: #333a39;
        min-height: calc(100vh - 80px);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(13,27,42,0.08);
    }
    .ficha-header {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #0d1b2a;
        color: #fff;
        padding: 16px 20px;
    }
    .ficha-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: linear-gradient(135deg, #30c5c5, #98c544);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        flex-shrink: 0;
        color: #fff;
    }
    .ficha-paciente {
        flex: 1;
        min-width: 0;
    }
    .ficha-paciente-nombre {
        font-size: 0.96rem;
        font-weight: 600;
        margin-bottom: 3px;
    }
    .ficha-paciente-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 0.72rem;
        opacity: 0.9;
    }
    .ficha-badge {
        background: rgba(152,197,68,.18);
        color: #c9f06b;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.68rem;
        font-weight: 600;
        border: 1px solid rgba(152,197,68,.28);
    }
    .ficha-card {
        background: #fff;
        border-radius: 12px;
        margin: 14px 14px 0;
        box-shadow: 0 1px 6px rgba(13,27,42,.07);
        overflow: hidden;
    }
    .ficha-card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8e6;
    }
    .ficha-card-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .ficha-card-icon.blue { background: rgba(26,165,165,.12); color: var(--teal-bright, #30c5c5); }
    .ficha-card-icon.green { background: rgba(152,197,68,.12); color: var(--green-dk, #7aad2e); }
    .ficha-card-icon.amber { background: rgba(245,158,11,.12); color: #d97706; }
    .ficha-card-icon.red { background: rgba(224,85,85,.12); color: #c94f4f; }
    .ficha-card-title { font-size: .9rem; font-weight: 600; color: #0d1b2a; flex: 1; }
    .ficha-card-body { padding: 12px 14px 14px; }
    .ficha-field { margin-bottom: 12px; }
    .ficha-field-label {
        font-size: 0.68rem;
        font-weight: 600;
        color: #555f5e;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .ficha-textarea,
    .ficha-input,
    .ficha-select {
        width: 100%;
        box-sizing: border-box;
        padding: 9px 10px;
        border: 1px solid #dfe7e3;
        border-radius: 8px;
        background: #edf1f0;
        color: #333a39;
        font-size: 0.8rem;
        font-family: 'Montserrat', sans-serif;
    }
    .ficha-textarea:focus,
    .ficha-input:focus,
    .ficha-select:focus {
        outline: none;
        border-color: #30c5c5;
        box-shadow: 0 0 0 2px rgba(26,165,165,.12);
        background: #fff;
    }
    .ficha-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all .15s ease;
        padding: 7px 12px;
        text-decoration: none;
    }
    .ficha-btn-primary { background: #7aad2e; color: #fff; }
    .ficha-btn-primary:hover { background: #98c544; transform: translateY(-1px); }
    .ficha-btn-outline {
        background: transparent;
        border: 1px solid #30c5c5;
        color: #30c5c5;
    }
    .ficha-btn-outline:hover { background: rgba(26,165,165,.06); }
    .ficha-btn-teal { background: #30c5c5; color: #fff; }
    .ficha-btn-teal:hover { background: #148080; transform: translateY(-1px); }
    .ficha-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.76rem;
    }
    .ficha-table th {
        padding: 7px 9px;
        text-align: left;
        color: #8aada9;
        border-bottom: 1px solid #e2e8e6;
        white-space: nowrap;
        font-weight: 600;
    }
    .ficha-table td {
        padding: 7px 9px;
        border-bottom: 1px solid #e2e8e6;
        vertical-align: middle;
    }
    .ficha-table tr:last-child td { border-bottom: none; }
    .ficha-table tr:hover td { background: rgba(237,241,240,.5); }
    .ficha-empty {
        text-align: center;
        padding: 28px 16px 12px;
        color: #8aada9;
        font-size: 0.8rem;
    }
    .ficha-empty-icon { font-size: 1.8rem; margin-bottom: 8px; opacity: .7; }
    .ficha-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .ficha-list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8e6;
    }
    .ficha-list li:last-child { border-bottom: none; }
    .ficha-list strong { color: #0d1b2a; }
    .ficha-list .mini-btn {
        margin-left: auto;
        background: transparent;
        border: none;
        color: #c94f4f;
        cursor: pointer;
        padding: 0 4px;
        font-size: 0.75rem;
    }
    @media (max-width: 640px) {
        .ficha-header { align-items: flex-start; }
        .ficha-paciente-meta { flex-direction: column; gap: 4px; }
        .ficha-card { margin-left: 10px; margin-right: 10px; }
    }
</style>

<div class="ficha-medica">
    <div class="ficha-header">
        <div class="ficha-avatar"><?= strtoupper(substr($paciente['nombre'], 0, 1)) . strtoupper(substr($paciente['apellido'], 0, 1)) ?></div>
        <div class="ficha-paciente">
            <div class="ficha-paciente-nombre"><?= htmlspecialchars($paciente['apellido'] . ', ' . $paciente['nombre']) ?></div>
            <div class="ficha-paciente-meta">
                <span>🪪 <?= htmlspecialchars($paciente['dni']) ?></span>
                <span>📞 <?= htmlspecialchars($paciente['telefono'] ?: '-') ?></span>
            </div>
        </div>
        <div class="ficha-badge">Nutrición</div>
        <button type="button" class="ficha-btn ficha-btn-outline" onclick="history.back()" style="margin-left:8px;">← Volver</button>
    </div>

    <div class="ficha-card">
        <div class="ficha-card-header">
            <div class="ficha-card-icon green">🎯</div>
            <div class="ficha-card-title">Objetivo nutricional</div>
        </div>
        <div class="ficha-card-body">
            <?php if ($puedeEditar): ?>
                <form class="portal-form" method="post" action="<?= b('/portal/nutricion/guardar_objetivo.php') ?>">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="ficha-field">
                        <div class="ficha-field-label">Objetivo</div>
                        <textarea class="ficha-textarea" name="objetivo" rows="3" placeholder="Ej: Bajar 4kg en 3 meses, reducir consumo de azúcares..."><?= htmlspecialchars($objetivo['objetivo'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="ficha-btn ficha-btn-primary">Guardar objetivo</button>
                </form>
            <?php else: ?>
                <div class="ficha-empty" style="padding:12px 0 0;">
                    <div class="ficha-empty-icon">🎯</div>
                    <div><?= nl2br(htmlspecialchars($objetivo['objetivo'] ?? 'Todavía no se definió un objetivo.')) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ficha-card">
        <div class="ficha-card-header">
            <div class="ficha-card-icon blue">⚖️</div>
            <div class="ficha-card-title">Mediciones</div>
        </div>
        <div class="ficha-card-body">
            <?php if (!$mediciones): ?>
                <div class="ficha-empty">
                    <div class="ficha-empty-icon">📏</div>
                    <div>Todavía no hay mediciones cargadas.</div>
                </div>
            <?php else: ?>
                <table class="ficha-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Peso</th>
                            <th>Altura</th>
                            <th>IMC</th>
                            <th>Observaciones</th>
                            <?php if ($puedeEditar): ?><th></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($mediciones as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['fecha_fmt']) ?></td>
                            <td><?= $m['peso'] !== null ? htmlspecialchars($m['peso']) . ' kg' : '-' ?></td>
                            <td><?= $m['altura'] !== null ? htmlspecialchars($m['altura']) . ' cm' : '-' ?></td>
                            <td><?= $m['imc'] !== null ? htmlspecialchars($m['imc']) : '-' ?></td>
                            <td><?= htmlspecialchars($m['observaciones'] ?: '-') ?></td>
                            <?php if ($puedeEditar): ?>
                            <td>
                                <form method="post" action="<?= b('/portal/nutricion/eliminar_medicion.php') ?>" onsubmit="return confirm('¿Eliminar esta medición?');">
                                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="ficha-btn ficha-btn-outline" style="padding:5px 8px;">Borrar</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if ($puedeCargar): ?>
                <form class="portal-form" method="post" action="<?= b('/portal/nutricion/agregar_medicion.php') ?>" style="margin-top:18px;">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="ficha-field">
                        <div class="ficha-field-label">Fecha</div>
                        <input class="ficha-input" type="date" name="fecha" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="ficha-field">
                        <div class="ficha-field-label">Peso (kg)</div>
                        <input class="ficha-input" type="number" step="0.1" name="peso" placeholder="Ej: 72.5">
                    </div>
                    <div class="ficha-field">
                        <div class="ficha-field-label">Altura (cm)</div>
                        <input class="ficha-input" type="number" step="0.1" name="altura" placeholder="Ej: 168">
                    </div>
                    <div class="ficha-field">
                        <div class="ficha-field-label">Observaciones</div>
                        <input class="ficha-input" type="text" name="observaciones" placeholder="Opcional">
                    </div>
                    <button type="submit" class="ficha-btn ficha-btn-teal">Agregar medición</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="ficha-card">
        <div class="ficha-card-header">
            <div class="ficha-card-icon amber">🍽️</div>
            <div class="ficha-card-title">Comidas registradas</div>
        </div>
        <div class="ficha-card-body">
            <?php if (!$comidas): ?>
                <div class="ficha-empty">
                    <div class="ficha-empty-icon">🥗</div>
                    <div>Todavía no hay comidas cargadas.</div>
                </div>
            <?php else: ?>
                <?php
                $tiposNombres = ['desayuno' => 'Desayuno', 'almuerzo' => 'Almuerzo', 'merienda' => 'Merienda', 'cena' => 'Cena', 'colacion' => 'Colación'];
                $fechaActual = null;
                foreach ($comidas as $c):
                    if ($c['fecha_fmt'] !== $fechaActual):
                        if ($fechaActual !== null) echo '</ul>';
                        $fechaActual = $c['fecha_fmt'];
                        echo '<div style="margin:10px 0 6px;font-weight:600;color:#0d1b2a;">' . htmlspecialchars($fechaActual) . '</div><ul class="ficha-list">';
                    endif;
                ?>
                    <li>
                        <div style="flex:1;">
                            <strong><?= htmlspecialchars($tiposNombres[$c['tipo_comida']] ?? $c['tipo_comida']) ?>:</strong>
                            <?= nl2br(htmlspecialchars($c['descripcion'])) ?>
                        </div>
                        <?php if ($puedeEditar): ?>
                            <form method="post" action="<?= b('/portal/nutricion/eliminar_comida.php') ?>" onsubmit="return confirm('¿Eliminar este registro?');">
                                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="mini-btn">✕</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($puedeCargar): ?>
                <form class="portal-form" method="post" action="<?= b('/portal/nutricion/agregar_comida.php') ?>" style="margin-top:18px;">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="ficha-field">
                        <div class="ficha-field-label">Fecha</div>
                        <input class="ficha-input" type="date" name="fecha" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="ficha-field">
                        <div class="ficha-field-label">Comida</div>
                        <select class="ficha-select" name="tipo_comida" required>
                            <option value="desayuno">Desayuno</option>
                            <option value="almuerzo">Almuerzo</option>
                            <option value="merienda">Merienda</option>
                            <option value="cena">Cena</option>
                            <option value="colacion">Colación</option>
                        </select>
                    </div>
                    <div class="ficha-field">
                        <div class="ficha-field-label">Qué comiste</div>
                        <textarea class="ficha-textarea" name="descripcion" rows="3" required placeholder="Ej: Tostadas integrales con palta y huevo, café con leche descremada"></textarea>
                    </div>
                    <button type="submit" class="ficha-btn ficha-btn-primary">Registrar comida</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
