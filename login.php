<?php
require_once __DIR__ . '/includes/auth.php';

$base = BASE_URL;

if (portal_logueado()) {
    switch (portal_rol()) {
        case 'paciente':    header('Location: ' . b('/portal/paciente/dashboard.php')); break;
        case 'profesional': header('Location: ' . b('/portal/profesional/dashboard.php')); break;
        case 'admin':       header('Location: ' . b('/admin/tablero.php')); break;
    }
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim($_POST['identificador'] ?? '');
    $password = $_POST['password'] ?? '';

    $res = portal_login($identificador, $password);
    if ($res['ok']) {
        switch ($res['user']['rol']) {
            case 'paciente':    header('Location: ' . b('/portal/paciente/dashboard.php')); break;
            case 'profesional': header('Location: ' . b('/portal/profesional/dashboard.php')); break;
            case 'admin':       header('Location: ' . b('/admin/tablero.php')); break;
        }
        exit;
    }
    switch ($res['motivo']) {
        case 'pendiente':
            header('Location: ' . b('/pendiente.php'));
            exit;
        case 'password_incorrecta':
        case 'no_existe':
            $error = 'Email/DNI o contraseña incorrectos.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Portal – TECNOMEDIC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?=b('/static/tecnomedic.css') ?>">
</head>

<body class="login-box">

    <div class="login-square">
        <div class="square"></div>
        <div class="square"></div>
        <div class="square"></div>
    </div>

    <div class="login-box">

        <div class="login-logo">
            <img src="<?= b('/static/img/tecno-logo.jpeg') ?>" alt="TECNOMEDIC">
        </div>

        <div class="login-card">
            <div class="login-title">Mi <span>Portal</span></div>
            <div class="login-subtitle">Turnos · Pacientes · Profesionales · Administración</div>

            <?php if ($error): ?>
            <div class="login-error">🔒 <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= b('/login.php') ?>">
                <div class="login-group">
                    <div class="login-label">Email o DNI</div>
                    <div class="login-input-wrap">
                        <span class="login-input-icon">👤</span>
                        <input class="login-input" type="text" name="identificador" placeholder="Ingresá tu email o DNI"
                            required autofocus>
                    </div>
                </div>

                <div class="login-group">
                    <div class="login-label">Contraseña</div>
                    <div class="login-input-wrap">
                        <span class="login-input-icon">🔑</span>
                        <input class="login-input" type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="login-divider"></div>

                <button type="submit" class="login-btn">
                    Ingresar →
                </button>
                <div class="login-divider"></div>
                <button type="button" class="login-btn" onclick="window.location.href='<?= b('/register.php') ?>'">
                    📝 &nbsp; Crear cuenta de paciente
                </button>
                <button type="button" class="login-btn" onclick="window.location.href='<?= HOME_URL ?>/'">
                    ⮌ &nbsp; Volver a Inicio
                </button>
            </form>
        </div>
        <p style="margin-top:16px;font-size:0.88rem;">
            ¿Todavía no tenés cuenta? <a href="<?= b('/register.php') ?>">Creá una como paciente</a>
        </p>
        <div class="login-footer">© <?= date('Y') ?> TECNOMEDIC · Acceso restringido</div>
    </div>

</body>

</html>