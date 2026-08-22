<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';
$miPropioId = (int)($_SESSION['portal_uid'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        try {
            $rolNuevo = $_POST['rol'] ?? 'paciente';
            $base = [
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'dni' => preg_replace('/\D/', '', $_POST['dni']),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'password' => $_POST['password'],
            ];
            if ($rolNuevo === 'paciente') {
                usuario_crear_paciente_admin($base + ['obra_social_id' => (int)($_POST['obra_social_id'] ?? 0)]);
            } elseif ($rolNuevo === 'profesional') {
                usuario_crear_profesional($base + ['area' => $_POST['area'], 'matricula' => trim($_POST['matricula'] ?? '')]);
            } elseif ($rolNuevo === 'admin') {
                usuario_crear_admin($base);
            }
            $mensaje = 'Usuario creado con éxito.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($accion === 'editar') {
        usuario_editar((int)$_POST['id'], [
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'email' => trim($_POST['email']),
            'dni' => preg_replace('/\D/', '', $_POST['dni']),
            'telefono' => trim($_POST['telefono'] ?? ''),
        ]);
        $mensaje = 'Usuario actualizado.';
    } elseif ($accion === 'eliminar') {
        $idBorrar = (int)$_POST['id'];
        if ($idBorrar === $miPropioId) {
            $error = 'No podés eliminar tu propia cuenta mientras estás logueado con ella.';
        } else {
            usuario_eliminar($idBorrar);
            $mensaje = 'Usuario eliminado.';
        }
    } elseif ($accion === 'resetear_password') {
        usuario_cambiar_password((int)$_POST['id'], $_POST['nueva_password']);
        $mensaje = 'Contraseña actualizada.';
    }
}

$editando = null;
if (isset($_GET['editar'])) {
    $editando = usuario_by_id((int)$_GET['editar']);
}

$usuarios = usuarios_todos();
$obrasSociales = obras_sociales_todas();

$portal_titulo = 'Usuarios · Mi Portal';
$portal_activo = 'usuarios';
require __DIR__ . '/../includes/portal_header.php';
?>

<?php if ($mensaje): ?><div class="portal-alert ok"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="portal-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($editando): ?>
<div class="portal-card" style="border:2px solid var(--tm-teal);">
    <h2>Editar: <?= htmlspecialchars($editando['nombre'] . ' ' . $editando['apellido']) ?> (<?= htmlspecialchars($editando['rol']) ?>)</h2>
    <form class="portal-form" method="post">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" value="<?= $editando['id'] ?>">
        <label>Nombre</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($editando['nombre']) ?>">
        <label>Apellido</label>
        <input type="text" name="apellido" required value="<?= htmlspecialchars($editando['apellido']) ?>">
        <label>DNI</label>
        <input type="text" name="dni" required value="<?= htmlspecialchars($editando['dni']) ?>">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($editando['email']) ?>">
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($editando['telefono'] ?? '') ?>">
        <button type="submit" class="portal-btn">Guardar cambios</button>
        <a href="<?= b('/admin/usuarios.php') ?>" class="portal-btn secundario" style="margin-left:8px;">Cancelar</a>
    </form>

    <form class="portal-form" method="post" style="margin-top:20px;border-top:1px solid #e5e5e5;padding-top:16px;">
        <input type="hidden" name="accion" value="resetear_password">
        <input type="hidden" name="id" value="<?= $editando['id'] ?>">
        <label>Restablecer contraseña</label>
        <input type="password" name="nueva_password" required placeholder="Nueva contraseña">
        <button type="submit" class="portal-btn secundario" style="margin-top:10px;">Cambiar contraseña</button>
    </form>
</div>
<?php endif; ?>

<div class="portal-card">
    <h2>Crear usuario</h2>
    <form class="portal-form" method="post" id="formCrearUsuario">
        <input type="hidden" name="accion" value="crear">
        <label>Rol*</label>
        <select name="rol" id="rolNuevo" required onchange="mostrarCamposRol()">
            <option value="paciente">Paciente</option>
            <option value="profesional">Profesional</option>
            <option value="admin">Administrador</option>
        </select>
        <label>Nombre*</label>
        <input type="text" name="nombre" required>
        <label>Apellido*</label>
        <input type="text" name="apellido" required>
        <label>DNI*</label>
        <input type="text" name="dni" required>
        <label>Email*</label>
        <input type="email" name="email" required>
        <label>Teléfono</label>
        <input type="text" name="telefono">

        <div id="camposPaciente">
            <label>Obra social</label>
            <select name="obra_social_id">
                <option value="">Seleccioná una opción</option>
                <?php foreach ($obrasSociales as $os): ?>
                    <option value="<?= $os['id'] ?>"><?= htmlspecialchars($os['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="camposProfesional" style="display:none;">
            <label>Área</label>
            <select name="area">
                <option value="audiologia">Audiología</option>
                <option value="hiperbarica">Medicina hiperbárica</option>
                <option value="nutricion">Nutrición</option>
                <option value="ortopedia">Ortopedia y rehabilitación</option>
                <option value="equipamiento">Equipamiento médico y quirúrgico</option>
            </select>
            <label>Matrícula</label>
            <input type="text" name="matricula">
        </div>

        <label>Contraseña provisoria*</label>
        <input type="password" name="password" required>
        <button type="submit" class="portal-btn">Crear usuario</button>
    </form>
</div>
<script>
function mostrarCamposRol() {
    var rol = document.getElementById('rolNuevo').value;
    document.getElementById('camposPaciente').style.display = rol === 'paciente' ? 'block' : 'none';
    document.getElementById('camposProfesional').style.display = rol === 'profesional' ? 'block' : 'none';
}
</script>

<div class="portal-card">
    <h2>Todos los usuarios (<?= count($usuarios) ?>)</h2>
    <table class="portal-table">
        <thead><tr><th>Nombre</th><th>DNI</th><th>Email</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['dni']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($u['rol'])) ?></td>
                <td>
                    <span class="badge <?= $u['activo'] ? 'aprobado' : 'pendiente' ?>">
                        <?= $u['activo'] ? 'Activo' : 'Pendiente' ?>
                    </span>
                </td>
                <td style="white-space:nowrap;">
                    <a href="<?= b('/admin/usuarios.php?editar=' . $u['id']) ?>" class="portal-btn secundario" style="padding:5px 12px;margin:0;">Editar</a>
                    <?php if ((int)$u['id'] !== $miPropioId): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar a este usuario? Esto borra también su perfil, asignaciones y (si es paciente) su historia clínica/tratamientos/estudios cargados en el Portal. Los turnos ya reservados NO se borran (no dependen de esta cuenta). No se puede deshacer.');">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="portal-btn peligro" style="padding:5px 12px;margin:0;">Eliminar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
