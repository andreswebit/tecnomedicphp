<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$portal_titulo = 'Mensajes · Mi Portal';
$portal_activo = 'mensajes';
require __DIR__ . '/../includes/portal_header.php';

?>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div class="page-title"> Mensajes</div>
            <div class="chip">Presupuestos · WhatsApp · Email</div>
        </div>
    </div>
    <p style="color:#94a3b8;font-size:13px;margin:0 0 24px;">
        Accesos directos a la gestión de presupuestos y al envío por WhatsApp o Email.
    </p>
</div>

<div class="tablero-grid">
    <a class="tablero-tile" href="<?= b('/admin/presupuestos.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/calculadora.ico" alt="" /></div>
        <div class="tablero-title" data-tooltip="Ver/administrar presupuestos">Presupuesto</div>
        <div class="tablero-desc">Listar y administrar PDFs</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/presupuestos.php#whatsapp') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/whatsapp.ico" alt=""  /></div>
        <div class="tablero-title" data-tooltip="Enviar presupuestos por WhatsApp">WhatsApp</div>
        <div class="tablero-desc">Enviar por WhatsApp</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/presupuestos.php#email') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/email.ico" alt="" /></div>
        <div class="tablero-title" data-tooltip="Enviar presupuestos por Email">Email</div>
        <div class="tablero-desc">Enviar por Email</div>
    </a>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
