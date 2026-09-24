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
$historiaRegistros = historia_clinica_get($pacienteId);
$historia     = !empty($historiaRegistros) ? $historiaRegistros[0] : null;
$tratamientos = tratamientos_listar($pacienteId);
$estudios     = estudios_listar($pacienteId);
$puedeEditar  = ficha_puede_editar($pacienteId);
$puedeCargarTratamiento = portal_rol() === 'profesional' || portal_rol() === 'admin';

// TODO: Agregar función para medicamentos recetados
$medicamentos = medicamentos_listar($pacienteId);
$puedeGestionarMedicamentos = portal_rol() === 'profesional' || portal_rol() === 'admin';

$obrasSociales = obras_sociales_todas();

$datosEdicionPaciente = [
    'id' => (int) $pacienteId,
    'nombre' => $paciente['nombre'] ?? '',
    'apellido' => $paciente['apellido'] ?? '',
    'dni' => $paciente['dni'] ?? '',
    'telefono' => $paciente['telefono'] ?? '',
    'email' => $paciente['email'] ?? '',
    'fecha_nacimiento' => $perfil['fecha_nacimiento'] ?? '',
    'obra_social_id' => $perfil['obra_social_id'] ?? '',
];

if (!$esModal) {
    $portal_titulo = 'Ficha médica · ' . $paciente['nombre'];
    require __DIR__ . '/../../includes/portal_header.php';
} else {
    // Modal: necesitamos los estilos de la ficha médica
    ?>
<?php
}
?>
<link rel="stylesheet" href="<?= b('/portal/css/ficha.css') ?>">
<link rel="stylesheet" href="<?= b('/static/tecnomedic.css') ?>">

