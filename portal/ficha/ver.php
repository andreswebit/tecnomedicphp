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

// TODO: Agregar función para medicamentos recetados
$medicamentos = medicamentos_listar($pacienteId);
$puedeGestionarMedicamentos = portal_rol() === 'profesional' || portal_rol() === 'admin';

$obrasSociales = obras_sociales_todas();

if (!$esModal) {
    $portal_titulo = 'Ficha médica · ' . $paciente['nombre'];
    require __DIR__ . '/../../includes/portal_header.php';
} else {
    // Modal: necesitamos los estilos de la ficha médica
    ?><style>
    .ficha-medica{font-family:'Montserrat',sans-serif;background:#edf1f0;color:#333a39;min-height:100vh}
    .ficha-header{display:flex;align-items:center;gap:16px;background:#0d1b2a;color:#fff;padding:14px 20px}
    .ficha-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#30c5c5,#98c544);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;flex-shrink:0}
    .ficha-paciente{flex:1}
    .ficha-paciente-nombre{font-size:.95rem;font-weight:600;margin-bottom:3px}
    .ficha-paciente-meta{display:flex;gap:10px;flex-wrap:wrap;font-size:.72rem;opacity:.85}
    .ficha-badge{background:rgba(152,197,68,.18);color:#7aad2e;padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:600;border:1px solid rgba(152,197,68,.3)}
    .ficha-tabs{display:flex;background:#fff;border-bottom:1px solid #e2e8e6}
    .ficha-tab{padding:9px 14px;border:none;background:transparent;color:#555f5e;font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px}
    .ficha-tab.active{color:#30c5c5;border-bottom:2px solid #30c5c5}
    .ficha-card{background:#fff;border-radius:8px;margin-bottom:10px;box-shadow:0 1px 6px rgba(13,27,42,.07)}
    .ficha-card-header{display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px solid #e2e8e6}
    .ficha-card-icon{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
    .ficha-card-icon.blue{background:rgba(26,165,165,.12);color:#30c5c5}
    .ficha-card-icon.green{background:rgba(152,197,68,.12);color:#7aad2e}
    .ficha-card-icon.amber{background:rgba(245,158,11,.12);color:#d97706}
    .ficha-card-icon.red{background:rgba(224,85,85,.12);color:#e05555}
    .ficha-card-icon.purple{background:rgba(139,92,246,.12);color:#7c3aed}
    .ficha-card-title{font-size:.88rem;font-weight:600;color:#0d1b2a;flex:1}
    .ficha-card-body{padding:10px 12px}
    .kv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px}
    .kv-item{background:#edf1f0;padding:8px;border-radius:6px}
    .kv-label{font-size:.65rem;font-weight:600;color:#8aada9;text-transform:uppercase;margin-bottom:2px}
    .kv-value{font-size:.82rem;color:#333a39}
    .ficha-field{margin-bottom:10px}
    .ficha-field-label{font-size:.68rem;font-weight:600;color:#555f5e;margin-bottom:3px}
    .ficha-textarea{width:100%;padding:8px 10px;border:1px solid #e2e8e6;border-radius:6px;font-size:.78rem;background:#edf1f0;color:#333a39;resize:vertical;min-height:55px}
    .ficha-textarea:focus{outline:none;border-color:#30c5c5;box-shadow:0 0 0 2px rgba(26,165,165,.15);background:#fff}
    .ficha-table{width:100%;border-collapse:collapse;font-size:.76rem}
    .ficha-table th{padding:7px 9px;text-align:left;color:#8aada9;border-bottom:1px solid #e2e8e6;white-space:nowrap;font-weight:500}
    .ficha-table td{padding:7px 9px;border-bottom:1px solid #e2e8e6;vertical-align:middle}
    .ficha-table tr:last-child td{border-bottom:none}
    .ficha-table tr:hover td{background:rgba(237,241,240,.5)}
    .badge-area{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:10px;font-size:.68rem;font-weight:600}
    .badge-area.hiperbarica{background:rgba(26,165,165,.12);color:#148080}
    .badge-area.nutricion{background:rgba(152,197,68,.12);color:#7aad2e}
    .badge-estado{display:inline-flex;align-items:center;padding:2px 8px;border-radius:10px;font-size:.68rem;font-weight:600}
    .badge-estado.activo{background:rgba(152,197,68,.12);color:#7aad2e}
    .badge-estado.suspendido{background:rgba(245,158,11,.12);color:#d97706}
    .badge-estado.finalizado{background:rgba(138,138,138,.12);color:#888}
    .ficha-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-family:'Poppins',sans-serif;font-size:.75rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;white-space:nowrap}
    .ficha-btn-primary{background:#7aad2e;color:#fff;box-shadow:0 2px 6px rgba(152,197,68,.25)}
    .ficha-btn-primary:hover{background:#98c544;transform:translateY(-1px)}
    .ficha-btn-outline{background:transparent;color:#30c5c5;border:1px solid #30c5c5}
    .ficha-btn-outline:hover{background:rgba(26,165,165,.06)}
    .ficha-btn-teal{background:#30c5c5;color:#fff;box-shadow:0 2px 6px rgba(26,165,165,.25)}
    .ficha-btn-teal:hover{background:#148080;transform:translateY(-1px)}
    .ficha-btn-sm{padding:5px 9px;font-size:.7rem}
    .ficha-btn-xs{padding:3px 7px;font-size:.68rem;border-radius:5px}
    .ficha-card-footer{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-top:1px solid #e2e8e6;font-size:.7rem}
    .ficha-card-footer .date{color:#8aada9}
    .lista-item{display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #e2e8e6}
    .lista-item:last-child{border-bottom:none}
    .lista-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
    .lista-icon.blue{background:rgba(26,165,165,.1);color:#30c5c5}
    .lista-icon.purple{background:rgba(139,92,246,.1);color:#7c3aed}
    .lista-icon.green{background:rgba(152,197,68,.1);color:#7aad2e}
    .lista-info{flex:1;min-width:0}
    .lista-nombre{font-size:.8rem;font-weight:600;color:#0d1b2a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .lista-meta{font-size:.7rem;color:#555f5e;margin-top:1px;display:flex;gap:8px;flex-wrap:wrap}
    .lista-actions{display:flex;gap:4px;flex-shrink:0}
    .ficha-empty{text-align:center;padding:24px 16px;color:#8aada9}
    .ficha-empty-icon{font-size:1.8rem;margin-bottom:8px;opacity:.7}
    .ficha-empty-text{font-size:.8rem;font-weight:500;margin-bottom:12px}
    </style><?php
}
?>
<div class="ficha-medica" data-paciente-id="<?= $pacienteId ?>">

    <!-- Header compacto -->
    <div class="ficha-header">
        <div class="ficha-avatar">
            <?= strtoupper(substr($paciente['nombre'], 0, 1)) . substr($paciente['apellido'], 0, 1) ?>
        </div>
        <div class="ficha-paciente">
            <div class="ficha-paciente-nombre"><?= htmlspecialchars($paciente['apellido'] . ', ' . $paciente['nombre']) ?></div>
            <div class="ficha-paciente-meta">
                <span>🪪 <?= htmlspecialchars($paciente['dni']) ?></span>
                <span>📞 <?= htmlspecialchars($paciente['telefono'] ?: '-') ?></span>
                <span>🏥 <?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?></span>
            </div>
        </div>
        <div class="ficha-badge"><?= $paciente['estado'] ?? 'Activo' ?></div>
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
                <button class="ficha-btn ficha-btn-xs ficha-btn-outline" id="btnEditarDatos" onclick="toggleEdicionDatos()">Editar</button>
                <?php endif; ?>
            </div>
            <div class="ficha-card-body" id="datosBody">
                <div class="kv-grid" id="datosView">
                    <div class="kv-item">
                        <div class="kv-label">Nombre</div>
                        <div class="kv-value" id="view_nombre"><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">DNI</div>
                        <div class="kv-value" id="view_dni"><?= htmlspecialchars($paciente['dni']) ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Tel</div>
                        <div class="kv-value" id="view_telefono"><?= htmlspecialchars($paciente['telefono'] ?: '-') ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Email</div>
                        <div class="kv-value" id="view_email"><?= htmlspecialchars($paciente['email'] ?: '-') ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Obra</div>
                        <div class="kv-value" id="view_obra"><?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?></div>
                    </div>
                    <div class="kv-item">
                        <div class="kv-label">Nac.</div>
                        <div class="kv-value" id="view_nac"><?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '-') ?></div>
                    </div>
                </div>
                <form id="formDatos" data-ficha-form style="display:none"
                      action="<?= b('/portal/ficha/guardar_persona.php') ?>" method="post"
                      onsubmit="return guardarPersona(event)">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="kv-grid">
                        <div class="kv-item">
                            <div class="kv-label">Nombre</div>
                            <input class="ficha-textarea" name="nombre" id="inp_nombre" value="<?= htmlspecialchars($paciente['nombre']) ?>" required>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Apellido</div>
                            <input class="ficha-textarea" name="apellido" id="inp_apellido" value="<?= htmlspecialchars($paciente['apellido']) ?>" required>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">DNI</div>
                            <input class="ficha-textarea" name="dni" id="inp_dni" value="<?= htmlspecialchars($paciente['dni']) ?>" required readonly>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Tel</div>
                            <input class="ficha-textarea" name="telefono" id="inp_telefono" value="<?= htmlspecialchars($paciente['telefono'] ?: '') ?>">
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Email</div>
                            <input class="ficha-textarea" name="email" id="inp_email" value="<?= htmlspecialchars($paciente['email'] ?: '') ?>" type="email">
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Obra social</div>
                            <select class="ficha-textarea" name="obra_social_id" id="inp_obra">
                                <option value="">— Sin cobertura —</option>
                                <?php foreach ($obrasSociales as $os): ?>
                                <option value="<?= $os['id'] ?>" <?= ((int)($perfil['obra_social_id'] ?? 0) === (int)$os['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($os['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="kv-item">
                            <div class="kv-label">Nac.</div>
                            <input class="ficha-textarea" name="fecha_nacimiento" id="inp_nac" value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?: '') ?>" type="date">
                        </div>
                    </div>
                    <div style="margin-top:10px;display:flex;gap:8px">
                        <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-primary">💾 Guardar</button>
                        <button type="button" class="ficha-btn ficha-btn-sm ficha-btn-outline" onclick="toggleEdicionDatos()">Cancelar</button>
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
                <button class="ficha-btn ficha-btn-xs ficha-btn-outline" onclick="abrirModalHistoria()">✏️ Modificar</button>
                <?php endif; ?>
            </div>
            <div class="ficha-card-body">
                <?php if ($puedeEditar): ?>
                <form data-ficha-form method="post" action="<?= b('/portal/ficha/guardar_historia.php') ?>">
                    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">
                    <div class="ficha-field">
                        <label class="ficha-field-label">Antecedentes</label>
                        <textarea name="antecedentes" rows="2" class="ficha-textarea"><?= htmlspecialchars($historia['antecedentes'] ?? '') ?></textarea>
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label">Diagnóstico</label>
                        <textarea name="diagnostico" rows="2" class="ficha-textarea"><?= htmlspecialchars($historia['diagnostico'] ?? '') ?></textarea>
                    </div>
                    <div class="ficha-field">
                        <label class="ficha-field-label">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="ficha-textarea"><?= htmlspecialchars($historia['observaciones'] ?? '') ?></textarea>
                    </div>
                    <div class="ficha-card-footer">
                        <span class="date">Última: <?= !empty($historia['actualizado_en']) ? date('d/m/Y', strtotime($historia['actualizado_en'])) : '-' ?></span>
                        <button type="submit" class="ficha-btn ficha-btn-sm ficha-btn-primary">💾 Guardar</button>
                    </div>
                </form>
                <?php else: ?>
                <div class="kv-grid" style="grid-template-columns:1fr 1fr">
                    <div class="kv-item">
                        <div class="kv-label">Antecedentes</div>
                        <div class="kv-value"><?= nl2br(htmlspecialchars($historia['antecedentes'] ?? 'Sin datos cargados.')) ?></div>
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
                            <td><?= htmlspecialchars($t['profesional_apellido'] . ', ' . $t['profesional_nombre']) ?></td>
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
                <button class="ficha-btn ficha-btn-xs ficha-btn-teal" onclick="abrirModalMedicamento()">➕ Agregar</button>
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
                        <a href="<?= b('/portal/ficha/descargar_estudio.php?id=' . $e['id']) ?>" target="_blank" class="ficha-btn ficha-btn-xs ficha-btn-outline" title="Descargar">⬇️</a>
                        <?php if ($puedeEditar): ?>
                        <button class="ficha-btn ficha-btn-xs ficha-btn-outline" onclick="eliminarEstudio(<?= $e['id'] ?>)" title="Eliminar">🗑️</button>
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
            // Detecta si la ficha está dentro de un modal scrollable o en la página completa
            const modalBody = document.getElementById('fichaModalBody');
            if (modalBody && modalBody.contains(target)) {
                // Modal: hace scroll dentro del body del modal hasta el card
                const top = target.offsetTop - 8;
                modalBody.scrollTo({ top, behavior: 'smooth' });
            } else {
                // Página completa: hace scroll de la ventana hasta el card
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});

// Funciones de edición inline
function toggleEdicionDatos() {
    const view = document.getElementById('datosView');
    const form = document.getElementById('formDatos');
    const btn = document.getElementById('btnEditarDatos');
    const editing = form.style.display === 'block';
    view.style.display = editing ? 'grid' : 'none';
    form.style.display = editing ? 'none' : 'block';
    btn.textContent = editing ? 'Editar' : '❌ Cancelar';
}

function guardarPersona(e) {
    e.preventDefault();
    const form = document.getElementById('formDatos');
    const data = new FormData(form);
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    fetch(form.action, { method: 'POST', body: data })
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

function abrirModalHistoria() {
    alert('Modal de edición de historia - Por implementar');
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
</script>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>