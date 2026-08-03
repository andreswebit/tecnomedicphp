<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
iniciar_sesion_php();

$base  = BASE_URL;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
        $_SESSION['tm_logged'] = true;
        header('Location: ' . $base . '/admin/index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Admin – TECNOMEDIC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/static/tecnomedic.css">
</head>

<body class="login-box">

<div class="login-square">
    <div class="square"></div><div class="square"></div><div class="square"></div>
</div>

<div class="login-box">

    <div class="login-logo">
        <img src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC">
    </div>

    <div class="login-card">
        <div class="login-title">Acceso <span>Admin</span></div>
        <div class="login-subtitle">Panel de gestión de turnos</div>

        <?php if ($error): ?>
        <div class="login-error">🔒 <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $base ?>/admin/login.php">
            <div class="login-group">
                <div class="login-label">Usuario</div>
                <div class="login-input-wrap">
                    <span class="login-input-icon">👤</span>
                    <input class="login-input" type="text" name="usuario"
                            placeholder="Ingresá tu usuario" required autofocus>
                </div>
            </div>

            <div class="login-group">
                <div class="login-label">Contraseña</div>
                <div class="login-input-wrap">
                    <span class="login-input-icon">🔑</span>
                    <input class="login-input" type="password" name="password"
                            placeholder="••••••••" required>
                </div>
            </div>

            <div class="login-divider"></div>

            <button type="submit" class="login-btn">
                Ingresar al panel →
            </button>
            <div class="login-divider"></div>
            <button type="button" class="login-btn" onclick="window.location.href='<?= HOME_URL ?>/'">
                ⮌ &nbsp; Volver a Inicio
            </button>
        </form>
    </div>

    <div class="login-footer">© <?= date('Y') ?> TECNOMEDIC · Acceso restringido</div>
</div>

</body>
</html>
