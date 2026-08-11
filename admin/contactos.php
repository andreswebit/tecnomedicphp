<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_contacto.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'atendido') {
    contacto_marcar_atendido((int)$_POST['id']);
}

$contactos = contactos_listar();

$portal_titulo = 'Mensajes de contacto · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>
<div class="portal-card">
    <h2>Mensajes de contacto (<?= count($contactos) ?>)</h2>
    <?php if (!$contactos): ?>
        <p>No hay mensajes todavía.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Fecha</th><th>Nombre</th><th>Contacto</th><th>Motivo</th><th>Mensaje</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($contactos as $c): ?>
                <tr style="<?= $c['atendido'] ? 'opacity:.55;' : '' ?>">
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['creado_en']))) ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?><br><small><?= htmlspecialchars($c['telefono'] ?: '-') ?></small></td>
                    <td><?= htmlspecialchars($c['motivo'] ?: '-') ?></td>
                    <td style="max-width:280px;white-space:pre-wrap;"><?= htmlspecialchars($c['mensaje']) ?></td>
                    <td>
                        <?php if (!$c['atendido']): ?>
                        <form method="post">
                            <input type="hidden" name="accion" value="atendido">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="portal-btn" style="padding:6px 12px;margin:0;">Marcar atendido</button>
                        </form>
                        <?php else: ?>
                            <span class="badge aprobado">Atendido</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
