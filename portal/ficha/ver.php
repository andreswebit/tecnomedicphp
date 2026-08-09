<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$pacienteId = (int)($_GET['paciente_id'] ?? 0);
$esModal = isset($_GET['modal']);

if (!$pacienteId || !ficha_puede_ver($pacienteId)) {
    http_response_code(403);
    echo '<div class="portal-alert error">No tenés permiso para ver esta ficha.</div>';
    exit;
}

$paciente = usuario_by_id($pacienteId);
if (!$paciente || $paciente['rol'] !== 'paciente') {
    http_response_code(404);
    echo '<div class="portal-alert error">Paciente no encontrado.</div>';
    exit;
}

$perfil       = perfil_paciente($pacienteId);
$historia     = historia_clinica_get($pacienteId);
$tratamientos = tratamientos_listar($pacienteId);
$estudios     = estudios_listar($pacienteId);
$puedeEditar  = ficha_puede_editar($pacienteId);
$puedeCargarTratamiento = portal_rol() === 'profesional' || portal_rol() === 'admin';

if (!$esModal) {
    $portal_titulo = 'Ficha médica · ' . $paciente['nombre'];
    require __DIR__ . '/../../includes/portal_header.php';
}
?>
<div class="ficha-medica" data-paciente-id="<?= $pacienteId ?>">

    <div class="portal-card">
        <h2>👤 <?= htmlspecialchars($paciente['apellido'] . ', ' . $paciente['nombre']) ?></h2>
        <p style="margin:4px 0;"><strong>DNI:</strong> <?= htmlspecialchars($paciente['dni']) ?>
           &nbsp;·&nbsp; <strong>Tel:</strong> <?= htmlspecialchars($paciente['telefono'] ?: '-') ?>
           &nbsp;·&nbsp; <strong>Email:</strong> <?= htmlspecialchars($paciente['email']) ?></p>
        <p style="margin:4px 0;">
            <strong>Obra social:</strong> <?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?>
            &nbsp;·&nbsp; <strong>Fecha nac.:</strong> <?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '-') ?>
        </p>
    </div>

    <div class="portal-card">
        <h2>📋 Historia clínica</h2>
        <?php if ($puedeEditar): ?>
            <form data-ficha-form method="post" action="<?= b('/portal/ficha/guardar_historia.php') ?>">
                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                <label class="ficha-label">Antecedentes</label>
                <textarea name="antecedentes" rows="2" class="ficha-textarea"><?= htmlspecialchars($historia['antecedentes'] ?? '') ?></textarea>
                <label class="ficha-label">Diagnóstico</label>
                <textarea name="diagnostico" rows="2" class="ficha-textarea"><?= htmlspecialchars($historia['diagnostico'] ?? '') ?></textarea>
                <label class="ficha-label">Observaciones</label>
                <textarea name="observaciones" rows="2" class="ficha-textarea"><?= htmlspecialchars($historia['observaciones'] ?? '') ?></textarea>
                <button type="submit" class="portal-btn" style="margin-top:10px;">Guardar historia clínica</button>
            </form>
        <?php else: ?>
            <p><strong>Antecedentes:</strong> <?= nl2br(htmlspecialchars($historia['antecedentes'] ?? 'Sin datos cargados.')) ?></p>
            <p><strong>Diagnóstico:</strong> <?= nl2br(htmlspecialchars($historia['diagnostico'] ?? '-')) ?></p>
            <p><strong>Observaciones:</strong> <?= nl2br(htmlspecialchars($historia['observaciones'] ?? '-')) ?></p>
        <?php endif; ?>
    </div>

    <div class="portal-card">
        <h2>💉 Tratamientos realizados</h2>
        <?php if (!$tratamientos): ?>
            <p>Todavía no hay tratamientos cargados.</p>
        <?php else: ?>
            <table class="portal-table">
                <thead><tr><th>Fecha</th><th>Área</th><th>Profesional</th><th>Descripción</th></tr></thead>
                <tbody>
                <?php foreach ($tratamientos as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['fecha_fmt']) ?></td>
                        <td><?= htmlspecialchars($t['area'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($t['profesional_apellido'] . ', ' . $t['profesional_nombre']) ?></td>
                        <td><?= nl2br(htmlspecialchars($t['descripcion'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($puedeCargarTratamiento && $puedeEditar): ?>
            <form data-ficha-form method="post" action="<?= b('/portal/ficha/agregar_tratamiento.php') ?>" style="margin-top:16px;">
                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                <label class="ficha-label">Fecha</label>
                <input type="date" name="fecha" class="ficha-input" required value="<?= date('Y-m-d') ?>">
                <label class="ficha-label">Área</label>
                <select name="area" class="ficha-input">
                    <option value="audiologia">Audiología</option>
                    <option value="hiperbarica">Medicina hiperbárica</option>
                    <option value="nutricion">Nutrición</option>
                    <option value="ortopedia">Ortopedia y rehabilitación</option>
                    <option value="equipamiento">Equipamiento médico y quirúrgico</option>
                </select>
                <label class="ficha-label">Descripción</label>
                <textarea name="descripcion" rows="2" class="ficha-textarea" required placeholder="Qué se hizo en esta sesión/tratamiento"></textarea>
                <button type="submit" class="portal-btn" style="margin-top:10px;">Agregar tratamiento</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="portal-card">
        <h2>🧪 Estudios</h2>
        <?php if (!$estudios): ?>
            <p>Todavía no hay estudios cargados.</p>
        <?php else: ?>
            <table class="portal-table">
                <thead><tr><th>Archivo</th><th>Tipo</th><th>Fecha estudio</th><th>Subido por</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($estudios as $e): ?>
                    <tr>
                        <td>
                            <a href="<?= b('/portal/ficha/descargar_estudio.php?id=' . $e['id']) ?>" target="_blank">
                                📎 <?= htmlspecialchars($e['nombre_original']) ?>
                            </a>
                            <?php if ($e['notas']): ?><br><small style="color:#64748b;"><?= htmlspecialchars($e['notas']) ?></small><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($e['tipo'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($e['fecha_estudio_fmt'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($e['subido_por_apellido'] . ', ' . $e['subido_por_nombre']) ?></td>
                        <td>
                            <?php if ($puedeEditar): ?>
                            <form data-ficha-form method="post" action="<?= b('/portal/ficha/eliminar_estudio.php') ?>" onsubmit="return confirm('¿Eliminar este estudio?');">
                                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="portal-btn peligro" style="padding:4px 10px;margin:0;">Borrar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($puedeEditar): ?>
            <form data-ficha-form data-upload="1" method="post" action="<?= b('/portal/ficha/subir_estudio.php') ?>" enctype="multipart/form-data" style="margin-top:16px;">
                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                <label class="ficha-label">Archivo (PDF o imagen, máx. 10MB)</label>
                <input type="file" name="archivo" class="ficha-input" required accept=".pdf,.jpg,.jpeg,.png">
                <label class="ficha-label">Fecha del estudio</label>
                <input type="date" name="fecha_estudio" class="ficha-input">
                <label class="ficha-label">Notas (opcional)</label>
                <input type="text" name="notas" class="ficha-input" placeholder="Ej: Audiometría tonal">
                <button type="submit" class="portal-btn" style="margin-top:10px;">Subir estudio</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php
if (!$esModal) {
    require __DIR__ . '/../../includes/portal_footer.php';
}
