<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$user = portal_current_user();
$portal_titulo = 'Tablero · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>
<div class="portal-card">
    <h2>Hola, <?= htmlspecialchars($user['nombre']) ?> 👋</h2>
    <p style="color:#64748b;">Accesos rápidos según tu rol de administrador.</p>
</div>

<div class="tablero-grid">
    <a class="tablero-tile" href="<?= b('/admin/index.php') ?>">
        <div class="tablero-icon">📅</div>
        <div class="tablero-title">Turnos</div>
        <div class="tablero-desc">Ver, confirmar, modificar y cancelar turnos</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/pacientes.php') ?>">
        <div class="tablero-icon">🧑‍⚕️</div>
        <div class="tablero-title">Pacientes</div>
        <div class="tablero-desc">Aprobar cuentas y ver ficha médica</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/profesionales.php') ?>">
        <div class="tablero-icon">👨‍⚕️</div>
        <div class="tablero-title">Profesionales</div>
        <div class="tablero-desc">Dar de alta y asignar pacientes</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/usuarios.php') ?>">
        <div class="tablero-icon">🧑‍💼</div>
        <div class="tablero-title">Usuarios</div>
        <div class="tablero-desc">Crear, editar y eliminar cuentas</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/consultar_dni.php') ?>">
        <div class="tablero-icon">🔎</div>
        <div class="tablero-title">Consultar DNI</div>
        <div class="tablero-desc">Ver si una persona ya está en el sistema</div>
    </a>

    <a class="tablero-tile" href="<?= b('/admin/contactos.php') ?>">
        <div class="tablero-icon">✉️</div>
        <div class="tablero-title">Mensajes</div>
        <div class="tablero-desc">Consultas del formulario de contacto</div>
    </a>

    <a class="tablero-tile disabled" href="#" onclick="return false;">
        <div class="tablero-icon">📄</div>
        <div class="tablero-title">Presupuestos</div>
        <div class="tablero-desc">Próximamente</div>
    </a>

    <a class="tablero-tile" href="<?= b('/tienda/') ?>">
        <div class="tablero-icon">🛒</div>
        <div class="tablero-title">Tienda</div>
        <div class="tablero-desc">Catálogo de productos</div>
    </a>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
