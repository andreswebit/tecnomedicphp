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
    }
}

$pendientes = usuarios_pendientes();
$pacientes = pacientes_todos_activos();

$portal_titulo = 'Pacientes · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>

<?php if ($mensaje): ?><div class="portal-alert ok"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="portal-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="portal-card">
    <h2>Pendientes de aprobación (<?= count($pendientes) ?>)</h2>
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
    <h2>Pacientes activos (<?= count($pacientes) ?>)</h2>
    <?php if (!$pacientes): ?>
        <p>Todavía no hay pacientes activos.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Nombre</th><th>DNI</th><th>Email</th><th>Teléfono</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?: '-') ?></td>
                    <td>
                        <button type="button" class="portal-btn secundario" style="padding:5px 12px;margin:0;" onclick="abrirFicha(<?= $p['id'] ?>)">📋 Ver ficha médica</button>
                        <a href="<?= b('/portal/nutricion/registro.php?paciente_id=' . $p['id']) ?>" class="portal-btn secundario" style="padding:5px 12px;margin:0;">🥗 Nutrición</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