<div class="ficha-medica" data-paciente-id="<?= $pacienteId ?>">

    <!-- Header compacto -->
    <div class="ficha-header">
        <div class="ficha-avatar">
            <?= strtoupper(substr($paciente['nombre'], 0, 1)) . substr($paciente['apellido'], 0, 1) ?>
        </div>
        <div class="ficha-paciente">
            <div class="ficha-paciente-nombre">
                <?= htmlspecialchars($paciente['apellido'] . ', ' . $paciente['nombre']) ?></div>
            <div class="ficha-paciente-meta">
                <span>🪪 <?= htmlspecialchars($paciente['dni']) ?></span>
                <span>📞 <?= htmlspecialchars($paciente['telefono'] ?: '-') ?></span>
                <span>🏥 <?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?></span>
            </div>
        </div>
        <div class="ficha-badge"><?= $paciente['estado'] ?? 'Activo' ?></div>
        <button type="button" class="ficha-btn ficha-btn-outline ficha-btn-sm" onclick="window.history.back()"
            style="margin-left:8px;">Volver</button>
    </div>

    <!-- Tabs compactos -->
    <div class="ficha-tabs">
        <button class="ficha-tab active" data-tab="historia"><span class="icon">📋</span>Historia</button>
        <button class="ficha-tab" data-tab="tratamientos"><span class="icon">💉</span>Tratamientos</button>
        <button class="ficha-tab" data-tab="medicamentos"><span class="icon">💊</span>Medicamentos</button>
        <button class="ficha-tab" data-tab="estudios"><span class="icon">🧪</span>Estudios</button>
    </div>

    <div style="padding:10px">

        <!-- CARD 1: Datos personales -->
        <div class="ficha-card" data-card="datos">
            <div class="ficha-card-header">
                <div class="ficha-card-icon blue">👤</div>
                <div class="ficha-card-title">Datos</div>
                <?php if ($puedeEditar): ?>
                <button class="btn-actions btn-action btn-mod ficha-btn-xs" data-tooltip="Editar"
                    onclick="toggleEdicionDatos()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path
                            d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                    </svg>

                </button>
                <?php endif; ?>
            </div>
            <div class="ficha-card-body" id="datosBody">
                <div class="kv-grid" id="datosView">
                    <div class="kv-item">
                        <div class="kv-label">Nombre</div>
                        <div class="kv-value" id="view_nombre">
                            <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">DNI</div>
                        <div class="kv-value" id="view_dni"><?= htmlspecialchars($paciente['dni']) ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Tel</div>
                        <div class="kv-value" id="view_telefono"><?= htmlspecialchars($paciente['telefono'] ?: '-') ?>
                        </div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Email</div>
                        <div class="kv-value" id="view_email"><?= htmlspecialchars($paciente['email'] ?: '-') ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Obra</div>
                        <div class="kv-value" id="view_obra">
                            <?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Nac.</div>
                        <div class="kv-value" id="view_nac"><?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '-') ?>
                        </div>
                    </div>
                </div>
                <form id="formDatos" data-ficha-form style="display:none"
                    action="<?= b('/portal/ficha/guardar_persona.php') ?>" method="post"
                    onsubmit="return guardarPersona(event)">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="kv-grid">
                        <div class="kv-item">
                            <div class="kv-label">Nombre</div>
                            <input class="ficha-textarea" name="nombre" id="inp_nombre"
                                value="<?= htmlspecialchars($paciente['nombre']) ?>" required>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Apellido</div>
                            <input class="ficha-textarea" name="apellido" id="inp_apellido"
                                value="<?= htmlspecialchars($paciente['apellido']) ?>" required>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">DNI</div>
                            <input class="ficha-textarea" name="dni" id="inp_dni"
                                value="<?= htmlspecialchars($paciente['dni']) ?>" required readonly>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Tel</div>
                            <input class="ficha-textarea" name="telefono" id="inp_telefono"
                                value="<?= htmlspecialchars($paciente['telefono'] ?: '') ?>">
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Email</div>
                            <input class="ficha-textarea" name="email" id="inp_email"
                                value="<?= htmlspecialchars($paciente['email'] ?: '') ?>" type="email">
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Obra social</div>
                            <select class="ficha-textarea" name="obra_social_id" id="inp_obra">
                                <option value="">— Sin cobertura —</option>
                                <?php foreach ($obrasSociales as $os): ?>
                                <option value="<?= $os['id'] ?>"
                                    <?= ((int)($perfil['obra_social_id'] ?? 0) === (int)$os['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($os['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Nac.</div>
                            <input class="ficha-textarea" name="fecha_nacimiento" id="inp_nac"
                                value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?: '') ?>" type="date">
                        </div>
                    </div>
                    <div style="margin-top:10px;display:flex;gap:8px">
                        <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-primary">💾 Guardar</button>
                        <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline"
                            onclick="toggleEdicionDatos()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CARD 2: Historia clínica -->
        <div class="ficha-card" data-card="historia">
            <div class="ficha-card-header">
                <div class="ficha-card-icon green">📋</div>
                <div class="ficha-card-title">Historia clínica</div>
            </div>
            <!-- Fila de agregar nueva información -->
            <div class="ficha-card-body ">
                <div style="background:#fff;border:1px solid #e2e8e6;border-radius:8px; padding:10px ;box-shadow:0 1px 4px rgba(13,27,42,.05);gap:8px"
                    id="filaNueva">
                    <div style="font-weight:600;color:#555f5e;font-size:.85rem;margin-bottom:6px">Agregar
                        (<?= date('d/m/Y') ?>)</div>
                    <div
                        style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;border-bottom:1px solid #e2e8e6;padding-bottom:6px">
                        <div style="flex:1 1 160px;min-width:130px; padding-right:6px">
                            <textarea id="new_ante" rows="2" class="ficha-textarea"
                                style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px"
                                placeholder="Antecedentes…"></textarea>
                        </div>
                        <div style="flex:1 1 160px;min-width:130px">
                            <textarea id="new_dia" rows="2" class="ficha-textarea"
                                style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px"
                                placeholder="Diagnóstico…"></textarea>
                        </div>
                    </div>
                    <div style="margin-top:6px">
                        <textarea id="new_obs" rows="2" class="ficha-textarea"
                            style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px"
                            placeholder="Observaciones…"></textarea>
                    </div>
                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-primary"
                            onclick="guardarNuevaHistoria()">💾 Guardar</button>
                        <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline"
                            onclick="limpiarNuevaHistoria()">Cancelar</button>
                    </div>
                </div>
            </div>
            <!-- registros historial ficha  -->
            <div class="ficha-card-body">
                <?php if ($puedeEditar): ?>
                <div id="historiaView" style="display:flex;flex-direction:column;gap:6px;font-size:.85rem">
                    <?php foreach ($historiaRegistros as $h): ?>
                    <div class="historia-fila" id="filaHist<?= (int)($h['id'] ?? 0) ?>"
                        style="background:#fff;border:1px solid #e2e8e6;border-radius:8px;padding:10px;box-shadow:0 1px 4px rgba(13,27,42,.05)"
                        data-id="<?= (int)($h['id'] ?? 0) ?>">
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;border-bottom:1px solid #e2e8e6;padding-bottom:6px">
                            <span style="font-weight:600;color:#0d1b2a;font-size:.85rem">📅
                                <?= htmlspecialchars($h['fecha_fmt'] ?? '-') ?></span>
                            <div style="display:flex;gap:6px">
                                <button type="button" class="btn-actions btn-action btn-mod ficha-btn-xs"
                                    data-tooltip="Editar" onclick="editarFilaHistoria(<?= (int)($h['id'] ?? 0) ?>)"
                                    title="Editar registro">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                    </svg>
                                </button>
                                <button type="button" class="btn-action btn-del" data-tooltip="Eliminar"
                                    onclick="eliminarFilaHistoria(<?= (int)($h['id'] ?? 0) ?>)"
                                    title="Eliminar registro" style="border-color:#ffff">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap">
                            <div style="flex:1 1 160px;min-width:130px">
                                <div style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase">
                                    Antecedentes</div>
                                <div style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px"
                                    data-ante="<?= htmlspecialchars($h['antecedentes'] ?? '') ?>">
                                    <?= nl2br(htmlspecialchars($h['antecedentes'] ?? '-')) ?></div>
                            </div>
                            <div style="flex:1 1 160px;min-width:130px">
                                <div style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase">
                                    Diagnóstico</div>
                                <div style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px"
                                    data-dia="<?= htmlspecialchars($h['diagnostico'] ?? '') ?>">
                                    <?= nl2br(htmlspecialchars($h['diagnostico'] ?? '-')) ?></div>
                            </div>
                        </div>
                        <div style="margin-top:6px">
                            <div style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase">
                                Observaciones</div>
                            <div style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px"
                                data-obs="<?= htmlspecialchars($h['observaciones'] ?? '') ?>">
                                <?= nl2br(htmlspecialchars($h['observaciones'] ?? '-')) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($historiaRegistros)): ?><div class="ficha-empty">
                        <div class="ficha-empty-text">Sin registros aún.</div>
                    </div><?php endif; ?>
                </div>
                <?php else: ?>
                <div class="kv-grid" style="grid-template-columns:1fr 1fr">
                    <div class="kv-item">
                        <div class="kv-label">Antecedentes</div>
                        <div class="kv-value">
                            <?= nl2br(htmlspecialchars($historia['antecedentes'] ?? 'Sin datos cargados.')) ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Diagnóstico</div>
                        <div class="kv-value"><?= nl2br(htmlspecialchars($historia['diagnostico'] ?? '-')) ?></div>
                    </div>
                </div>
                <div class="ficha-field" style="margin-top:8px">
                    <div class="ficha-field-label">Observaciones</div>
                    <p><?= nl2br(htmlspecialchars($historia['observaciones'] ?? '-')) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARD 3: Tratamientos -->
        <div class="ficha-card" data-card="tratamientos">
            <div class="ficha-card-header">
                <div class="ficha-card-icon amber">💉</div>
                <div class="ficha-card-title">Tratamientos</div>
                <?php if ($puedeCargarTratamiento && $puedeEditar): ?>
                <button class="btn-actions btn-action btn-mod ficha-btn-xs" data-tooltip="Agregar"
                    style="background:var(--green-dk)" onclick="abrirModalTratamiento()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path
                            d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                    </svg>
                </button>
                <?php endif; ?>
            </div>
            <div class="ficha-card-body">
                <?php if (!$tratamientos): ?>
                <div class="ficha-empty">
                    <div class="ficha-empty-icon">💉</div>
                    <div class="ficha-empty-text">Todavía no hay tratamientos cargados.</div>
                </div>
                <?php else: ?>
                <table class="ficha-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Área</th>
                            <th>Profesional</th>
                            <th>Descripción</th>
                            <?php if ($puedeCargarTratamiento && $puedeEditar): ?>
                            <th style="width:40px;text-align:right">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tratamientos as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['fecha_fmt']) ?></td>
                            <td>
                                <span class="badge-area <?= strtolower($t['area']) ?>">
                                    <?= htmlspecialchars(ucfirst($t['area'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($t['profesional_apellido'] . ', ' . $t['profesional_nombre']) ?>
                            </td>
                            <td style="white-space:normal;word-break:break-word;overflow-wrap:anywhere;max-width:520px;">
                                <?= nl2br(htmlspecialchars($t['descripcion'])) ?>
                            </td>
                            <?php if ($puedeCargarTratamiento && $puedeEditar): ?>
                            <td style="text-align:right">
                                <button type="button" class="btn-action btn-del" data-tooltip="Eliminar"
                                    onclick="eliminarTratamiento(<?= (int)$t['id'] ?>)" title="Eliminar tratamiento"
                                    style="border-color:#ffff">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                    </svg>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARD 4: Medicamentos recetados -->
        <div class="ficha-card" data-card="medicamentos">
            <div class="ficha-card-header">
                <div class="ficha-card-icon purple">💊</div>
                <div class="ficha-card-title">Medicamentos recetados</div>
                <?php if ($puedeGestionarMedicamentos && $puedeEditar): ?>
                <button class="btn-actions btn-action btn-mod ficha-btn-xs" data-tooltip="Agregar"
                    style="background:var(--green-dk)" onclick="abrirModalMedicamento()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path
                            d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                    </svg>
                </button>
                <?php endif; ?>
            </div>
            <div class="ficha-card-body">
                <?php if (empty($medicamentos)): ?>
                <div class="ficha-empty">
                    <div class="ficha-empty-icon">💊</div>
                    <div class="ficha-empty-text">No hay medicamentos recetados.</div>
                </div>
                <?php else: ?>
                <table class="ficha-table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Droga</th>
                            <th>Dosis</th>
                            <th>Frecuencia</th>
                            <th>Vía</th>
                            <th>Estado</th>
                            <?php if ($puedeGestionarMedicamentos && $puedeEditar): ?>
                            <th style="width:30px;text-align:right">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicamentos as $m): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['nombre_comercial'] ?? '-') ?></strong></td>
                            <td><?= htmlspecialchars($m['principio_activo'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($m['dosis'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($m['frecuencia'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($m['via_administracion'] ?? '-') ?></td>
                            <td>
                                <span class="badge-estado <?= strtolower($m['estado'] ?? 'activo') ?>">
                                    ● <?= ucfirst(htmlspecialchars($m['estado'] ?? 'activo')) ?>
                                </span>
                            </td>
                            <?php if ($puedeGestionarMedicamentos && $puedeEditar): ?>
                            <td style="text-align:right">
                                <button type="button" class="btn-action btn-del" data-tooltip="Eliminar"
                                    onclick="eliminarMedicamento(<?= (int)$m['id'] ?>)" title="Eliminar medicamento"
                                    style="border-color:#ffff"> <svg xmlns="http://www.w3.org/2000/svg"
                                        width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                    </svg>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARD 5: Estudios -->
        <div class="ficha-card" data-card="estudios">
            <div class="ficha-card-header">
                <div class="ficha-card-icon red">🧪</div>
                <div class="ficha-card-title">Estudios</div>
                <?php if ($puedeEditar): ?>
                <button class="btn-action btn-mod" data-tooltip="Subir estudio" style="background:blue ;border-color:#ffff" onclick="abrirModalEstudio()">
                    <svg width="16px" height="16px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3 15C3 17.8284 3 19.2426 3.87868 20.1213C4.75736 21 6.17157 21 9 21H15C17.8284 21 19.2426 21 20.1213 20.1213C21 19.2426 21 17.8284 21 15" stroke="#ffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 16V3M12 3L16 7.375M12 3L8 7.375" stroke="#ffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg></button>
                <?php endif; ?>
            </div>
            <div class="ficha-card-body">
                <?php if (!$estudios): ?>
                <div class="ficha-empty">
                    <div class="ficha-empty-icon">🧪</div>
                    <div class="ficha-empty-text">Todavía no hay estudios cargados.</div>
                </div>
                <?php else: ?>
                <?php foreach ($estudios as $e): ?>
                <div class="lista-item">
                    <div class="lista-icon blue">📄</div>
                    <div class="lista-info">
                        <div class="lista-nombre"><?= htmlspecialchars($e['nombre_original']) ?></div>
                        <div class="lista-meta">
                            <?= htmlspecialchars($e['tipo'] ?? '-') ?> ·
                            <?= !empty($e['fecha_estudio']) ? date('d/m/y', strtotime($e['fecha_estudio'])) : '-' ?> ·
                            <?= htmlspecialchars($e['subido_por_apellido'] . ', ' . $e['subido_por_nombre']) ?>
                        </div>
                    </div>
                    <div class="btn-actions">
                        <button onclick="location.href='<?= b('/portal/ficha/descargar_estudio.php?id=' . $e['id']) ?>' " target="_blank"
                                class="btn-action btn-descarga" data-tooltip="Descargar"
                                >
                                <svg  width="16px" height="16px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.5535 16.5061C12.4114 16.6615 12.2106 16.75 12 16.75C11.7894 16.75 11.5886 16.6615 11.4465 16.5061L7.44648 12.1311C7.16698 11.8254 7.18822 11.351 7.49392 11.0715C7.79963 10.792 8.27402 10.8132 8.55352 11.1189L11.25 14.0682V3C11.25 2.58579 11.5858 2.25 12 2.25C12.4142 2.25 12.75 2.58579 12.75 3V14.0682L15.4465 11.1189C15.726 10.8132 16.2004 10.792 16.5061 11.0715C16.8118 11.351 16.833 11.8254 16.5535 12.1311L12.5535 16.5061Z"
                                        fill="#f5f6fa" />
                                    <path
                                        d="M3.75 15C3.75 14.5858 3.41422 14.25 3 14.25C2.58579 14.25 2.25 14.5858 2.25 15V15.0549C2.24998 16.4225 2.24996 17.5248 2.36652 18.3918C2.48754 19.2919 2.74643 20.0497 3.34835 20.6516C3.95027 21.2536 4.70814 21.5125 5.60825 21.6335C6.47522 21.75 7.57754 21.75 8.94513 21.75H15.0549C16.4225 21.75 17.5248 21.75 18.3918 21.6335C19.2919 21.5125 20.0497 21.2536 20.6517 20.6516C21.2536 20.0497 21.5125 19.2919 21.6335 18.3918C21.75 17.5248 21.75 16.4225 21.75 15.0549V15C21.75 14.5858 21.4142 14.25 21 14.25C20.5858 14.25 20.25 14.5858 20.25 15C20.25 16.4354 20.2484 17.4365 20.1469 18.1919C20.0482 18.9257 19.8678 19.3142 19.591 19.591C19.3142 19.8678 18.9257 20.0482 18.1919 20.1469C17.4365 20.2484 16.4354 20.25 15 20.25H9C7.56459 20.25 6.56347 20.2484 5.80812 20.1469C5.07435 20.0482 4.68577 19.8678 4.40901 19.591C4.13225 19.3142 3.9518 18.9257 3.85315 18.1919C3.75159 17.4365 3.75 16.4354 3.75 15Z"
                                        fill="#f3f5fa" />
                                </svg>
                            
                            
                            
                            </button>
                        <?php if ($puedeEditar): ?>
                        <button class="btn-action btn-del" data-tooltip="Eliminar" style="border-color:#ffff"
                            onclick="eliminarEstudio(<?= $e['id'] ?>)" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                <path
                                    d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /padding -->

    <?php if ($puedeEditar): ?>

    <!-- ── SUBMODAL: TRATAMIENTO ── -->
    <div id="submodalTratamiento" class="ficha-submodal-overlay"
        onclick="if(event.target===this)cerrarSubmodal('submodalTratamiento')">
        <div class="ficha-submodal">
            <div class="ficha-submodal-header">
                <span>💉 Registrar Tratamiento</span>
                <button type="button" class="ficha-submodal-close"
                    onclick="cerrarSubmodal('submodalTratamiento')">&times;</button>
            </div>
            <form id="formTratamiento" action="<?= b('/portal/ficha/agregar_tratamiento.php') ?>" method="post"
                onsubmit="return guardarTratamiento(event)">
                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                <div class="ficha-submodal-body">
                    <div class="form-group-ficha">
                        <label for="trat_fecha">Fecha</label>
                        <input type="date" id="trat_fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group-ficha">
                        <label for="trat_area">Área / Especialidad</label>
                        <select id="trat_area" name="area" required>
                            <option value="audiologia">Audiología</option>
                            <option value="hiperbarica">Medicina Hiperbárica</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="ortopedia">Ortopedia y Rehabilitación</option>
                            <option value="equipamiento">Equipamiento Médico</option>
                            <option value="general">Clínica / General</option>
                        </select>
                    </div>
                    <div class="form-group-ficha">
                        <label for="trat_descripcion">Descripción / Evolución</label>
                        <textarea id="trat_descripcion" name="descripcion" rows="4"
                            placeholder="Detalle de la sesión o indicación terapéutica..." required></textarea>
                    </div>
                </div>
                <div class="ficha-submodal-footer">
                    <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline"
                        onclick="cerrarSubmodal('submodalTratamiento')">Cancelar</button>
                    <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-primary">💾 Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>

    <?php if ($puedeEditar): ?>

    <!-- ── SUBMODAL: MEDICAMENTO ── -->
    <div id="submodalMedicamento" class="ficha-submodal-overlay"
        onclick="if(event.target===this)cerrarSubmodal('submodalMedicamento')">
        <div class="ficha-submodal">
            <div class="ficha-submodal-header">
                <span>💊 Recetar Medicamento</span>
                <button type="button" class="ficha-submodal-close"
                    onclick="cerrarSubmodal('submodalMedicamento')">&times;</button>
            </div>
            <form id="formMedicamento" action="<?= b('/portal/ficha/agregar_medicamento.php') ?>" method="post"
                onsubmit="return guardarMedicamento(event)">
                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                <div class="ficha-submodal-body">
                    <div class="form-group-ficha">
                        <label for="med_nombre">Nombre Comercial / Medicamento *</label>
                        <input type="text" id="med_nombre" name="nombre_comercial"
                            placeholder="Ej: Paracetamol / Tafirol" required>
                    </div>
                    <div class="form-group-ficha">
                        <label for="med_droga">Principio Activo / Droga</label>
                        <input type="text" id="med_droga" name="principio_activo" placeholder="Ej: Paracetamol">
                    </div>
                    <div style="display:flex;gap:10px">
                        <div class="form-group-ficha" style="flex:1">
                            <label for="med_dosis">Dosis</label>
                            <input type="text" id="med_dosis" name="dosis" placeholder="Ej: 500 mg / 1 comp.">
                        </div>
                        <div class="form-group-ficha" style="flex:1">
                            <label for="med_frecuencia">Frecuencia</label>
                            <input type="text" id="med_frecuencia" name="frecuencia" placeholder="Ej: Cada 8 hs">
                        </div>
                    </div>
                    <div style="display:flex;gap:10px">
                        <div class="form-group-ficha" style="flex:1">
                            <label for="med_via">Vía de administración</label>
                            <select id="med_via" name="via_administracion">
                                <option value="Oral">Oral</option>
                                <option value="Sublingual">Sublingual</option>
                                <option value="Tópica">Tópica</option>
                                <option value="Inhalatoria">Inhalatoria</option>
                                <option value="Intramuscular">Intramuscular</option>
                                <option value="Intravenosa">Intravenosa</option>
                                <option value="Oftálmica">Oftálmica</option>
                                <option value="Ótica">Ótica</option>
                                <option value="Otra">Otra</option>
                            </select>
                        </div>
                        <div class="form-group-ficha" style="flex:1">
                            <label for="med_estado">Estado</label>
                            <select id="med_estado" name="estado">
                                <option value="activo" selected>Activo</option>
                                <option value="suspendido">Suspendido</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="ficha-submodal-footer">
                    <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline"
                        onclick="cerrarSubmodal('submodalMedicamento')">Cancelar</button>
                    <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-teal">💾 Recetar</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>

    <?php if ($puedeEditar): ?>

    <!-- ── SUBMODAL: ESTUDIO ── -->
    <div id="submodalEstudio" class="ficha-submodal-overlay"
        onclick="if(event.target===this)cerrarSubmodal('submodalEstudio')">
        <div class="ficha-submodal">
            <div class="ficha-submodal-header">
                <span>🧪 Subir Estudio Médico</span>
                <button type="button" class="ficha-submodal-close"
                    onclick="cerrarSubmodal('submodalEstudio')">&times;</button>
            </div>
            <form id="formEstudio" action="<?= b('/portal/ficha/subir_estudio.php') ?>" method="post"
                enctype="multipart/form-data" onsubmit="return guardarEstudio(event)">
                <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                <div class="ficha-submodal-body">
                    <div class="form-group-ficha">
                        <label for="est_archivo">Archivo (PDF, JPG, PNG - Máx. 10MB) *</label>
                        <input type="file" id="est_archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="form-group-ficha">
                        <label for="est_tipo">Tipo de estudio</label>
                        <select id="est_tipo" name="tipo">
                            <option value="Audiometría">Audiometría</option>
                            <option value="Laboratorio">Laboratorio / Análisis Clínicos</option>
                            <option value="Resonancia">Resonancia Magnética</option>
                            <option value="Tomografía">Tomografía Computada</option>
                            <option value="Radiografía">Radiografía</option>
                            <option value="Ecografía">Ecografía</option>
                            <option value="Informe">Informe Médico</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group-ficha">
                        <label for="est_fecha">Fecha del estudio</label>
                        <input type="date" id="est_fecha" name="fecha_estudio" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group-ficha">
                        <label for="est_notas">Notas / Observaciones</label>
                        <textarea id="est_notas" name="notas" rows="3"
                            placeholder="Diagnóstico, conclusiones o notas relevantes..."></textarea>
                    </div>
                </div>
                <div class="ficha-submodal-footer">
                    <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline"
                        onclick="cerrarSubmodal('submodalEstudio')">Cancelar</button>
                    <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-primary">⬆️ Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>
</div><!-- /.ficha-medica -->

<script>
window.pacienteFichaData = {
    id: <?= $pacienteId ?>,
    nombre: <?= json_encode($paciente['nombre'] ?? '') ?>,
    apellido: <?= json_encode($paciente['apellido'] ?? '') ?>,
    dni: <?= json_encode($paciente['dni'] ?? '') ?>,
    telefono: <?= json_encode($paciente['telefono'] ?? '') ?>,
    email: <?= json_encode($paciente['email'] ?? '') ?>,
    fecha_nacimiento: <?= json_encode($perfil['fecha_nacimiento'] ?? '') ?>,
    obra_social_id: <?= json_encode($perfil['obra_social_id'] ?? '') ?>
};

function recargarFichaActual() {
    var pid = <?= (int)$pacienteId ?>;
    if (typeof window.abrirFicha === 'function') {
        window.abrirFicha(pid);
    } else {
        location.reload();
    }
}

// Tab switching - muestra solo el card seleccionado y hace scroll hasta él
document.querySelectorAll('.ficha-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.ficha-tab').forEach(t => t.classList.remove('active'));
        // Oculta todos los cards
        document.querySelectorAll('.ficha-card').forEach(c => c.style.display = 'none');
        tab.classList.add('active');
        const tabName = tab.dataset.tab;
        const target = document.querySelector(`.ficha-card[data-card="${tabName}"]`);
        if (target) {
            target.style.display = 'block';
            // Scroll dentro del modal (.ficha-modal tiene max-height + overflow-y: auto)
            // o scroll de página completa si no está en modal
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Funciones de edición inline
function abrirEdicionPaciente() {
    toggleEdicionDatos();
}

function toggleEdicionDatos() {
    const view = document.getElementById('datosView');
    const form = document.getElementById('formDatos');
    const btn = document.getElementById('btnEditarDatos');
    if (!view || !form) return;
    const editing = form.style.display === 'block';
    view.style.display = editing ? 'grid' : 'none';
    form.style.display = editing ? 'none' : 'block';
    if (btn) btn.textContent = editing ? 'Editar' : '❌ Cancelar';
}

function toastFicha(msj) {
    let t = document.getElementById('fichaToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'fichaToast';
        t.className = 'ficha-toast';
        document.body.appendChild(t);
    }
    t.textContent = msj;
    t.classList.add('show');
    setTimeout(function() {
        t.classList.remove('show');
    }, 3000);
}

function openEdit(d) {
    if (document.getElementById('modalEditar')) {
        document.getElementById('f-id').value = d.id;
        document.getElementById('f-nombre').value = d.nombre || '';
        document.getElementById('f-apellido').value = d.apellido || '';
        document.getElementById('f-dni').value = d.dni || '';
        document.getElementById('f-telefono').value = d.telefono || '';
        document.getElementById('f-email').value = d.email || '';
        document.getElementById('f-fecha-nacimiento').value = d.fecha_nacimiento || '';
        var obraSocialSelect = document.getElementById('f-obra-social');
        if (obraSocialSelect) {
            obraSocialSelect.value = d.obra_social_id || '';
            Array.prototype.forEach.call(obraSocialSelect.options, function(option) {
                option.selected = option.value === (d.obra_social_id || '');
            });
        }
        document.getElementById('modalEditar').classList.add('open');
        return;
    }

    const view = document.getElementById('datosView');
    const form = document.getElementById('formDatos');
    const btn = document.getElementById('btnEditarDatos');

    if (!view || !form) {
        alert('No se pueden mostrar los datos de edición.');
        return;
    }

    const inp_nombre = document.getElementById('inp_nombre');
    const inp_apellido = document.getElementById('inp_apellido');
    const inp_dni = document.getElementById('inp_dni');
    const inp_telefono = document.getElementById('inp_telefono');
    const inp_email = document.getElementById('inp_email');
    const inp_obra = document.getElementById('inp_obra');
    const inp_nac = document.getElementById('inp_nac');

    if (inp_nombre) inp_nombre.value = d.nombre || '';
    if (inp_apellido) inp_apellido.value = d.apellido || '';
    if (inp_dni) inp_dni.value = d.dni || '';
    if (inp_telefono) inp_telefono.value = d.telefono || '';
    if (inp_email) inp_email.value = d.email || '';
    if (inp_nac) inp_nac.value = d.fecha_nacimiento || '';

    if (inp_obra) {
        inp_obra.value = d.obra_social_id || '';
        const obraSelect = inp_obra;
        if (obraSelect.options) {
            for (let i = 0; i < obraSelect.options.length; i++) {
                if (obraSelect.options[i].value === (d.obra_social_id || '')) {
                    obraSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    toggleEdicionDatos();
    if (btn) btn.textContent = '❌ Cancelar';
}

function guardarHistoria(e) {
    if (e && e.preventDefault) e.preventDefault();
    const form = e.target && e.target.closest ? e.target.closest('form') : null;
    if (!form) return false;
    const data = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');
    if (!btn) return false;

    const ante = (data.get('antecedentes') || '').toString().trim();
    const dia = (data.get('diagnostico') || '').toString().trim();
    const obs = (data.get('observaciones') || '').toString().trim();
    if (!ante && !dia && !obs) {
        toastFicha('⚠️ Al menos un campo es obligatorio.');
        return false;
    }

    btn.disabled = true;
    btn.textContent = 'Guardando...';
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Historia clínica guardada'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error al guardar: ' + (res.error || 'Error desconocido'));
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            btn.disabled = false;
            btn.textContent = '💾 Guardar';
        });
    return false;
}

function nl2br(str) {
    if (!str) return '';
    return str.replace(/\n/g, '<br>');
}

function guardarPersona(e) {
    if (e && e.preventDefault) e.preventDefault();
    const form = document.getElementById('formDatos');
    if (!form) return false;
    const data = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(r => {
            if (r.ok) {
                toastFicha('✅ ' + (r.message || 'Datos actualizados'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ ' + (r.error || 'Error al guardar'));
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '💾 Guardar';
                }
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        });
    return false;
}

function abrirEdicionHistoria() {
    return false;
}

function cerrarSubmodal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('open');
    const form = modal.querySelector('form');
    if (form) form.reset();
}

function abrirModalTratamiento() {
    const modal = document.getElementById('submodalTratamiento');
    if (modal) modal.classList.add('open');
}

function guardarTratamiento(e) {
    if (e && e.preventDefault) e.preventDefault();
    const form = document.getElementById('formTratamiento');
    if (!form) return false;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }
    const data = new FormData(form);
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Tratamiento guardado'));
                cerrarSubmodal('submodalTratamiento');
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo guardar el tratamiento'));
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '💾 Guardar';
                }
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        });
    return false;
}

function eliminarTratamiento(id) {
    if (!confirm('¿Eliminar este tratamiento?')) return;
    fetch('<?= b('/portal/ficha/eliminar_tratamiento.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Tratamiento eliminado'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo eliminar el tratamiento'));
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
        });
}

function abrirModalMedicamento() {
    const modal = document.getElementById('submodalMedicamento');
    if (modal) modal.classList.add('open');
}

function guardarMedicamento(e) {
    if (e && e.preventDefault) e.preventDefault();
    const form = document.getElementById('formMedicamento');
    if (!form) return false;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }
    const data = new FormData(form);
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Medicamento guardado'));
                cerrarSubmodal('submodalMedicamento');
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo recetar el medicamento'));
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '💾 Recetar';
                }
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Recetar';
            }
        });
    return false;
}

