<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'aprobar') {
        usuario_aprobar((int)$_POST['id']);
        $mensaje = 'Paciente aprobado.';
    } elseif ($accion === 'rechazar') {
        usuario_rechazar((int)$_POST['id']);
        $mensaje = 'Solicitud rechazada.';
    } elseif ($accion === 'crear_profesional') {
        try {
            usuario_crear_profesional([
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'dni' => preg_replace('/\D/', '', $_POST['dni']),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'password' => $_POST['password'],
                'area' => $_POST['area'],
                'matricula' => trim($_POST['matricula'] ?? ''),
            ]);
            $mensaje = 'Profesional creado con éxito.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($accion === 'asignar') {
        asignar_paciente_profesional((int)$_POST['paciente_id'], (int)$_POST['profesional_id'], $_POST['area']);
        $mensaje = 'Asignación creada.';
    } elseif ($accion === 'desasignar') {
        desasignar((int)$_POST['asignacion_id']);
        $mensaje = 'Asignación quitada.';
    }
}

$pendientes = usuarios_pendientes();
$profesionales = profesionales_todos();
$pacientes = pacientes_todos_activos();
$asignaciones = asignaciones_todas();

$portal_titulo = 'Administración · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>

<?php if ($mensaje): ?><div class="portal-alert ok"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="portal-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="portal-card">
    <h2>Pacientes pendientes de aprobación (<?= count($pendientes) ?>)</h2>
    <?php if (!$pendientes): ?>
        <p>No hay solicitudes pendientes.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Nombre</th><th>DNI</th><th>Email</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pendientes as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_alta']) ?></td>
                    <td style="white-space:nowrap;">
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="accion" value="aprobar">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="portal-btn" style="padding:6px 14px;margin:0;">Aprobar</button>
                        </form>
                        <form method="post" style="display:inline;" onsubmit="return confirm('¿Rechazar y borrar esta solicitud?');">
                            <input type="hidden" name="accion" value="rechazar">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="portal-btn peligro" style="padding:6px 14px;margin:0;">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="portal-card">
    <h2>Dar de alta un profesional</h2>
    <form class="portal-form" method="post">
        <input type="hidden" name="accion" value="crear_profesional">
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
        <label>Área*</label>
        <select name="area" required>
            <option value="audiologia">Audiología</option>
            <option value="hiperbarica">Medicina hiperbárica</option>
            <option value="nutricion">Nutrición</option>
            <option value="ortopedia">Ortopedia y rehabilitación</option>
            <option value="equipamiento">Equipamiento médico y quirúrgico</option>
        </select>
        <label>Matrícula</label>
        <input type="text" name="matricula">
        <label>Contraseña provisoria*</label>
        <input type="password" name="password" required>
        <button type="submit" class="portal-btn">Crear profesional</button>
    </form>
</div>

<div class="portal-card">
    <h2>Asignar paciente a profesional</h2>
    <form class="portal-form" method="post">
        <input type="hidden" name="accion" value="asignar">
        <label>Paciente</label>
        <select name="paciente_id" required>
            <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre'] . ' (DNI ' . $p['dni'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
        <label>Profesional</label>
        <select name="profesional_id" required>
            <?php foreach ($profesionales as $pr): ?>
                <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['apellido'] . ', ' . $pr['nombre'] . ' — ' . $pr['area']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Área de la asignación</label>
        <select name="area" required>
            <option value="audiologia">Audiología</option>
            <option value="hiperbarica">Medicina hiperbárica</option>
            <option value="nutricion">Nutrición</option>
            <option value="ortopedia">Ortopedia y rehabilitación</option>
            <option value="equipamiento">Equipamiento médico y quirúrgico</option>
        </select>
        <button type="submit" class="portal-btn">Asignar</button>
    </form>
</div>

<div class="portal-card">
    <h2>Asignaciones activas (<?= count($asignaciones) ?>)</h2>
    <?php if (!$asignaciones): ?>
        <p>No hay asignaciones activas todavía.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Paciente</th><th>Profesional</th><th>Área</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($asignaciones as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['paciente_apellido'] . ', ' . $a['paciente_nombre']) ?></td>
                    <td><?= htmlspecialchars($a['profesional_apellido'] . ', ' . $a['profesional_nombre']) ?></td>
                    <td><?= htmlspecialchars($a['area']) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('¿Quitar esta asignación?');">
                            <input type="hidden" name="accion" value="desasignar">
                            <input type="hidden" name="asignacion_id" value="<?= $a['id'] ?>">
                            <button type="submit" class="portal-btn peligro" style="padding:4px 12px;margin:0;">Quitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?> 
