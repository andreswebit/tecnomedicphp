<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_presupuestos.php';
require_once __DIR__ . '/../includes/email.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($accion === 'subir_pdf') {
        if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
            $error = 'No se pudo subir el archivo.';
        } elseif (strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION)) !== 'pdf') {
            $error = 'El presupuesto debe ser un archivo PDF.';
        } elseif ($_FILES['pdf']['size'] > 10 * 1024 * 1024) {
            $error = 'El archivo supera los 10MB.';
        } else {
            $carpeta = __DIR__ . '/../storage/presupuestos';
            if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
            $nombreArchivo = 'presupuesto_' . $id . '_' . uniqid() . '.pdf';
            $rutaRelativa = 'storage/presupuestos/' . $nombreArchivo;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
                presupuesto_adjuntar_pdf($id, $rutaRelativa);
                $mensaje = 'PDF cargado. Ya podés enviarlo por email.';
            } else {
                $error = 'Error al guardar el archivo en el servidor.';
            }
        }
    } elseif ($accion === 'enviar') {
        $p = presupuesto_get($id);
        if ($p && $p['archivo_pdf']) {
            $rutaAbsoluta = __DIR__ . '/../' . $p['archivo_pdf'];
            try {
                $ok = email_presupuesto_elaborado(trim($p['nombre'] . ' ' . $p['apellido']), $p['email'], $rutaAbsoluta);
                if ($ok) {
                    presupuesto_marcar_enviado($id);
                    $mensaje = 'Presupuesto enviado por email.';
                } else {
                    $error = 'No se pudo enviar el email (revisá el log de errores).';
                }
            } catch (Throwable $e) {
                error_log('Error enviando presupuesto: ' . $e->getMessage());
                $error = 'Error al enviar el email.';
            }
        } else {
            $error = 'Primero subí el PDF del presupuesto.';
        }
    } elseif ($accion === 'eliminar') {
        $borrado = presupuesto_eliminar($id);
        if ($borrado && $borrado['archivo_pdf']) {
            $ruta = __DIR__ . '/../' . $borrado['archivo_pdf'];
            if (is_file($ruta)) unlink($ruta);
        }
        $mensaje = 'Solicitud eliminada.';
    }
}

$presupuestos = presupuestos_listar();
$areasNombres = [
    'audiologia' => 'Audiología', 'hiperbarica' => 'Medicina Hiperbárica',
    'nutricion' => 'Nutrición', 'ortopedia' => 'Ortopedia y Rehabilitación',
    'equipamiento' => 'Equipamiento Médico y Quirúrgico', 'tienda' => 'Tienda', 'otro' => 'Otro',
];

$portal_titulo = 'Presupuestos · Mi Portal';
$portal_activo = 'presupuestos';
require __DIR__ . '/../includes/portal_header.php';
?>

<?php if ($mensaje): ?><div class="portal-alert ok"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="portal-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="portal-card">
    <h2>Solicitudes de presupuesto (<?= count($presupuestos) ?>)</h2>
    <?php if (!$presupuestos): ?>
        <p>Todavía no hay solicitudes.</p>
    <?php else: ?>
        <?php foreach ($presupuestos as $p): ?>
        <div class="portal-card" style="margin-bottom:14px;border-left:4px solid <?= $p['estado'] === 'Pendiente' ? '#f59e0b' : ($p['estado'] === 'Elaborado' ? '#1aa5a5' : '#7aad2e') ?>;">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div>
                    <strong><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></strong>
                    &nbsp;·&nbsp; DNI/CUIT <?= htmlspecialchars($p['dni_cuit']) ?>
                    &nbsp;·&nbsp; <?= htmlspecialchars($p['email']) ?>
                    <?php if ($p['telefono']): ?>&nbsp;·&nbsp; <?= htmlspecialchars($p['telefono']) ?><?php endif; ?>
                </div>
                <span class="badge <?= $p['estado'] === 'Pendiente' ? 'pendiente' : 'aprobado' ?>"><?= htmlspecialchars($p['estado']) ?></span>
            </div>
            <p style="margin:8px 0;color:#64748b;font-size:0.9rem;">
                <strong><?= htmlspecialchars($areasNombres[$p['area']] ?? 'General') ?>:</strong>
                <?= nl2br(htmlspecialchars($p['descripcion'])) ?>
            </p>
            <p style="font-size:0.78rem;color:#94a3b8;">Solicitado: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['creado_en']))) ?></p>

            <div class="btn-actions" style="flex-wrap:wrap;margin-top:10px;">
                <?php if ($p['archivo_pdf']): ?>
                    <a href="<?= b('/admin/presupuestos_descargar.php?id=' . $p['id']) ?>" target="_blank" class="btn-action btn-save" style="flex:none;padding:8px 14px;" data-tooltip="Ver PDF">📄 Ver PDF actual</a>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="accion" value="subir_pdf">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="file" name="pdf" accept=".pdf" required style="font-size:0.82rem;">
                    <button type="submit" class="btn-action btn-mod" style="flex:none;padding:8px 14px;">⬆ Subir PDF</button>
                </form>

                <?php if ($p['archivo_pdf'] && $p['estado'] !== 'Enviado'): ?>
                <form method="post" onsubmit="return confirm('¿Enviar este presupuesto por email a ' + <?= json_encode($p['email']) ?> + '?');">
                    <input type="hidden" name="accion" value="enviar">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-action btn-print-turn" style="flex:none;padding:8px 14px;">✉️ Enviar por email</button>
                </form>
                <?php endif; ?>

                <form method="post" onsubmit="return confirm('¿Eliminar esta solicitud? No se puede deshacer.');">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-action btn-del" style="flex:none;padding:8px 14px;">🗑 Eliminar</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