function eliminarMedicamento(id) {
    if (!confirm('¿Eliminar este medicamento recetado?')) return;
    fetch('<?= b('/portal/ficha/eliminar_medicamento.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Medicamento eliminado'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo eliminar el medicamento'));
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
        });
}

function abrirModalEstudio() {
    const modal = document.getElementById('submodalEstudio');
    if (modal) modal.classList.add('open');
}

function guardarEstudio(e) {
    if (e && e.preventDefault) e.preventDefault();
    const form = document.getElementById('formEstudio');
    if (!form) return false;
    const fileInp = document.getElementById('est_archivo');
    if (fileInp && fileInp.files && fileInp.files[0]) {
        if (fileInp.files[0].size > 10 * 1024 * 1024) {
            toastFicha('⚠️ El archivo supera el límite de 10 MB.');
            return false;
        }
    }
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Subiendo...';
    }
    const data = new FormData(form);
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Estudio subido'));
                cerrarSubmodal('submodalEstudio');
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo subir el estudio'));
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '💾 Subir';
                }
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Subir';
            }
        });
    return false;
}

function eliminarEstudio(id) {
    if (!confirm('¿Eliminar este estudio y su archivo adjunto?')) return;
    fetch('<?= b('/portal/ficha/eliminar_estudio.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Estudio eliminado'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo eliminar el estudio'));
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
        });
}

