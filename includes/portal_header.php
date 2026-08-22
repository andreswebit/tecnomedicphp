<?php
// Incluir DESPUÉS de require auth.php y de resolver la lógica de la página.
// Variable opcional $portal_titulo para el <title>.
// Variable opcional $portal_activo para resaltar el item activo del sidebar
// (valores según rol, ver los value= de cada <a> más abajo).
$rol = portal_rol();
$nombreSesion = $_SESSION['portal_nombre'] ?? '';
$activo = $portal_activo ?? '';

$etiquetaRol = [
    'paciente'    => 'Portal Paciente',
    'profesional' => 'Portal Profesional',
    'admin'       => 'Panel Admin',
][$rol] ?? 'Mi Portal';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($portal_titulo ?? 'Mi Portal · TECNOMEDIC') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= b('/static/tecnomedic.css') ?>">
<link rel="stylesheet" href="<?= b('/portal/css/portal.css') ?>">
</head>
<body class="portal">

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <div class="wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <a href="<?= HOME_URL ?>/">
                    <img src="<?= b('/static/img/tecno-logo.jpeg') ?>" alt="TECNOMEDIC" class="logo-img">
                </a>
            </div>
            <div class="sidebar-sub"><?= htmlspecialchars($etiquetaRol) ?></div>

            <div class="nav-label">Menú</div>
            <?php if ($rol === 'paciente'): ?>
                <a href="<?= b('/portal/paciente/dashboard.php') ?>" class="nav-item <?= $activo === 'inicio' ? 'active' : '' ?>"><span>🏠</span><span>Inicio</span></a>
                <a href="<?= b('/portal/paciente/perfil.php') ?>" class="nav-item <?= $activo === 'perfil' ? 'active' : '' ?>"><span>👤</span><span>Mi perfil</span></a>
                <a href="<?= b('/portal/recursos.php') ?>" class="nav-item <?= $activo === 'recursos' ? 'active' : '' ?>"><span>📚</span><span>Recursos</span></a>

            <?php elseif ($rol === 'profesional'): ?>
                <a href="<?= b('/portal/profesional/dashboard.php') ?>" class="nav-item <?= $activo === 'pacientes' ? 'active' : '' ?>"><span>🧑‍⚕️</span><span>Mis pacientes</span></a>
                <a href="<?= b('/portal/recursos.php') ?>" class="nav-item <?= $activo === 'recursos' ? 'active' : '' ?>"><span>📚</span><span>Recursos</span></a>

            <?php elseif ($rol === 'admin'): ?>
                <a href="<?= b('/admin/tablero.php') ?>" class="nav-item <?= $activo === 'tablero' ? 'active' : '' ?>"><span>🧭</span><span>Tablero</span></a>
                <a href="<?= b('/admin/index.php') ?>" class="nav-item"><span>📅</span><span>Turnos</span></a>
                <a href="<?= b('/admin/pacientes.php') ?>" class="nav-item <?= $activo === 'pacientes' ? 'active' : '' ?>"><span>🧑‍⚕️</span><span>Pacientes</span></a>
                <a href="<?= b('/admin/profesionales.php') ?>" class="nav-item <?= $activo === 'profesionales' ? 'active' : '' ?>"><span>👨‍⚕️</span><span>Profesionales</span></a>
                <a href="<?= b('/admin/usuarios.php') ?>" class="nav-item <?= $activo === 'usuarios' ? 'active' : '' ?>"><span>🧑‍💼</span><span>Usuarios</span></a>
                <a href="<?= b('/admin/recursos.php') ?>" class="nav-item <?= $activo === 'recursos' ? 'active' : '' ?>"><span>📚</span><span>Recursos</span></a>
                <a href="<?= b('/admin/contactos.php') ?>" class="nav-item <?= $activo === 'contactos' ? 'active' : '' ?>"><span>✉️</span><span>Mensajes</span></a>
                <a href="<?= b('/admin/consultar_dni.php') ?>" class="nav-item <?= $activo === 'dni' ? 'active' : '' ?>"><span>🔎</span><span>Consultar DNI</span></a>
            <?php endif; ?>

            <div class="sidebar-footer">
                <div style="margin-bottom:12px;">
                    <span class="status-dot"></span>
                    <span class="status-text"><?= htmlspecialchars($nombreSesion) ?></span>
                </div>
                <a href="<?= b('/logout.php') ?>"
                    style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--g100);text-decoration:none;transition:color .2s;"
                    onmouseover="this.style.color='var(--amber)'" onmouseout="this.style.color='var(--g100)'">
                    <span>🚪</span><span>Cerrar sesión</span>
                </a>
            </div>
        </aside>

        <div class="main">
            <div class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger-btn" onclick="toggleSidebar()">
                        <span></span><span></span><span></span>
                    </button>
                    <div class="page-title">Mi <span>Portal</span></div>
                </div>
            </div>
            <div class="portal-container" style="padding:0;max-width:none;margin:0;">

<!-- Modal Ficha Médica (Fase D) -->
<div class="ficha-modal-overlay" id="fichaModalOverlay" onclick="if(event.target===this) cerrarFicha()">
    <div class="ficha-modal">
        <div class="ficha-modal-header">
            <strong>Ficha médica</strong>
            <button type="button" class="ficha-modal-close" onclick="cerrarFicha()">✕</button>
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
        .then(function(r) { return r.text(); })
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

function bindFichaForms(pacienteId) {
    document.querySelectorAll('#fichaModalBody form[data-ficha-form]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type=submit]');
            if (btn) { btn.disabled = true; btn.textContent = 'Guardando…'; }
            fetch(form.action, { method: 'POST', body: new FormData(form) })
                .then(function(r) { return r.text(); })
                .then(function(txt) {
                    if (txt.trim() !== 'ok') alert('No se pudo guardar: ' + txt);
                    abrirFicha(pacienteId);
                })
                .catch(function() { alert('Error de conexión al guardar.'); });
        });
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarFicha();
});

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>
