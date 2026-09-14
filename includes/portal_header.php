<?php
// Incluir DESPUÉS de require auth.php y de resolver la lógica de la página.
// Variable opcional $portal_titulo para el <title>.
// Variable opcional $portal_activo para resaltar el item activo del sidebar
// (valores según rol, ver los value= de cada <a> más abajo).
$rol = portal_rol();
$nombreSesion = $_SESSION['portal_nombre'] ?? '';
$activo = $portal_activo ?? '';
$base = b('');

$etiquetaRol = [
    'paciente'    => 'Portal Paciente',
    'profesional' => 'Portal Profesional',
    'admin'       => 'Portal Administrador',
][$rol] ?? 'Mi Portal';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($portal_titulo ?? 'Mi Portal · TECNOMEDIC') ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Montserrat:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= b('/static/tecnomedic.css') ?>">
    <link rel="stylesheet" href="<?= b('/portal/css/portal.css') ?>">
</head>

<body class="portal"
    style="background: var(--g300) url('<?= b('/static/img/fondo/fondo1.jfif') ?>');background-repeat: repeat; background-blend-mode: soft-light; background-size: contain;">

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <div class="wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <a href="<?= HOME_URL ?>/">
                    <img src="<?= b('/static/img/tecno-logo.jpeg') ?>" alt="TECNOMEDIC" class="logo-img">
                </a>
            </div>
            <div class="sidebar-sub"></div>

            <div class="nav-label">Menú</div>
            <?php if ($rol === 'paciente'): ?>
            <a href="<?= b('/portal/paciente/dashboard.php') ?>"
                class="nav-item <?= $activo === 'inicio' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/home.ico" alt="" /></span><span>Inicio</span></a>
            <a href="<?= b('/portal/paciente/perfil.php') ?>"
                class="nav-item <?= $activo === 'perfil' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/usuario.ico" alt="" /></span><span>Mi perfil</span></a>
            <a href="<?= b('/portal/recursos.php') ?>"
                class="nav-item <?= $activo === 'recursos' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/carpeta.ico" alt="" /></span><span>Recursos</span></a>

            <?php elseif ($rol === 'profesional'): ?>
            <a href="<?= b('/portal/profesional/dashboard.php') ?>"
                class="nav-item <?= $activo === 'pacientes' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/doctor.ico" alt="" /></span><span>Mis pacientes</span></a>
            <a href="<?= b('/portal/recursos.php') ?>"
                class="nav-item <?= $activo === 'recursos' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/carpeta.ico" alt="" /></span><span>Recursos</span></a>

            <?php elseif ($rol === 'admin'): ?>
            <a href="<?= b('/admin/tablero.php') ?>"
                class="nav-item <?= $activo === 'tablero' ? 'active' : '' ?>"><span></span><span>Tablero</span></a>
            <a href="<?= b('/admin/index.php') ?>" class="nav-item"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/calendario2.ico" alt="" /></span><span>Turnos</span></a>
            <a href="<?= b('/admin/pacientes.php') ?>"
                class="nav-item <?= $activo === 'pacientes' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/pacientes.ico" alt="" /></span><span>Pacientes</span></a>
            <a href="<?= b('/admin/profesionales.php') ?>"
                class="nav-item <?= $activo === 'profesionales' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/doctor.ico" alt="" /></span><span>Profesionales</span></a>
            <a href="<?= b('/admin/usuarios.php') ?>"
                class="nav-item <?= $activo === 'usuarios' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/personas.ico" alt="" /></span><span>Usuarios</span></a>
            <a href="<?= b('/admin/presupuestos.php') ?>"
                class="nav-item <?= $activo === 'presupuestos' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/calculadora.ico"
                        alt="" /></span><span>Presupuestos</span></a>
            <a href="<?= b('/admin/recursos.php') ?>"
                class="nav-item <?= $activo === 'recursos' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/carpeta.ico" alt="" /></span><span>Recursos</span></a>
            <a href="<?= b('/admin/contactos.php') ?>"
                class="nav-item <?= $activo === 'contactos' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/email.ico" alt="" /></span><span>Mensajes</span></a>
            <a href="<?= b('/admin/consultar_dni.php') ?>"
                class="nav-item <?= $activo === 'dni' ? 'active' : '' ?>"><span><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/lupa.ico" alt="" /></span><span>Consultar DNI</span></a>
            <a href="<?= b('/admin/multimedia.php') ?>"
                class="nav-item <?= $activo === 'multimedia' || in_array($activo, ['novedades','staff','testimonios','destacados','config']) ? 'active' : '' ?>"><span><img
                        class="tm-thumb" src="<?= $base ?>/static/img/icons/multimedia.ico"
                        alt="" /></span><span>Multimedia</span></a>
            <?php endif; ?>

            <div class="sidebar-footer">
                <div style="margin-bottom:12px;">
                    <span class="status-dot"></span>
                    <span class="status-text"><?= htmlspecialchars($nombreSesion) ?></span>
                </div>
                <a href="<?= b('/logout.php') ?>"
                    style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--g100);text-decoration:none;transition:color .2s;"
                    onmouseover="this.style.color='var(--amber)'" onmouseout="this.style.color='var(--g100)'">
                    <span><svg height="16" width="16" version="1.1" id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                                    <path style="fill:#FF6643;"
                                        d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                                    <polygon style="fill:#ffffff;"
                                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                                </svg></span><span>Cerrar sesión</span>
                </a>
            </div>
        </aside>

        <div class="main">
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger-btn" onclick="toggleSidebar()">
                        <span></span><span></span><span></span>
                    </button>
                    <div class="page-title">Mi <span> <?= htmlspecialchars($etiquetaRol) ?></span></div>
                </div>
            </div>
            <div class="portal-container" style="padding:0;max-width:none;margin:0;">

                <!-- Modal Ficha Médica (Fase D) -->
                <div class="ficha-modal-overlay" id="fichaModalOverlay" onclick="if(event.target===this) cerrarFicha()">
                    <div class="ficha-modal">
                        <div class="ficha-modal-header">
                            <strong>Ficha médica</strong>
                            <button type="button" class="ficha-modal-close" onclick="cerrarFicha()">
                                <svg height="26" width="26" version="1.1" id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                                    <path style="fill:#FF6643;"
                                        d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                                    <polygon style="fill:#ffffff;"
                                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                                </svg>
                            </button>
                        </div>
                        <div class="ficha-modal-body" id="fichaModalBody">Cargando…</div>
                    </div>
                </div>
                <script>
                const TM_BASE = "<?= b('') ?>";

                function abrirFicha(pacienteId) {
                    var overlay = document.getElementById('fichaModalOverlay');
                    var body = document.getElementById('fichaModalBody');
                    body.innerHTML = 'Cargando…';
                    overlay.classList.add('open');
                    fetch(TM_BASE + '/portal/ficha/ver.php?modal=1&paciente_id=' + pacienteId)
                        .then(function(r) {
                            return r.text();
                        })
                        .then(function(html) {
                            body.innerHTML = html;
                            bindFichaForms(pacienteId);
                        })
                        .catch(function() {
                            body.innerHTML = '<div class="portal-alert error">No se pudo cargar la ficha.</div>';
                        });
                }

                function cerrarFicha() {
                    document.getElementById('fichaModalOverlay').classList.remove('open');
                }

                // Puente para que la ficha médica (cargada por fetch dentro del modal)
                // pueda abrir el modal global de edición de admin/pacientes.php.
                function abrirEdicionPaciente() {
                    var ficha = document.querySelector('.ficha-medica');
                    var pid = ficha ? (ficha.dataset.pacienteId || ficha.getAttribute('data-paciente-id')) : null;
                    // Si openEdit admin existe (modal admin en la pág), abrirlo con datos de los inputs
                    if (typeof openEdit === 'function') {
                        if (document.getElementById('formDatos') && document.getElementById('inp_nombre')) {
                            openEdit({
                                id: pid || 0,
                                nombre: document.getElementById('inp_nombre').value || '',
                                apellido: document.getElementById('inp_apellido').value || '',
                                dni: document.getElementById('inp_dni').value || '',
                                telefono: document.getElementById('inp_telefono').value || '',
                                email: document.getElementById('inp_email').value || '',
                                fecha_nacimiento: document.getElementById('inp_nac').value || '',
                                obra_social_id: document.getElementById('inp_obra').value || ''
                            });
                        }
                        return;
                    }
                    // Contexto ficha (fetch): toggle edición inline usando clases CSS
                    var view = document.getElementById('datosView');
                    var form = document.getElementById('formDatos');
                    var btn = document.getElementById('btnEditarDatos');
                    if (!view || !form) return;
                    // Alternar modo edición
                    if (form.classList.contains('editing-mode')) {
                        // SALIR modo edición - quitar clase y volver a state inicial
                        form.classList.remove('editing-mode');
                        // Re-aplicar readonly/default a los inputs para que no sean editables
                        var inputs = form.querySelectorAll('input, select');
                        inputs.forEach(function(inp) {
                            inp.removeAttribute('readonly');
                            inp.style.pointerEvents = '';
                        });
                        view.style.display = 'grid';
                        form.style.display = 'none';
                        btn.textContent = 'Editar';
                    } else {
                        // ENTRAR modo edición - agregar clase y quitar readonly/pointer-events
                        form.classList.add('editing-mode');
                        var inputs = form.querySelectorAll('input, select');
                        inputs.forEach(function(inp) {
                            inp.removeAttribute('readonly');
                            inp.style.pointerEvents = 'auto';
                        });
                        view.style.display = 'none';
                        form.style.display = 'block';
                        btn.textContent = '❌ Cancelar';
                    }
                }

                function bindFichaForms(pacienteId) {
                    document.querySelectorAll('#fichaModalBody form[data-ficha-form]').forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            var btn = form.querySelector('button[type=submit]');
                            if (btn) {
                                btn.disabled = true;
                                btn.textContent = 'Guardando…';
                            }
                            fetch(form.action, {
                                    method: 'POST',
                                    body: new FormData(form)
                                })
                                .then(function(r) {
                                    return r.text();
                                })
                                .then(function(txt) {
                                    if (txt.trim() !== 'ok') alert('No se pudo guardar: ' + txt);
                                    abrirFicha(pacienteId);
                                })
                                .catch(function() {
                                    alert('Error de conexión al guardar.');
                                });
                        });
                    });
                }

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') cerrarFicha();
                });

                // Historia clínica — alternar edición (botón verde "Guardar" al activar)
                function abrirEdicionHistoria() {
                    if (typeof toggleEdicionHistoria === 'function') {
                        toggleEdicionHistoria();
                    }
                }

                function toggleEdicionHistoria() {
                    var view = document.getElementById('historiaView');
                    var form = document.getElementById('historiaEdit');
                    var btn = document.getElementById('btnEditarHistoria');
                    if (!view || !form) return;
                    var editing = form.classList.contains('editing-mode');
                    if (editing) {
                        // Salir modo edición
                        form.classList.remove('editing-mode');
                        view.style.display = 'block';
                        form.style.display = 'none';
                        if (btn) {
                            btn.textContent = '✏️ Modificar';
                            btn.classList.add('ficha-btn-outline');
                            btn.classList.remove('ficha-btn-primary');
                        }
                    } else {
                        // Entrar modo edición — botón verde "Guardar"
                        form.classList.add('editing-mode');
                        view.style.display = 'none';
                        form.style.display = 'block';
                        if (btn) {
                            btn.textContent = '💾 Guardar';
                            btn.classList.add('ficha-btn-primary');
                            btn.classList.remove('ficha-btn-outline');
                        }
                    }
                }

                // Guardar historia clínica (vive aquí porque ver.php carga por fetch y sus scripts no se ejecutan)
                function guardarHistoria(e) {
                    if (e && e.preventDefault) e.preventDefault();
                    var formContainer = document.getElementById('historiaEdit');
                    if (!formContainer) return false;
                    var form = formContainer.querySelector('form');
                    if (!form) return false;
                    var data = new FormData(form);
                    // Botón único de la cabecera (el form ya no tiene botón submit)
                    var btn = document.getElementById('btnEditarHistoria');
                    if (btn) { btn.disabled = true; btn.textContent = 'Guardando...'; }
                    fetch(form.action, { method: 'POST', body: data })
                        .then(function(r) { return r.text(); })
                        .then(function(txt) {
                            if (txt.trim() === 'ok') {
                                // Actualizar vistas
                                var vAnt = document.getElementById('view_antecedentes');
                                var vDia = document.getElementById('view_diagnostico');
                                var vObs = document.getElementById('view_observaciones');
                                if (vAnt) vAnt.innerHTML = (data.get('antecedentes') || '').replace(/\n/g, '<br>') || 'Sin datos cargados.';
                                if (vDia) vDia.innerHTML = (data.get('diagnostico') || '').replace(/\n/g, '<br>') || '-';
                                if (vObs) vObs.innerHTML = '<p>' + (data.get('observaciones') || '').replace(/\n/g, '<br>') + '</p>';
                                form.classList.remove('editing-mode');
                                var view = document.getElementById('historiaView');
                                if (view) view.style.display = 'block';
                                form.style.display = 'none';
                                if (btn) { btn.disabled = false; btn.textContent = '✏️ Modificar'; btn.classList.add('ficha-btn-outline'); btn.classList.remove('ficha-btn-primary'); }
                                toastFicha('✅ Historia clínica guardada');
                            } else {
                                toastFicha('⚠️ Error al guardar: ' + txt);
                                if (btn) { btn.disabled = false; btn.textContent = ' Guardar'; }
                            }
                        })
                        .catch(function() {
                            toastFicha('⚠️ Error de conexión');
                            if (btn) { btn.disabled = false; btn.textContent = '💾 Guardar'; }
                        });
                    return false;
                }

                function toggleSidebar() {
                    document.getElementById('sidebar').classList.toggle('open');
                    document.getElementById('sidebarOverlay').classList.toggle('open');
                }

                function closeSidebar() {
                    document.getElementById('sidebar').classList.remove('open');
                    document.getElementById('sidebarOverlay').classList.remove('open');
                }
                </script>