function eliminarFilaHistoria(id) {
    if (!confirm('¿Eliminar este registro de la historia clínica?')) return;

    fetch('<?= b('/portal/ficha/eliminar_historia.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Registro eliminado'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo eliminar'));
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
        });
}

function editarFilaHistoria(id) {
    const fila = document.getElementById('filaHist' + id);
    if (!fila) return;

    const anteDiv = fila.querySelector('[data-ante]');
    const diaDiv = fila.querySelector('[data-dia]');
    const obsDiv = fila.querySelector('[data-obs]');
    const ante = anteDiv ? (anteDiv.getAttribute('data-ante') || '') : '';
    const dia = diaDiv ? (diaDiv.getAttribute('data-dia') || '') : '';
    const obs = obsDiv ? (obsDiv.getAttribute('data-obs') || '') : '';

    fila.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;border-bottom:1px solid #e2e8e6;padding-bottom:6px">
            <span style="font-weight:600;color:#0d1b2a;font-size:.85rem">📅 Editando registro #` + id + `</span>
            <div style="display:flex;gap:6px">
                <button type="button" class="ficha-btn ficha-btn-xs ficha-btn-primary" onclick="guardarFilaHistoria(` +
        id + `, this)">💾 Guardar</button>
                <button type="button" class="ficha-btn ficha-btn-xs ficha-btn-outline" onclick="recargarFichaActual()">Cancelar</button>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div style="flex:1 1 160px;min-width:130px">
                <label style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;display:block;margin-bottom:3px">Antecedentes</label>
                <textarea id="fila_ante_` + id + `" rows="2" class="ficha-textarea editing" style="font-size:.82rem;min-height:48px"></textarea>
            </div>
            <div style="flex:1 1 160px;min-width:130px">
                <label style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;display:block;margin-bottom:3px">Diagnóstico</label>
                <textarea id="fila_dia_` + id + `" rows="2" class="ficha-textarea editing" style="font-size:.82rem;min-height:48px"></textarea>
            </div>
        </div>
        <div style="margin-top:6px">
            <label style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;display:block;margin-bottom:3px">Observaciones</label>
            <textarea id="fila_obs_` + id + `" rows="2" class="ficha-textarea editing" style="font-size:.82rem;min-height:48px"></textarea>
        </div>
    `;

    document.getElementById('fila_ante_' + id).value = ante;
    document.getElementById('fila_dia_' + id).value = dia;
    document.getElementById('fila_obs_' + id).value = obs;
}

