<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_config.php';
portal_require_role(['admin']);

$mensaje = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_url = trim($_POST['hero_video_url'] ?? '');
    if ($video_url) {
        config_set('hero_video_url', $video_url);
        $mensaje = 'Video del hero actualizado.';
    } else {
        $error = 'La URL no puede estar vacía.';
    }
}

$hero_video = @config_get('hero_video_url') ?: '';

$portal_titulo = 'Configuración · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Video hero</div>
        <div class="stat-value" style="font-size:20px;word-break:break-all;">
            <?= $hero_video ? '<span style="color:#10b981;">✔</span>' : '<span style="color:#f59e0b;">—</span>' ?>
        </div>
        <div class="stat-sub"><?= $hero_video ? 'Configurado' : 'Sin configurar' ?></div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Config del sitio</div>
            <div class="chip"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/multimedia.png" alt="" /> Multimedia</div>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><img class="tm-thumb"
                        src="<?= $base ?>/static/img/icons/multimedia.png" alt="" /> Video del Hero</div>
    </div>
    <div style="padding:20px 24px;">

        <?php if ($mensaje): ?>
        <div style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;display:flex;align-items:center;gap:10px;">
            ✅ <?= htmlspecialchars($mensaje) ?>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div style="background:#c94f4f;color:#fff;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <p style="color:#94a3b8;margin:0 0 20px;font-size:13px;">
            URL del video de YouTube que se muestra como fondo en la sección hero de la página principal.
        </p>

        <form method="post">
            <div style="display:grid;gap:16px;max-width:640px;">
                <div>
                    <label style="display:block;color:#ccc;font-weight:600;margin-bottom:8px;font-size:13px;">URL del video (YouTube embed)</label>
                    <input type="text" name="hero_video_url" required
                        placeholder="https://www.youtube.com/embed/ID_DEL_VIDEO"
                        value="<?= htmlspecialchars($hero_video) ?>"
                        style="width:100%;background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;font-family:'Montserrat',sans-serif;font-size:14px;color:#fff;outline:none;box-sizing:border-box;">
                    <p style="color:#64748b;font-size:12px;margin:6px 0 0;">
                        Solo URLs de YouTube en formato embed. Ej: <code style="color:#7aad2e;">https://www.youtube.com/embed/IqcZl86vtXU</code>
                    </p>
                </div>

                <?php if ($hero_video): ?>
                <div>
                    <p style="color:#ccc;font-weight:600;margin-bottom:8px;font-size:13px;">Vista previa:</p>
                    <div style="position:relative;width:320px;max-width:100%;height:180px;overflow:hidden;border-radius:10px;background:#000;">
                        <iframe width="320" height="180"
                            src="<?= htmlspecialchars($hero_video) ?>?autoplay=0&mute=1&controls=1&showinfo=0"
                            frameborder="0" allowfullscreen
                            style="position:absolute;top:0;left:0;opacity:0.6;"></iframe>
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <button type="submit" class="btn-edit-save" style="padding:12px 32px;">
                        💾 Guardar video
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Otros ajustes placeholder -->
<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">⚙️ Otros ajustes</div>
    </div>
    <div style="padding:20px 24px;">
        <p style="color:#64748b;font-size:13px;margin:0 0 16px;">Más opciones de configuración se irán agregando aquí.</p>
        <ul style="color:#64748b;font-size:13px;padding-left:20px;line-height:2;margin:0;">
            <li>Logo del sitio</li>
            <li>Información de contacto (teléfono, WhatsApp, dirección)</li>
            <li>Redes sociales</li>
            <li>Horarios de atención</li>
        </ul>
    </div>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
