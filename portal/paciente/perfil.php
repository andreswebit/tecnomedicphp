<?php
require_once __DIR__ . '/../../includes/auth.php';
portal_require_role(['paciente']);

$user = portal_current_user();
$perfil = perfil_paciente($user['id']);
$ok = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['nueva_password'])) {
        if (strlen($_POST['nueva_password']) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($_POST['nueva_password'] !== ($_POST['nueva_password2'] ?? '')) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } else {
            usuario_cambiar_password($user['id'], $_POST['nueva_password']);
        }
    }
    if (!$error) {
        perfil_paciente_actualizar($user['id'], [
            'telefono' => trim($_POST['telefono'] ?? ''),
            'obra_social_id' => (int)($_POST['obra_social_id'] ?? 0),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
        ]);
        $ok = true;
        $user = portal_current_user();
        $perfil = perfil_paciente($user['id']);
    }
}

$portal_titulo = 'Mi perfil · Mi Portal';
require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="portal-card" style="max-width:480px;margin:0 auto;">
    <h2>Mis datos</h2>
    <?php if ($ok): ?><div class="portal-alert ok">Datos actualizados.</div><?php endif; ?>
    <?php if ($error): ?><div class="portal-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form class="portal-form" method="post">
        <label>Nombre</label>
        <input type="text" value="<?= htmlspecialchars($user['nombre'] . ' ' . $user['apellido']) ?>" disabled>
        <label>DNI</label>
        <input type="text" value="<?= htmlspecialchars($user['dni']) ?>" disabled>
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
        <label>Obra social</label>
        <select name="obra_social_id">
            <option value="">Seleccioná una opción</option>
            <?php foreach (obras_sociales_todas() as $os): ?>
                <option value="<?= $os['id'] ?>" <?= ($perfil['obra_social_id'] ?? null) == $os['id'] ? 'selected' : '' ?>><?= htmlspecialchars($os['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '') ?>">

        <h2 style="margin-top:24px;">Cambiar contraseña (opcional)</h2>
        <label>Nueva contraseña</label>
        <input type="password" name="nueva_password">
        <label>Repetir nueva contraseña</label>
        <input type="password" name="nueva_password2">

        <button type="submit" class="portal-btn">Guardar cambios</button>
    </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
