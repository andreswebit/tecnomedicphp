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
<link rel="stylesheet" href="<?= b('/static/portal.css') ?>">
</head>
<body class="portal">
<nav class="portal-nav">
    <span class="brand">TECNOMEDIC · Mi Portal</span>
    <div>
        <?php if ($rol === 'paciente'): ?>
            <a href="<?= b('/portal/paciente/dashboard.php') ?>">Inicio</a>
            <a href="<?= b('/portal/paciente/perfil.php') ?>">Mi perfil</a>
        <?php elseif ($rol === 'profesional'): ?>
            <a href="<?= b('/portal/profesional/dashboard.php') ?>">Mis pacientes</a>
        <?php elseif ($rol === 'admin'): ?>
            <a href="<?= b('/admin/portal_usuarios.php') ?>">Administración</a>
            <a href="<?= b('/admin/consultar_dni.php') ?>">Consultar DNI</a>
        <?php endif; ?>
        <?php if ($nombreSesion): ?>
            <span style="margin-left:18px; opacity:0.8;">👤 <?= htmlspecialchars($nombreSesion) ?></span>
            <a href="<?= b('/logout.php') ?>">Salir</a>
        <?php endif; ?>
    </div>
</nav>
<div class="portal-container">
