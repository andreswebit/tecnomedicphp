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

        <!-- CARD 1: Datos personales   cambiar si no funciona onclick="abrirEdicionPaciente()" -->
        <div class="ficha-card" data-card="datos">
            <div class="ficha-card-header">
                <div class="ficha-card-icon blue">👤</div>
                <div class="ficha-card-title">Datos</div>
                <?php if ($puedeEditar): ?>
                <button class="ficha-btn ficha-btn-xs ficha-btn-outline" id="btnEditarDatos"
                    onclick="abrirEdicionPaciente()"
                    >Editar</button>
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
                <?php if ($puedeEditar): ?>
                <button type="button" class="ficha-btn ficha-btn-xs ficha-btn-outline" id="btnEditarHistoria"
                    onclick="abrirEdicionHistoria()">
                    Modificar</button>
                    
                <?php endif; ?>
            </div>
            <div class="ficha-card-body">
                <?php if ($puedeEditar): ?>
                <div id="historiaView" style="display:flex;flex-direction:column;gap:6px;font-size:.85rem">
                    <?php foreach ($historiaRegistros as $h): ?>
                    <div class="historia-fila" style="background:#fff;border:1px solid #e2e8e6;border-radius:8px;padding:10px;box-shadow:0 1px 4px rgba(13,27,42,.05)" data-id="<?= $h['id'] ?? 0 ?>" id="filaHist<?= $h['id'] ?? 0 ?>">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;border-bottom:1px solid #e2e8e6;padding-bottom:6px">
                            <span style="font-weight:600;color:#0d1b2a;font-size:.85rem">📅 <?= htmlspecialchars($h['fecha_fmt'] ?? '-') ?></span>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap">
                            <div style="flex:1 1 160px;min-width:130px">
                                <div style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase">Antecedentes</div>
                                <div style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px" data-ante="<?= htmlspecialchars($h['antecedentes'] ?? '-') ?>"> <?= nl2br(htmlspecialchars($h['antecedentes'] ?? '-')) ?> </div>
                            </div>
                            <div style="flex:1 1 160px;min-width:130px">
                                <div style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase">Diagnóstico</div>
                                <div style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px" data-dia="<?= htmlspecialchars($h['diagnostico'] ?? '-') ?>"> <?= nl2br(htmlspecialchars($h['diagnostico'] ?? '-')) ?> </div>
                            </div>
                        </div>
                        <div style="margin-top:6px">
                            <div style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase">Observaciones</div>
                            <div style="font-size:.82rem;color:#333;background:#edf1f0;padding:6px 8px;border-radius:6px" data-obs="<?= htmlspecialchars($h['observaciones'] ?? '-') ?>"> <?= nl2br(htmlspecialchars($h['observaciones'] ?? '-')) ?> </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <!-- Fila de agregar nueva información -->
                    <div class="historia-fila" style="background:#e2e8e6;border:2px dashed #8aada9;border-radius:8px;padding:10px" id="filaNueva">
                        <div style="font-weight:600;color:#555f5e;font-size:.85rem;margin-bottom:6px">➕ Nueva entrada (<?= date('d/m/Y') ?>)</div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap">
                            <div style="flex:1 1 160px;min-width:130px">
                                <textarea id="new_ante" rows="2" class="ficha-textarea" style="font-size:.82rem;min-height:48px" placeholder="Antecedentes…"></textarea>
                            </div>
                            <div style="flex:1 1 160px;min-width:130px">
                                <textarea id="new_dia" rows="2" class="ficha-textarea" style="font-size:.82rem;min-height:48px" placeholder="Diagnóstico…"></textarea>
                            </div>
                        </div>
                        <div style="margin-top:6px">
                            <textarea id="new_obs" rows="2" class="ficha-textarea" style="font-size:.82rem;min-height:48px" placeholder="Observaciones…"></textarea>
                        </div>
                        <div style="margin-top:8px;display:flex;gap:8px">
                            <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-primary" onclick="guardarNuevaHistoria()">💾 Guardar</button>
                            <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline" onclick="limpiarNuevaHistoria()">Cancelar</button>
                        </div>
                    </div>
                    <?php if (empty($historiaRegistros)): ?><div class="ficha-empty"><div class="ficha-empty-text">Sin registros aún.</div></div><?php endif; ?>
                </div>
                <div id="historiaEdit" style="display:none">
                    <form data-ficha-form method="post" action="<?= b('/portal/ficha/guardar_historia.php') ?>"
                        onsubmit="return guardarHistoria(event)">
                        <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                        <div class="ficha-field">
                            <label class="ficha-field-label">Antecedentes</label>
                            <textarea name="antecedentes" rows="2" id="inp_antecedentes"
                                class="ficha-textarea editing"><?= htmlspecialchars($historia['antecedentes'] ?? '') ?></textarea>
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label">Diagnóstico</label>
                            <textarea name="diagnostico" rows="2" id="inp_diagnostico"
                                class="ficha-textarea editing"><?= htmlspecialchars($historia['diagnostico'] ?? '') ?></textarea>
                        </div>
                        <div class="ficha-field">
                            <label class="ficha-field-label">Observaciones</label>
                            <textarea name="observaciones" rows="2" id="inp_observaciones"
                                class="ficha-textarea editing"><?= htmlspecialchars($historia['observaciones'] ?? '') ?></textarea>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:6px">
                            <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-primary">💾 Guardar</button>
                        </div>
                        <div class="ficha-card-footer" style="margin-top:4px">
                            <span class="date">Última: <?= !empty($historia['actualizado_en']) ? date('d/m/Y', strtotime($historia['actualizado_en'])) : '-' ?></span>
                        </div>
                    </form>
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
                <button class="ficha-btn ficha-btn-xs ficha-btn-outline" onclick="abrirModalTratamiento()">➕</button>
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
                            <td><?= nl2br(htmlspecialchars($t['descripcion'])) ?></td>
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
                <?php if ($puedeGestionarMedicamentos): ?>
                <button class="ficha-btn ficha-btn-xs ficha-btn-teal" onclick="abrirModalMedicamento()">➕
                    Agregar</button>
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
                <button class="ficha-btn ficha-btn-xs ficha-btn-primary" onclick="abrirModalEstudio()">⬆️ Subir</button>
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
                    <div class="lista-actions">
                        <a href="<?= b('/portal/ficha/descargar_estudio.php?id=' . $e['id']) ?>" target="_blank"
                            class="ficha-btn ficha-btn-xs ficha-btn-outline" title="Descargar">⬇️</a>
                        <?php if ($puedeEditar): ?>
                        <button class="ficha-btn ficha-btn-xs ficha-btn-outline"
                            onclick="eliminarEstudio(<?= $e['id'] ?>)" title="Eliminar">🗑️</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /padding -->