function guardarFilaHistoria(id, btn) {
    const ante = (document.getElementById('fila_ante_' + id)?.value || '').trim();
    const dia = (document.getElementById('fila_dia_' + id)?.value || '').trim();
    const obs = (document.getElementById('fila_obs_' + id)?.value || '').trim();

    if (!ante && !dia && !obs) {
        toastFicha('⚠️ Al menos un campo es obligatorio.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }

    fetch('<?= b('/portal/ficha/guardar_historia_fila.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id) + '&antecedentes=' + encodeURIComponent(ante) + '&diagnostico=' +
                encodeURIComponent(dia) + '&observaciones=' + encodeURIComponent(obs)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Historia actualizada'));
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo actualizar'));
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '💾 Guardar';
                }
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        });
}

function guardarNuevaHistoria() {
    const ante = (document.getElementById('new_ante')?.value || '').trim();
    const dia = (document.getElementById('new_dia')?.value || '').trim();
    const obs = (document.getElementById('new_obs')?.value || '').trim();

    if (!ante && !dia && !obs) {
        toastFicha('⚠️ Al menos un campo es obligatorio.');
        return;
    }

    const btn = document.querySelector('#filaNueva button.ficha-btn-primary') || document.querySelector(
        '#filaNueva button[type="button"]');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
    }

    fetch('<?= b('/portal/ficha/guardar_historia.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'paciente_id=<?= $pacienteId ?>&antecedentes=' + encodeURIComponent(ante) + '&diagnostico=' +
                encodeURIComponent(dia) + '&observaciones=' + encodeURIComponent(obs)
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                toastFicha('✅ ' + (res.message || 'Historia guardada'));
                limpiarNuevaHistoria();
                setTimeout(() => recargarFichaActual(), 600);
            } else {
                toastFicha('⚠️ Error: ' + (res.error || 'No se pudo guardar'));
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '💾 Guardar';
                }
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            if (btn) {
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        });
}

