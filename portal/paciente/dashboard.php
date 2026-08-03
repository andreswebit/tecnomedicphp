<?php
require_once __DIR__ . '/../../includes/auth.php';
portal_require_role(['paciente']);

$user = portal_current_user();
$perfil = perfil_paciente($user['id']);
$turnos = turnos_de_paciente($user['dni']);

$portal_titulo = 'Mi Portal · ' . $user['nombre'];
require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="portal-card">
    <h2>Hola, <?= htmlspecialchars($user['nombre']) ?> 👋</h2>
    <p><strong>DNI:</strong> <?= htmlspecialchars($user['dni']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($user['telefono'] ?: '-') ?></p>
    <p><strong>Obra social:</strong> <?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?></p>
    <a href="<?= b('/portal/paciente/perfil.php') ?>" class="portal-btn secundario">Editar mis datos</a>
</div>

<div class="portal-card">
    <h2>Mis turnos</h2>
    <?php if (!$turnos): ?>
        <p>No tenés turnos registrados todavía.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($turnos as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['fecha']) ?></td>
                    <td><?= htmlspecialchars($t['hora']) ?></td>
                    <td><?= htmlspecialchars($t['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