</div><!-- /.ficha-medica -->

<?php
if (!$esModal) {
    require __DIR__ . '/../../includes/portal_footer.php';
}
?>
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
</script>
<script>
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

Funciones de edición inline
function abrirEdicionPaciente() {
    if (typeof openEdit === 'function') {
        openEdit({
            id: <?= $pacienteId ?>,
            nombre: <?= json_encode($paciente['nombre']) ?>,
            apellido: <?= json_encode($paciente['apellido']) ?>,
            dni: <?= json_encode($paciente['dni']) ?>,
            telefono: <?= json_encode($paciente['telefono'] ?: '') ?>,
            email: <?= json_encode($paciente['email'] ?: '') ?>,
            fecha_nacimiento: <?= json_encode($perfil['fecha_nacimiento'] ?: '') ?>,
            obra_social_id: <?= json_encode($perfil['obra_social_id'] ?: '') ?>
        });
    } else {
        toggleEdicionDatos();
    }
}



function toggleEdicionDatos() {
    const view = document.getElementById('datosView');
    const form = document.getElementById('formDatos');
    const btn = document.getElementById('btnEditarDatos');
    const editing = form.style.display === 'block';
    view.style.display = editing ? 'grid' : 'none';
    form.style.display = editing ? 'none' : 'block';
    btn.textContent = editing ? 'Editar' : '❌ Cancelar';
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

// Función openEdit: abre el modal de edición de paciente en el contexto de la ficha médica.
// Llama al openEdit de admin/pacientes.php si está disponible (contexto admin),
// de lo contrario, muestra el formulario inline de la ficha pre-cargado con los datos.
function openEdit(d) {
    // Si estamos en contexto admin (openEdit ya definido globalmente), delegamos ahí
    if (typeof openEdit === 'function' && typeof modalEditar !== 'undefined') {
        // Ya existe el openEdit global - el puente en portal_header.php lo llamó,
        // pero como openEdit admin ya existe, lo re-ejecutamos con los datos de la ficha
        // para que el modal admin se abra con esos datos. Si el admin tiene su propio
        // modal, este es un fallback; si no, continuamos abajo.
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
    }

    // Contexto ficha médica (portal): muestra el formulario inline pre-cargado
    const view = document.getElementById('datosView');
    const form = document.getElementById('formDatos');
    const btn = document.getElementById('btnEditarDatos');

    if (!view || !form) {
        alert('No se pueden mostrar los datos de edición.');
        return;
    }

    // Pre-cargar los datos del paciente en los campos del formulario
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

    // Seleccionar obra social si existe
    if (inp_obra) {
        inp_obra.value = d.obra_social_id || '';
        // Buscar y seleccionar el option correspondiente
        const obraSelect = inp_obra; // el select ya está en el DOM
        if (obraSelect.options) {
            for (let i = 0; i < obraSelect.options.length; i++) {
                if (obraSelect.options[i].value === d.obra_social_id || '') {
                    obraSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }

    // Mostrar formulario y ocultar vista
    toggleEdicionDatos();
    // Asegurar que el botón diga "❌ Cancelar"
    if (btn) btn.textContent = '❌ Cancelar';
}

function guardarHistoria(e) {
    e.preventDefault();
    const form = document.getElementById('historiaEdit').querySelector('form');
    const data = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.text())
        .then(txt => {
            if (txt.trim() === 'ok') {
                // Actualizar los valores de vista con los datos enviados
                document.getElementById('view_antecedentes').innerHTML = data.get('antecedentes') ? nl2br(data.get(
                    'antecedentes')) : 'Sin datos cargados.';
                document.getElementById('view_diagnostico').innerHTML = data.get('diagnostico') ? nl2br(data.get(
                    'diagnostico')) : '-';
                const obsDiv = document.getElementById('view_observaciones');
                if (obsDiv) obsDiv.innerHTML = '<p>' + nl2br(data.get('observaciones')) + '</p>';
                // Quitar el fondo blanco (editing) de los textareas -> volver a g100
                form.querySelectorAll('.ficha-textarea').forEach(function(ta) {
                    ta.classList.remove('editing');
                });
                // Volver a la vista
                const view = document.getElementById('historiaView');
                form.style.display = 'none';
                view.style.display = 'block';
                // Restaurar botón a "Modificar"
                const btnEdit = document.getElementById('btnEditarHistoria');
                if (btnEdit) btnEdit.textContent = '✏️ Modificar';
                // Toast de éxito
                toastFicha('✅ Historia clínica guardada');
            } else {
                toastFicha('⚠️ Error al guardar: ' + txt);
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        })
        .catch(() => {
            toastFicha('⚠️ Error de conexión');
            btn.disabled = false;
            btn.textContent = '💾 Guardar';
        });
}

function nl2br(str) {
    if (!str) return '';
    return str.replace(/\n/g, '<br>');
}

function guardarPersona(e) {
    e.preventDefault();
    const form = document.getElementById('formDatos');
    const data = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    fetch(form.action, {
            method: 'POST',
            body: data
        })
        .then(r => r.json())
        .then(r => {
            if (r.ok) {
                location.reload();
            } else {
                alert(r.error || 'Error al guardar');
                btn.disabled = false;
                btn.textContent = '💾 Guardar';
            }
        })
        .catch(() => {
            alert('Error de conexión');
            btn.disabled = false;
            btn.textContent = '💾 Guardar';
        });
}

function abrirEdicionHistoria() {
    const view = document.getElementById('historiaView');
    const form = document.getElementById('historiaEdit');
    const btn = document.getElementById('btnEditarHistoria');

    const editing = form.style.display === 'block';
    view.style.display = editing ? 'block' : 'none';
    form.style.display = editing ? 'none' : 'block';
    btn.textContent = editing ? 'Modificar' : '❌ Cancelar';

    // Si vamos a mostrar el formulario, también mostrar todas las filas editables
    if (!editing) {
        document.querySelectorAll('.historia-fila').forEach(fila => {
            if (!fila.id.startsWith('filaNueva')) {
                fila.style.display = 'none';
            }
        });
        document.getElementById('filaNueva').style.display = 'block';
    } else {
        document.querySelectorAll('.historia-fila').forEach(fila => {
            fila.style.display = 'block';
        });
    }
}

function abrirModalTratamiento() {
    alert('Modal de nuevo tratamiento - Por implementar');
}

function abrirModalMedicamento() {
    alert('Modal de nuevo medicamento - Por implementar');
}

function abrirModalEstudio() {
    alert('Modal de nuevo estudio - Por implementar');
}

function eliminarEstudio(id) {
    if (!confirm('¿Eliminar este estudio?')) return;
    alert('Estudio eliminado - Por implementar la lógica real');
}

function editarFilaHistoria(id) {
    const fila = document.getElementById('filaHist' + id);
    if (!fila) return;

    // Obtener datos actuales (o desde los data-attributes)
    const anteDiv = fila.querySelector('[data-ante]');
    const diaDiv = fila.querySelector('[data-dia]');
    const obsDiv = fila.querySelector('[data-obs]');
    const ante = anteDiv ? (anteDiv.getAttribute('data-ante') || '') : '';
    const dia  = diaDiv ? (diaDiv.getAttribute('data-dia') || '') : '';
    const obs  = obsDiv ? (obsDiv.getAttribute('data-obs') || '') : '';

    // Reemplazar contenido con textareas editables
    fila.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;border-bottom:1px solid #e2e8e6;padding-bottom:6px">
            <span style="font-weight:600;color:#0d1b2a;font-size:.85rem">📅 Editando registro #` + id + `</span>
            <button type="button" class="ficha-btn ficha-btn-xs ficha-btn-primary" onclick="guardarFilaHistoria(` + id + `, this)">💾 Guardar</button>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div style="flex:1 1 160px;min-width:130px">
                <label style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;display:block;margin-bottom:3px">Antecedentes</label>
                <textarea id="fila_ante_` + id + `" rows="2" class="ficha-textarea editing" style="font-size:.82rem;min-height:48px">` + ante.replace(/</g, '&lt;').replace(/>/g, '&gt;') + `</textarea>
            </div>
            <div style="flex:1 1 160px;min-width:130px">
                <label style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;display:block;margin-bottom:3px">Diagnóstico</label>
                <textarea id="fila_dia_` + id + `" rows="2" class="ficha-textarea editing" style="font-size:.82rem;min-height:48px">` + dia.replace(/</g, '&lt;').replace(/>/g, '&gt;') + `</textarea>
            </div>
        </div>
        <div style="margin-top:6px">
            <label style="font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;display:block;margin-bottom:3px">Observaciones</label>
            <textarea id="fila_obs_` + id + `" rows="2" class="ficha-textarea editing" style="font-size:.82rem;min-height:48px">` + obs.replace(/</g, '&lt;').replace(/>/g, '&gt;') + `</textarea>
        </div>
    `;
}

function guardarFilaHistoria(id, btn) {
    const ante = document.getElementById('fila_ante_' + id).value;
    const dia  = document.getElementById('fila_dia_' + id).value;
    const obs  = document.getElementById('fila_obs_' + id).value;

    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('<?= b('/portal/ficha/guardar_historia_fila.php') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id) + '&antecedentes=' + encodeURIComponent(ante) + '&diagnostico=' + encodeURIComponent(dia) + '&observaciones=' + encodeURIComponent(obs)
    })
    .then(r => r.text())
    .then(txt => {
        if (txt.trim() === 'ok') {
            toastFicha('✅ Historia actualizada');
            // Recargar página para reflejar cambios (o reconstruir fila)
            setTimeout(() => location.reload(), 800);
        } else {
            toastFicha('⚠️ Error: ' + txt);
            btn.disabled = false;
            btn.textContent = '💾 Guardar';
        }
    })
    .catch(() => {
        toastFicha('⚠️ Error de conexión');
        btn.disabled = false;
        btn.textContent = '💾 Guardar';
    });
}

function guardarNuevaHistoria() {
    const ante = document.getElementById('new_ante').value;
    const dia  = document.getElementById('new_dia').value;
    const obs  = document.getElementById('new_obs').value;
    const btn = document.querySelector('#filaNueva button[type="button"]');
    if (!btn) return;

    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('<?= b('/portal/ficha/guardar_historia.php') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'paciente_id=<?= $pacienteId ?>&antecedentes=' + encodeURIComponent(ante) + '&diagnostico=' + encodeURIComponent(dia) + '&observaciones=' + encodeURIComponent(obs)
    })
    .then(r => r.text())
    .then(txt => {
        if (txt.trim() === 'ok') {
            toastFicha('✅ Historia guardada');
            setTimeout(() => location.reload(), 800);
        } else {
            toastFicha('⚠️ Error: ' + txt);
            btn.disabled = false;
            btn.textContent = '💾 Guardar';
        }
    })
    .catch(() => {
        toastFicha('⚠️ Error de conexión');
        btn.disabled = false;
        btn.textContent = '💾 Guardar';
    });
}

function limpiarNuevaHistoria() {
    document.getElementById('new_ante').value = '';
    document.getElementById('new_dia').value = '';
    document.getElementById('new_obs').value = '';
}
</script>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>