function limpiarNuevaHistoria() {
    const ante = document.getElementById('new_ante');
    const dia = document.getElementById('new_dia');
    const obs = document.getElementById('new_obs');
    if (ante) ante.value = '';
    if (dia) dia.value = '';
    if (obs) obs.value = '';
}

// Exponer todas las funciones en el ámbito global window para manejadores inline
window.recargarFichaActual = recargarFichaActual;
window.abrirEdicionPaciente = abrirEdicionPaciente;
window.toggleEdicionDatos = toggleEdicionDatos;
window.toastFicha = toastFicha;
window.openEdit = openEdit;
window.guardarHistoria = guardarHistoria;
window.guardarPersona = guardarPersona;
window.abrirEdicionHistoria = abrirEdicionHistoria;
window.cerrarSubmodal = cerrarSubmodal;
window.abrirModalTratamiento = abrirModalTratamiento;
window.guardarTratamiento = guardarTratamiento;
window.eliminarTratamiento = eliminarTratamiento;
window.abrirModalMedicamento = abrirModalMedicamento;
window.guardarMedicamento = guardarMedicamento;
window.eliminarMedicamento = eliminarMedicamento;
window.abrirModalEstudio = abrirModalEstudio;
window.guardarEstudio = guardarEstudio;
window.eliminarEstudio = eliminarEstudio;
window.eliminarFilaHistoria = eliminarFilaHistoria;
window.editarFilaHistoria = editarFilaHistoria;
window.guardarFilaHistoria = guardarFilaHistoria;
window.guardarNuevaHistoria = guardarNuevaHistoria;
window.limpiarNuevaHistoria = limpiarNuevaHistoria;
</script>
<?php
if (!$esModal) {
    require __DIR__ . '/../../includes/portal_footer.php';
}
?>