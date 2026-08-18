<?php
// Incluir DESPUÉS de require auth.php y de resolver la lógica de la página.
// Variable opcional $portal_titulo para el <title>.
$rol = portal_rol();
$nombreSesion = $_SESSION['portal_nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($portal_titulo ?? 'Mi Portal · TECNOMEDIC') ?></title>
<link rel="stylesheet" href="<?= b('/portal/css/portal.css') ?>">
</head>
<body class="portal">
<nav class="portal-nav">
    <span class="brand">TECNOMEDIC · Mi Portal</span>
    <div>
        <?php if ($rol === 'paciente'): ?>
            <a href="<?= b('/portal/paciente/dashboard.php') ?>">Inicio</a>
            <a href="<?= b('/portal/paciente/perfil.php') ?>">Mi perfil</a>
            <a href="<?= b('/portal/recursos.php') ?>">Recursos</a>
        <?php elseif ($rol === 'profesional'): ?>
            <a href="<?= b('/portal/profesional/dashboard.php') ?>">Mis pacientes</a>
            <a href="<?= b('/portal/recursos.php') ?>">Recursos</a>
        <?php elseif ($rol === 'admin'): ?>
            <a href="<?= b('/admin/tablero.php') ?>">Tablero</a>
            <a href="<?= b('/admin/index.php') ?>">Turnos</a>
            <a href="<?= b('/admin/pacientes.php') ?>">Pacientes</a>
            <a href="<?= b('/admin/profesionales.php') ?>">Profesionales</a>
            <a href="<?= b('/admin/usuarios.php') ?>">Usuarios</a>
            <a href="<?= b('/admin/recursos.php') ?>">Recursos</a>
            <a href="<?= b('/admin/consultar_dni.php') ?>">Consultar DNI</a>
        <?php endif; ?>
        <?php if ($nombreSesion): ?>
            <span style="margin-left:18px; opacity:0.8;">👤 <?= htmlspecialchars($nombreSesion) ?></span>
            <a href="<?= b('/logout.php') ?>">Salir</a>
        <?php endif; ?>
    </div>
</nav>
<div class="portal-container">

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
                    abrirFicha(pacienteId); // recarga el contenido del modal
                })
                .catch(function() { alert('Error de conexión al guardar.'); });
        });
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarFicha();
});
</script>
