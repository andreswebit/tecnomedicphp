<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_recursos.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'No se pudo subir el archivo.';
        } else {
            $permitidas = ['pdf' => 'PDF', 'jpg' => 'Imagen', 'jpeg' => 'Imagen', 'png' => 'Imagen', 'doc' => 'Word', 'docx' => 'Word'];
            $nombreOriginal = $_FILES['archivo']['name'];
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            if (!isset($permitidas[$ext])) {
                $error = 'Tipo de archivo no permitido (solo PDF, JPG, PNG, DOC/DOCX).';
            } elseif ($_FILES['archivo']['size'] > 15 * 1024 * 1024) {
                $error = 'El archivo supera los 15MB.';
            } else {
                $carpeta = __DIR__ . '/../storage/recursos';
                if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
                $nombreArchivo = uniqid('recurso_') . '.' . $ext;
                $rutaRelativa = 'storage/recursos/' . $nombreArchivo;

                if (move_uploaded_file($_FILES['archivo']['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
                    recurso_crear([
                        'titulo' => trim($_POST['titulo']),
                        'descripcion' => trim($_POST['descripcion'] ?? ''),
                        'area' => $_POST['area'] ?: null,
                        'archivo_nombre' => $nombreOriginal,
                        'archivo_ruta' => $rutaRelativa,
                        'tipo' => $permitidas[$ext],
                        'publico' => isset($_POST['publico']) ? 1 : 0,
                        'subido_por' => (int)($_SESSION['portal_uid'] ?? 0),
                    ]);
                    $mensaje = 'Recurso subido con éxito.';
                } else {
                    $error = 'Error al guardar el archivo en el servidor.';
                }
            }
        }
    } elseif ($accion === 'eliminar') {
        $borrado = recurso_eliminar((int)$_POST['id']);
        if ($borrado) {
            $ruta = __DIR__ . '/../' . $borrado['archivo_ruta'];
            if (is_file($ruta)) unlink($ruta);
            $mensaje = 'Recurso eliminado.';
        }
    }
}

$recursos = recursos_listar_todos();
$portal_titulo = 'Recursos · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>

<?php if ($mensaje): ?><div class="portal-alert ok"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="portal-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="portal-card">
    <h2>Subir un recurso</h2>
    <form class="portal-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="crear">
        <label>Título*</label>
        <input type="text" name="titulo" required placeholder="Ej: Ficha de admisión">
        <label>Descripción</label>
        <input type="text" name="descripcion" placeholder="Opcional">
        <label>Área (opcional, dejar vacío si es general)</label>
        <select name="area">
            <option value="">— General —</option>
            <option value="audiologia">Audiología</option>
            <option value="hiperbarica">Medicina hiperbárica</option>
            <option value="nutricion">Nutrición</option>
            <option value="ortopedia">Ortopedia y rehabilitación</option>
            <option value="equipamiento">Equipamiento médico y quirúrgico</option>
        </select>
        <label>Archivo* (PDF, JPG, PNG, DOC/DOCX — máx. 15MB)</label>
        <input type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        <label style="display:flex;align-items:center;gap:8px;margin-top:14px;">
            <input type="checkbox" name="publico" value="1" style="width:auto;margin:0;">
            Visible públicamente en el sitio (sin necesidad de login)
        </label>
        <button type="submit" class="portal-btn" style="margin-top:10px;">Subir recurso</button>
    </form>
</div>

<div class="portal-card">
    <h2>Recursos cargados (<?= count($recursos) ?>)</h2>
    <?php if (!$recursos): ?>
        <p>Todavía no hay recursos cargados.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Título</th><th>Área</th><th>Tipo</th><th>Visibilidad</th><th>Subido por</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recursos as $r): ?>
                <tr>
                    <td>
                        <a href="<?= b('/recursos_descargar.php?id=' . $r['id']) ?>" target="_blank"><?= htmlspecialchars($r['titulo']) ?></a>
                        <?php if ($r['descripcion']): ?><br><small style="color:#64748b;"><?= htmlspecialchars($r['descripcion']) ?></small><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['area'] ?: 'General') ?></td>
                    <td><?= htmlspecialchars($r['tipo']) ?></td>
                    <td><span class="badge <?= $r['publico'] ? 'aprobado' : 'pendiente' ?>"><?= $r['publico'] ? 'Público' : 'Solo Portal' ?></span></td>
                    <td><?= htmlspecialchars($r['subido_por_apellido'] . ', ' . $r['subido_por_nombre']) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('¿Eliminar este recurso?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="portal-btn peligro" style="padding:5px 12px;margin:0;">Borrar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
