<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$portal_titulo = 'Multimedia · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">🎬 Multimedia</div>
            <div class="chip">Gestión de contenido</div>
        </div>
    </div>
    <p style="color:#94a3b8;font-size:13px;margin:0 0 24px;">
        Gestioná todo el contenido visual y editorial del sitio.
    </p>
</div>

<div class="tablero-grid">
    <a href="<?= b('/admin/novedades.php') ?>" class="tablero-tile">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/reloj.ico" alt="" /></div>
        <div class="tablero-title">Novedades</div>
        <div class="tablero-desc">Artículos del blog y noticias</div>
    </a>

    <a href="<?= b('/admin/staff.php') ?>" class="tablero-tile">
        <div class="tablero-icon" >
            
            <img class="icon-image" src="<?= $base ?>/static/img/icons/doctor.ico" alt="" style="height: 4rem; width: 4rem; margin-bottom: 0.5rem;" />
            <img class="icon-image" src="<?= $base ?>/static/img/icons/doctor.ico" alt="" style="height: 3rem; width: 3rem;" />
        </div>
        <div class="tablero-title">Staff Médico</div>
        <div class="tablero-desc">Profesionales y su información</div>
    </a>

    <a href="<?= b('/admin/testimonios.php') ?>" class="tablero-tile">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/mensaje.ico" alt="" /></div>
        <div class="tablero-title">Testimonios</div>
        <div class="tablero-desc">Reseñas y experiencias de pacientes</div>
    </a>

    <a href="<?= b('/admin/productos_destacados.php') ?>" class="tablero-tile">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/estrella.ico" alt="" /></div>
        <div class="tablero-title">Destacados</div>
        <div class="tablero-desc">Productos visibles en el homepage</div>
    </a>

    <a href="<?= b('/admin/config_sitio.php') ?>" class="tablero-tile">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/engranaje.ico" alt="" /></div>
        <div class="tablero-title">Config del sitio</div>
        <div class="tablero-desc">Video hero y otros ajustes</div>
    </a>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
