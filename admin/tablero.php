<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$user = portal_current_user();
$portal_titulo = 'Tablero · Mi Portal';
$portal_activo = 'tablero';
require __DIR__ . '/../includes/portal_header.php';
?>
<div class="portal-card">
    <h2>Hola, <?= htmlspecialchars($user['nombre']) ?> <img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/home.png" alt="" /></h2>
    <p style="color:#64748b;">Accesos rápidos según tu rol de administrador.</p>
</div>

<div class="tablero-grid">
    <a class="tablero-tile" href="<?= b('/admin/index.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/calendario2.png" alt="" /></div>
        <div class="tablero-title">Turnos</div>
        <div class="tablero-desc">Ver, confirmar, modificar y cancelar turnos</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/pacientes.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/pacientes.png" alt="" /></div>
        <div class="tablero-title">Pacientes</div>
        <div class="tablero-desc">Aprobar cuentas y ver ficha médica</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/profesionales.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/doctor.png" alt="" /></div>
        <div class="tablero-title">Profesionales</div>
        <div class="tablero-desc">Dar de alta y asignar pacientes</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/usuarios.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/personas.PNG" alt="" /></div>
        <div class="tablero-title">Usuarios</div>
        <div class="tablero-desc">Crear, editar y eliminar cuentas</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/consultar_dni.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/lupa.png" alt="" /></div>
        <div class="tablero-title">Consultar DNI</div>
        <div class="tablero-desc">Ver si una persona ya está en el sistema</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/contactos.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/email.png" alt="" /></div>
        <div class="tablero-title">Mensajes</div>
        <div class="tablero-desc">Consultas del formulario de contacto</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/recursos.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/carpeta.PNG" alt="" /></div>
        <div class="tablero-title">Recursos</div>
        <div class="tablero-desc">Subir formularios y material descargable</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/presupuestos.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/calculadora.PNG" alt="" /></div>
        <div class="tablero-title">Presupuestos</div>
        <div class="tablero-desc">Cargar PDF y enviar por email</div>
    </a>

    <a class="tablero-tile" href="<?= b('/tienda/') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/tienda.png" alt="" /></div>
        <div class="tablero-title">Tienda</div>
        <div class="tablero-desc">Catálogo de productos</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/multimedia.php') ?>">
        <div class="tablero-icon"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/multimedia.png" alt="" /></div>
        <div class="tablero-title">Multimedia</div>
        <div class="tablero-desc">Novedades, staff, testimonios, destacados y config del sitio</div>
    </a>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
