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

<div class="portal-card">
    <h2>🥗 Registro alimentario de <?= htmlspecialchars($paciente['apellido'] . ', ' . $paciente['nombre']) ?></h2>
    <p style="color:#64748b;font-size:0.88rem;">DNI <?= htmlspecialchars($paciente['dni']) ?></p>
</div>

<div class="portal-card">
    <h2>🎯 Objetivo nutricional</h2>
    <?php if ($puedeEditar): ?>
        <form class="portal-form" method="post" action="<?= b('/portal/nutricion/guardar_objetivo.php') ?>">
            <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
            <textarea name="objetivo" rows="2" style="width:100%;box-sizing:border-box;padding:10px;border-radius:8px;border:1px solid #ccc;font-family:inherit;"
                placeholder="Ej: Bajar 4kg en 3 meses, reducir consumo de azúcares..."><?= htmlspecialchars($objetivo['objetivo'] ?? '') ?></textarea>
            <button type="submit" class="portal-btn" style="margin-top:10px;">Guardar objetivo</button>
        </form>
    <?php else: ?>
        <p><?= nl2br(htmlspecialchars($objetivo['objetivo'] ?? 'Todavía no se definió un objetivo.')) ?></p>
    <?php endif; ?>
</div>

<div class="portal-card">
    <h2>⚖️ Mediciones</h2>
    <?php if (!$mediciones): ?>
        <p>Todavía no hay mediciones cargadas.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Fecha</th><th>Peso</th><th>Altura</th><th>IMC</th><th>Observaciones</th><th>Cargado por</th><?php if ($puedeEditar): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($mediciones as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['fecha_fmt']) ?></td>
                    <td><?= $m['peso'] !== null ? htmlspecialchars($m['peso']) . ' kg' : '-' ?></td>
                    <td><?= $m['altura'] !== null ? htmlspecialchars($m['altura']) . ' cm' : '-' ?></td>
                    <td><?= $m['imc'] !== null ? htmlspecialchars($m['imc']) : '-' ?></td>
                    <td><?= htmlspecialchars($m['observaciones'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($m['registrado_por_apellido'] . ', ' . $m['registrado_por_nombre']) ?></td>
                    <?php if ($puedeEditar): ?>
                    <td>
                        <form method="post" action="<?= b('/portal/nutricion/eliminar_medicion.php') ?>" onsubmit="return confirm('¿Eliminar esta medición?');">
                            <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="portal-btn peligro" style="padding:4px 10px;margin:0;">Borrar</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($puedeCargar): ?>
        <form class="portal-form" method="post" action="<?= b('/portal/nutricion/agregar_medicion.php') ?>" style="margin-top:16px;">
            <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
            <label>Fecha</label>
            <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>">
            <label>Peso (kg)</label>
            <input type="number" step="0.1" name="peso" placeholder="Ej: 72.5">
            <label>Altura (cm) — opcional si ya la cargaste antes</label>
            <input type="number" step="0.1" name="altura" placeholder="Ej: 168">
            <label>Observaciones</label>
            <input type="text" name="observaciones" placeholder="Opcional">
            <button type="submit" class="portal-btn" style="margin-top:10px;">Agregar medición</button>
        </form>
    <?php endif; ?>
</div>

<div class="portal-card">
    <h2>🍽️ Comidas registradas</h2>
    <?php if (!$comidas): ?>
        <p>Todavía no hay comidas cargadas.</p>
    <?php else: ?>
        <?php
        $tiposNombres = ['desayuno' => 'Desayuno', 'almuerzo' => 'Almuerzo', 'merienda' => 'Merienda', 'cena' => 'Cena', 'colacion' => 'Colación'];
        $fechaActual = null;
        foreach ($comidas as $c):
            if ($c['fecha_fmt'] !== $fechaActual):
                if ($fechaActual !== null) echo '</ul>';
                $fechaActual = $c['fecha_fmt'];
                echo '<p style="margin:14px 0 6px;font-weight:600;color:var(--tm-navy);">' . htmlspecialchars($fechaActual) . '</p><ul style="margin:0;padding-left:20px;">';
            endif;
        ?>
            <li style="margin-bottom:6px;">
                <strong><?= htmlspecialchars($tiposNombres[$c['tipo_comida']] ?? $c['tipo_comida']) ?>:</strong>
                <?= nl2br(htmlspecialchars($c['descripcion'])) ?>
                <?php if ($puedeEditar): ?>
                    <form method="post" action="<?= b('/portal/nutricion/eliminar_comida.php') ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar este registro?');">
                        <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" style="border:none;background:none;color:#c94f4f;cursor:pointer;font-size:0.8rem;">✕ borrar</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($puedeCargar): ?>
        <form class="portal-form" method="post" action="<?= b('/portal/nutricion/agregar_comida.php') ?>" style="margin-top:16px;">
            <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
            <label>Fecha</label>
            <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>">
            <label>Comida</label>
            <select name="tipo_comida" required>
                <option value="desayuno">Desayuno</option>
                <option value="almuerzo">Almuerzo</option>
                <option value="merienda">Merienda</option>
                <option value="cena">Cena</option>
                <option value="colacion">Colación</option>
            </select>
            <label>Qué comiste</label>
            <textarea name="descripcion" rows="2" required placeholder="Ej: Tostadas integrales con palta y huevo, café con leche descremada" style="width:100%;box-sizing:border-box;padding:10px;border-radius:8px;border:1px solid #ccc;font-family:inherit;margin-top:4px;"></textarea>
            <button type="submit" class="portal-btn" style="margin-top:10px;">Registrar comida</button>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
