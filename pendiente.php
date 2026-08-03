<?php
require_once __DIR__ . '/includes/auth.php';
$portal_titulo = 'Cuenta pendiente · Mi Portal';
require __DIR__ . '/portal/portal_header.php';
?>
<div class="portal-card" style="max-width:480px;margin:0 auto;text-align:center;">
    <h2>¡Ya recibimos tu registro!</h2>
    <div class="portal-alert info">
        Tu cuenta está pendiente de aprobación por el equipo de TECNOMEDIC.
        Te vamos a avisar por email en cuanto quede activa.
    </div>
    <a href="<?= b('/login.php') ?>" class="portal-btn secundario">Volver a intentar ingresar</a>
</div>
<?php require __DIR__ . '/portal/portal_footer.php'; ?>
