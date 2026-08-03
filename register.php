<?php
require_once __DIR__ . '/includes/auth.php';

if (portal_logueado()) {
    header('Location: ' . b('/login.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $dni       = preg_replace('/\D/', '', $_POST['dni'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$nombre || !$apellido || !$email || !$dni || !$password) {
        $error = 'Completá todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            usuario_crear_paciente([
                'nombre' => $nombre, 'apellido' => $apellido, 'email' => $email,
                'dni' => $dni, 'telefono' => $telefono, 'password' => $password,
                'obra_social_id' => (int)($_POST['obra_social_id'] ?? 0),
            ]);
            header('Location: ' . b('/pendiente.php'));
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$portal_titulo = 'Crear cuenta · Mi Portal';
require __DIR__ . '/includes/portal_header.php';
?>
<div class="portal-card" style="max-width:480px;margin:0 auto;">
    <h2>Crear mi cuenta de paciente</h2>
    <?php if ($error): ?>
        <div class="portal-alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form class="portal-form" method="post">
        <label>Nombre*</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        <label>Apellido*</label>
        <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
        <label>DNI*</label>
        <input type="text" name="dni" required value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
        <label>Email*</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
        <label>Obra social*</label>
        <select name="obra_social_id" required>
            <option value="">Seleccioná una opción</option>
            <?php foreach (obras_sociales_todas() as $os): ?>
                <option value="<?= $os['id'] ?>"><?= htmlspecialchars($os['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Contraseña*</label>
        <input type="password" name="password" required>
        <label>Repetir contraseña*</label>
        <input type="password" name="password2" required>
        <button type="submit" class="portal-btn">Crear cuenta</button>
    </form>
    <p style="margin-top:16px;font-size:0.88rem;">
        ¿Ya tenés cuenta? <a href="<?= b('/login.php') ?>">Iniciá sesión</a>
    </p>
</div>
<?php require __DIR__ . '/includes/portal_footer.php'; ?>
