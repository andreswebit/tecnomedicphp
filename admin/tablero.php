<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$user = portal_current_user();
$portal_titulo = 'Tablero · Mi Portal';
$portal_activo = 'tablero';
require __DIR__ . '/../includes/portal_header.php';

?>
<div class="portal-card">
    <h2>
        <img class="icon-image" src="<?= $base ?>/static/img/icons/home.ico" alt="" />
        <span style="margin-left: 40px; text-transform: capitalize;">Hola  ,  <?= htmlspecialchars($user['nombre']) ?></span>
    </h2>
    
    <p style="color:#64748b; margin-top: 10px;">Accesos rápidos según tu rol .</p>
</div>

<div class="tablero-grid">
    <a class="tablero-tile" href="<?= b('/admin/index.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/calendario2.ico" alt="" />
        </div>
        <div class="tablero-title" data-tooltip="Ver, confirmar, modificar y cancelar turnos">Turnos</div> 
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/pacientes.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/pacientes.ico" alt="" />
        </div>
        <div class="tablero-title" data-tooltip="Aprobar cuentas y ver ficha médica">Pacientes</div>
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/profesionales.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/doctor.ico" alt="" /></div>
        <div class="tablero-title" data-tooltip="Dar de alta y asignar pacientes">Profesionales</div>
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/usuarios.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/personas2.ico" alt="" />
        </div>
        <div class="tablero-title" data-tooltip="Crear, editar y eliminar cuentas">Usuarios</div>
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/consultar_dni.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/lupa.ico" alt="" /></div>
        <div class="tablero-title" data-tooltip="Ver si una persona ya está en el sistema">Consultar DNI</div>
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/recursos.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/carpeta.ico" alt="" />
        </div>
        <div class="tablero-title" data-tooltip="Subir formularios y material descargable">Recursos</div>
        <div class="tablero-desc"></div>
    </a>

    
    <a class="tablero-tile" href="<?= b('/admin/mensajes.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/mensaje.ico" alt="" /></div>
        <div class="tablero-title" data-tooltip="WhatsApp - Email -(presupuesto)">Mensajes</div>
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/tienda/') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/tienda.ico" alt="" /></div>
        <div class="tablero-title" data-tooltip="Catálogo de productos">Tienda</div>
        <div class="tablero-desc"></div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/multimedia.php') ?>">
        <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/multimedia.ico" alt="" />
        </div>
        <div class="tablero-title" data-tooltip="Novedades, staff, testimonios, destacados y config del sitio">Multimedia</div>
        <div class="tablero-desc"></div>
    </a>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>