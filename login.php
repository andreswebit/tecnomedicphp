<?php
require_once __DIR__ . '/includes/auth.php';

if (portal_logueado()) {
    switch (portal_rol()) {
        case 'paciente':    header('Location: ' . b('/portal/paciente/dashboard.php')); break;
        case 'profesional': header('Location: ' . b('/portal/profesional/dashboard.php')); break;
        case 'admin':       header('Location: ' . b('/admin/portal_usuarios.php')); break;
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim($_POST['identificador'] ?? '');
    $password = $_POST['password'] ?? '';

    $res = portal_login($identificador, $password);
    if ($res['ok']) {
        switch ($res['user']['rol']) {
            case 'paciente':    header('Location: ' . b('/portal/paciente/dashboard.php')); break;
            case 'profesional': header('Location: ' . b('/portal/profesional/dashboard.php')); break;
            case 'admin':       header('Location: ' . b('/admin/portal_usuarios.php')); break;
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

$portal_titulo = 'Ingresar · Mi Portal';
require __DIR__ . '/includes/portal_header.php';
?>
<div class="portal-card" style="max-width:420px;margin:0 auto;">
    <h2>Ingresar a Mi Portal</h2>
    <?php if ($error): ?>
        <div class="portal-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form class="portal-form" method="post">
        <label>Email o DNI</label>
        <input type="text" name="identificador" required autofocus>
        <label>Contraseña</label>
        <input type="password" name="password" required>
        <button type="submit" class="portal-btn">Ingresar</button>
    </form>
    <p style="margin-top:16px;font-size:0.88rem;">
        ¿Todavía no tenés cuenta? <a href="<?= b('/register.php') ?>">Creá una como paciente</a>
    </p>
</div>
<?php require __DIR__ . '/includes/portal_footer.php'; ?>
